# Aplikasi Kesiswaan

Aplikasi manajemen kesiswaan berbasis **Laravel 13 + Breeze (Blade + Tailwind CSS)** dengan **konsep 2 database**: data operasional disimpan di database `kesiswaan`, sedangkan **master siswa, kelas, dan guru dibaca dari aplikasi Data Center** (`datacenter_v2`) secara read-only.

## Fitur / Modul

| Modul | Sumber Data | Keterangan |
|-------|-------------|-----------|
| **Dashboard** | gabungan | Statistik, tren pelanggaran, poin tertinggi, aktivitas terbaru |
| **Kelas** | Data Center (read-only) | Rombongan belajar per tahun ajaran + wali kelas |
| **Data Siswa** | Data Center (read-only) | Master siswa, kelas per tahun ajaran, rekap poin |
| **Data Guru** | Data Center (read-only) | Daftar guru/PTK + mata pelajaran |
| **Jenis Pelanggaran** | kesiswaan | Kategori pelanggaran |
| **Data Pelanggaran** | kesiswaan | Butir pelanggaran + bobot poin |
| **Pelanggaran** | kesiswaan | Pencatatan pelanggaran siswa (poin otomatis) |
| **Program Kerja** | kesiswaan | Program kerja kesiswaan/OSIS |
| **Ekstrakurikuler** | kesiswaan | Ekskul + kelola anggota (siswa dari Data Center) |
| **Prestasi** | kesiswaan | Prestasi akademik & non-akademik |
| **Setelan** | kesiswaan | Identitas sekolah (awal diambil dari Data Center) |
| **Akun** | kesiswaan | Manajemen pengguna & peran |

## Konsep 2 Database

| Koneksi | Database | Isi | Akses |
|---------|----------|-----|-------|
| `mysql` (default) | `kesiswaan` | pelanggaran, prestasi, program kerja, ekstrakurikuler, setelan, akun login | baca/tulis |
| `datacenter` | `datacenter_v2` | siswa, guru, rombongan_belajar (kelas), tahun ajaran, jurusan | **read-only** |

- Konfigurasi koneksi ada di [`config/database.php`](config/database.php) (`datacenter`) dan variabel `DB_DATACENTER_*` di `.env`.
- Data operasional (pelanggaran/prestasi/ekskul) menyimpan `siswa_id` yang merujuk ke `datacenter_v2.siswa` **tanpa foreign key fisik** (lintas-database).
- **Kelas siswa mengikuti tahun ajaran.** Pilih tahun ajaran lewat dropdown di header; pilihan disimpan di session. Default = tahun ajaran aktif.
- Master siswa/kelas/guru **tidak bisa diedit** dari aplikasi ini — dikelola di aplikasi Data Center.

## Export Excel, Word & PDF

Menu **Data Siswa, Pencatatan Pelanggaran, Program Kerja, Ekstrakurikuler, dan Prestasi** memiliki tombol **Export** (dropdown) untuk mengunduh data sebagai **Excel (.xlsx)**, **Word (.docx)**, atau **PDF**. Ekspor mengikuti filter/pencarian yang sedang aktif. Menggunakan PhpSpreadsheet, PhpWord, dan dompdf; logika ada di [`app/Support/Exporter.php`](app/Support/Exporter.php) (PDF memakai view [`resources/views/exports/table.blade.php`](resources/views/exports/table.blade.php)).

## Peran Pengguna

- **Admin** — akses penuh termasuk Setelan & Akun.
- **Guru / Pembina** — akses modul operasional; **tanpa** Setelan & Akun (menu disembunyikan + diproteksi 403).

## Akun Demo (setelah seeding)

| Peran | Email | Password |
|-------|-------|----------|
| Admin | `admin@kesiswaan.test` | `password` |
| Guru | `guru@kesiswaan.test` | `password` |

> Registrasi publik dinonaktifkan — akun dibuat admin melalui modul **Akun**.

## Menjalankan Aplikasi (Laragon / Windows)

Binary Laragon jika belum di PATH: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`, dsb.

```bash
# 1. Dependensi
composer install
npm install

# 2. .env — pastikan dua koneksi terisi:
#    DB_DATABASE=kesiswaan
#    DB_DATACENTER_DATABASE=datacenter_v2  (host/user/pass sama; root tanpa password)
php artisan key:generate     # jika perlu
php artisan storage:link

# 3. Buat database "kesiswaan" (database "datacenter_v2" sudah ada), lalu:
php artisan migrate:fresh --seed

# 4. Build aset
npm run build                # atau: npm run dev

# 5. Jalankan  (port 8000 dipakai proyek lain, gunakan 8001)
php artisan serve --port=8001
```

## Struktur Data (ringkas)

```
[datacenter_v2 — read-only]
  tahun_ajaran ─< rombongan_belajar (Kelas) ─< siswa_rombel >─ siswa
  guru ── wali_kelas / pembina / mata_pelajaran

[kesiswaan]
  jenis_pelanggaran ─< data_pelanggaran ─< pelanggaran >── (siswa_id → datacenter)
  ekstrakurikuler ─< ekstrakurikuler_siswa (siswa_id → datacenter)
  prestasi (siswa_id → datacenter),  program_kerja,  settings,  users (admin|guru)
```

## Teknologi

- Laravel 13, PHP 8.3, MySQL 8 (dua database pada server yang sama)
- Laravel Breeze (autentikasi) — Blade + Tailwind CSS 3
- Alpine.js (sidebar/dropdown/selektor tahun ajaran)
