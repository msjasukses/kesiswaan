<?php

namespace App\Http\Controllers;

use App\Models\BukuInduk;
use App\Models\Kelas;
use App\Models\Prestasi;
use App\Models\Siswa;
use App\Support\Exporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Buku Induk Siswa — identitas lengkap + riwayat hasil belajar per semester,
 * dengan format Kurikulum 2013 atau Kurikulum Merdeka.
 */
class BukuIndukController extends Controller
{
    public function index(Request $request)
    {
        $siswa = $this->filteredQuery($request)->paginate(25)->withQueryString();
        $this->attachBukuInduk($siswa->getCollection());

        $daftarKelas = Kelas::forTahunAjaran(current_ta_id())->orderBy('nama_rombel')->get();
        $defaultKurikulum = BukuInduk::defaultKurikulum();

        return view('buku_induk.index', compact('siswa', 'daftarKelas', 'defaultKurikulum'));
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['excel', 'word', 'pdf'], true), 404);

        $items = $this->filteredQuery($request)->get();
        $this->attachBukuInduk($items);

        $headers = ['No', 'NIS', 'NISN', 'Nama Siswa', 'L/P', 'Tempat, Tgl Lahir', 'Kelas', 'Kurikulum', 'Tanggal Masuk', 'Asal Sekolah', 'Jml Semester'];

        $rows = $items->values()->map(fn ($s, $i) => [
            $i + 1,
            $s->nis,
            $s->nisn,
            $s->nama_siswa,
            $s->jenis_kelamin,
            collect([$s->tempat_lahir, optional($s->tanggal_lahir)->format('d/m/Y')])->filter()->implode(', '),
            optional($s->kelas)->nama_rombel,
            $s->bukuInduk->kurikulum_label,
            optional($s->bukuInduk->tanggal_masuk)->format('d/m/Y'),
            $s->bukuInduk->asal_sekolah,
            (int) $s->bukuInduk->semester_count,
        ])->all();

        $ta = optional(current_tahun_ajaran())->nama_tahun_ajaran;
        $filename = 'buku-induk-' . now()->format('Ymd-His');

        return Exporter::{$format}($filename, 'Buku Induk Siswa' . ($ta ? " T.A. {$ta}" : ''), $headers, $rows);
    }

    public function show(Siswa $siswa)
    {
        return view('buku_induk.show', $this->bukuData($siswa));
    }

    public function pdf(Siswa $siswa)
    {
        return Pdf::loadView('buku_induk.pdf', $this->bukuData($siswa))
            ->setPaper('a4', 'portrait')
            ->stream('buku-induk-' . ($siswa->nisn ?: $siswa->id) . '.pdf');
    }

    public function edit(Siswa $siswa)
    {
        $bukuInduk = BukuInduk::forSiswa($siswa->id);

        return view('buku_induk.edit', compact('siswa', 'bukuInduk'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $bukuInduk = BukuInduk::forSiswa($siswa->id);
        $bukuInduk->fill($this->validateData($request));

        if ($bukuInduk->kurikulum !== 'K13') {
            $bukuInduk->nomor_skhun = null;
        }

        $bukuInduk->save();

        return redirect()->route('buku-induk.show', $siswa)->with('success', 'Data buku induk berhasil disimpan.');
    }

    /** Data lengkap untuk halaman detail & cetak PDF. */
    private function bukuData(Siswa $siswa): array
    {
        $siswa->load(['siswaRombel.kelas.waliKelas', 'siswaRombel.tahunAjaran']);

        $bukuInduk = BukuInduk::forSiswa($siswa->id);
        $semester = $bukuInduk->exists ? $bukuInduk->semester()->with('nilai')->get() : collect();
        $prestasi = Prestasi::where('siswa_id', $siswa->id)->orderBy('tanggal')->get();
        $riwayatKelas = $siswa->siswaRombel->sortBy('tahun_ajaran_id')->values();
        $identitas = $this->identitas($siswa, $bukuInduk);

        return compact('siswa', 'bukuInduk', 'semester', 'prestasi', 'riwayatKelas', 'identitas');
    }

    /** Lembar identitas buku induk: [judul bagian => [label => nilai]]. */
    private function identitas(Siswa $s, BukuInduk $b): array
    {
        $tgl = fn ($d) => $d ? Carbon::parse($d)->translatedFormat('d F Y') : null;

        $saudara = collect([
            'kandung' => $b->jumlah_saudara_kandung,
            'tiri' => $b->jumlah_saudara_tiri,
            'angkat' => $b->jumlah_saudara_angkat,
        ])->reject(fn ($n) => $n === null)->map(fn ($n, $jenis) => "{$n} {$jenis}")->implode(', ');

        $meninggalkan = [
            'Tanggal Lulus' => $tgl($b->tanggal_lulus),
            'Nomor Ijazah' => $b->nomor_ijazah,
        ];
        if ($b->isK13()) {
            $meninggalkan['Nomor SKHUN'] = $b->nomor_skhun;
        }
        $meninggalkan += [
            'Melanjutkan ke' => $b->melanjutkan_ke,
            'Tanggal Keluar / Pindah' => $tgl($b->tanggal_keluar),
            'Alasan Keluar / Pindah' => $b->alasan_keluar,
            'Status Siswa' => $s->status_siswa . ($s->tanggal_status ? ' (sejak ' . $tgl($s->tanggal_status) . ')' : ''),
        ];

        return [
            'A. Keterangan Tentang Diri Siswa' => [
                'Nomor Induk Siswa (NIS)' => $s->nis,
                'NISN' => $s->nisn,
                'Nama Lengkap' => $s->nama_siswa,
                'Nama Panggilan' => $b->nama_panggilan,
                'Jenis Kelamin' => $s->jenis_kelamin ? $s->jenis_kelamin_label : null,
                'Tempat, Tanggal Lahir' => collect([$s->tempat_lahir, $tgl($s->tanggal_lahir)])->filter()->implode(', '),
                'Agama' => $s->agama,
                'Kewarganegaraan' => $b->kewarganegaraan,
                'Anak ke-' => $b->anak_ke,
                'Jumlah Saudara' => $saudara,
                'Status Anak' => $b->status_anak,
                'Bahasa Sehari-hari' => $b->bahasa_sehari_hari,
            ],
            'B. Keterangan Tempat Tinggal' => [
                'Alamat' => $s->alamat,
                'Nomor HP' => $s->nomor_hp,
                'Tinggal Bersama' => $b->tinggal_dengan,
                'Jarak ke Sekolah' => $b->jarak_ke_sekolah,
            ],
            'C. Keterangan Kesehatan' => [
                'Golongan Darah' => $b->golongan_darah,
                'Tinggi / Berat Badan' => collect([
                    $b->tinggi_badan ? "{$b->tinggi_badan} cm" : null,
                    $b->berat_badan ? "{$b->berat_badan} kg" : null,
                ])->filter()->implode(' / '),
                'Penyakit yang Pernah Diderita' => $b->riwayat_penyakit,
                'Kelainan Jasmani' => $b->kelainan_jasmani,
            ],
            'D. Keterangan Pendidikan' => [
                'Status Masuk' => $b->status_masuk,
                'Diterima di Kelas' => $b->diterima_di_kelas,
                'Tanggal Diterima' => $tgl($b->tanggal_masuk),
                'Asal Sekolah' => $b->asal_sekolah,
                'Alamat Asal Sekolah' => $b->alamat_asal_sekolah,
                'Nomor Ijazah Sekolah Asal' => $b->nomor_ijazah_asal,
                'Tanggal Ijazah Sekolah Asal' => $tgl($b->tanggal_ijazah_asal),
                'Pindahan dari Sekolah' => $b->pindahan_dari,
                'Alasan Pindah' => $b->alasan_pindah,
            ],
            'E. Keterangan Orang Tua' => [
                'Nama Ayah' => $s->nama_ayah,
                'Pendidikan Ayah' => $b->pendidikan_ayah,
                'Pekerjaan Ayah' => $b->pekerjaan_ayah,
                'Penghasilan Ayah' => $b->penghasilan_ayah,
                'Nama Ibu' => $s->nama_ibu,
                'Pendidikan Ibu' => $b->pendidikan_ibu,
                'Pekerjaan Ibu' => $b->pekerjaan_ibu,
                'Penghasilan Ibu' => $b->penghasilan_ibu,
                'Alamat Orang Tua' => $b->alamat_orang_tua,
                'Nomor HP Orang Tua' => $s->nomor_hp_ortu,
            ],
            'F. Keterangan Wali' => [
                'Nama Wali' => $b->nama_wali,
                'Hubungan dengan Siswa' => $b->hubungan_wali,
                'Pendidikan' => $b->pendidikan_wali,
                'Pekerjaan' => $b->pekerjaan_wali,
                'Alamat' => $b->alamat_wali,
                'Telepon' => $b->telepon_wali,
            ],
            'G. Kegemaran' => [
                'Kesenian' => $b->hobi_kesenian,
                'Olahraga' => $b->hobi_olahraga,
                'Organisasi / Kemasyarakatan' => $b->hobi_organisasi,
                'Lain-lain' => $b->hobi_lainnya,
            ],
            'H. Keterangan Meninggalkan Sekolah' => $meninggalkan,
        ];
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

        // Siswa tanpa catatan buku induk dianggap memakai kurikulum default.
        $kurikulum = $request->input('kurikulum');
        if ($kurikulum && array_key_exists($kurikulum, config('buku_induk.kurikulum'))) {
            if ($kurikulum === BukuInduk::defaultKurikulum()) {
                $query->whereNotIn('id', BukuInduk::where('kurikulum', '!=', $kurikulum)->pluck('siswa_id')->all());
            } else {
                $query->whereIn('id', BukuInduk::where('kurikulum', $kurikulum)->pluck('siswa_id')->all());
            }
        }

        return $query->orderBy('nama_siswa');
    }

    /** Lampirkan buku induk (dari DB kesiswaan) ke tiap siswa; dua query, tanpa join lintas-database. */
    private function attachBukuInduk($collection): void
    {
        if ($collection->isEmpty()) {
            return;
        }

        $map = BukuInduk::withCount('semester')
            ->whereIn('siswa_id', $collection->pluck('id')->all())
            ->get()
            ->keyBy('siswa_id');

        $collection->each(function ($s) use ($map) {
            $s->setRelation('bukuInduk', $map[$s->id] ?? new BukuInduk([
                'siswa_id' => $s->id,
                'kurikulum' => BukuInduk::defaultKurikulum(),
            ]));
        });
    }

    private function validateData(Request $request): array
    {
        $text = ['nullable', 'string', 'max:191'];
        $angka = ['nullable', 'integer', 'min:0', 'max:50'];

        return $request->validate([
            'kurikulum' => ['required', Rule::in(array_keys(config('buku_induk.kurikulum')))],

            'nama_panggilan' => $text,
            'kewarganegaraan' => $text,
            'anak_ke' => $angka,
            'jumlah_saudara_kandung' => $angka,
            'jumlah_saudara_tiri' => $angka,
            'jumlah_saudara_angkat' => $angka,
            'status_anak' => ['nullable', 'in:Lengkap,Yatim,Piatu,Yatim Piatu'],
            'bahasa_sehari_hari' => $text,

            'tinggal_dengan' => $text,
            'jarak_ke_sekolah' => $text,

            'golongan_darah' => ['nullable', 'in:A,B,AB,O'],
            'tinggi_badan' => ['nullable', 'integer', 'min:50', 'max:250'],
            'berat_badan' => ['nullable', 'integer', 'min:10', 'max:250'],
            'riwayat_penyakit' => $text,
            'kelainan_jasmani' => $text,

            'status_masuk' => ['nullable', 'in:Siswa Baru,Pindahan'],
            'tanggal_masuk' => ['nullable', 'date'],
            'diterima_di_kelas' => $text,
            'asal_sekolah' => $text,
            'alamat_asal_sekolah' => $text,
            'nomor_ijazah_asal' => $text,
            'tanggal_ijazah_asal' => ['nullable', 'date'],
            'pindahan_dari' => $text,
            'alasan_pindah' => $text,

            'pendidikan_ayah' => $text,
            'pekerjaan_ayah' => $text,
            'penghasilan_ayah' => $text,
            'pendidikan_ibu' => $text,
            'pekerjaan_ibu' => $text,
            'penghasilan_ibu' => $text,
            'alamat_orang_tua' => ['nullable', 'string', 'max:500'],

            'nama_wali' => $text,
            'hubungan_wali' => $text,
            'pendidikan_wali' => $text,
            'pekerjaan_wali' => $text,
            'telepon_wali' => ['nullable', 'string', 'max:30'],
            'alamat_wali' => ['nullable', 'string', 'max:500'],

            'hobi_kesenian' => $text,
            'hobi_olahraga' => $text,
            'hobi_organisasi' => $text,
            'hobi_lainnya' => $text,

            'tanggal_lulus' => ['nullable', 'date'],
            'nomor_ijazah' => $text,
            'nomor_skhun' => $text,
            'melanjutkan_ke' => $text,
            'tanggal_keluar' => ['nullable', 'date'],
            'alasan_keluar' => $text,

            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
