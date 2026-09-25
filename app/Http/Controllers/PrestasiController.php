<?php

namespace App\Http\Controllers;

use App\Models\Prestasi;
use App\Models\Siswa;
use App\Support\Exporter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PrestasiController extends Controller
{
    public function index(Request $request)
    {
        $prestasi = $this->filteredQuery($request)->paginate(15)->withQueryString();

        return view('prestasi.index', compact('prestasi'));
    }

    private function filteredQuery(Request $request)
    {
        $query = Prestasi::with('siswa.siswaRombel.kelas');

        if ($search = $request->input('q')) {
            $siswaIds = Siswa::where('nama_siswa', 'like', "%{$search}%")->pluck('id')->all();
            $query->where(function ($q) use ($search, $siswaIds) {
                $q->where('nama_prestasi', 'like', "%{$search}%")
                    ->orWhereIn('siswa_id', $siswaIds);
            });
        }

        if ($tingkat = $request->input('tingkat')) {
            $query->where('tingkat', $tingkat);
        }

        if ($bidang = $request->input('bidang')) {
            $query->where('bidang', $bidang);
        }

        return $query->latest('tanggal');
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['excel', 'word', 'pdf'], true), 404);

        $headers = ['No', 'Nama Prestasi', 'Siswa', 'Kelas', 'Bidang', 'Tingkat', 'Peringkat', 'Penyelenggara', 'Tanggal', 'Keterangan'];

        $rows = $this->filteredQuery($request)->get()->values()->map(fn ($p, $i) => [
            $i + 1,
            $p->nama_prestasi,
            $p->siswa?->nama_siswa,
            optional($p->siswa?->kelas)->nama_rombel,
            $p->bidang,
            $p->tingkat,
            $p->peringkat,
            $p->penyelenggara,
            optional($p->tanggal)->format('d/m/Y'),
            $p->keterangan,
        ])->all();

        $filename = 'prestasi-' . now()->format('Ymd-His');

        return Exporter::{$format}($filename, 'Data Prestasi Siswa', $headers, $rows);
    }

    public function create()
    {
        return view('prestasi.create', [
            'prestasi' => new Prestasi(),
            'daftarSiswa' => $this->daftarSiswa(),
        ]);
    }

    public function store(Request $request)
    {
        Prestasi::create($this->validateData($request));

        return redirect()->route('prestasi.index')->with('success', 'Prestasi berhasil ditambahkan.');
    }

    public function edit(Prestasi $prestasi)
    {
        return view('prestasi.edit', [
            'prestasi' => $prestasi,
            'daftarSiswa' => $this->daftarSiswa(),
        ]);
    }

    public function update(Request $request, Prestasi $prestasi)
    {
        $prestasi->update($this->validateData($request));

        return redirect()->route('prestasi.index')->with('success', 'Prestasi berhasil diperbarui.');
    }

    public function destroy(Prestasi $prestasi)
    {
        $prestasi->delete();

        return redirect()->route('prestasi.index')->with('success', 'Prestasi berhasil dihapus.');
    }

    private function daftarSiswa()
    {
        $ta = current_ta_id();

        return Siswa::whereHas('siswaRombel', fn ($q) => $q->where('tahun_ajaran_id', $ta))
            ->with(['siswaRombel' => fn ($q) => $q->where('tahun_ajaran_id', $ta)->with('kelas')])
            ->orderBy('nama_siswa')->get();
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'siswa_id' => ['required', Rule::exists('datacenter.siswa', 'id')],
            'nama_prestasi' => ['required', 'string', 'max:200'],
            'bidang' => ['required', 'in:Akademik,Non-Akademik'],
            'tingkat' => ['required', 'in:Sekolah,Kecamatan,Kabupaten/Kota,Provinsi,Nasional,Internasional'],
            'peringkat' => ['nullable', 'string', 'max:100'],
            'penyelenggara' => ['nullable', 'string', 'max:200'],
            'tanggal' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string'],
        ]);
    }
}
