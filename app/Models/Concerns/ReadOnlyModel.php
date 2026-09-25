<?php

namespace App\Models\Concerns;

use RuntimeException;

/**
 * Menandai model sebagai read-only. Data master (siswa, kelas, guru) dikelola
 * di aplikasi Data Center, sehingga kesiswaan tidak boleh menulis balik.
 */
trait ReadOnlyModel
{
    public static function bootReadOnlyModel(): void
    {
        static::saving(function () {
            throw new RuntimeException('Model ini read-only (dikelola di aplikasi Data Center).');
        });

        static::deleting(function () {
            throw new RuntimeException('Model ini read-only (dikelola di aplikasi Data Center).');
        });
    }
}
