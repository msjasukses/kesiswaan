<?php

namespace App\Http\Controllers;

use App\Models\ProgramKerja;
use App\Support\Exporter;
use Illuminate\Http\Request;

class ProgramKerjaController extends Controller
{
    public function index(Request $request)
    {
        $program = $this->filteredQuery($request)->paginate(15)->withQueryString();

        return view('program_kerja.index', compact('program'));
    }

    private function filteredQuery(Request $request)
    {
        $query = ProgramKerja::query();

        if ($search = $request->input('q')) {
            $query->where(fn ($q) => $q->where('nama', 'like', "%{$search}%")
                ->orWhere('bidang', 'like', "%{$search}%"));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return $query->latest('tanggal_mulai');
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['excel', 'word', 'pdf'], true), 404);

        $headers = ['No', 'Nama Program', 'Bidang', 'Penanggung Jawab', 'Tanggal Mulai', 'Tanggal Selesai', 'Anggaran', 'Status', 'Deskripsi'];

        $rows = $this->filteredQuery($request)->get()->values()->map(fn ($p, $i) => [
            $i + 1,
            $p->nama,
            $p->bidang,
            $p->penanggung_jawab,
            optional($p->tanggal_mulai)->format('d/m/Y'),
            optional($p->tanggal_selesai)->format('d/m/Y'),
            rupiah($p->anggaran),
            ucfirst($p->status),
            $p->deskripsi,
        ])->all();

        $filename = 'program-kerja-' . now()->format('Ymd-His');

        return Exporter::{$format}($filename, 'Data Program Kerja', $headers, $rows);
    }

    public function create()
    {
        return view('program_kerja.create', ['program' => new ProgramKerja()]);
    }

    public function store(Request $request)
    {
        ProgramKerja::create($this->validateData($request));

        return redirect()->route('program-kerja.index')->with('success', 'Program kerja berhasil ditambahkan.');
    }

    public function edit(ProgramKerja $programKerja)
    {
        return view('program_kerja.edit', ['program' => $programKerja]);
    }

    public function update(Request $request, ProgramKerja $programKerja)
    {
        $programKerja->update($this->validateData($request));

        return redirect()->route('program-kerja.index')->with('success', 'Program kerja berhasil diperbarui.');
    }

    public function destroy(ProgramKerja $programKerja)
    {
        $programKerja->delete();

        return redirect()->route('program-kerja.index')->with('success', 'Program kerja berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string'],
            'penanggung_jawab' => ['nullable', 'string', 'max:150'],
            'bidang' => ['nullable', 'string', 'max:100'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'anggaran' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:rencana,berjalan,selesai,batal'],
        ]);
    }
}
