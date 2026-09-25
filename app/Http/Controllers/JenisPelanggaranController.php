<?php

namespace App\Http\Controllers;

use App\Models\JenisPelanggaran;
use Illuminate\Http\Request;

class JenisPelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $query = JenisPelanggaran::withCount('dataPelanggaran');

        if ($search = $request->input('q')) {
            $query->where('nama', 'like', "%{$search}%");
        }

        $jenis = $query->orderBy('nama')->paginate(15)->withQueryString();

        return view('jenis_pelanggaran.index', compact('jenis'));
    }

    public function create()
    {
        return view('jenis_pelanggaran.create', ['jenis' => new JenisPelanggaran()]);
    }

    public function store(Request $request)
    {
        JenisPelanggaran::create($this->validateData($request));

        return redirect()->route('jenis-pelanggaran.index')->with('success', 'Jenis pelanggaran berhasil ditambahkan.');
    }

    public function edit(JenisPelanggaran $jenisPelanggaran)
    {
        return view('jenis_pelanggaran.edit', ['jenis' => $jenisPelanggaran]);
    }

    public function update(Request $request, JenisPelanggaran $jenisPelanggaran)
    {
        $jenisPelanggaran->update($this->validateData($request));

        return redirect()->route('jenis-pelanggaran.index')->with('success', 'Jenis pelanggaran berhasil diperbarui.');
    }

    public function destroy(JenisPelanggaran $jenisPelanggaran)
    {
        $jenisPelanggaran->delete();

        return redirect()->route('jenis-pelanggaran.index')->with('success', 'Jenis pelanggaran berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
        ]);
    }
}
