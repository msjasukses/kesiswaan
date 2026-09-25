<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalEkstrakurikuler extends Model
{
    protected $connection = 'mysql';
    protected $table = 'jurnal_ekstrakurikuler';

    protected $fillable = [
        'ekstrakurikuler_id', 'tanggal', 'pembina', 'pelatih', 'materi',
        'siswa_hadir', 'siswa_tidak_hadir', 'foto', 'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'siswa_hadir' => 'integer',
        'siswa_tidak_hadir' => 'integer',
    ];

    public function ekstrakurikuler(): BelongsTo
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Jumlah siswa yang terdata pada kegiatan ini. */
    public function getTotalSiswaAttribute(): int
    {
        return $this->siswa_hadir + $this->siswa_tidak_hadir;
    }

    /** Persentase kehadiran (0-100), null bila belum ada data siswa. */
    public function getPersenHadirAttribute(): ?int
    {
        return $this->total_siswa > 0
            ? (int) round($this->siswa_hadir / $this->total_siswa * 100)
            : null;
    }
}
