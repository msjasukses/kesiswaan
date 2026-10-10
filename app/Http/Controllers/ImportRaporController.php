<?php

namespace App\Http\Controllers;

use App\Models\BukuInduk;
use App\Models\BukuIndukSemester;
use App\Models\EkstrakurikulerSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaRombel;
use App\Support\RaporSpreadsheet;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Import nilai rapor per kelas & semester dari Excel ke buku induk, sesuai kurikulum yang dipilih.
 */
class ImportRaporController extends Controller
{
    public function index()
    {
        return view('buku_induk.import', [
            'daftarKelas' => Kelas::forTahunAjaran(current_ta_id())->orderBy('nama_rombel')->get(),
            'defaultKurikulum' => BukuInduk::defaultKurikulum(),
            'defaultSemester' => setting('semester') === 'Genap' ? 2 : 1,
        ]);
    }

    public function template(Request $request)
    {
        $opsi = $this->validateOpsi($request);
        $kelas = Kelas::with(['waliKelas', 'tahunAjaran'])->findOrFail($opsi['kelas_id']);
        $kurikulum = $opsi['kurikulum'];
        $tahunPelajaran = $kelas->tahunAjaran?->nama_tahun_ajaran;
        $siswa = $this->anggota($kelas);

        // Semester yang sudah tercatat (kurikulum sama) dipakai sebagai isian awal, agar bisa diunduh-ubah-unggah ulang.
        $tercatat = BukuIndukSemester::with(['nilai', 'bukuInduk'])
            ->where('tahun_pelajaran', $tahunPelajaran)
            ->where('semester', $opsi['semester'])
            ->where('kurikulum', $kurikulum)
            ->whereIn('buku_induk_id', BukuInduk::whereIn('siswa_id', $siswa->pluck('id')->all())->pluck('id')->all())
            ->get()
            ->keyBy(fn ($s) => $s->bukuInduk->siswa_id);

        $kolomNilai = array_keys(RaporSpreadsheet::kolomNilai($kurikulum));
        $kolomRekap = array_keys(RaporSpreadsheet::kolomRekap($kurikulum));
        $mapel = collect(config("buku_induk.mapel.{$kurikulum}"))
            ->map(fn ($m) => $m + ['kkm' => $kurikulum === 'K13' ? 70 : null])
            ->all();

        $nilaiRows = [];
        $rekapRows = [];
        foreach ($siswa as $s) {
            $identitas = ['nisn' => $s->nisn, 'nis' => $s->nis, 'nama' => $s->nama_siswa];
            $semester = $tercatat[$s->id] ?? null;

            $baris = $semester && $semester->nilai->isNotEmpty()
                ? $semester->nilai->map->only($kolomNilai)->all()
                : $mapel;

            foreach ($baris as $r) {
                $nilaiRows[] = array_merge($r, $identitas);
            }
            $rekapRows[] = array_merge($semester ? $semester->only($kolomRekap) : [], $identitas);
        }

        $filename = 'template-nilai-' . Str::slug(BukuInduk::kurikulumLabel($kurikulum)) . '-' . Str::slug($kelas->nama_rombel) . '-sem' . $opsi['semester'];

        return RaporSpreadsheet::template($filename, [
            'kurikulum' => $kurikulum,
            'kelas' => $kelas->nama_rombel,
            'tahun_pelajaran' => $tahunPelajaran,
            'semester' => $opsi['semester'],
        ], $nilaiRows, $rekapRows);
    }

