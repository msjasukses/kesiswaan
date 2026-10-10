<?php

namespace App\Http\Controllers;

use App\Models\BukuInduk;
use App\Models\BukuIndukSemester;
use App\Models\Siswa;
use App\Models\SiswaRombel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Laporan hasil belajar per semester pada buku induk (format K13 / Merdeka).
 */
class BukuIndukSemesterController extends Controller
{
    public function create(Request $request, Siswa $siswa)
    {
        $bukuInduk = BukuInduk::forSiswa($siswa->id);
        $kurikulum = $bukuInduk->kurikulum;

        $rombel = SiswaRombel::with(['kelas.waliKelas', 'tahunAjaran'])
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', current_ta_id())
            ->first();

        $semester = new BukuIndukSemester([
            'kurikulum' => $kurikulum,
            'tahun_pelajaran' => $rombel?->tahunAjaran?->nama_tahun_ajaran ?? current_tahun_ajaran()?->nama_tahun_ajaran,
            'semester' => setting('semester') === 'Genap' ? 2 : 1,
            'kelas' => $rombel?->kelas?->nama_rombel,
            'tingkat' => $rombel?->kelas?->tingkat,
            'wali_kelas' => $rombel?->kelas?->waliKelas?->nama_ptk,
            'sakit' => 0,
            'izin' => 0,
            'alpa' => 0,
            'ekstrakurikuler' => $siswa->ekstrakurikuler
                ->map(fn ($e) => ['nama' => $e->nama, 'predikat' => '', 'keterangan' => $e->pivot->jabatan])
                ->values()->all(),
            'projek' => [],
        ]);

        // Pakai daftar mapel semester terakhir (kurikulum sama) bila ada, agar KKM & urutan konsisten.
        $sebelumnya = $bukuInduk->exists
            ? $bukuInduk->semester()->where('kurikulum', $kurikulum)->with('nilai')->get()->last()
            : null;

        $nilai = $sebelumnya
            ? $sebelumnya->nilai->map->only(['kelompok', 'nama_mapel', 'kkm'])->all()
            : $this->templates()[$kurikulum];

        return view('buku_induk.semester.create', [
            'siswa' => $siswa,
            'semester' => $semester,
            'nilai' => $nilai,
            'templates' => $this->templates(),
        ]);
    }

