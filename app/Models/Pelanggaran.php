<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pelanggaran extends Model
{
    protected $connection = 'mysql';
    protected $table = 'pelanggaran';

    protected $fillable = [
        'siswa_id', 'data_pelanggaran_id', 'tanggal', 'poin',
        'keterangan', 'tindak_lanjut', 'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'poin' => 'integer',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function dataPelanggaran(): BelongsTo
    {
        return $this->belongsTo(DataPelanggaran::class);
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
