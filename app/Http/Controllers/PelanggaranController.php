<?php

namespace App\Http\Controllers;

use App\Models\DataPelanggaran;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\SiswaRombel;
use App\Support\Exporter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $pelanggaran = $this->filteredQuery($request)->paginate(15)->withQueryString();
        $daftarKelas = Kelas::forTahunAjaran(current_ta_id())->orderBy('nama_rombel')->get();

        return view('pelanggaran.index', compact('pelanggaran', 'daftarKelas'));
    }

    private function filteredQuery(Request $request)
    {
        $ta = current_ta_id();

        $query = Pelanggaran::with(['siswa.siswaRombel.kelas', 'dataPelanggaran.jenisPelanggaran', 'pencatat']);

        if ($search = $request->input('q')) {
            $ids = Siswa::where('nama_siswa', 'like', "%{$search}%")
                ->orWhere('nis', 'like', "%{$search}%")->pluck('id')->all();
            $query->whereIn('siswa_id', $ids);
        }

        if ($kelasId = $request->input('kelas_id')) {
            $ids = SiswaRombel::where('tahun_ajaran_id', $ta)
                ->where('rombongan_belajar_id', $kelasId)->pluck('siswa_id')->all();
            $query->whereIn('siswa_id', $ids);
        }

        if ($bulan = $request->input('bulan')) {
            $query->whereMonth('tanggal', $bulan);
        }

        return $query->latest('tanggal')->latest('id');
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['excel', 'word', 'pdf'], true), 404);

        $headers = ['No', 'Tanggal', 'NIS', 'Nama Siswa', 'Kelas', 'Kategori', 'Pelanggaran', 'Poin', 'Tindak Lanjut', 'Pencatat', 'Keterangan'];

        $rows = $this->filteredQuery($request)->get()->values()->map(fn ($p, $i) => [
            $i + 1,
            optional($p->tanggal)->format('d/m/Y'),
            $p->siswa?->nis,
            $p->siswa?->nama_siswa,
            optional($p->siswa?->kelas)->nama_rombel,
            $p->dataPelanggaran?->jenisPelanggaran?->nama,
            $p->dataPelanggaran?->nama,
            $p->poin,
            $p->tindak_lanjut,
            $p->pencatat?->name,
            $p->keterangan,
        ])->all();

        $filename = 'pencatatan-pelanggaran-' . now()->format('Ymd-His');

        return Exporter::{$format}($filename, 'Data Pencatatan Pelanggaran', $headers, $rows);
    }

    public function create()
    {
        return view('pelanggaran.create', [
            'pelanggaran' => new Pelanggaran(['tanggal' => now()->toDateString()]),
            'daftarSiswa' => $this->daftarSiswa(),
            'daftarButir' => DataPelanggaran::with('jenisPelanggaran')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['poin'] = DataPelanggaran::findOrFail($data['data_pelanggaran_id'])->poin;
        $data['user_id'] = $request->user()->id;

        Pelanggaran::create($data);

        return redirect()->route('pelanggaran.index')->with('success', 'Pelanggaran berhasil dicatat.');
    }

    public function edit(Pelanggaran $pelanggaran)
    {
        return view('pelanggaran.edit', [
            'pelanggaran' => $pelanggaran,
            'daftarSiswa' => $this->daftarSiswa(),
            'daftarButir' => DataPelanggaran::with('jenisPelanggaran')->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Pelanggaran $pelanggaran)
    {
        $data = $this->validateData($request);
        $data['poin'] = DataPelanggaran::findOrFail($data['data_pelanggaran_id'])->poin;

        $pelanggaran->update($data);

        return redirect()->route('pelanggaran.index')->with('success', 'Pelanggaran berhasil diperbarui.');
    }

    public function destroy(Pelanggaran $pelanggaran)
    {
        $pelanggaran->delete();

        return redirect()->route('pelanggaran.index')->with('success', 'Data pelanggaran berhasil dihapus.');
    }

    /** Daftar siswa pada tahun ajaran aktif (untuk dropdown). */
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
            'data_pelanggaran_id' => ['required', 'exists:data_pelanggaran,id'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
            'tindak_lanjut' => ['nullable', 'string', 'max:200'],
        ]);
    }
}
