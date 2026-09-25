<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\JurnalEkstrakurikuler;
use App\Support\Exporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LaporanJurnalEkstrakurikulerController extends Controller
{
    public function index(Request $request)
    {
        return view('laporan.jurnal_ekstrakurikuler', $this->laporan($request) + [
            'daftarEkskul' => Ekstrakurikuler::orderBy('nama')->get(),
        ]);
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['excel', 'word', 'pdf'], true), 404);

        $l = $this->laporan($request);

        $headers = ['Rekap', 'Keterangan', 'Pertemuan', 'Siswa Hadir', 'Siswa Tidak Hadir', 'Rata-rata Kehadiran'];
        $rows = [];

        $rows[] = ['— REKAP PER EKSTRAKURIKULER —', '', '', '', '', ''];
        foreach ($l['perEkskul'] as $r) {
            $rows[] = [$r->label, $r->pembina, $r->pertemuan, $r->hadir, $r->tidak_hadir, $r->persen . '%'];
        }
        $rows[] = ['TOTAL', '', $l['ringkasan']['pertemuan'], $l['ringkasan']['hadir'], $l['ringkasan']['tidak_hadir'], $l['ringkasan']['persen'] . '%'];

        $rows[] = array_fill(0, 6, '');
        $rows[] = ['— REKAP PER BULAN —', '', '', '', '', ''];
        foreach ($l['perBulan'] as $r) {
            $rows[] = [$r['label'], '', $r['pertemuan'], $r['hadir'], $r['tidak_hadir'], $r['persen'] . '%'];
        }

        $judul = 'Laporan Jurnal Ekstrakurikuler (' . $l['dari']->translatedFormat('d M Y')
            . ' - ' . $l['sampai']->translatedFormat('d M Y') . ')';

        $filename = 'laporan-jurnal-ekstrakurikuler-' . now()->format('Ymd-His');

        return Exporter::{$format}($filename, $judul, $headers, $rows);
    }

    /** Menyusun seluruh angka laporan untuk periode & ekskul yang dipilih. */
    private function laporan(Request $request): array
    {
        [$dari, $sampai] = $this->periode($request);
        $ekskulId = $request->input('ekstrakurikuler_id');

        $base = JurnalEkstrakurikuler::whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->when($ekskulId, fn ($q) => $q->where('ekstrakurikuler_id', $ekskulId));

        $perEkskul = $this->perEkskul($base);

        return [
            'dari' => $dari,
            'sampai' => $sampai,
            'ekskulDipilih' => $ekskulId ? Ekstrakurikuler::find($ekskulId) : null,
            'ringkasan' => $this->ringkasan($base),
            'perBulan' => $this->perBulan($base, $dari, $sampai),
            'perEkskul' => $perEkskul,
            'kehadiranTertinggi' => $perEkskul->sortByDesc('persen')->take(5)->values(),
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
        $r = (clone $base)->selectRaw('COUNT(*) as pertemuan,
            COUNT(DISTINCT ekstrakurikuler_id) as ekskul,
            COALESCE(SUM(siswa_hadir), 0) as hadir,
            COALESCE(SUM(siswa_tidak_hadir), 0) as tidak_hadir')->first();

        return [
            'pertemuan' => (int) $r->pertemuan,
            'ekskul' => (int) $r->ekskul,
            'hadir' => (int) $r->hadir,
            'tidak_hadir' => (int) $r->tidak_hadir,
            'persen' => $this->persen($r->hadir, $r->tidak_hadir),
        ];
    }

    /** Rekap per bulan sepanjang periode (bulan tanpa kegiatan tetap ditampilkan). */
    private function perBulan(Builder $base, Carbon $dari, Carbon $sampai)
    {
        $data = (clone $base)->selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as bulan,
            COUNT(*) as pertemuan,
            COALESCE(SUM(siswa_hadir), 0) as hadir,
            COALESCE(SUM(siswa_tidak_hadir), 0) as tidak_hadir")
            ->groupBy('bulan')->get()->keyBy('bulan');

        $rekap = collect();
        for ($b = $dari->copy()->startOfMonth(); $b->lte($sampai); $b->addMonth()) {
            $r = $data->get($b->format('Y-m'));

            $rekap->push([
                'label' => $b->translatedFormat('M Y'),
                'pertemuan' => (int) ($r->pertemuan ?? 0),
                'hadir' => (int) ($r->hadir ?? 0),
                'tidak_hadir' => (int) ($r->tidak_hadir ?? 0),
                'persen' => $this->persen($r->hadir ?? 0, $r->tidak_hadir ?? 0),
            ]);
        }

        // Batasi agar grafik tetap terbaca bila periodenya sangat panjang.
        return $rekap->count() > 12 ? $rekap->slice(-12)->values() : $rekap;
    }

    private function perEkskul(Builder $base)
    {
        $rows = (clone $base)->selectRaw('ekstrakurikuler_id,
                COUNT(*) as pertemuan,
                COALESCE(SUM(siswa_hadir), 0) as hadir,
                COALESCE(SUM(siswa_tidak_hadir), 0) as tidak_hadir,
                MAX(tanggal) as terakhir')
            ->groupBy('ekstrakurikuler_id')
            ->orderByDesc('pertemuan')
            ->get();

        $ekskul = Ekstrakurikuler::whereIn('id', $rows->pluck('ekstrakurikuler_id')->all())->get()->keyBy('id');

        return $rows->map(function ($r) use ($ekskul) {
            $r->label = $ekskul->get($r->ekstrakurikuler_id)?->nama ?? 'Ekskul #' . $r->ekstrakurikuler_id;
            $r->pembina = $ekskul->get($r->ekstrakurikuler_id)?->pembina ?? '-';
            $r->persen = $this->persen($r->hadir, $r->tidak_hadir);
            $r->terakhir_label = $r->terakhir ? Carbon::parse($r->terakhir)->translatedFormat('d M Y') : '-';

            return $r;
        });
    }

    /** Persentase kehadiran; 0 bila belum ada data siswa. */
    private function persen($hadir, $tidakHadir): int
    {
        $total = (int) $hadir + (int) $tidakHadir;

        return $total > 0 ? (int) round($hadir / $total * 100) : 0;
    }
}
