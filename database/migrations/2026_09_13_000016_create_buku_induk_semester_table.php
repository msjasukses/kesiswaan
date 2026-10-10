<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Laporan perkembangan hasil belajar per semester.
        Schema::create('buku_induk_semester', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buku_induk_id')->constrained('buku_induk')->cascadeOnDelete();
            // Disimpan per semester agar masa transisi kurikulum tetap tercatat apa adanya.
            $table->string('kurikulum', 10); // K13 | Merdeka
            $table->string('tahun_pelajaran', 20);
            $table->unsignedTinyInteger('semester'); // 1 = Ganjil, 2 = Genap
            $table->string('kelas', 50);
            $table->unsignedTinyInteger('tingkat')->nullable();
            $table->string('wali_kelas')->nullable();

            // Sikap (Kurikulum 2013)
            $table->string('sikap_spiritual_predikat')->nullable();
            $table->text('sikap_spiritual_deskripsi')->nullable();
            $table->string('sikap_sosial_predikat')->nullable();
            $table->text('sikap_sosial_deskripsi')->nullable();

            // [{nama, predikat, keterangan}]
            $table->json('ekstrakurikuler')->nullable();
            // Projek profil pelajar Pancasila / kokurikuler (Kurikulum Merdeka): [{tema, capaian, catatan}]
            $table->json('projek')->nullable();

            $table->unsignedSmallInteger('sakit')->default(0);
            $table->unsignedSmallInteger('izin')->default(0);
            $table->unsignedSmallInteger('alpa')->default(0);

            $table->text('catatan_wali_kelas')->nullable();
            $table->string('keputusan')->nullable(); // Naik ke kelas 8 / Tinggal di kelas 7 / Lulus
            $table->date('tanggal_rapor')->nullable();
            $table->timestamps();

            $table->unique(['buku_induk_id', 'tahun_pelajaran', 'semester'], 'buku_induk_semester_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_induk_semester');
    }
};
