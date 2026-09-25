<?php

namespace App\Http\Controllers;

use App\Models\DataPelanggaran;
use App\Models\JenisPelanggaran;
use Illuminate\Http\Request;

class DataPelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $query = DataPelanggaran::with('jenisPelanggaran');

        if ($search = $request->input('q')) {
            $query->where('nama', 'like', "%{$search}%");
        }

        if ($jenisId = $request->input('jenis_pelanggaran_id')) {
            $query->where('jenis_pelanggaran_id', $jenisId);
        }

        $data = $query->orderBy('nama')->paginate(15)->withQueryString();
        $daftarJenis = JenisPelanggaran::orderBy('nama')->get();

        return view('data_pelanggaran.index', compact('data', 'daftarJenis'));
    }

    public function create()
    {
        return view('data_pelanggaran.create', [
            'data' => new DataPelanggaran(),
            'daftarJenis' => JenisPelanggaran::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        DataPelanggaran::create($this->validateData($request));

        return redirect()->route('data-pelanggaran.index')->with('success', 'Data pelanggaran berhasil ditambahkan.');
    }

    public function edit(DataPelanggaran $dataPelanggaran)
    {
        return view('data_pelanggaran.edit', [
            'data' => $dataPelanggaran,
            'daftarJenis' => JenisPelanggaran::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, DataPelanggaran $dataPelanggaran)
    {
        $dataPelanggaran->update($this->validateData($request));

        return redirect()->route('data-pelanggaran.index')->with('success', 'Data pelanggaran berhasil diperbarui.');
    }

    public function destroy(DataPelanggaran $dataPelanggaran)
    {
        $dataPelanggaran->delete();

        return redirect()->route('data-pelanggaran.index')->with('success', 'Data pelanggaran berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'jenis_pelanggaran_id' => ['required', 'exists:jenis_pelanggaran,id'],
            'nama' => ['required', 'string', 'max:200'],
            'poin' => ['required', 'integer', 'min:0', 'max:1000'],
            'deskripsi' => ['nullable', 'string'],
        ]);
    }
}
