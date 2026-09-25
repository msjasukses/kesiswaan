<?php

namespace App\Models;

use App\Models\Concerns\ReadOnlyModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Siswa = tabel siswa pada Data Center (read-only).
 * Relasi operasional (pelanggaran, prestasi, ekskul) berada di database kesiswaan
 * dan dihubungkan lintas-koneksi lewat siswa_id (tanpa foreign key fisik).
 */
class Siswa extends Model
{
    use ReadOnlyModel;

    protected $connection = 'datacenter';
    protected $table = 'siswa';
    protected $guarded = [];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'is_aktif' => 'boolean',
    ];

    /* ---------- Relasi Data Center ---------- */

    public function siswaRombel(): HasMany
    {
        return $this->hasMany(SiswaRombel::class, 'siswa_id');
    }

    /* ---------- Relasi lintas-DB (kesiswaan) ---------- */

    public function pelanggaran(): HasMany
    {
        return $this->hasMany(Pelanggaran::class, 'siswa_id');
    }

    public function prestasi(): HasMany
    {
        return $this->hasMany(Prestasi::class, 'siswa_id');
    }

    public function ekstrakurikuler(): BelongsToMany
    {
        return $this->belongsToMany(Ekstrakurikuler::class, 'ekstrakurikuler_siswa', 'siswa_id', 'ekstrakurikuler_id')
            ->withPivot('jabatan')
            ->withTimestamps();
    }

    /* ---------- Accessor jembatan ---------- */

    /** Nama siswa. */
    public function getNamaAttribute(): string
    {
        return $this->nama_siswa;
    }

    /** Status siswa (Aktif|Lulus|Keluar). */
    public function getStatusAttribute(): ?string
    {
        return $this->status_siswa;
    }

    public function getJenisKelaminLabelAttribute(): string
    {
        return $this->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-laki';
    }

    /** Alias nomor HP siswa. */
    public function getTeleponAttribute(): ?string
    {
        return $this->nomor_hp;
    }

    /** Kelas (rombel) siswa pada tahun ajaran yang sedang dipilih. */
    public function getKelasAttribute()
    {
        $ta = current_ta_id();

        if ($this->relationLoaded('siswaRombel')) {
            return optional($this->siswaRombel->firstWhere('tahun_ajaran_id', $ta))->kelas;
        }

        return optional($this->siswaRombel()->where('tahun_ajaran_id', $ta)->first())->kelas;
    }

    /** Total poin pelanggaran (di-set manual di daftar, atau dihitung saat diakses). */
    public function getTotalPoinAttribute(): int
    {
        if (array_key_exists('total_poin', $this->attributes)) {
            return (int) $this->attributes['total_poin'];
        }

        return (int) $this->pelanggaran()->sum('poin');
    }
}
