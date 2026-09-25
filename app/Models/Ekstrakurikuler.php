<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Ekstrakurikuler extends Model
{
    protected $connection = 'mysql';
    protected $table = 'ekstrakurikuler';

    protected $fillable = ['nama', 'pembina', 'jadwal', 'tempat', 'deskripsi'];

    /** Baris keanggotaan (pivot lokal di database kesiswaan). */
    public function anggotaPivot(): HasMany
    {
        return $this->hasMany(EkstrakurikulerSiswa::class);
    }

    /**
     * Daftar siswa anggota (diambil dari Data Center) lengkap dengan jabatan.
     * Dikerjakan dua query untuk menghindari join lintas-database.
     */
    public function anggota(): Collection
    {
        $rows = $this->anggotaPivot()->get(['siswa_id', 'jabatan']);

        if ($rows->isEmpty()) {
            return collect();
        }

        $jabatan = $rows->pluck('jabatan', 'siswa_id');

        return Siswa::with('siswaRombel.kelas')
            ->whereIn('id', $rows->pluck('siswa_id')->all())
            ->get()
            ->each(fn ($s) => $s->jabatan = $jabatan[$s->id] ?? 'Anggota')
            ->sortBy('nama_siswa')
            ->values();
    }
}
