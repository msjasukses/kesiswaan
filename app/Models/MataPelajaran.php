<?php

namespace App\Models;

use App\Models\Concerns\ReadOnlyModel;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use ReadOnlyModel;

    protected $connection = 'datacenter';
    protected $table = 'mata_pelajaran';
    protected $guarded = [];

    public function getNamaAttribute(): string
    {
        return $this->nama_mapel;
    }
}
