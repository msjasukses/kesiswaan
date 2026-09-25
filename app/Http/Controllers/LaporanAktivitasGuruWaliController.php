<?php

namespace App\Http\Controllers;

use App\Models\AktivitasGuruWali;
use App\Models\GuruWali;
use App\Models\Siswa;
use App\Support\Exporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LaporanAktivitasGuruWaliController extends Controller
{
    public function index(Request $request)
    {
        return view('laporan.aktivitas_guru_wali', $this->laporan($request) + [
            'daftarWali' => GuruWali::orderBy('nama')->get(),
        ]);
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['excel', 'word', 'pdf'], true), 404);

        $l = $this->laporan($request);

        $headers = ['Rekap', 'Keterangan', 'Total Aktivitas', 'Siswa Dibina', 'Selesai', 'Proses', 'Perlu Tindak Lanjut'];
        $rows = [];

        $rows[] = ['— REKAP PER PERWALIAN —', '', '', '', '', '', ''];
        foreach ($l['perPerwalian'] as $r) {
            $rows[] = [$r->label, $r->keterangan, $r->total, $r->siswa_dibina, $r->selesai, $r->proses, $r->perlu];
        }
        $rows[] = ['TOTAL', '', $l['ringkasan']['total'], $l['ringkasan']['siswa'], $l['ringkasan']['selesai'], $l['ringkasan']['proses'], $l['ringkasan']['perlu']];

        $rows[] = array_fill(0, 7, '');
        $rows[] = ['— REKAP PER KATEGORI PEMBINAAN —', '', '', '', '', '', ''];
        foreach ($l['perKategori'] as $r) {
            $rows[] = [$r->label, '', $r->total, $r->siswa_dibina, $r->selesai, $r->proses, $r->perlu];
        }

        $rows[] = array_fill(0, 7, '');
        $rows[] = ['— SISWA PALING SERING DIBINA —', '', '', '', '', '', ''];
        foreach ($l['perSiswa'] as $r) {
            $rows[] = [$r->nama, $r->kelas, $r->total, '', $r->selesai, $r->proses, $r->perlu];
        }

        $judul = 'Laporan Aktivitas Guru Wali (' . $l['dari']->translatedFormat('d M Y')
            . ' - ' . $l['sampai']->translatedFormat('d M Y') . ')';

        $filename = 'laporan-aktivitas-guru-wali-' . now()->format('Ymd-His');

        return Exporter::{$format}($filename, $judul, $headers, $rows);
    }

    /** Menyusun seluruh angka laporan untuk periode & perwalian yang dipilih. */
    private function laporan(Request $request): array
    {
        [$dari, $sampai] = $this->periode($request);
        $waliId = $request->input('guru_wali_id');

        $base = AktivitasGuruWali::whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->when($waliId, fn ($q) => $q->where('guru_wali_id', $waliId));

        return [
            'dari' => $dari,
            'sampai' => $sampai,
            'waliDipilih' => $waliId ? GuruWali::with('guru')->find($waliId) : null,
            'ringkasan' => $this->ringkasan($base),
            'tren' => $this->tren($base, $dari, $sampai),
            'perPerwalian' => $this->perPerwalian($base),
            'perKategori' => $this->perKategori($base),
            'perSiswa' => $this->perSiswa($base),
        ];
    }

    /** Periode laporan; bawaan enam bulan terakhir. */
    private function periode(Request $request): array
    {
        $dari = $this->tanggal($request->input('dari')) ?? now()->startOfMonth()->subMonths(5);
        $sampai = $this->tanggal($request->input('sampai')) ?? now()->endOfMonth();

        if ($sampai->lt($dari)) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        return [$dari->startOfDay(), $sampai->endOfDay()];
    }

    private function tanggal($nilai): ?Carbon
    {
        return blank($nilai) ? null : rescue(fn () => Carbon::parse($nilai), null, false);
    }

    private function ringkasan(Builder $base): array
    {
        $perStatus = (clone $base)->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        return [
            'total' => (int) $perStatus->sum(),
            'siswa' => (clone $base)->distinct()->count('siswa_id'),
            'selesai' => (int) ($perStatus['Selesai'] ?? 0),
            'proses' => (int) ($perStatus['Proses'] ?? 0),
            'perlu' => (int) ($perStatus['Perlu Tindak Lanjut'] ?? 0),
        ];
    }

    /** Jumlah aktivitas per bulan sepanjang periode (bulan kosong tetap ditampilkan). */
    private function tren(Builder $base, Carbon $dari, Carbon $sampai)
    {
        $data = (clone $base)->selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as bulan, COUNT(*) as total")
            ->groupBy('bulan')->pluck('total', 'bulan');

        $tren = collect();
        for ($b = $dari->copy()->startOfMonth(); $b->lte($sampai); $b->addMonth()) {
            $tren->push([
                'label' => $b->translatedFormat('M Y'),
                'total' => (int) ($data[$b->format('Y-m')] ?? 0),
            ]);
        }

        // Batasi agar grafik tetap terbaca bila periodenya sangat panjang.
        return $tren->count() > 12 ? $tren->slice(-12)->values() : $tren;
    }

    private function perPerwalian(Builder $base)
    {
        $rows = $this->agregat($base, 'guru_wali_id');

        $wali = GuruWali::with('guru')->whereIn('id', $rows->pluck('kunci')->all())->get()->keyBy('id');

        return $rows->map(function ($r) use ($wali) {
            $r->label = $wali->get($r->kunci)?->nama ?? 'Perwalian #' . $r->kunci;
            $r->keterangan = $wali->get($r->kunci)?->guru?->nama_ptk ?? '-';

            return $r;
        });
    }

    private function perKategori(Builder $base)
    {
        return $this->agregat($base, 'kategori')->each(fn ($r) => $r->label = $r->kunci);
    }

    private function perSiswa(Builder $base)
    {
        $rows = $this->agregat($base, 'siswa_id')->take(10)->values();

        $siswa = Siswa::with('siswaRombel.kelas')
            ->whereIn('id', $rows->pluck('kunci')->all())->get()->keyBy('id');

        return $rows->map(function ($r) use ($siswa) {
            $r->nama = $siswa->get($r->kunci)?->nama_siswa ?? 'Siswa #' . $r->kunci;
            $r->kelas = $siswa->get($r->kunci)?->kelas?->nama_rombel ?? '-';

            return $r;
        });
    }

    /**
     * Agregasi jumlah aktivitas + rincian status per kolom pengelompokan.
     * $kolom selalu berasal dari kode (bukan input pengguna).
     */
    private function agregat(Builder $base, string $kolom)
    {
        return (clone $base)
            ->selectRaw("{$kolom} as kunci,
                COUNT(*) as total,
                COUNT(DISTINCT siswa_id) as siswa_dibina,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as proses,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as perlu",
                AktivitasGuruWaliController::STATUS)
            ->groupBy($kolom)
            ->orderByDesc('total')
            ->get();
    }
}
