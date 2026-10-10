<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BukuIndukNilai extends Model
{
    protected $connection = 'mysql';
    protected $table = 'buku_induk_nilai';

    protected $fillable = [
        'urutan', 'kelompok', 'nama_mapel', 'kkm',
        'nilai_pengetahuan', 'predikat_pengetahuan', 'deskripsi_pengetahuan',
        'nilai_keterampilan', 'predikat_keterampilan', 'deskripsi_keterampilan',
        'nilai_akhir', 'capaian_kompetensi',
    ];

    public function semester(): BelongsTo
    {
        return $this->belongsTo(BukuIndukSemester::class, 'buku_induk_semester_id');
    }

    /**
     * Predikat Kurikulum 2013 berdasarkan KKM: rentang di atas KKM dibagi tiga.
     * Contoh KKM 70 → D < 70, C 70–79, B 80–89, A 90–100.
     */
    public static function predikatK13(?int $nilai, ?int $kkm): ?string
    {
        if ($nilai === null) {
            return null;
        }

        $kkm = $kkm ?: 70;
        $interval = (100 - $kkm) / 3;

        return match (true) {
            $nilai < $kkm => 'D',
            $nilai < $kkm + $interval => 'C',
            $nilai < $kkm + 2 * $interval => 'B',
            default => 'A',
        };
    }
}
