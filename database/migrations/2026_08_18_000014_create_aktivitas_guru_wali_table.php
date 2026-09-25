<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktivitas_guru_wali', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_wali_id')->constrained('guru_wali')->cascadeOnDelete();
            // Referensi ke datacenter_v2.siswa (koneksi lain) — tanpa foreign key fisik.
            $table->unsignedBigInteger('siswa_id')->index();
            $table->date('tanggal');
            $table->string('kategori'); // Akademik, Kedisiplinan, dst.
            $table->string('status')->default('Selesai'); // Selesai, Proses, Perlu Tindak Lanjut
            $table->text('aktivitas');
            $table->text('catatan')->nullable();       // catatan guru wali
            $table->text('tindak_lanjut')->nullable();
            $table->text('hasil')->nullable();         // hasil / perkembangan siswa
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // pencatat
            $table->timestamps();

            $table->index(['guru_wali_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas_guru_wali');
    }
};