    public function store(Request $request)
    {
        $opsi = $this->validateOpsi($request, true);
        $kelas = Kelas::with(['waliKelas', 'tahunAjaran'])->findOrFail($opsi['kelas_id']);
        $kurikulum = $opsi['kurikulum'];
        $isK13 = $kurikulum === 'K13';
        $tahunPelajaran = $kelas->tahunAjaran?->nama_tahun_ajaran;

        $file = RaporSpreadsheet::baca($request->file('file')->getRealPath(), [
            'kurikulum' => $kurikulum,
            'kelas' => $kelas->nama_rombel,
            'tahun_pelajaran' => $tahunPelajaran,
            'semester' => $opsi['semester'],
        ]);

        [$perSiswa, $errors] = $this->periksa($file, $kurikulum, $kelas);

        if ($errors) {
            throw ValidationException::withMessages(['file' => $errors]);
        }
        if (! $perSiswa) {
            throw ValidationException::withMessages(['file' => 'Tidak ada nilai yang terisi pada file.']);
        }

        $ekskul = $this->ekskulSiswa(array_keys($perSiswa));
        $ringkasan = ['baru' => 0, 'diperbarui' => 0, 'mapel' => 0];

        DB::connection('mysql')->transaction(function () use ($perSiswa, $opsi, $kelas, $kurikulum, $isK13, $tahunPelajaran, $ekskul, &$ringkasan) {
            foreach ($perSiswa as $siswaId => $data) {
                $bukuInduk = BukuInduk::forSiswa($siswaId);
                if (! $bukuInduk->exists || $opsi['perbarui_kurikulum']) {
                    $bukuInduk->kurikulum = $kurikulum;
                    $bukuInduk->save();
                }

                $semester = $bukuInduk->semester()->firstOrNew([
                    'tahun_pelajaran' => $tahunPelajaran,
                    'semester' => $opsi['semester'],
                ]);
                $baru = ! $semester->exists;

                $semester->fill([
                    'kurikulum' => $kurikulum,
                    'kelas' => $kelas->nama_rombel,
                    'tingkat' => $kelas->tingkat,
                    'wali_kelas' => $semester->wali_kelas ?: $kelas->waliKelas?->nama_ptk,
                ]);

                if ($baru) {
                    $semester->fill(['sakit' => 0, 'izin' => 0, 'alpa' => 0, 'ekstrakurikuler' => $ekskul[$siswaId] ?? [], 'projek' => []]);
                }

                if (isset($data['rekap'])) {
                    $semester->fill($data['rekap']);
                }

                if ($isK13) {
                    $semester->projek = [];
                } else {
                    $semester->fill(array_fill_keys(['sikap_spiritual_predikat', 'sikap_spiritual_deskripsi', 'sikap_sosial_predikat', 'sikap_sosial_deskripsi'], null));
                }

                $kurikulumBerubah = $semester->exists && $semester->isDirty('kurikulum');
                $semester->save();

                if (! empty($data['nilai'])) {
                    $semester->syncNilai($data['nilai']);
                    $ringkasan['mapel'] += count($data['nilai']);
                } elseif ($kurikulumBerubah) {
                    // Rapikan kolom nilai lama agar sesuai format kurikulum yang baru.
                    $semester->syncNilai($semester->nilai()->get()->toArray());
                }

                $ringkasan[$baru ? 'baru' : 'diperbarui']++;
            }
        });

        $jumlah = count($perSiswa);

        return redirect()->route('buku-induk.import')->with('success',
            "Import nilai " . BukuInduk::kurikulumLabel($kurikulum) . " kelas {$kelas->nama_rombel} semester {$opsi['semester']} berhasil: "
            . "{$jumlah} siswa ({$ringkasan['baru']} semester baru, {$ringkasan['diperbarui']} diperbarui), {$ringkasan['mapel']} nilai mata pelajaran."
        );
    }

