<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_wali', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // contoh: Perwalian 7A
            // Referensi ke datacenter_v2.guru (koneksi lain) — tanpa foreign key fisik.
            $table->unsignedBigInteger('guru_id')->index();
            $table->string('jadwal')->nullable(); // contoh: Senin, 07.00 - 07.30
            $table->string('tempat')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_wali');
    }
};
