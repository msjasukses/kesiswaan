<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AktivitasGuruWali extends Model
{
    protected $connection = 'mysql';
    protected $table = 'aktivitas_guru_wali';

    protected $fillable = [
        'guru_wali_id', 'siswa_id', 'tanggal', 'kategori', 'status',
        'aktivitas', 'catatan', 'tindak_lanjut', 'hasil', 'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function guruWali(): BelongsTo
    {
        return $this->belongsTo(GuruWali::class);
    }

    /** Siswa binaan (dari Data Center, read-only). */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
