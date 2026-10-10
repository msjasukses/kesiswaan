<?php

/*
|--------------------------------------------------------------------------
| Buku Induk Siswa
|--------------------------------------------------------------------------
| Pilihan kurikulum beserta template mata pelajaran yang diisikan otomatis
| saat menambah catatan hasil belajar per semester. Daftar mapel tetap bisa
| diubah per semester melalui form.
*/

return [

    'kurikulum' => [
        'K13' => 'Kurikulum 2013',
        'Merdeka' => 'Kurikulum Merdeka',
    ],

    'keterangan' => [
        'K13' => 'Nilai pengetahuan & keterampilan, KKM, predikat A–D, sikap spiritual & sosial.',
        'Merdeka' => 'Nilai akhir & capaian kompetensi, projek penguatan profil pelajar Pancasila.',
    ],

    'default_kurikulum' => 'Merdeka',

    // Predikat sikap spiritual & sosial (Kurikulum 2013)
    'predikat_sikap' => ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'],

    // Capaian projek penguatan profil pelajar Pancasila / kokurikuler (Kurikulum Merdeka)
    'capaian_p5' => [
        'MB' => 'Mulai Berkembang',
        'SB' => 'Sedang Berkembang',
        'BSH' => 'Berkembang Sesuai Harapan',
        'SAB' => 'Sangat Berkembang',
    ],

    'predikat_ekskul' => ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'],

    'mapel' => [
        'K13' => [
            ['kelompok' => 'Kelompok A (Umum)', 'nama_mapel' => 'Pendidikan Agama dan Budi Pekerti'],
            ['kelompok' => 'Kelompok A (Umum)', 'nama_mapel' => 'Pendidikan Pancasila dan Kewarganegaraan'],
            ['kelompok' => 'Kelompok A (Umum)', 'nama_mapel' => 'Bahasa Indonesia'],
            ['kelompok' => 'Kelompok A (Umum)', 'nama_mapel' => 'Matematika'],
            ['kelompok' => 'Kelompok A (Umum)', 'nama_mapel' => 'Ilmu Pengetahuan Alam'],
            ['kelompok' => 'Kelompok A (Umum)', 'nama_mapel' => 'Ilmu Pengetahuan Sosial'],
            ['kelompok' => 'Kelompok A (Umum)', 'nama_mapel' => 'Bahasa Inggris'],
            ['kelompok' => 'Kelompok B (Umum)', 'nama_mapel' => 'Seni Budaya'],
            ['kelompok' => 'Kelompok B (Umum)', 'nama_mapel' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan'],
            ['kelompok' => 'Kelompok B (Umum)', 'nama_mapel' => 'Prakarya'],
            ['kelompok' => 'Kelompok B (Umum)', 'nama_mapel' => 'Muatan Lokal'],
        ],
        'Merdeka' => [
            ['kelompok' => 'Mata Pelajaran Umum', 'nama_mapel' => 'Pendidikan Agama dan Budi Pekerti'],
            ['kelompok' => 'Mata Pelajaran Umum', 'nama_mapel' => 'Pendidikan Pancasila'],
            ['kelompok' => 'Mata Pelajaran Umum', 'nama_mapel' => 'Bahasa Indonesia'],
            ['kelompok' => 'Mata Pelajaran Umum', 'nama_mapel' => 'Matematika'],
            ['kelompok' => 'Mata Pelajaran Umum', 'nama_mapel' => 'Ilmu Pengetahuan Alam'],
            ['kelompok' => 'Mata Pelajaran Umum', 'nama_mapel' => 'Ilmu Pengetahuan Sosial'],
            ['kelompok' => 'Mata Pelajaran Umum', 'nama_mapel' => 'Bahasa Inggris'],
            ['kelompok' => 'Mata Pelajaran Umum', 'nama_mapel' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan'],
            ['kelompok' => 'Mata Pelajaran Umum', 'nama_mapel' => 'Informatika'],
            ['kelompok' => 'Mata Pelajaran Umum', 'nama_mapel' => 'Seni dan Prakarya'],
            ['kelompok' => 'Muatan Lokal', 'nama_mapel' => 'Muatan Lokal'],
        ],
    ],

];
