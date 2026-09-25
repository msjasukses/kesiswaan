<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class GuruWali extends Model
{
    protected $connection = 'mysql';
    protected $table = 'guru_wali';

    protected $fillable = ['nama', 'guru_id', 'jadwal', 'tempat', 'deskripsi'];

    /** Guru pembimbing (dari Data Center, read-only). */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    /** Baris siswa asuh (pivot lokal di database kesiswaan). */
    public function anggotaPivot(): HasMany
    {
        return $this->hasMany(GuruWaliSiswa::class);
    }

    /**
     * Daftar siswa asuh (diambil dari Data Center) lengkap dengan statusnya.
     * Dikerjakan dua query untuk menghindari join lintas-database.
     */
    public function anggota(): Collection
    {
        $rows = $this->anggotaPivot()->get(['siswa_id', 'status']);

        if ($rows->isEmpty()) {
            return collect();
        }

        $status = $rows->pluck('status', 'siswa_id');

        return Siswa::with('siswaRombel.kelas')
            ->whereIn('id', $rows->pluck('siswa_id')->all())
            ->get()
            ->each(fn ($s) => $s->status_bimbingan = $status[$s->id] ?? 'Aktif')
            ->sortBy('nama_siswa')
            ->values();
    }
}
