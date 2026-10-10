@extends('layouts.admin')

@section('title', 'Setelan Aplikasi')

@section('content')
    <div class="max-w-4xl">
        <form method="POST" action="{{ route('setelan.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PUT')

            <x-card title="Identitas Sekolah">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-field name="nama_sekolah" label="Nama Sekolah" :value="old('nama_sekolah', $settings['nama_sekolah'] ?? '')" required />
                    </div>
                    <x-field name="npsn" label="NPSN" :value="old('npsn', $settings['npsn'] ?? '')" />
                    <x-field name="telepon" label="Telepon" :value="old('telepon', $settings['telepon'] ?? '')" />
                    <x-field name="email" label="Email" type="email" :value="old('email', $settings['email'] ?? '')" />
                    <x-field name="website" label="Website" :value="old('website', $settings['website'] ?? '')" />
                    <div class="md:col-span-2">
                        <x-textarea name="alamat" label="Alamat" :value="old('alamat', $settings['alamat'] ?? '')" rows="2" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-1 text-sm font-medium text-slate-700">Logo Sekolah</label>
                        @if (! empty($settings['logo']))
                            <img src="{{ asset('storage/' . $settings['logo']) }}" class="object-contain w-16 h-16 mb-2 bg-white border rounded-lg border-slate-200">
                        @endif
                        <input type="file" name="logo" accept="image/*"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('logo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-1 text-xs text-slate-400">Format gambar, maks. 2MB.</p>
                    </div>
                </div>
            </x-card>

            <x-card title="Kepala Sekolah & Tahun Ajaran">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-field name="kepala_sekolah" label="Nama Kepala Sekolah" :value="old('kepala_sekolah', $settings['kepala_sekolah'] ?? '')" />
                    <x-field name="nip_kepala_sekolah" label="NIP Kepala Sekolah" :value="old('nip_kepala_sekolah', $settings['nip_kepala_sekolah'] ?? '')" />
                    <x-field name="tahun_ajaran" label="Tahun Ajaran" :value="old('tahun_ajaran', $settings['tahun_ajaran'] ?? '')" placeholder="Contoh: 2025/2026" />
                    <x-select name="semester" label="Semester">
                        <option value="">- Pilih -</option>
                        @foreach (['Ganjil', 'Genap'] as $sem)
                            <option value="{{ $sem }}" @selected(old('semester', $settings['semester'] ?? '') === $sem)>{{ $sem }}</option>
                        @endforeach
                    </x-select>
                    <x-select name="kurikulum_default" label="Kurikulum Default Buku Induk"
                        help="Dipakai untuk siswa yang belum diatur kurikulumnya pada buku induk.">
                        @foreach (config('buku_induk.kurikulum') as $kode => $label)
                            <option value="{{ $kode }}" @selected(old('kurikulum_default', $settings['kurikulum_default'] ?? config('buku_induk.default_kurikulum')) === $kode)>{{ $label }}</option>
                        @endforeach
                    </x-select>
                </div>
            </x-card>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan Setelan</button>
            </div>
        </form>
    </div>
@endsection
