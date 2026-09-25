<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_pelanggaran_id')->constrained('jenis_pelanggaran')->cascadeOnDelete();
            $table->string('nama');                 // butir, contoh: Terlambat masuk sekolah
            $table->unsignedInteger('poin')->default(0);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_pelanggaran');
    }
};
