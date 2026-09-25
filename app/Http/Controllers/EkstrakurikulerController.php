<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\EkstrakurikulerSiswa;
use App\Models\Siswa;
use App\Support\Exporter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EkstrakurikulerController extends Controller
{
    public function index(Request $request)
    {
        $ekskul = $this->filteredQuery($request)->paginate(15)->withQueryString();

        return view('ekstrakurikuler.index', compact('ekskul'));
    }

    private function filteredQuery(Request $request)
    {
        $query = Ekstrakurikuler::withCount('anggotaPivot as siswa_count');

        if ($search = $request->input('q')) {
            $query->where(fn ($q) => $q->where('nama', 'like', "%{$search}%")
                ->orWhere('pembina', 'like', "%{$search}%"));
        }

        return $query->orderBy('nama');
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['excel', 'word', 'pdf'], true), 404);

        $headers = ['No', 'Nama Ekstrakurikuler', 'Pembina', 'Jadwal', 'Tempat', 'Jumlah Anggota', 'Deskripsi'];

        $rows = $this->filteredQuery($request)->get()->values()->map(fn ($e, $i) => [
            $i + 1,
            $e->nama,
            $e->pembina,
            $e->jadwal,
            $e->tempat,
            $e->siswa_count,
            $e->deskripsi,
        ])->all();

        $filename = 'ekstrakurikuler-' . now()->format('Ymd-His');

        return Exporter::{$format}($filename, 'Data Ekstrakurikuler', $headers, $rows);
    }

    public function create()
    {
        return view('ekstrakurikuler.create', ['ekskul' => new Ekstrakurikuler()]);
    }

    public function store(Request $request)
    {
        Ekstrakurikuler::create($this->validateData($request));

        return redirect()->route('ekstrakurikuler.index')->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }

    public function show(Ekstrakurikuler $ekstrakurikuler)
    {
        $ta = current_ta_id();
        $anggota = $ekstrakurikuler->anggota();
        $memberIds = $anggota->pluck('id')->all();

        $calonAnggota = Siswa::whereHas('siswaRombel', fn ($q) => $q->where('tahun_ajaran_id', $ta))
            ->whereNotIn('id', $memberIds)
            ->with(['siswaRombel' => fn ($q) => $q->where('tahun_ajaran_id', $ta)->with('kelas')])
            ->orderBy('nama_siswa')->get();

        return view('ekstrakurikuler.show', compact('ekstrakurikuler', 'anggota', 'calonAnggota'));
    }

    public function edit(Ekstrakurikuler $ekstrakurikuler)
    {
        return view('ekstrakurikuler.edit', ['ekskul' => $ekstrakurikuler]);
    }

    public function update(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $ekstrakurikuler->update($this->validateData($request));

        return redirect()->route('ekstrakurikuler.index')->with('success', 'Ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy(Ekstrakurikuler $ekstrakurikuler)
    {
        $ekstrakurikuler->anggotaPivot()->delete();
        $ekstrakurikuler->delete();

        return redirect()->route('ekstrakurikuler.index')->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }

    public function addAnggota(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $data = $request->validate([
            'siswa_id' => ['required', Rule::exists('datacenter.siswa', 'id')],
            'jabatan' => ['required', 'string', 'max:50'],
        ]);

        EkstrakurikulerSiswa::updateOrCreate(
            ['ekstrakurikuler_id' => $ekstrakurikuler->id, 'siswa_id' => $data['siswa_id']],
            ['jabatan' => $data['jabatan']],
        );

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function removeAnggota(Ekstrakurikuler $ekstrakurikuler, $siswa)
    {
        $ekstrakurikuler->anggotaPivot()->where('siswa_id', $siswa)->delete();

        return back()->with('success', 'Anggota berhasil dikeluarkan.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'pembina' => ['nullable', 'string', 'max:150'],
            'jadwal' => ['nullable', 'string', 'max:150'],
            'tempat' => ['nullable', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
        ]);
    }
}
