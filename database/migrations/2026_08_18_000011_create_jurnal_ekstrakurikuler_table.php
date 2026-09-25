<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikuler')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('pembina');
            $table->string('pelatih');
            $table->string('materi'); // contoh: Latihan Baris Berbaris
            $table->unsignedInteger('siswa_hadir')->default(0);
            $table->unsignedInteger('siswa_tidak_hadir')->default(0);
            $table->string('foto')->nullable(); // dokumentasi kegiatan (disk public)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // pencatat
            $table->timestamps();

            $table->index(['ekstrakurikuler_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_ekstrakurikuler');
    }
};
