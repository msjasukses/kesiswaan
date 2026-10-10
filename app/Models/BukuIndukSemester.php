<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BukuIndukSemester extends Model
{
    protected $connection = 'mysql';
    protected $table = 'buku_induk_semester';

    protected $fillable = [
        'kurikulum', 'tahun_pelajaran', 'semester', 'kelas', 'tingkat', 'wali_kelas',
        'sikap_spiritual_predikat', 'sikap_spiritual_deskripsi', 'sikap_sosial_predikat', 'sikap_sosial_deskripsi',
        'ekstrakurikuler', 'projek', 'sakit', 'izin', 'alpa',
        'catatan_wali_kelas', 'keputusan', 'tanggal_rapor',
    ];

    protected $casts = [
        'ekstrakurikuler' => 'array',
        'projek' => 'array',
        'tanggal_rapor' => 'date',
    ];

    public function bukuInduk(): BelongsTo
    {
        return $this->belongsTo(BukuInduk::class);
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(BukuIndukNilai::class)->orderBy('urutan');
    }

    public function isK13(): bool
    {
        return $this->kurikulum === 'K13';
    }

    /**
     * Ganti seluruh baris nilai semester ini. Kolom yang tidak dipakai kurikulumnya dikosongkan
     * dan predikat Kurikulum 2013 dihitung dari KKM.
     */
    public function syncNilai(array $rows): void
    {
        $isK13 = $this->isK13();

        $this->nilai()->delete();
        $this->nilai()->createMany(collect($rows)->values()->map(function ($r, $i) use ($isK13) {
            $kkm = $isK13 ? ($r['kkm'] ?? null) : null;

            return [
                'urutan' => $i + 1,
                'kelompok' => $r['kelompok'] ?? null,
                'nama_mapel' => $r['nama_mapel'],
                'kkm' => $kkm,
                'nilai_pengetahuan' => $isK13 ? ($r['nilai_pengetahuan'] ?? null) : null,
                'predikat_pengetahuan' => $isK13 ? BukuIndukNilai::predikatK13($r['nilai_pengetahuan'] ?? null, $kkm) : null,
                'deskripsi_pengetahuan' => $isK13 ? ($r['deskripsi_pengetahuan'] ?? null) : null,
                'nilai_keterampilan' => $isK13 ? ($r['nilai_keterampilan'] ?? null) : null,
                'predikat_keterampilan' => $isK13 ? BukuIndukNilai::predikatK13($r['nilai_keterampilan'] ?? null, $kkm) : null,
                'deskripsi_keterampilan' => $isK13 ? ($r['deskripsi_keterampilan'] ?? null) : null,
                'nilai_akhir' => $isK13 ? null : ($r['nilai_akhir'] ?? null),
                'capaian_kompetensi' => $isK13 ? null : ($r['capaian_kompetensi'] ?? null),
            ];
        })->all());
    }

    public function getKurikulumLabelAttribute(): string
    {
        return BukuInduk::kurikulumLabel($this->kurikulum);
    }

    public function getSemesterLabelAttribute(): string
    {
        return $this->semester == 2 ? '2 (Genap)' : '1 (Ganjil)';
    }
}
