<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Template & pembaca file Excel import nilai rapor (format Kurikulum 2013 / Merdeka).
 * Sheet: Petunjuk (identitas template), Nilai (satu baris per siswa per mapel), Rekap (satu baris per siswa).
 */
class RaporSpreadsheet
{
    public const SHEET_PETUNJUK = 'Petunjuk';
    public const SHEET_NILAI = 'Nilai';
    public const SHEET_REKAP = 'Rekap';

    private const KOLOM_ANGKA = ['kkm', 'nilai_pengetahuan', 'nilai_keterampilan', 'nilai_akhir', 'sakit', 'izin', 'alpa'];
    private const KOLOM_IDENTITAS = ['nisn', 'nis', 'nama'];

    /** Kolom sheet Nilai: key => judul kolom. */
    public static function kolomNilai(string $kurikulum): array
    {
        $awal = ['nisn' => 'NISN', 'nis' => 'NIS', 'nama' => 'Nama Siswa', 'kelompok' => 'Kelompok', 'nama_mapel' => 'Mata Pelajaran'];

        return $awal + ($kurikulum === 'K13'
            ? [
                'kkm' => 'KKM',
                'nilai_pengetahuan' => 'Nilai Pengetahuan',
                'deskripsi_pengetahuan' => 'Deskripsi Pengetahuan',
                'nilai_keterampilan' => 'Nilai Keterampilan',
                'deskripsi_keterampilan' => 'Deskripsi Keterampilan',
            ]
            : [
                'nilai_akhir' => 'Nilai Akhir',
                'capaian_kompetensi' => 'Capaian Kompetensi',
            ]);
    }

