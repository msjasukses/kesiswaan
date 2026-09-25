<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\Pelanggaran;
use App\Models\Prestasi;
use App\Models\ProgramKerja;
use App\Models\Siswa;
use App\Models\SiswaRombel;

class DashboardController extends Controller
{
    public function index()
    {
        $ta = current_ta_id();

        $stats = [
            'siswa' => SiswaRombel::where('tahun_ajaran_id', $ta)->distinct('siswa_id')->count('siswa_id'),
            'pelanggaran_bulan_ini' => Pelanggaran::whereYear('tanggal', now()->year)
                ->whereMonth('tanggal', now()->month)->count(),
            'prestasi' => Prestasi::count(),
            'ekstrakurikuler' => Ekstrakurikuler::count(),
            'program_kerja' => ProgramKerja::count(),
            'program_berjalan' => ProgramKerja::where('status', 'berjalan')->count(),
        ];

        // Grafik pelanggaran 6 bulan terakhir
        $chart = collect(range(5, 0))->map(function ($i) {
            $bulan = now()->startOfMonth()->subMonths($i);
            return [
                'label' => $bulan->translatedFormat('M Y'),
                'total' => Pelanggaran::whereYear('tanggal', $bulan->year)
                    ->whereMonth('tanggal', $bulan->month)->count(),
            ];
        });

        // Siswa dengan poin pelanggaran tertinggi (agregasi di DB kesiswaan lalu ambil siswa dari datacenter)
        $poinRows = Pelanggaran::selectRaw('siswa_id, SUM(poin) as total_poin')
            ->groupBy('siswa_id')
            ->orderByDesc('total_poin')
            ->limit(5)
            ->get();

        $siswaMap = Siswa::with('siswaRombel.kelas')
            ->whereIn('id', $poinRows->pluck('siswa_id')->all())
            ->get()
            ->keyBy('id');

        $topPelanggar = $poinRows->map(fn ($r) => (object) [
            'nama' => $siswaMap[$r->siswa_id]->nama ?? 'Siswa #' . $r->siswa_id,
            'kelas' => optional($siswaMap[$r->siswa_id]?->kelas)->nama,
            'total_poin' => (int) $r->total_poin,
        ]);

        // Pelanggaran & prestasi terbaru
        $pelanggaranTerbaru = Pelanggaran::with(['siswa.siswaRombel.kelas', 'dataPelanggaran'])
            ->latest('tanggal')->limit(5)->get();

        $prestasiTerbaru = Prestasi::with('siswa.siswaRombel.kelas')
            ->latest('tanggal')->limit(5)->get();

        return view('dashboard', compact('stats', 'chart', 'topPelanggar', 'pelanggaranTerbaru', 'prestasiTerbaru'));
    }
}