    /**
     * Periksa seluruh baris file sebelum menyimpan apa pun.
     *
     * @return array{0: array<int, array>, 1: array<int, string>} data per siswa & daftar kesalahan
     */
    private function periksa(array $file, string $kurikulum, Kelas $kelas): array
    {
        $siswaKelas = $this->anggota($kelas)->keyBy(fn ($s) => (string) $s->nisn);
        $kolomNilai = RaporSpreadsheet::kolomNilai($kurikulum);
        $kolomRekap = RaporSpreadsheet::kolomRekap($kurikulum);
        $predikatSikap = config('buku_induk.predikat_sikap');

        $perSiswa = [];
        $errors = [];

        $cariSiswa = function (array $r, string $lokasi) use ($siswaKelas, $kelas, &$errors) {
            if (blank($r['nisn'] ?? null)) {
                $errors[] = "{$lokasi}: NISN wajib diisi.";

                return null;
            }
            $siswa = $siswaKelas[$r['nisn']] ?? null;
            if (! $siswa) {
                $errors[] = "{$lokasi}: NISN {$r['nisn']} tidak terdaftar sebagai anggota kelas {$kelas->nama_rombel}.";
            }

            return $siswa;
        };

        foreach ($file['nilai'] as $baris => $r) {
            $lokasi = "Sheet Nilai baris {$baris}";
            if (! $this->adaIsi($r, RaporSpreadsheet::kolomIsian($kolomNilai))) {
                continue;
            }
            if (! $siswa = $cariSiswa($r, $lokasi)) {
                continue;
            }
            if (blank($r['nama_mapel'] ?? null)) {
                $errors[] = "{$lokasi}: Mata Pelajaran wajib diisi.";

                continue;
            }

            foreach (['kkm', 'nilai_pengetahuan', 'nilai_keterampilan', 'nilai_akhir'] as $f) {
                if (array_key_exists($f, $kolomNilai)) {
                    [$r[$f], $pesan] = $this->angka($r[$f] ?? null, 100);
                    if ($pesan) {
                        $errors[] = "{$lokasi}: {$kolomNilai[$f]} {$pesan}.";
                    }
                }
            }
            $this->cekPanjang($r, ['nama_mapel' => 191, 'kelompok' => 100, 'deskripsi_pengetahuan' => 2000, 'deskripsi_keterampilan' => 2000, 'capaian_kompetensi' => 2000], $kolomNilai, $lokasi, $errors);

            $kunci = mb_strtolower($r['nama_mapel']);
            if (isset($perSiswa[$siswa->id]['mapel'][$kunci])) {
                $errors[] = "{$lokasi}: Mata pelajaran \"{$r['nama_mapel']}\" untuk {$siswa->nama_siswa} tercantum lebih dari sekali.";

                continue;
            }

            $perSiswa[$siswa->id]['mapel'][$kunci] = true;
            $perSiswa[$siswa->id]['nilai'][] = array_diff_key($r, array_flip(['nisn', 'nis', 'nama']));
        }

        foreach ($file['rekap'] as $baris => $r) {
            $lokasi = "Sheet Rekap baris {$baris}";
            if (! $this->adaIsi($r, RaporSpreadsheet::kolomIsian($kolomRekap))) {
                continue;
            }
            if (! $siswa = $cariSiswa($r, $lokasi)) {
                continue;
            }
            if (isset($perSiswa[$siswa->id]['rekap'])) {
                $errors[] = "{$lokasi}: {$siswa->nama_siswa} tercantum lebih dari sekali.";

                continue;
            }

            foreach (['sakit', 'izin', 'alpa'] as $f) {
                [$nilai, $pesan] = $this->angka($r[$f] ?? null, 365);
                $r[$f] = (int) $nilai;
                if ($pesan) {
                    $errors[] = "{$lokasi}: {$kolomRekap[$f]} {$pesan}.";
                }
            }

            foreach (['sikap_spiritual_predikat', 'sikap_sosial_predikat'] as $f) {
                if (! array_key_exists($f, $kolomRekap) || blank($r[$f] ?? null)) {
                    continue;
                }
                $cocok = collect($predikatSikap)->first(fn ($p) => mb_strtolower($p) === mb_strtolower($r[$f]));
                if ($cocok) {
                    $r[$f] = $cocok;
                } else {
                    $errors[] = "{$lokasi}: {$kolomRekap[$f]} harus salah satu dari " . implode(', ', $predikatSikap) . '.';
                }
            }
            $this->cekPanjang($r, ['catatan_wali_kelas' => 2000, 'keputusan' => 100, 'sikap_spiritual_deskripsi' => 2000, 'sikap_sosial_deskripsi' => 2000], $kolomRekap, $lokasi, $errors);

            $perSiswa[$siswa->id]['rekap'] = array_diff_key($r, array_flip(['nisn', 'nis', 'nama']));
        }

        foreach ($perSiswa as &$data) {
            unset($data['mapel']);
        }

        return [$perSiswa, $errors];
    }

