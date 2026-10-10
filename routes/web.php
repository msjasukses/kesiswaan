<?php

use App\Http\Controllers\AktivitasGuruWaliController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\BukuIndukController;
use App\Http\Controllers\BukuIndukSemesterController;
use App\Http\Controllers\BukuKlaperController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataPelanggaranController;
use App\Http\Controllers\EkstrakurikulerController;
use App\Http\Controllers\JenisPelanggaranController;
use App\Http\Controllers\JurnalEkstrakurikulerController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LaporanAktivitasGuruWaliController;
use App\Http\Controllers\LaporanJurnalEkstrakurikulerController;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\PrestasiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\GuruWaliController;
use App\Http\Controllers\ImportRaporController;
use App\Http\Controllers\ProgramKerjaController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TahunAjaranController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Ganti tahun ajaran aktif (session)
    Route::post('/tahun-ajaran', [TahunAjaranController::class, 'set'])->name('tahun-ajaran.set');

    // Buku Klaper (register siswa alfabetis)
    Route::get('buku-klaper', [BukuKlaperController::class, 'index'])->name('buku-klaper.index');
    Route::get('buku-klaper/export/{format}', [BukuKlaperController::class, 'export'])->name('buku-klaper.export');

    // Buku Induk Siswa (format Kurikulum 2013 / Kurikulum Merdeka)
    Route::get('buku-induk', [BukuIndukController::class, 'index'])->name('buku-induk.index');
    Route::get('buku-induk/export/{format}', [BukuIndukController::class, 'export'])->name('buku-induk.export');
    // Import nilai rapor (didaftarkan sebelum buku-induk/{siswa} agar "import" tidak dianggap id siswa)
    Route::get('buku-induk/import', [ImportRaporController::class, 'index'])->name('buku-induk.import');
    Route::get('buku-induk/import/template', [ImportRaporController::class, 'template'])->name('buku-induk.import.template');
    Route::post('buku-induk/import', [ImportRaporController::class, 'store'])->name('buku-induk.import.store');
    Route::get('buku-induk/{siswa}', [BukuIndukController::class, 'show'])->name('buku-induk.show');
    Route::get('buku-induk/{siswa}/edit', [BukuIndukController::class, 'edit'])->name('buku-induk.edit');
    Route::put('buku-induk/{siswa}', [BukuIndukController::class, 'update'])->name('buku-induk.update');
    Route::get('buku-induk/{siswa}/pdf', [BukuIndukController::class, 'pdf'])->name('buku-induk.pdf');
    Route::get('buku-induk/{siswa}/semester/create', [BukuIndukSemesterController::class, 'create'])->name('buku-induk.semester.create');
    Route::post('buku-induk/{siswa}/semester', [BukuIndukSemesterController::class, 'store'])->name('buku-induk.semester.store');
    Route::get('buku-induk/{siswa}/semester/{semester}/edit', [BukuIndukSemesterController::class, 'edit'])->name('buku-induk.semester.edit');
    Route::put('buku-induk/{siswa}/semester/{semester}', [BukuIndukSemesterController::class, 'update'])->name('buku-induk.semester.update');
    Route::delete('buku-induk/{siswa}/semester/{semester}', [BukuIndukSemesterController::class, 'destroy'])->name('buku-induk.semester.destroy');

    // Export Excel / Word / PDF ({format} = excel|word|pdf)
    Route::get('siswa/export/{format}', [SiswaController::class, 'export'])->name('siswa.export');
    Route::get('pelanggaran/export/{format}', [PelanggaranController::class, 'export'])->name('pelanggaran.export');
    Route::get('program-kerja/export/{format}', [ProgramKerjaController::class, 'export'])->name('program-kerja.export');
    Route::get('ekstrakurikuler/export/{format}', [EkstrakurikulerController::class, 'export'])->name('ekstrakurikuler.export');
    Route::get('jurnal-ekstrakurikuler/export/{format}', [JurnalEkstrakurikulerController::class, 'export'])->name('jurnal-ekstrakurikuler.export');
    Route::get('guru-wali/export/{format}', [GuruWaliController::class, 'export'])->name('guru-wali.export');
    Route::get('aktivitas-guru-wali/export/{format}', [AktivitasGuruWaliController::class, 'export'])->name('aktivitas-guru-wali.export');
    Route::get('prestasi/export/{format}', [PrestasiController::class, 'export'])->name('prestasi.export');

    // Master data dari Data Center (read-only)
    Route::resource('kelas', KelasController::class)->parameters(['kelas' => 'kelas'])->only(['index', 'show']);
    Route::resource('siswa', SiswaController::class)->parameters(['siswa' => 'siswa'])->only(['index', 'show']);
    Route::resource('guru', GuruController::class)->parameters(['guru' => 'guru'])->only(['index', 'show']);

    Route::resource('jenis-pelanggaran', JenisPelanggaranController::class)
        ->parameters(['jenis-pelanggaran' => 'jenisPelanggaran'])->except('show');
    Route::resource('data-pelanggaran', DataPelanggaranController::class)
        ->parameters(['data-pelanggaran' => 'dataPelanggaran'])->except('show');
    Route::resource('pelanggaran', PelanggaranController::class)
        ->parameters(['pelanggaran' => 'pelanggaran'])->except('show');

    Route::resource('program-kerja', ProgramKerjaController::class)
        ->parameters(['program-kerja' => 'programKerja'])->except('show');

    Route::resource('ekstrakurikuler', EkstrakurikulerController::class)
        ->parameters(['ekstrakurikuler' => 'ekstrakurikuler']);
    Route::post('ekstrakurikuler/{ekstrakurikuler}/anggota', [EkstrakurikulerController::class, 'addAnggota'])
        ->name('ekstrakurikuler.anggota.store');
    Route::delete('ekstrakurikuler/{ekstrakurikuler}/anggota/{siswa}', [EkstrakurikulerController::class, 'removeAnggota'])
        ->name('ekstrakurikuler.anggota.destroy');

    Route::resource('jurnal-ekstrakurikuler', JurnalEkstrakurikulerController::class)
        ->parameters(['jurnal-ekstrakurikuler' => 'jurnalEkstrakurikuler'])->except('show');

    Route::resource('guru-wali', GuruWaliController::class)
        ->parameters(['guru-wali' => 'guruWali']);
    Route::post('guru-wali/{guruWali}/siswa', [GuruWaliController::class, 'addAnggota'])
        ->name('guru-wali.siswa.store');
    Route::delete('guru-wali/{guruWali}/siswa/{siswa}', [GuruWaliController::class, 'removeAnggota'])
        ->name('guru-wali.siswa.destroy');

    Route::resource('aktivitas-guru-wali', AktivitasGuruWaliController::class)
        ->parameters(['aktivitas-guru-wali' => 'aktivitasGuruWali'])->except('show');

    // Laporan rekapitulasi aktivitas guru wali
    Route::get('laporan/aktivitas-guru-wali', [LaporanAktivitasGuruWaliController::class, 'index'])
        ->name('laporan-aktivitas-guru-wali.index');
    Route::get('laporan/aktivitas-guru-wali/export/{format}', [LaporanAktivitasGuruWaliController::class, 'export'])
        ->name('laporan-aktivitas-guru-wali.export');

    // Laporan rekapitulasi jurnal ekstrakurikuler
    Route::get('laporan/jurnal-ekstrakurikuler', [LaporanJurnalEkstrakurikulerController::class, 'index'])
        ->name('laporan-jurnal-ekstrakurikuler.index');
    Route::get('laporan/jurnal-ekstrakurikuler/export/{format}', [LaporanJurnalEkstrakurikulerController::class, 'export'])
        ->name('laporan-jurnal-ekstrakurikuler.export');

    Route::resource('prestasi', PrestasiController::class)
        ->parameters(['prestasi' => 'prestasi'])->except('show');

    // Profil pengguna yang sedang login (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Modul khusus admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Setelan aplikasi/sekolah
    Route::get('/setelan', [SettingController::class, 'edit'])->name('setelan.index');
    Route::put('/setelan', [SettingController::class, 'update'])->name('setelan.update');

    // Manajemen akun pengguna
    Route::resource('akun', AkunController::class)->parameters(['akun' => 'akun'])->except('show');
});

require __DIR__.'/auth.php';
