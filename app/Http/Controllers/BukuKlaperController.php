<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Support\Exporter;
use Illuminate\Http\Request;

/**
 * Buku Klaper — buku induk/register siswa yang disusun alfabetis (read-only dari Data Center).
 */
class BukuKlaperController extends Controller
{
    public function index(Request $request)
    {
        $siswa = $this->filteredQuery($request)->paginate(25)->withQueryString();
        $daftarKelas = Kelas::forTahunAjaran(current_ta_id())->orderBy('nama_rombel')->get();
        $abjad = range('A', 'Z');

        return view('buku_klaper.index', compact('siswa', 'daftarKelas', 'abjad'));
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['excel', 'word', 'pdf'], true), 404);

        $items = $this->filteredQuery($request)->get();

        $headers = ['No', 'NIS', 'NISN', 'Nama Siswa', 'L/P', 'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Nama Orang Tua', 'Kelas', 'Alamat'];

        $rows = $items->values()->map(fn ($s, $i) => [
            $i + 1,
            $s->nis,
            $s->nisn,
            $s->nama_siswa,
            $s->jenis_kelamin,
            $s->tempat_lahir,
            optional($s->tanggal_lahir)->format('d/m/Y'),
            $s->agama,
            collect([$s->nama_ayah, $s->nama_ibu])->filter()->implode(' / '),
            optional($s->kelas)->nama_rombel,
            $s->alamat,
        ])->all();

        $ta = optional(current_tahun_ajaran())->nama_tahun_ajaran;
        $filename = 'buku-klaper-' . now()->format('Ymd-His');

        return Exporter::{$format}($filename, 'Buku Klaper Siswa' . ($ta ? " T.A. {$ta}" : ''), $headers, $rows);
    }

    private function filteredQuery(Request $request)
    {
        $ta = current_ta_id();

        $query = Siswa::query()
            ->whereHas('siswaRombel', fn ($q) => $q->where('tahun_ajaran_id', $ta))
            ->with(['siswaRombel' => fn ($q) => $q->where('tahun_ajaran_id', $ta)->with('kelas')]);

        if ($huruf = $request->input('huruf')) {
            $query->where('nama_siswa', 'like', "{$huruf}%");
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($kelasId = $request->input('kelas_id')) {
            $query->whereHas('siswaRombel', fn ($q) => $q->where('tahun_ajaran_id', $ta)->where('rombongan_belajar_id', $kelasId));
        }

        return $query->orderBy('nama_siswa');
    }
}
