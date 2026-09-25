<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\GuruWali;
use App\Models\GuruWaliSiswa;
use App\Models\Siswa;
use App\Support\Exporter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuruWaliController extends Controller
{
    /** Pilihan status bimbingan siswa asuh. */
    public const STATUS = ['Aktif', 'Perlu Pendampingan', 'Pengawasan Khusus'];

    public function index(Request $request)
    {
        $guruWali = $this->filteredQuery($request)->paginate(15)->withQueryString();

        return view('guru_wali.index', compact('guruWali'));
    }

    private function filteredQuery(Request $request)
    {
        $query = GuruWali::with('guru')->withCount('anggotaPivot as siswa_count');

        if ($search = $request->input('q')) {
            // Nama guru berada di koneksi lain, jadi id-nya diresolusi lebih dulu.
            $guruIds = Guru::where('nama_ptk', 'like', "%{$search}%")->pluck('id')->all();

            $query->where(fn ($q) => $q->where('nama', 'like', "%{$search}%")
                ->orWhereIn('guru_id', $guruIds));
        }

        return $query->orderBy('nama');
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['excel', 'word', 'pdf'], true), 404);

        $headers = ['No', 'Nama Perwalian', 'Guru Wali', 'Jadwal', 'Tempat', 'Jumlah Siswa Asuh', 'Deskripsi'];

        $rows = $this->filteredQuery($request)->get()->values()->map(fn ($w, $i) => [
            $i + 1,
            $w->nama,
            $w->guru?->nama_ptk,
            $w->jadwal,
            $w->tempat,
            $w->siswa_count,
            $w->deskripsi,
        ])->all();

        $filename = 'guru-wali-' . now()->format('Ymd-His');

        return Exporter::{$format}($filename, 'Data Guru Wali', $headers, $rows);
    }

    public function create()
    {
        return view('guru_wali.create', [
            'wali' => new GuruWali(),
            'daftarGuru' => $this->daftarGuru(),
        ]);
    }

    public function store(Request $request)
    {
        GuruWali::create($this->validateData($request));

        return redirect()->route('guru-wali.index')->with('success', 'Guru wali berhasil ditambahkan.');
    }

    public function show(GuruWali $guruWali)
    {
        $ta = current_ta_id();
        $anggota = $guruWali->anggota();
        $memberIds = $anggota->pluck('id')->all();

        $calonAnggota = Siswa::whereHas('siswaRombel', fn ($q) => $q->where('tahun_ajaran_id', $ta))
            ->whereNotIn('id', $memberIds)
            ->with(['siswaRombel' => fn ($q) => $q->where('tahun_ajaran_id', $ta)->with('kelas')])
            ->orderBy('nama_siswa')->get();

        return view('guru_wali.show', compact('guruWali', 'anggota', 'calonAnggota'));
    }

    public function edit(GuruWali $guruWali)
    {
        return view('guru_wali.edit', [
            'wali' => $guruWali,
            'daftarGuru' => $this->daftarGuru(),
        ]);
    }

    public function update(Request $request, GuruWali $guruWali)
    {
        $guruWali->update($this->validateData($request));

        return redirect()->route('guru-wali.index')->with('success', 'Guru wali berhasil diperbarui.');
    }

    public function destroy(GuruWali $guruWali)
    {
        $guruWali->anggotaPivot()->delete();
        $guruWali->delete();

        return redirect()->route('guru-wali.index')->with('success', 'Guru wali berhasil dihapus.');
    }

    public function addAnggota(Request $request, GuruWali $guruWali)
    {
        $data = $request->validate([
            'siswa_id' => ['required', Rule::exists('datacenter.siswa', 'id')],
            'status' => ['required', Rule::in(self::STATUS)],
        ]);

        GuruWaliSiswa::updateOrCreate(
            ['guru_wali_id' => $guruWali->id, 'siswa_id' => $data['siswa_id']],
            ['status' => $data['status']],
        );

        return back()->with('success', 'Siswa asuh berhasil ditambahkan.');
    }

    public function removeAnggota(GuruWali $guruWali, $siswa)
    {
        $guruWali->anggotaPivot()->where('siswa_id', $siswa)->delete();

        return back()->with('success', 'Siswa asuh berhasil dikeluarkan.');
    }

    private function daftarGuru()
    {
        return Guru::orderBy('nama_ptk')->get();
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'guru_id' => ['required', Rule::exists('datacenter.guru', 'id')],
            'jadwal' => ['nullable', 'string', 'max:150'],
            'tempat' => ['nullable', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
        ], [], ['guru_id' => 'guru wali']);
    }
}