    /** Kolom sheet Rekap: key => judul kolom. */
    public static function kolomRekap(string $kurikulum): array
    {
        $kolom = ['nisn' => 'NISN', 'nis' => 'NIS', 'nama' => 'Nama Siswa', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpa' => 'Tanpa Keterangan'];

        if ($kurikulum === 'K13') {
            $kolom += [
                'sikap_spiritual_predikat' => 'Predikat Sikap Spiritual',
                'sikap_spiritual_deskripsi' => 'Deskripsi Sikap Spiritual',
                'sikap_sosial_predikat' => 'Predikat Sikap Sosial',
                'sikap_sosial_deskripsi' => 'Deskripsi Sikap Sosial',
            ];
        }

        return $kolom + ['catatan_wali_kelas' => 'Catatan Wali Kelas', 'keputusan' => 'Keputusan'];
    }

    /** Kolom isian (selain identitas siswa) — dipakai untuk menentukan baris yang benar-benar diisi. */
    public static function kolomIsian(array $kolom): array
    {
        return array_values(array_diff(array_keys($kolom), self::KOLOM_IDENTITAS, ['kelompok', 'nama_mapel', 'kkm']));
    }

    /**
     * @param  array  $info  kurikulum, kelas, tahun_pelajaran, semester
     */
    public static function template(string $filename, array $info, array $nilaiRows, array $rekapRows): BinaryFileResponse
    {
        $kurikulum = $info['kurikulum'];
        $ss = new Spreadsheet();

        $p = $ss->getActiveSheet()->setTitle(self::SHEET_PETUNJUK);
        $p->setCellValue('A1', 'Template Import Nilai Rapor');
        $p->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        foreach (self::meta($info) as $i => [$label, $nilai]) {
            $p->setCellValue('A' . ($i + 3), $label);
            $p->setCellValueExplicit('B' . ($i + 3), $nilai, DataType::TYPE_STRING);
        }
        $p->getStyle('A3:A6')->getFont()->setBold(true);

        $petunjuk = [
            'Petunjuk Pengisian',
            '1. Sheet "Nilai": satu baris untuk setiap siswa per mata pelajaran. Jangan mengubah judul kolom maupun isi sheet ini.',
            '2. NISN dipakai untuk mencocokkan siswa; kolom NIS dan Nama Siswa hanya sebagai keterangan.',
            $kurikulum === 'K13'
                ? '3. Nilai pengetahuan, keterampilan, dan KKM berupa angka 0–100. Predikat A–D dihitung otomatis dari KKM.'
                : '3. Nilai akhir berupa angka 0–100; capaian kompetensi berupa deskripsi.',
            '4. Baris tanpa nilai/deskripsi diabaikan. Siswa yang tidak diisi sama sekali tidak diimpor.',
            $kurikulum === 'K13'
                ? '5. Sheet "Rekap": ketidakhadiran (hari), predikat sikap (Sangat Baik/Baik/Cukup/Kurang), catatan wali kelas, keputusan.'
                : '5. Sheet "Rekap": ketidakhadiran (hari), catatan wali kelas, keputusan.',
            '6. Unggah dengan pilihan kurikulum, kelas, dan semester yang sama seperti di atas.',
            '7. Mengimpor ulang semester yang sama akan menimpa nilai yang sudah ada.',
        ];
        foreach ($petunjuk as $i => $teks) {
            $p->setCellValue('A' . ($i + 8), $teks);
        }
        $p->getStyle('A8')->getFont()->setBold(true);
        $p->getColumnDimension('A')->setWidth(22);
        $p->getColumnDimension('B')->setWidth(40);

        self::isiSheet($ss->createSheet()->setTitle(self::SHEET_NILAI), self::kolomNilai($kurikulum), $nilaiRows);

        $pilihanSikap = array_fill_keys(['sikap_spiritual_predikat', 'sikap_sosial_predikat'], config('buku_induk.predikat_sikap'));
        self::isiSheet($ss->createSheet()->setTitle(self::SHEET_REKAP), self::kolomRekap($kurikulum), $rekapRows, $pilihanSikap);

        $ss->setActiveSheetIndex(1);

        $tmp = tempnam(sys_get_temp_dir(), 'rapor_');
        (new Xlsx($ss))->save($tmp);
        $ss->disconnectWorksheets();

        return response()->download($tmp, $filename . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Baca file unggahan.
     *
     * @return array{nilai: array<int, array>, rekap: array<int, array>} baris data, di-key nomor baris Excel
     *
     * @throws ValidationException bila file/sheet/kolom tidak sesuai template
     */
    public static function baca(string $path, array $info): array
    {
        try {
            $ss = IOFactory::load($path);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['file' => 'File tidak dapat dibaca. Pastikan formatnya .xlsx atau .xls.']);
        }

        $kurikulum = $info['kurikulum'];
        $errors = [];

        // Pastikan template dibuat untuk pilihan yang sama.
        if ($petunjuk = $ss->getSheetByName(self::SHEET_PETUNJUK)) {
            foreach (self::meta($info) as $i => [$label, $harapan]) {
                $isi = trim((string) $petunjuk->getCell('B' . ($i + 3))->getValue());
                if ($isi !== '' && $isi !== $harapan) {
                    $errors[] = "Template ini untuk {$label} \"{$isi}\", sedangkan pilihan import {$label} \"{$harapan}\".";
                }
            }
        }

        $sheetNilai = $ss->getSheetByName(self::SHEET_NILAI);
        if (! $sheetNilai) {
            $errors[] = 'Sheet "Nilai" tidak ditemukan. Gunakan file dari tombol Unduh Template.';
        }

        if ($errors) {
            $ss->disconnectWorksheets();
            throw ValidationException::withMessages(['file' => $errors]);
        }

        $nilai = self::bacaSheet($sheetNilai, self::kolomNilai($kurikulum), $kurikulum);
        $sheetRekap = $ss->getSheetByName(self::SHEET_REKAP);
        $rekap = $sheetRekap ? self::bacaSheet($sheetRekap, self::kolomRekap($kurikulum), $kurikulum) : [];

        $ss->disconnectWorksheets();

        return compact('nilai', 'rekap');
    }

    /** Identitas template yang dicantumkan & dicocokkan pada sheet Petunjuk. */
    private static function meta(array $info): array
    {
        return [
            ['Kurikulum', config("buku_induk.kurikulum.{$info['kurikulum']}")],
            ['Kelas', (string) $info['kelas']],
            ['Tahun Pelajaran', (string) $info['tahun_pelajaran']],
            ['Semester', $info['semester'] == 2 ? '2 (Genap)' : '1 (Ganjil)'],
        ];
    }

    private static function isiSheet(Worksheet $sheet, array $kolom, array $rows, array $pilihan = []): void
    {
        $keys = array_keys($kolom);
        $lastCol = Coordinate::stringFromColumnIndex(count($keys));
        $lastRow = max(2, count($rows) + 1);

        foreach (array_values($kolom) as $i => $judul) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . '1', $judul);
        }
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E7FF']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->freezePane('D2');

        foreach (array_values($rows) as $r => $row) {
            $n = $r + 2;
            foreach ($keys as $c => $key) {
                $val = $row[$key] ?? null;
                if ($val === null || $val === '') {
                    continue;
                }
                $cell = Coordinate::stringFromColumnIndex($c + 1) . $n;
                if (in_array($key, self::KOLOM_ANGKA, true) && is_numeric($val)) {
                    $sheet->setCellValue($cell, (int) $val);
                } else {
                    $sheet->setCellValueExplicit($cell, (string) $val, DataType::TYPE_STRING);
                }
            }
        }

        foreach ($keys as $c => $key) {
            $col = Coordinate::stringFromColumnIndex($c + 1);
            $sheet->getColumnDimension($col)->setWidth(match (true) {
                str_starts_with($key, 'deskripsi') || in_array($key, ['capaian_kompetensi', 'catatan_wali_kelas'], true) => 45,
                in_array($key, ['nama', 'nama_mapel'], true) => 30,
                str_contains($key, 'predikat') || in_array($key, ['kelompok', 'keputusan'], true) => 20,
                default => 13,
            });

            if (in_array($key, ['nisn', 'nis'], true)) {
                $sheet->getStyle("{$col}2:{$col}" . ($lastRow + 200))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            }

            if (isset($pilihan[$key])) {
                $dv = new DataValidation();
                $dv->setType(DataValidation::TYPE_LIST)->setAllowBlank(true)->setShowDropDown(true)
                    ->setFormula1('"' . implode(',', $pilihan[$key]) . '"');
                for ($n = 2; $n <= $lastRow; $n++) {
                    $sheet->getCell("{$col}{$n}")->setDataValidation(clone $dv);
                }
            }
        }

        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
        $sheet->getStyle("A2:C{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
        $sheet->getStyle("A2:{$lastCol}{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
    }

    private static function bacaSheet(Worksheet $sheet, array $kolom, string $kurikulum): array
    {
        $data = $sheet->toArray(null, true, false, false);
        $header = array_shift($data) ?? [];

        $normal = fn ($v) => mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $v)));
        $judul = array_map($normal, $kolom);

