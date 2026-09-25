<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    /** Simpan pilihan tahun ajaran aktif ke session. */
    public function set(Request $request)
    {
        $data = $request->validate([
            'tahun_ajaran_id' => ['required', 'integer'],
        ]);

        abort_unless(TahunAjaran::whereKey($data['tahun_ajaran_id'])->exists(), 404);

        session(['tahun_ajaran_id' => $data['tahun_ajaran_id']]);

        return back()->with('success', 'Tahun ajaran diubah.');
    }
}
