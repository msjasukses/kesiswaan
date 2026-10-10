<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku_induk_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buku_induk_semester_id')->constrained('buku_induk_semester')->cascadeOnDelete();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->string('kelompok')->nullable();
            $table->string('nama_mapel');

            // Kurikulum 2013
            $table->unsignedTinyInteger('kkm')->nullable();
            $table->unsignedTinyInteger('nilai_pengetahuan')->nullable();
            $table->string('predikat_pengetahuan', 2)->nullable();
            $table->text('deskripsi_pengetahuan')->nullable();
            $table->unsignedTinyInteger('nilai_keterampilan')->nullable();
            $table->string('predikat_keterampilan', 2)->nullable();
            $table->text('deskripsi_keterampilan')->nullable();

            // Kurikulum Merdeka
            $table->unsignedTinyInteger('nilai_akhir')->nullable();
            $table->text('capaian_kompetensi')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_induk_nilai');
    }
};
