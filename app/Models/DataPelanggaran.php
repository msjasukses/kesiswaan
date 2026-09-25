<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataPelanggaran extends Model
{
    protected $connection = 'mysql';
    protected $table = 'data_pelanggaran';

    protected $fillable = ['jenis_pelanggaran_id', 'nama', 'poin', 'deskripsi'];

    protected $casts = [
        'poin' => 'integer',
    ];

    public function jenisPelanggaran(): BelongsTo
    {
        return $this->belongsTo(JenisPelanggaran::class, 'jenis_pelanggaran_id');
    }

    public function pelanggaran(): HasMany
    {
        return $this->hasMany(Pelanggaran::class);
    }
}
