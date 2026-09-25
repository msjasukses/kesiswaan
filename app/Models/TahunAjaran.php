<?php

namespace App\Models;

use App\Models\Concerns\ReadOnlyModel;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use ReadOnlyModel;

    protected $connection = 'datacenter';
    protected $table = 'tahun_ajaran';
    protected $guarded = [];

    protected $casts = [
        'is_aktif' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function getNamaAttribute(): string
    {
        return $this->nama_tahun_ajaran;
    }
}