    private function adaIsi(array $row, array $kolom): bool
    {
        foreach ($kolom as $k) {
            if (filled($row[$k] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /** @return array{0: ?int, 1: ?string} nilai bulat & pesan kesalahan */
    private function angka(?string $nilai, int $maks): array
    {
        if ($nilai === null) {
            return [null, null];
        }

        $nilai = str_replace(',', '.', $nilai);
        if (! is_numeric($nilai)) {
            return [null, "harus berupa angka (tertulis \"{$nilai}\")"];
        }

        $bulat = (int) round((float) $nilai);
        if ($bulat < 0 || $bulat > $maks) {
            return [null, "harus antara 0–{$maks} (tertulis {$nilai})"];
        }

        return [$bulat, null];
    }

    private function cekPanjang(array $r, array $batas, array $kolom, string $lokasi, array &$errors): void
    {
        foreach ($batas as $f => $maks) {
            if (isset($r[$f], $kolom[$f]) && mb_strlen($r[$f]) > $maks) {
                $errors[] = "{$lokasi}: {$kolom[$f]} melebihi {$maks} karakter.";
            }
        }
    }

    /** Siswa anggota rombel (dua query, tanpa join lintas-database). */
    private function anggota(Kelas $kelas): Collection
    {
        $ids = SiswaRombel::where('rombongan_belajar_id', $kelas->id)->pluck('siswa_id')->all();

        return Siswa::whereIn('id', $ids)->orderBy('nama_siswa')->get();
    }

    /** Keanggotaan ekstrakurikuler untuk isian awal semester baru. */
    private function ekskulSiswa(array $siswaIds): array
    {
        return EkstrakurikulerSiswa::query()
            ->join('ekstrakurikuler', 'ekstrakurikuler.id', '=', 'ekstrakurikuler_siswa.ekstrakurikuler_id')
            ->whereIn('ekstrakurikuler_siswa.siswa_id', $siswaIds)
            ->get(['ekstrakurikuler_siswa.siswa_id', 'ekstrakurikuler.nama', 'ekstrakurikuler_siswa.jabatan'])
            ->groupBy('siswa_id')
            ->map(fn ($rows) => $rows->map(fn ($r) => ['nama' => $r->nama, 'predikat' => '', 'keterangan' => $r->jabatan])->values()->all())
            ->all();
    }

    private function validateOpsi(Request $request, bool $import = false): array
    {
        $rules = [
            'kurikulum' => ['required', Rule::in(array_keys(config('buku_induk.kurikulum')))],
            'kelas_id' => ['required', Rule::exists('datacenter.rombongan_belajar', 'id')->where('tahun_ajaran_id', current_ta_id())],
            'semester' => ['required', 'in:1,2'],
        ];

        if ($import) {
            $rules['file'] = ['required', 'file', 'mimes:xlsx,xls', 'max:5120'];
            $rules['perbarui_kurikulum'] = ['nullable', 'boolean'];
        }

        $data = $request->validate($rules, [
            'kelas_id.exists' => 'Kelas tidak ditemukan pada tahun ajaran yang sedang dipilih.',
            'file.mimes' => 'File harus berformat Excel (.xlsx atau .xls).',
        ], [
            'kelas_id' => 'kelas',
            'file' => 'file Excel',
        ]);

        $data['semester'] = (int) $data['semester'];
        $data['perbarui_kurikulum'] = (bool) ($data['perbarui_kurikulum'] ?? false);

        return $data;
    }
}