    public function store(Request $request, Siswa $siswa)
    {
        $bukuInduk = BukuInduk::forSiswa($siswa->id);
        [$data, $nilai] = $this->validateData($request, $bukuInduk);

        DB::connection('mysql')->transaction(function () use ($bukuInduk, $data, $nilai) {
            if (! $bukuInduk->exists) {
                $bukuInduk->kurikulum = $data['kurikulum'];
                $bukuInduk->save();
            }

            $semester = $bukuInduk->semester()->create($data);
            $semester->syncNilai($nilai);
        });

        return redirect()->route('buku-induk.show', $siswa)->with('success', 'Hasil belajar semester berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa, BukuIndukSemester $semester)
    {
        $this->ensureOwner($siswa, $semester);

        return view('buku_induk.semester.edit', [
            'siswa' => $siswa,
            'semester' => $semester,
            'nilai' => $semester->nilai->map->only([
                'kelompok', 'nama_mapel', 'kkm',
                'nilai_pengetahuan', 'deskripsi_pengetahuan', 'nilai_keterampilan', 'deskripsi_keterampilan',
                'nilai_akhir', 'capaian_kompetensi',
            ])->all(),
            'templates' => $this->templates(),
        ]);
    }

    public function update(Request $request, Siswa $siswa, BukuIndukSemester $semester)
    {
        $this->ensureOwner($siswa, $semester);
        [$data, $nilai] = $this->validateData($request, $semester->bukuInduk, $semester);

        DB::connection('mysql')->transaction(function () use ($semester, $data, $nilai) {
            $semester->update($data);
            $semester->syncNilai($nilai);
        });

        return redirect()->route('buku-induk.show', $siswa)->with('success', 'Hasil belajar semester berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa, BukuIndukSemester $semester)
    {
        $this->ensureOwner($siswa, $semester);
        $semester->delete();

        return redirect()->route('buku-induk.show', $siswa)->with('success', 'Hasil belajar semester berhasil dihapus.');
    }

    private function ensureOwner(Siswa $siswa, BukuIndukSemester $semester): void
    {
        abort_unless((int) $semester->bukuInduk?->siswa_id === (int) $siswa->id, 404);
    }

    /** Template mapel per kurikulum (K13 diberi KKM awal 70). */
    private function templates(): array
    {
        return collect(config('buku_induk.mapel'))
            ->map(fn ($rows, $kurikulum) => collect($rows)
                ->map(fn ($r) => $r + ['kkm' => $kurikulum === 'K13' ? 70 : null])
                ->all())
            ->all();
    }

    /** @return array{0: array, 1: array} data semester & baris nilai */
    private function validateData(Request $request, BukuInduk $bukuInduk, ?BukuIndukSemester $semester = null): array
    {
        $nilai100 = ['nullable', 'integer', 'min:0', 'max:100'];

        $v = $request->validate([
            'kurikulum' => ['required', Rule::in(array_keys(config('buku_induk.kurikulum')))],
            'tahun_pelajaran' => ['required', 'string', 'max:20'],
            'semester' => [
                'required', 'in:1,2',
                Rule::unique('buku_induk_semester', 'semester')
                    ->where('buku_induk_id', $bukuInduk->id)
                    ->where('tahun_pelajaran', $request->input('tahun_pelajaran'))
                    ->ignore($semester?->id),
            ],
            'kelas' => ['required', 'string', 'max:50'],
            'tingkat' => ['nullable', 'integer', 'min:1', 'max:13'],
            'wali_kelas' => ['nullable', 'string', 'max:191'],

            'sikap_spiritual_predikat' => ['nullable', Rule::in(config('buku_induk.predikat_sikap'))],
            'sikap_spiritual_deskripsi' => ['nullable', 'string', 'max:2000'],
            'sikap_sosial_predikat' => ['nullable', Rule::in(config('buku_induk.predikat_sikap'))],
            'sikap_sosial_deskripsi' => ['nullable', 'string', 'max:2000'],

            'sakit' => ['nullable', 'integer', 'min:0', 'max:365'],
            'izin' => ['nullable', 'integer', 'min:0', 'max:365'],
            'alpa' => ['nullable', 'integer', 'min:0', 'max:365'],
            'catatan_wali_kelas' => ['nullable', 'string', 'max:2000'],
            'keputusan' => ['nullable', 'string', 'max:100'],
            'tanggal_rapor' => ['nullable', 'date'],

            'nilai' => ['required', 'array', 'min:1'],
            'nilai.*.kelompok' => ['nullable', 'string', 'max:100'],
            'nilai.*.nama_mapel' => ['required', 'string', 'max:191'],
            'nilai.*.kkm' => $nilai100,
            'nilai.*.nilai_pengetahuan' => $nilai100,
            'nilai.*.deskripsi_pengetahuan' => ['nullable', 'string', 'max:2000'],
            'nilai.*.nilai_keterampilan' => $nilai100,
            'nilai.*.deskripsi_keterampilan' => ['nullable', 'string', 'max:2000'],
            'nilai.*.nilai_akhir' => $nilai100,
            'nilai.*.capaian_kompetensi' => ['nullable', 'string', 'max:2000'],

            'ekstrakurikuler' => ['nullable', 'array'],
            'ekstrakurikuler.*.nama' => ['nullable', 'string', 'max:191'],
            'ekstrakurikuler.*.predikat' => ['nullable', 'string', 'max:50'],
            'ekstrakurikuler.*.keterangan' => ['nullable', 'string', 'max:500'],

            'projek' => ['nullable', 'array'],
            'projek.*.tema' => ['nullable', 'string', 'max:191'],
            'projek.*.capaian' => ['nullable', Rule::in(array_keys(config('buku_induk.capaian_p5')))],
            'projek.*.catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'semester.unique' => 'Semester ini pada tahun pelajaran tersebut sudah tercatat.',
        ], [
            'nilai' => 'daftar mata pelajaran',
            'nilai.*.nama_mapel' => 'nama mata pelajaran',
            'nilai.*.kkm' => 'KKM',
            'nilai.*.nilai_pengetahuan' => 'nilai pengetahuan',
            'nilai.*.nilai_keterampilan' => 'nilai keterampilan',
            'nilai.*.nilai_akhir' => 'nilai akhir',
        ]);

        $isK13 = $v['kurikulum'] === 'K13';
        // validated() menyusun ulang array bersarang mengikuti urutan aturan, jadi urutkan kembali per indeks baris.
        $nilai = collect($v['nilai'])->sortKeys()->values()->all();
        unset($v['nilai']);

        $v['sakit'] = (int) ($v['sakit'] ?? 0);
        $v['izin'] = (int) ($v['izin'] ?? 0);
        $v['alpa'] = (int) ($v['alpa'] ?? 0);

        $v['ekstrakurikuler'] = collect($v['ekstrakurikuler'] ?? [])->sortKeys()->filter(fn ($e) => filled($e['nama'] ?? null))->values()->all();
        $v['projek'] = $isK13 ? [] : collect($v['projek'] ?? [])->sortKeys()->filter(fn ($p) => filled($p['tema'] ?? null))->values()->all();

        // Sikap spiritual & sosial hanya dinilai pada Kurikulum 2013.
        if (! $isK13) {
            foreach (['sikap_spiritual_predikat', 'sikap_spiritual_deskripsi', 'sikap_sosial_predikat', 'sikap_sosial_deskripsi'] as $f) {
                $v[$f] = null;
            }
        }

        return [$v, $nilai];
    }
}
