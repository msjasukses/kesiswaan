<?php

use App\Models\Setting;
use App\Models\TahunAjaran;

if (! function_exists('current_ta_id')) {
    /**
     * ID tahun ajaran yang sedang dipilih (session) atau default tahun ajaran aktif.
     */
    function current_ta_id(): ?int
    {
        static $memo = [];
        $key = session('tahun_ajaran_id') ?? 'default';

        if (array_key_exists($key, $memo)) {
            return $memo[$key];
        }

        $id = session('tahun_ajaran_id');
        if (! $id) {
            $id = TahunAjaran::where('is_aktif', 1)->value('id') ?? TahunAjaran::max('id');
        }

        return $memo[$key] = $id ? (int) $id : null;
    }
}

if (! function_exists('current_tahun_ajaran')) {
    /**
     * Model tahun ajaran yang sedang dipilih.
     */
    function current_tahun_ajaran(): ?TahunAjaran
    {
        $id = current_ta_id();

        return $id ? TahunAjaran::find($id) : null;
    }
}

if (! function_exists('setting')) {
    /**
     * Ambil nilai setelan aplikasi berdasarkan key.
     */
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('rupiah')) {
    /**
     * Format angka menjadi Rupiah.
     */
    function rupiah($angka): string
    {
        if ($angka === null || $angka === '') {
            return '-';
        }

        return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    }
}
