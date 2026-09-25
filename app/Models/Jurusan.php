<?php

namespace App\Models;

use App\Models\Concerns\ReadOnlyModel;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use ReadOnlyModel;

    protected $connection = 'datacenter';
    protected $table = 'jurusan';
    protected $guarded = [];

    public function getNamaAttribute(): string
    {
        return $this->nama_jurusan;
    }
}
