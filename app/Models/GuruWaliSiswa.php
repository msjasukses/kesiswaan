<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pivot siswa asuh guru wali (database kesiswaan). Kolom siswa_id merujuk
 * ke datacenter_v2.siswa lintas-koneksi tanpa foreign key fisik.
 */
class GuruWaliSiswa extends Model
{
    protected $connection = 'mysql';
    protected $table = 'guru_wali_siswa';

    protected $fillable = ['guru_wali_id', 'siswa_id', 'status'];
}
