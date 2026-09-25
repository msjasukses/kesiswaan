<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;

/**
 * Kelas (rombongan belajar) — data master read-only dari Data Center.
 */
class KelasController extends Controller
{
    public function index(Request $request)
    {
        $ta = current_ta_id();

        $query = Kelas::forTahunAjaran($ta)
            ->with(['waliKelas', 'jurusan'])
            ->withCount('siswa');

        if ($search = $request->input('q')) {
            $query->where('nama_rombel', 'like', "%{$search}%");
        }

        $kelas = $query->orderBy('tingkat')->orderBy('nama_rombel')->paginate(15)->withQueryString();

        return view('kelas.index', compact('kelas'));
    }

    public function show(Kelas $kelas)
    {
        $kelas->load(['waliKelas', 'jurusan', 'tahunAjaran', 'siswa']);
        $anggota = $kelas->siswa->sortBy('nama_siswa');

        return view('kelas.show', compact('kelas', 'anggota'));
    }
}
