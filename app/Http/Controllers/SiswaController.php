<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Support\Exporter;
use Illuminate\Http\Request;

/**
 * Data Siswa — master read-only dari Data Center. Kelas ditentukan per tahun ajaran.
 */
class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $siswa = $this->filteredQuery($request)->paginate(15)->withQueryString();
        $this->attachTotalPoin($siswa->getCollection());

        $daftarKelas = Kelas::forTahunAjaran(current_ta_id())->orderBy('nama_rombel')->get();

        return view('siswa.index', compact('siswa', 'daftarKelas'));
    }

    public function show(Siswa $siswa)
    {
        $siswa->load([
            'siswaRombel.kelas.waliKelas',
            'siswaRombel.tahunAjaran',
            'ekstrakurikuler',
            'prestasi',
            'pelanggaran.dataPelanggaran.jenisPelanggaran',
        ]);

        $siswa->setAttribute('total_poin', (int) $siswa->pelanggaran->sum('poin'));

        return view('siswa.show', compact('siswa'));
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['excel', 'word', 'pdf'], true), 404);

        $items = $this->filteredQuery($request)->get();

        $headers = ['No. Urut', 'NIS', 'NISN', 'Nama Siswa', 'L/P', 'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Nama Orang Tua', 'Alamat Siswa'];

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
            $s->alamat,
        ])->all();

        $ta = optional(current_tahun_ajaran())->nama_tahun_ajaran;
        $filename = 'data-siswa-' . now()->format('Ymd-His');

        return Exporter::{$format}($filename, 'Data Siswa' . ($ta ? " T.A. {$ta}" : ''), $headers, $rows);
    }

    private function filteredQuery(Request $request)
    {
        $ta = current_ta_id();

        $query = Siswa::query()
            ->whereHas('siswaRombel', fn ($q) => $q->where('tahun_ajaran_id', $ta))
            ->with(['siswaRombel' => fn ($q) => $q->where('tahun_ajaran_id', $ta)->with('kelas')]);

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

        if ($status = $request->input('status')) {
            $query->where('status_siswa', $status);
        }

        return $query->orderBy('nama_siswa');
    }

    /** Lampirkan total poin pelanggaran (dari DB kesiswaan) ke tiap siswa. */
    private function attachTotalPoin($collection): void
    {
        if ($collection->isEmpty()) {
            return;
        }

        $poin = Pelanggaran::whereIn('siswa_id', $collection->pluck('id')->all())
            ->selectRaw('siswa_id, SUM(poin) as total')
            ->groupBy('siswa_id')
            ->pluck('total', 'siswa_id');

        $collection->each(fn ($s) => $s->setAttribute('total_poin', (int) ($poin[$s->id] ?? 0)));
    }
}