        $posisi = [];
        foreach ($header as $i => $h) {
            $key = array_search($normal($h), $judul, true);
            if ($key !== false && ! isset($posisi[$key])) {
                $posisi[$key] = $i;
            }
        }

        $wajib = array_diff_key($kolom, array_flip(['nis', 'nama', 'kelompok']));
        $hilang = array_diff_key($wajib, $posisi);

        if ($hilang) {
            $pesan = "Sheet \"{$sheet->getTitle()}\": kolom " . implode(', ', $hilang) . ' tidak ditemukan.';

            $lain = $kurikulum === 'K13' ? 'Merdeka' : 'K13';
            $penanda = $lain === 'K13' ? 'nilai pengetahuan' : 'nilai akhir';
            if (in_array($penanda, array_map($normal, $header), true)) {
                $pesan .= ' File ini tampaknya template ' . config("buku_induk.kurikulum.{$lain}")
                    . ', sedangkan pilihan import ' . config("buku_induk.kurikulum.{$kurikulum}") . '.';
            }

            throw ValidationException::withMessages(['file' => $pesan]);
        }

        $rows = [];
        foreach ($data as $idx => $row) {
            $item = [];
            foreach ($posisi as $key => $i) {
                $item[$key] = self::sel($row[$i] ?? null);
            }

            if (array_filter($item, fn ($v) => $v !== null)) {
                $rows[$idx + 2] = $item;
            }
        }

        return $rows;
    }

    private static function sel(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }

        // NISN/nilai yang tersimpan sebagai angka di Excel: hindari notasi "3.2E+9" atau "85.0".
        if (is_float($v) && floor($v) == $v && abs($v) < PHP_INT_MAX) {
            $v = (string) (int) $v;
        }

        $v = trim((string) $v);

        return $v === '' ? null : $v;
    }
}
