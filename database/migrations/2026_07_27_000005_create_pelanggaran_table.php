<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggaran', function (Blueprint $table) {
            $table->id();
            // Referensi ke datacenter_v2.siswa (koneksi lain) — tanpa foreign key fisik.
            $table->unsignedBigInteger('siswa_id')->index();
            $table->foreignId('data_pelanggaran_id')->constrained('data_pelanggaran')->cascadeOnDelete();
            $table->date('tanggal');
            $table->unsignedInteger('poin')->default(0); // snapshot poin saat dicatat
            $table->text('keterangan')->nullable();
            $table->string('tindak_lanjut')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // pencatat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran');
    }
};
