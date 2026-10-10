<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Buku induk siswa — data pelengkap identitas + riwayat hasil belajar per semester.
 * Identitas pokok siswa tetap bersumber dari Data Center (model Siswa).
 */
class BukuInduk extends Model
{
    protected $connection = 'mysql';
    protected $table = 'buku_induk';

    // siswa_id tetap dapat diisi lewat firstOrNew; input form selalu melewati validasi eksplisit.
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_ijazah_asal' => 'date',
        'tanggal_lulus' => 'date',
        'tanggal_keluar' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function semester(): HasMany
    {
        return $this->hasMany(BukuIndukSemester::class)
            ->orderBy('tahun_pelajaran')
            ->orderBy('semester');
    }

    /** Buku induk milik siswa, atau instance baru (belum tersimpan) dengan kurikulum default. */
    public static function forSiswa(int $siswaId): self
    {
        return static::firstOrNew(['siswa_id' => $siswaId], ['kurikulum' => static::defaultKurikulum()]);
    }

    public static function defaultKurikulum(): string
    {
        $kurikulum = setting('kurikulum_default');

        return array_key_exists((string) $kurikulum, config('buku_induk.kurikulum'))
            ? $kurikulum
            : config('buku_induk.default_kurikulum');
    }

    public static function kurikulumLabel(?string $kurikulum): string
    {
        return config("buku_induk.kurikulum.{$kurikulum}", (string) $kurikulum);
    }

    public function getKurikulumLabelAttribute(): string
    {
        return static::kurikulumLabel($this->kurikulum);
    }

    public function isK13(): bool
    {
        return $this->kurikulum === 'K13';
    }
}
