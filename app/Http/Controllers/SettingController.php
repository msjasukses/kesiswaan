<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /** Daftar key setelan yang dikelola. */
    private array $keys = [
        'nama_sekolah', 'npsn', 'alamat', 'telepon', 'email', 'website',
        'kepala_sekolah', 'nip_kepala_sekolah', 'tahun_ajaran', 'semester', 'kurikulum_default',
    ];

    public function edit()
    {
        $settings = Setting::allValues();

        return view('setelan.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_sekolah' => ['nullable', 'string', 'max:200'],
            'npsn' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'website' => ['nullable', 'string', 'max:150'],
            'kepala_sekolah' => ['nullable', 'string', 'max:150'],
            'nip_kepala_sekolah' => ['nullable', 'string', 'max:50'],
            'tahun_ajaran' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'in:Ganjil,Genap'],
            'kurikulum_default' => ['nullable', 'in:' . implode(',', array_keys(config('buku_induk.kurikulum')))],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        foreach ($this->keys as $key) {
            Setting::put($key, $data[$key] ?? null);
        }

        if ($request->hasFile('logo')) {
            $old = Setting::get('logo');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            Setting::put('logo', $request->file('logo')->store('logo', 'public'));
        }

        return redirect()->route('setelan.index')->with('success', 'Setelan berhasil disimpan.');
    }
}
