<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Utilitas ekspor tabel sederhana ke Excel (.xlsx) dan Word (.docx).
 * Menerima judul, daftar header kolom, dan baris data (array of array).
 */
class Exporter
{
    public static function excel(string $filename, string $title, array $headers, array $rows): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $colCount = max(1, count($headers));
        $lastCol = Coordinate::stringFromColumnIndex($colCount);

        // Judul & sub-judul
        $sheet->mergeCells("A1:{$lastCol}1")->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sekolah = setting('nama_sekolah');
        if ($sekolah) {
            $sheet->mergeCells("A2:{$lastCol}2")->setCellValue('A2', $sekolah);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Header tabel
        $headerRow = 4;
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . $headerRow, $h);
        }
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E5E7EB');

        // Data (semua sebagai teks agar NIS/nol depan & tanggal tidak berubah)
        $r = $headerRow + 1;
        foreach ($rows as $row) {
            $c = 1;
            foreach ($row as $val) {
                $sheet->setCellValueExplicit(
                    Coordinate::stringFromColumnIndex($c) . $r,
                    (string) ($val ?? ''),
                    DataType::TYPE_STRING
                );
                $c++;
            }
            $r++;
        }

        // Border & auto-size
        $lastRow = max($headerRow, $r - 1);
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        for ($c = 1; $c <= $colCount; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
        (new Xlsx($spreadsheet))->save($tmp);
        $spreadsheet->disconnectWorksheets();

        return response()->download($tmp, $filename . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public static function pdf(string $filename, string $title, array $headers, array $rows)
    {
        return Pdf::loadView('exports.table', compact('title', 'headers', 'rows'))
            ->setPaper('a4', 'landscape')
            ->download($filename . '.pdf');
    }

    public static function word(string $filename, string $title, array $headers, array $rows): BinaryFileResponse
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection(['orientation' => 'landscape']);
        $section->addText($title, ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER]);

        $sekolah = setting('nama_sekolah');
        if ($sekolah) {
            $section->addText($sekolah, ['size' => 11], ['alignment' => Jc::CENTER]);
        }
        $section->addTextBreak(1);

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 60,
        ]);

        // Header
        $table->addRow();
        foreach ($headers as $h) {
            $table->addCell(null, ['bgColor' => 'E5E7EB'])->addText((string) $h, ['bold' => true]);
        }

        // Data
        foreach ($rows as $row) {
            $table->addRow();
            foreach ($row as $val) {
                $table->addCell()->addText((string) ($val ?? '-'));
            }
        }

        $tmp = tempnam(sys_get_temp_dir(), 'docx_');
        IOFactory::createWriter($phpWord, 'Word2007')->save($tmp);

        return response()->download($tmp, $filename . '.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
