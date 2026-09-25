<?php

namespace Database\Seeders;

use App\Models\DataPelanggaran;
use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerSiswa;
use App\Models\Guru;
use App\Http\Controllers\AktivitasGuruWaliController;
use App\Models\AktivitasGuruWali;
use App\Models\GuruWali;
use App\Models\GuruWaliSiswa;
use App\Models\JenisPelanggaran;
use App\Models\JurnalEkstrakurikuler;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Prestasi;
use App\Models\ProgramKerja;
use App\Models\Setting;
use App\Models\SiswaRombel;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedUsers();

        // ID siswa (dari Data Center) pada tahun ajaran aktif — dijadikan referensi data operasional.
        $taAktif = TahunAjaran::where('is_aktif', 1)->value('id') ?? TahunAjaran::max('id');
        $siswaIds = SiswaRombel::where('tahun_ajaran_id', $taAktif)->pluck('siswa_id');
        $guruNama = Guru::pluck('nama_ptk');

        $butir = $this->seedPelanggaranMaster();
        $this->seedPelanggaran($siswaIds, $butir);
        $this->seedEkstrakurikuler($siswaIds, $guruNama);
        $this->seedJurnalEkstrakurikuler($guruNama);
        $this->seedGuruWali();
        $this->seedAktivitasGuruWali();
        $this->seedProgramKerja();
        $this->seedPrestasi($siswaIds);
    }

    private function seedSettings(): void
    {
        // Ambil identitas sekolah dari Data Center bila tersedia.
        $sekolah = DB::connection('datacenter')->table('sekolah')->first();
        $taAktif = DB::connection('datacenter')->table('tahun_ajaran')->where('is_aktif', 1)->first();

        $data = $sekolah ? [
            'nama_sekolah' => $sekolah->nama_sekolah,
            'npsn' => $sekolah->npsn,
            'alamat' => trim(collect([$sekolah->alamat, $sekolah->kelurahan, $sekolah->kecamatan, $sekolah->kabupaten, $sekolah->provinsi])->filter()->implode(', ')),
            'telepon' => $sekolah->telepon,
            'email' => $sekolah->email,
            'website' => $sekolah->website,
            'kepala_sekolah' => $sekolah->kepala_sekolah,
            'nip_kepala_sekolah' => $sekolah->nip_kepala_sekolah,
        ] : [
            'nama_sekolah' => 'Aplikasi Kesiswaan',
        ];

        $data['tahun_ajaran'] = $taAktif->nama_tahun_ajaran ?? '';
        $data['semester'] = 'Ganjil';

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function seedUsers(): void
    {
        User::updateOrCreate(['email' => 'admin@kesiswaan.test'], [
            'name' => 'Administrator',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'jabatan' => 'Wakasek Kesiswaan',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::updateOrCreate(['email' => 'guru@kesiswaan.test'], [
            'name' => 'Siti Rahmawati, S.Pd.',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'jabatan' => 'Guru BK',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    private function seedPelanggaranMaster(): \Illuminate\Support\Collection
    {
        $data = [
            'Kedisiplinan' => [
                ['nama' => 'Terlambat masuk sekolah', 'poin' => 5],
                ['nama' => 'Bolos jam pelajaran', 'poin' => 15],
                ['nama' => 'Tidak mengikuti upacara', 'poin' => 10],
            ],
            'Kerapihan' => [
                ['nama' => 'Tidak memakai seragam sesuai ketentuan', 'poin' => 5],
                ['nama' => 'Rambut tidak rapi / diwarnai', 'poin' => 10],
                ['nama' => 'Tidak memakai atribut lengkap', 'poin' => 5],
            ],
            'Kelakuan' => [
                ['nama' => 'Berkelahi di lingkungan sekolah', 'poin' => 50],
                ['nama' => 'Merokok di lingkungan sekolah', 'poin' => 40],
                ['nama' => 'Membawa HP tanpa izin', 'poin' => 15],
            ],
            'Kehadiran' => [
                ['nama' => 'Alpa (tanpa keterangan)', 'poin' => 10],
                ['nama' => 'Meninggalkan kelas tanpa izin', 'poin' => 10],
            ],
        ];

        $butir = collect();
        foreach ($data as $kategori => $items) {
            $jenis = JenisPelanggaran::create(['nama' => $kategori]);
            foreach ($items as $item) {
                $butir->push(DataPelanggaran::create([
                    'jenis_pelanggaran_id' => $jenis->id,
                    'nama' => $item['nama'],
                    'poin' => $item['poin'],
                ]));
            }
        }

        return $butir;
    }

    private function seedPelanggaran(\Illuminate\Support\Collection $siswaIds, \Illuminate\Support\Collection $butir): void
    {
        if ($siswaIds->isEmpty()) {
            return;
        }

        $pencatat = User::where('role', 'guru')->first();

        foreach ($siswaIds->shuffle()->take(15) as $siswaId) {
            foreach (range(1, fake()->numberBetween(1, 3)) as $i) {
                $b = $butir->random();
                Pelanggaran::create([
                    'siswa_id' => $siswaId,
                    'data_pelanggaran_id' => $b->id,
                    'tanggal' => fake()->dateTimeBetween('-5 months', 'now')->format('Y-m-d'),
                    'poin' => $b->poin,
                    'keterangan' => fake()->optional()->sentence(),
                    'tindak_lanjut' => fake()->randomElement(['Teguran lisan', 'Peringatan tertulis', 'Panggilan orang tua', null]),
                    'user_id' => $pencatat?->id,
                ]);
            }
        }
    }

    private function seedEkstrakurikuler(\Illuminate\Support\Collection $siswaIds, \Illuminate\Support\Collection $guruNama): void
    {
        $ekskuls = [
            ['nama' => 'Pramuka', 'jadwal' => 'Jumat, 14.00 - 16.00', 'tempat' => 'Lapangan'],
            ['nama' => 'PMR', 'jadwal' => 'Sabtu, 08.00 - 10.00', 'tempat' => 'Ruang UKS'],
            ['nama' => 'Futsal', 'jadwal' => 'Rabu, 15.30 - 17.00', 'tempat' => 'Lapangan Futsal'],
            ['nama' => 'Paskibra', 'jadwal' => 'Selasa, 15.00 - 17.00', 'tempat' => 'Lapangan Upacara'],
            ['nama' => 'Rohis', 'jadwal' => 'Kamis, 15.00 - 16.30', 'tempat' => 'Musholla'],
        ];

        foreach ($ekskuls as $e) {
            $e['pembina'] = $guruNama->isNotEmpty() ? $guruNama->random() : null;
            $ekskul = Ekstrakurikuler::create($e);

            if ($siswaIds->isEmpty()) {
                continue;
            }

            $anggota = $siswaIds->shuffle()->take(fake()->numberBetween(4, 8));
            $first = true;
            foreach ($anggota as $siswaId) {
                EkstrakurikulerSiswa::updateOrCreate(
                    ['ekstrakurikuler_id' => $ekskul->id, 'siswa_id' => $siswaId],
                    ['jabatan' => $first ? 'Ketua' : 'Anggota'],
                );
                $first = false;
            }
        }
    }

    private function seedJurnalEkstrakurikuler(\Illuminate\Support\Collection $guruNama): void
    {
        // Materi latihan per ekskul untuk mengisi jurnal kegiatan.
        $materi = [
            'Pramuka' => ['Latihan Baris Berbaris', 'Sandi dan Morse', 'Tali Temali'],
            'PMR' => ['Pertolongan Pertama pada Luka', 'Perawatan Keluarga', 'Simulasi Evakuasi'],
            'Futsal' => ['Latihan Dribbling', 'Passing dan Kontrol Bola', 'Game Internal'],
            'Paskibra' => ['Latihan Baris Berbaris', 'Formasi Pengibaran Bendera', 'Latihan Fisik Dasar'],
            'Rohis' => ['Tahsin Al-Quran', 'Kajian Akhlak', 'Latihan Kultum'],
        ];

        $pencatat = User::where('role', 'admin')->first();

        foreach (Ekstrakurikuler::all() as $ekskul) {
            $judulEkskul = $materi[$ekskul->nama] ?? ['Kegiatan Rutin'];
            $pelatih = fake()->name();
            $anggota = EkstrakurikulerSiswa::where('ekstrakurikuler_id', $ekskul->id)->count() ?: 20;

            // Satu pertemuan tiap dua pekan selama kurang lebih empat bulan terakhir.
            foreach (range(0, 8) as $i) {
                $hadir = fake()->numberBetween((int) ceil($anggota * 0.6), $anggota);

                JurnalEkstrakurikuler::create([
                    'ekstrakurikuler_id' => $ekskul->id,
                    'tanggal' => now()->subWeeks($i * 2)->format('Y-m-d'),
                    'pembina' => $ekskul->pembina ?? ($guruNama->isNotEmpty() ? $guruNama->random() : 'Pembina'),
                    'pelatih' => $pelatih,
                    'materi' => $judulEkskul[$i % count($judulEkskul)],
                    'siswa_hadir' => $hadir,
                    'siswa_tidak_hadir' => $anggota - $hadir,
                    'user_id' => $pencatat?->id,
                ]);
            }
        }
    }

    /** Perwalian dibentuk per rombel pada tahun ajaran aktif, wali kelas jadi guru walinya. */
    private function seedGuruWali(): void
    {
        $taAktif = TahunAjaran::where('is_aktif', 1)->value('id') ?? TahunAjaran::max('id');
        $rombels = Kelas::forTahunAjaran($taAktif)->orderBy('nama_rombel')->get();
        $guruIds = Guru::pluck('id');

        if ($guruIds->isEmpty()) {
            return;
        }

        foreach ($rombels as $rombel) {
            $wali = GuruWali::create([
                'nama' => 'Perwalian ' . $rombel->nama_rombel,
                'guru_id' => $rombel->wali_kelas_id ?: $guruIds->random(),
                'jadwal' => 'Senin, 07.00 - 07.30',
                'tempat' => 'Ruang ' . $rombel->nama_rombel,
                'deskripsi' => 'Bimbingan dan pendampingan siswa kelas ' . $rombel->nama_rombel . '.',
            ]);

            $anggota = SiswaRombel::where('rombongan_belajar_id', $rombel->id)
                ->where('tahun_ajaran_id', $taAktif)
                ->pluck('siswa_id');

            foreach ($anggota as $siswaId) {
                GuruWaliSiswa::updateOrCreate(
                    ['guru_wali_id' => $wali->id, 'siswa_id' => $siswaId],
                    ['status' => fake()->randomElement(['Aktif', 'Aktif', 'Aktif', 'Perlu Pendampingan', 'Pengawasan Khusus'])],
                );
            }
        }
    }

    /** Catatan pembinaan tersebar pada empat bulan terakhir, sebagai isi awal laporan. */
    private function seedAktivitasGuruWali(): void
    {
        $contoh = [
            ['aktivitas' => 'Bimbingan belajar kelompok persiapan ulangan harian.', 'hasil' => 'Nilai latihan meningkat.'],
            ['aktivitas' => 'Pemanggilan siswa terkait keterlambatan berulang.', 'hasil' => 'Keterlambatan berkurang.'],
            ['aktivitas' => 'Konseling individu terkait konflik dengan teman sekelas.', 'hasil' => 'Siswa bersedia berdamai.'],
            ['aktivitas' => 'Koordinasi dengan orang tua mengenai kehadiran siswa.', 'hasil' => 'Orang tua bersedia mendampingi.'],
            ['aktivitas' => 'Pendampingan pemilihan ekstrakurikuler sesuai minat.', 'hasil' => 'Siswa memilih ekskul yang sesuai.'],
        ];

        $pencatat = User::where('role', 'guru')->first();

        foreach (GuruWali::all() as $wali) {
            $siswa = GuruWaliSiswa::where('guru_wali_id', $wali->id)->pluck('siswa_id')->shuffle()->take(6);

            foreach ($siswa as $siswaId) {
                foreach (range(1, fake()->numberBetween(1, 2)) as $i) {
                    $c = fake()->randomElement($contoh);

                    AktivitasGuruWali::create([
                        'guru_wali_id' => $wali->id,
                        'siswa_id' => $siswaId,
                        'tanggal' => fake()->dateTimeBetween('-4 months', 'now')->format('Y-m-d'),
                        'kategori' => fake()->randomElement(AktivitasGuruWaliController::KATEGORI),
                        'status' => fake()->randomElement(AktivitasGuruWaliController::STATUS),
                        'aktivitas' => $c['aktivitas'],
                        'catatan' => fake()->optional()->sentence(),
                        'tindak_lanjut' => fake()->randomElement(['Pemantauan mingguan', 'Koordinasi dengan guru BK', 'Pendampingan orang tua', null]),
                        'hasil' => $c['hasil'],
                        'user_id' => $pencatat?->id,
                    ]);
                }
            }
        }
    }

    private function seedProgramKerja(): void
    {
        $programs = [
            ['nama' => 'Masa Pengenalan Lingkungan Sekolah (MPLS)', 'bidang' => 'OSIS', 'penanggung_jawab' => 'Pengurus OSIS', 'status' => 'selesai', 'anggaran' => 5000000, 'tanggal_mulai' => '2026-07-13', 'tanggal_selesai' => '2026-07-15'],
            ['nama' => 'Peringatan HUT Kemerdekaan RI', 'bidang' => 'OSIS', 'penanggung_jawab' => 'Sie Acara', 'status' => 'berjalan', 'anggaran' => 8000000, 'tanggal_mulai' => '2026-08-11', 'tanggal_selesai' => '2026-08-17'],
            ['nama' => 'Class Meeting Semester Ganjil', 'bidang' => 'Olahraga', 'penanggung_jawab' => 'Sie Olahraga', 'status' => 'rencana', 'anggaran' => 6000000, 'tanggal_mulai' => '2026-12-08', 'tanggal_selesai' => '2026-12-12'],
            ['nama' => 'Pesantren Kilat Ramadhan', 'bidang' => 'Keagamaan', 'penanggung_jawab' => 'Rohis', 'status' => 'rencana', 'anggaran' => 4000000],
            ['nama' => 'Bakti Sosial', 'bidang' => 'Sosial', 'penanggung_jawab' => 'PMR', 'status' => 'rencana', 'anggaran' => 3000000],
        ];

        foreach ($programs as $p) {
            ProgramKerja::create($p);
        }
    }

    private function seedPrestasi(\Illuminate\Support\Collection $siswaIds): void
    {
        if ($siswaIds->isEmpty()) {
            return;
        }

        $prestasi = [
            ['nama_prestasi' => 'Juara 1 Olimpiade Matematika', 'bidang' => 'Akademik', 'tingkat' => 'Kabupaten/Kota', 'peringkat' => 'Juara 1', 'penyelenggara' => 'Dinas Pendidikan'],
            ['nama_prestasi' => 'Juara 2 Lomba Cerdas Cermat', 'bidang' => 'Akademik', 'tingkat' => 'Provinsi', 'peringkat' => 'Juara 2', 'penyelenggara' => 'Universitas Negeri'],
            ['nama_prestasi' => 'Juara 1 Futsal Antar SMP', 'bidang' => 'Non-Akademik', 'tingkat' => 'Kabupaten/Kota', 'peringkat' => 'Juara 1', 'penyelenggara' => 'Karang Taruna'],
            ['nama_prestasi' => 'Medali Perunggu Karate', 'bidang' => 'Non-Akademik', 'tingkat' => 'Nasional', 'peringkat' => 'Juara 3', 'penyelenggara' => 'FORKI'],
            ['nama_prestasi' => 'Juara Harapan 1 Lomba Pidato', 'bidang' => 'Non-Akademik', 'tingkat' => 'Kecamatan', 'peringkat' => 'Harapan 1', 'penyelenggara' => 'Kecamatan Cibinong'],
        ];

        foreach ($prestasi as $p) {
            $p['siswa_id'] = $siswaIds->random();
            $p['tanggal'] = fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d');
            Prestasi::create($p);
        }
    }
}
