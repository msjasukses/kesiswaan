<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;

/**
 * Data Guru — master read-only dari Data Center.
 */
class GuruController extends Controller
{
    public function index(Request $request)
    {
        $query = Guru::with('mataPelajaran');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_ptk', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_aktif', $request->input('status') === 'aktif');
        }

        $guru = $query->orderBy('nama_ptk')->paginate(15)->withQueryString();

        return view('guru.index', compact('guru'));
    }

    public function show(Guru $guru)
    {
        $guru->load('mataPelajaran');

        return view('guru.show', compact('guru'));
    }
}
