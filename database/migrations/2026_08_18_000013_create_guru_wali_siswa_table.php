<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_wali_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_wali_id')->constrained('guru_wali')->cascadeOnDelete();
            // Referensi ke datacenter_v2.siswa (koneksi lain) — tanpa foreign key fisik.
            $table->unsignedBigInteger('siswa_id')->index();
            $table->string('status')->default('Aktif'); // Aktif, Perlu Pendampingan, Pengawasan Khusus
            $table->timestamps();

            $table->unique(['guru_wali_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_wali_siswa');
    }
};
