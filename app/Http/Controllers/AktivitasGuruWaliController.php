<?php

namespace App\Http\Controllers;

use App\Models\AktivitasGuruWali;
use App\Models\GuruWali;
use App\Models\GuruWaliSiswa;
use App\Models\Siswa;
use App\Support\Exporter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AktivitasGuruWaliController extends Controller
{
    /** Kategori pembinaan yang tersedia. */
    public const KATEGORI = [
        'Akademik', 'Kedisiplinan', 'Kehadiran', 'Sosial & Emosional',
        'Karakter & Ibadah', 'Karier & Minat Bakat', 'Lainnya',
    ];

    /** Status penanganan aktivitas. */
    public const STATUS = ['Selesai', 'Proses', 'Perlu Tindak Lanjut'];

    public function index(Request $request)
    {
        $aktivitas = $this->filteredQuery($request)->paginate(15)->withQueryString();

        // Nama siswa berada di koneksi lain, jadi diambil sekali lalu dipetakan per id.
        $siswa = Siswa::with('siswaRombel.kelas')
            ->whereIn('id', $aktivitas->pluck('siswa_id')->unique()->all())
            ->get()->keyBy('id');

        return view('aktivitas_guru_wali.index', [
            'aktivitas' => $aktivitas,
            'siswa' => $siswa,
            'daftarWali' => GuruWali::orderBy('nama')->get(),
        ]);
    }

    private function filteredQuery(Request $request)
    {
        $query = AktivitasGuruWali::with('guruWali');

        if ($search = $request->input('q')) {
            $siswaIds = Siswa::where('nama_siswa', 'like', "%{$search}%")->pluck('id')->all();

            $query->where(fn ($q) => $q->where('aktivitas', 'like', "%{$search}%")
                ->orWhere('catatan', 'like', "%{$search}%")
                ->orWhereIn('siswa_id', $siswaIds));
        }

        if ($waliId = $request->input('guru_wali_id')) {
            $query->where('guru_wali_id', $waliId);
        }

        if ($kategori = $request->input('kategori')) {
            $query->where('kategori', $kategori);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter bulan dalam format YYYY-MM (input type="month").
        if (preg_match('/^\d{4}-\d{2}$/', (string) $request->input('bulan'))) {
            [$tahun, $bulan] = explode('-', $request->input('bulan'));
            $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
        }

        return $query->latest('tanggal')->latest('id');
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['excel', 'word', 'pdf'], true), 404);

        $rows = $this->filteredQuery($request)->get();
        $siswa = Siswa::whereIn('id', $rows->pluck('siswa_id')->unique()->all())->get()->keyBy('id');

        $headers = ['No', 'Tanggal', 'Perwalian', 'Siswa Binaan', 'Kategori', 'Status', 'Aktivitas', 'Catatan Guru Wali', 'Tindak Lanjut', 'Hasil'];

        $data = $rows->values()->map(fn ($a, $i) => [
            $i + 1,
            optional($a->tanggal)->format('d/m/Y'),
            $a->guruWali?->nama,
            $siswa->get($a->siswa_id)?->nama_siswa,
            $a->kategori,
            $a->status,
            $a->aktivitas,
            $a->catatan,
            $a->tindak_lanjut,
            $a->hasil,
        ])->all();

        $filename = 'aktivitas-guru-wali-' . now()->format('Ymd-His');

        return Exporter::{$format}($filename, 'Aktivitas Guru Wali', $headers, $data);
    }

    public function create(Request $request)
    {
        return view('aktivitas_guru_wali.create', $this->formData(
            new AktivitasGuruWali([
                'tanggal' => now(),
                'guru_wali_id' => $request->input('guru_wali_id'),
                'status' => 'Selesai',
            ])
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['user_id'] = auth()->id();

        AktivitasGuruWali::create($data);

        return redirect()->route('aktivitas-guru-wali.index')->with('success', 'Aktivitas guru wali berhasil disimpan.');
    }

    public function edit(AktivitasGuruWali $aktivitasGuruWali)
    {
        return view('aktivitas_guru_wali.edit', $this->formData($aktivitasGuruWali));
    }

    public function update(Request $request, AktivitasGuruWali $aktivitasGuruWali)
    {
        $aktivitasGuruWali->update($this->validateData($request));

        return redirect()->route('aktivitas-guru-wali.index')->with('success', 'Aktivitas guru wali berhasil diperbarui.');
    }

    public function destroy(AktivitasGuruWali $aktivitasGuruWali)
    {
        $aktivitasGuruWali->delete();

        return redirect()->route('aktivitas-guru-wali.index')->with('success', 'Aktivitas guru wali berhasil dihapus.');
    }

    private function formData(AktivitasGuruWali $aktivitas): array
    {
        return [
            'aktivitas' => $aktivitas,
            'daftarWali' => GuruWali::orderBy('nama')->get(),
            'siswaPerWali' => $this->siswaPerWali(),
        ];
    }

    /** Daftar siswa binaan per perwalian, untuk mengisi dropdown siswa saat perwalian dipilih. */
    private function siswaPerWali(): array
    {
        $pivot = GuruWaliSiswa::get(['guru_wali_id', 'siswa_id']);

        $siswa = Siswa::with('siswaRombel.kelas')
            ->whereIn('id', $pivot->pluck('siswa_id')->unique()->all())
            ->get()->keyBy('id');

        return $pivot->groupBy('guru_wali_id')
            ->map(fn ($rows) => $rows
                ->map(fn ($r) => [
                    'id' => (string) $r->siswa_id,
                    'label' => ($siswa->get($r->siswa_id)?->nama_siswa ?? 'Siswa #' . $r->siswa_id)
                        . ' — ' . ($siswa->get($r->siswa_id)?->kelas?->nama_rombel ?? 'tanpa kelas'),
                ])
                ->sortBy('label')->values()->all())
            ->all();
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'guru_wali_id' => ['required', 'exists:guru_wali,id'],
            // Siswa harus benar-benar siswa binaan pada perwalian yang dipilih.
            'siswa_id' => [
                'required',
                Rule::exists('guru_wali_siswa', 'siswa_id')
                    ->where('guru_wali_id', $request->input('guru_wali_id')),
            ],
            'tanggal' => ['required', 'date'],
            'kategori' => ['required', Rule::in(self::KATEGORI)],
            'status' => ['required', Rule::in(self::STATUS)],
            'aktivitas' => ['required', 'string'],
            'catatan' => ['nullable', 'string'],
            'tindak_lanjut' => ['nullable', 'string'],
            'hasil' => ['nullable', 'string'],
        ], [], [
            'guru_wali_id' => 'perwalian',
            'siswa_id' => 'siswa binaan',
        ]);
    }
}
