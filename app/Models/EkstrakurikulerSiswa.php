<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pivot keanggotaan ekstrakurikuler (database kesiswaan). Kolom siswa_id merujuk
 * ke datacenter_v2.siswa lintas-koneksi tanpa foreign key fisik.
 */
class EkstrakurikulerSiswa extends Model
{
    protected $connection = 'mysql';
    protected $table = 'ekstrakurikuler_siswa';

    protected $fillable = ['ekstrakurikuler_id', 'siswa_id', 'jabatan'];
}
