<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekstrakurikuler_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikuler')->cascadeOnDelete();
            // Referensi ke datacenter_v2.siswa (koneksi lain) — tanpa foreign key fisik.
            $table->unsignedBigInteger('siswa_id')->index();
            $table->string('jabatan')->default('Anggota'); // Ketua, Wakil, Anggota
            $table->timestamps();

            $table->unique(['ekstrakurikuler_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekstrakurikuler_siswa');
    }
};
