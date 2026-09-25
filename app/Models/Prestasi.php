<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prestasi extends Model
{
    protected $connection = 'mysql';
    protected $table = 'prestasi';

    protected $fillable = [
        'siswa_id', 'nama_prestasi', 'bidang', 'tingkat',
        'peringkat', 'penyelenggara', 'tanggal', 'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }
}
