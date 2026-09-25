<?php

namespace App\Models;

use App\Models\Concerns\ReadOnlyModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Kelas = rombongan_belajar pada Data Center. Read-only.
 * Setiap rombel terikat pada satu tahun ajaran (kolom tahun_ajaran_id).
 */
class Kelas extends Model
{
    use ReadOnlyModel;

    protected $connection = 'datacenter';
    protected $table = 'rombongan_belajar';
    protected $guarded = [];

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    /** Siswa anggota rombel (rombel sudah spesifik per tahun ajaran). */
    public function siswa(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'siswa_rombel', 'rombongan_belajar_id', 'siswa_id')
            ->withTimestamps();
    }

    public function scopeForTahunAjaran($query, $taId)
    {
        return $query->where('tahun_ajaran_id', $taId);
    }

    /** Nama kelas (rombel). */
    public function getNamaAttribute(): string
    {
        return $this->nama_rombel;
    }

    /** Nama wali kelas (string) untuk tampilan ringkas. */
    public function getWaliKelasNamaAttribute(): ?string
    {
        return $this->waliKelas?->nama_ptk;
    }

    public function getJurusanNamaAttribute(): ?string
    {
        return $this->jurusan?->nama_jurusan;
    }
}
