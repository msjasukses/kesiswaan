<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\JurnalEkstrakurikuler;
use App\Support\Exporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JurnalEkstrakurikulerController extends Controller
{
    public function index(Request $request)
    {
        $jurnal = $this->filteredQuery($request)->paginate(15)->withQueryString();

        return view('jurnal_ekstrakurikuler.index', [
            'jurnal' => $jurnal,
            'daftarEkskul' => $this->daftarEkskul(),
        ]);
    }

    private function filteredQuery(Request $request)
    {
        $query = JurnalEkstrakurikuler::with('ekstrakurikuler');

        if ($search = $request->input('q')) {
            $query->where(fn ($q) => $q->where('materi', 'like', "%{$search}%")
                ->orWhere('pembina', 'like', "%{$search}%")
                ->orWhere('pelatih', 'like', "%{$search}%"));
        }

        if ($ekskulId = $request->input('ekstrakurikuler_id')) {
            $query->where('ekstrakurikuler_id', $ekskulId);
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

        $headers = ['No', 'Tanggal', 'Ekstrakurikuler', 'Materi', 'Pembina', 'Pelatih', 'Hadir', 'Tidak Hadir', '% Hadir'];

        $rows = $this->filteredQuery($request)->get()->values()->map(fn ($j, $i) => [
            $i + 1,
            optional($j->tanggal)->format('d/m/Y'),
            $j->ekstrakurikuler?->nama,
            $j->materi,
            $j->pembina,
            $j->pelatih,
            $j->siswa_hadir,
            $j->siswa_tidak_hadir,
            $j->persen_hadir === null ? '-' : $j->persen_hadir . '%',
        ])->all();

        $filename = 'jurnal-ekstrakurikuler-' . now()->format('Ymd-His');

        return Exporter::{$format}($filename, 'Jurnal Kegiatan Ekstrakurikuler', $headers, $rows);
    }

    public function create()
    {
        return view('jurnal_ekstrakurikuler.create', [
            'jurnal' => new JurnalEkstrakurikuler(['tanggal' => now()]),
            'daftarEkskul' => $this->daftarEkskul(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, true);
        $data['foto'] = $request->file('foto')->store('jurnal-ekskul', 'public');
        $data['user_id'] = auth()->id();

        JurnalEkstrakurikuler::create($data);

        return redirect()->route('jurnal-ekstrakurikuler.index')->with('success', 'Jurnal ekstrakurikuler berhasil ditambahkan.');
    }

    public function edit(JurnalEkstrakurikuler $jurnalEkstrakurikuler)
    {
        return view('jurnal_ekstrakurikuler.edit', [
            'jurnal' => $jurnalEkstrakurikuler,
            'daftarEkskul' => $this->daftarEkskul(),
        ]);
    }

    public function update(Request $request, JurnalEkstrakurikuler $jurnalEkstrakurikuler)
    {
        $data = $this->validateData($request, false);

        if ($request->hasFile('foto')) {
            $this->hapusFoto($jurnalEkstrakurikuler);
            $data['foto'] = $request->file('foto')->store('jurnal-ekskul', 'public');
        }

        $jurnalEkstrakurikuler->update($data);

        return redirect()->route('jurnal-ekstrakurikuler.index')->with('success', 'Jurnal ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy(JurnalEkstrakurikuler $jurnalEkstrakurikuler)
    {
        $this->hapusFoto($jurnalEkstrakurikuler);
        $jurnalEkstrakurikuler->delete();

        return redirect()->route('jurnal-ekstrakurikuler.index')->with('success', 'Jurnal ekstrakurikuler berhasil dihapus.');
    }

    private function daftarEkskul()
    {
        return Ekstrakurikuler::orderBy('nama')->get();
    }

    private function hapusFoto(JurnalEkstrakurikuler $jurnal): void
    {
        if ($jurnal->foto) {
            Storage::disk('public')->delete($jurnal->foto);
        }
    }

    private function validateData(Request $request, bool $fotoWajib): array
    {
        return $request->validate([
            'ekstrakurikuler_id' => ['required', 'exists:ekstrakurikuler,id'],
            'tanggal' => ['required', 'date'],
            'pembina' => ['required', 'string', 'max:150'],
            'pelatih' => ['required', 'string', 'max:150'],
            'materi' => ['required', 'string', 'max:200'],
            'siswa_hadir' => ['required', 'integer', 'min:0', 'max:10000'],
            'siswa_tidak_hadir' => ['required', 'integer', 'min:0', 'max:10000'],
            'foto' => [$fotoWajib ? 'required' : 'nullable', 'image', 'max:2048'],
        ], [], [
            'ekstrakurikuler_id' => 'jenis ekskul',
            'foto' => 'dokumentasi foto',
        ]);
    }
}
