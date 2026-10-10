<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Data pelengkap buku induk. Identitas pokok (nama, NIS, NISN, TTL, agama,
        // alamat, nama orang tua) tetap dibaca dari datacenter_v2.siswa.
        Schema::create('buku_induk', function (Blueprint $table) {
            $table->id();
            // Referensi ke datacenter_v2.siswa (koneksi lain) — tanpa foreign key fisik.
            $table->unsignedBigInteger('siswa_id')->unique();
            $table->string('kurikulum', 10)->default('Merdeka'); // K13 | Merdeka

            // Keterangan diri siswa
            $table->string('nama_panggilan')->nullable();
            $table->string('kewarganegaraan')->nullable();
            $table->unsignedTinyInteger('anak_ke')->nullable();
            $table->unsignedTinyInteger('jumlah_saudara_kandung')->nullable();
            $table->unsignedTinyInteger('jumlah_saudara_tiri')->nullable();
            $table->unsignedTinyInteger('jumlah_saudara_angkat')->nullable();
            $table->string('status_anak')->nullable(); // Lengkap, Yatim, Piatu, Yatim Piatu
            $table->string('bahasa_sehari_hari')->nullable();

            // Tempat tinggal
            $table->string('tinggal_dengan')->nullable();
            $table->string('jarak_ke_sekolah')->nullable();

            // Kesehatan
            $table->string('golongan_darah', 5)->nullable();
            $table->unsignedSmallInteger('tinggi_badan')->nullable(); // cm
            $table->unsignedSmallInteger('berat_badan')->nullable();  // kg
            $table->string('riwayat_penyakit')->nullable();
            $table->string('kelainan_jasmani')->nullable();

            // Pendidikan sebelumnya & penerimaan
            $table->string('status_masuk')->nullable(); // Siswa Baru | Pindahan
            $table->date('tanggal_masuk')->nullable();
            $table->string('diterima_di_kelas')->nullable();
            $table->string('asal_sekolah')->nullable();
            $table->string('alamat_asal_sekolah')->nullable();
            $table->string('nomor_ijazah_asal')->nullable();
            $table->date('tanggal_ijazah_asal')->nullable();
            $table->string('pindahan_dari')->nullable();
            $table->string('alasan_pindah')->nullable();

            // Orang tua
            $table->string('pendidikan_ayah')->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('penghasilan_ayah')->nullable();
            $table->string('pendidikan_ibu')->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            $table->string('penghasilan_ibu')->nullable();
            $table->text('alamat_orang_tua')->nullable();

            // Wali
            $table->string('nama_wali')->nullable();
            $table->string('hubungan_wali')->nullable();
            $table->string('pendidikan_wali')->nullable();
            $table->string('pekerjaan_wali')->nullable();
            $table->string('telepon_wali')->nullable();
            $table->text('alamat_wali')->nullable();

            // Kegemaran
            $table->string('hobi_kesenian')->nullable();
            $table->string('hobi_olahraga')->nullable();
            $table->string('hobi_organisasi')->nullable();
            $table->string('hobi_lainnya')->nullable();

            // Meninggalkan sekolah
            $table->date('tanggal_lulus')->nullable();
            $table->string('nomor_ijazah')->nullable();
            $table->string('nomor_skhun')->nullable(); // hanya Kurikulum 2013
            $table->string('melanjutkan_ke')->nullable();
            $table->date('tanggal_keluar')->nullable();
            $table->string('alasan_keluar')->nullable();

            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_induk');
    }
};
