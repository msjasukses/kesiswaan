<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPelanggaran extends Model
{
    protected $connection = 'mysql';
    protected $table = 'jenis_pelanggaran';

    protected $fillable = ['nama', 'deskripsi'];

    public function dataPelanggaran(): HasMany
    {
        return $this->hasMany(DataPelanggaran::class, 'jenis_pelanggaran_id');
    }
}
