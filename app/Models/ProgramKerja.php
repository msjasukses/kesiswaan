<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramKerja extends Model
{
    protected $connection = 'mysql';
    protected $table = 'program_kerja';

    protected $fillable = [
        'nama', 'deskripsi', 'penanggung_jawab', 'bidang',
        'tanggal_mulai', 'tanggal_selesai', 'anggaran', 'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'anggaran' => 'decimal:2',
    ];
}
