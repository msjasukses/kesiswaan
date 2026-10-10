@extends('layouts.admin')

@section('title', 'Import Nilai Rapor')

@use('App\Support\RaporSpreadsheet')

@php
    $pesanFile = $errors->get('file');
@endphp

@section('content')
    <div class="mb-5">
        <a href="{{ route('buku-induk.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Buku Induk
        </a>
    </div>

    @if ($pesanFile)
        <div class="p-4 mb-5 text-sm text-red-700 border border-red-200 rounded-lg bg-red-50">
            <p class="font-semibold">Import dibatalkan — tidak ada data yang disimpan. Perbaiki {{ count($pesanFile) }} kesalahan berikut lalu unggah ulang:</p>
            <ul class="mt-2 space-y-1 overflow-y-auto list-disc list-inside max-h-72">
                @foreach (array_slice($pesanFile, 0, 100) as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
            @if (count($pesanFile) > 100)
                <p class="mt-2 text-xs">… dan {{ count($pesanFile) - 100 }} kesalahan lainnya.</p>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3" x-data="{ kurikulum: @js(old('kurikulum', $defaultKurikulum)) }">
        <form method="POST" action="{{ route('buku-induk.import.store') }}" enctype="multipart/form-data" class="lg:col-span-2" x-ref="form">
            @csrf

            <x-card title="Pengaturan Import">
                <div class="grid grid-cols-1 gap-3 mb-5 sm:grid-cols-2">
                    @foreach (config('buku_induk.kurikulum') as $kode => $label)
                        <label class="flex items-start gap-3 p-4 border rounded-lg cursor-pointer"
                            :class="kurikulum === @js($kode) ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="kurikulum" value="{{ $kode }}" x-model="kurikulum" class="mt-0.5 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                            <span>
                                <span class="block text-sm font-semibold text-slate-800">{{ $label }}</span>
                                <span class="block text-xs text-slate-500">{{ config("buku_induk.keterangan.{$kode}") }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-select name="kelas_id" label="Kelas" required x-ref="kelas">
                        <option value="">- Pilih Kelas -</option>
                        @foreach ($daftarKelas as $k)
                            <option value="{{ $k->id }}" @selected(old('kelas_id') == $k->id)>{{ $k->nama_rombel }}</option>
                        @endforeach
                    </x-select>
                    <x-select name="semester" label="Semester" required x-ref="semester">
                        <option value="1" @selected(old('semester', $defaultSemester) == 1)>1 (Ganjil)</option>
                        <option value="2" @selected(old('semester', $defaultSemester) == 2)>2 (Genap)</option>
                    </x-select>
                    <div>
                        <span class="block mb-1 text-sm font-medium text-slate-700">Tahun Pelajaran</span>
                        <p class="px-3 py-2 text-sm border rounded-lg bg-slate-50 border-slate-200 text-slate-600">{{ optional(current_tahun_ajaran())->nama_tahun_ajaran ?? '-' }}</p>
                        <p class="mt-1 text-xs text-slate-400">Mengikuti T.A. yang dipilih di bagian atas.</p>
                    </div>
                </div>

                <div class="p-4 mt-5 border border-dashed rounded-lg border-slate-300 bg-slate-50">
                    <label for="file" class="block mb-2 text-sm font-medium text-slate-700">File Excel Nilai <span class="text-red-500">*</span></label>
                    <input type="file" name="file" id="file" accept=".xlsx,.xls" required
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-2 text-xs text-slate-400">Format .xlsx atau .xls, maks. 5 MB. Gunakan template yang diunduh dengan pilihan yang sama.</p>
                </div>

                <label class="flex items-start gap-2 mt-4 text-sm text-slate-600">
                    <input type="hidden" name="perbarui_kurikulum" value="0">
                    <input type="checkbox" name="perbarui_kurikulum" value="1" @checked(old('perbarui_kurikulum', '1') == '1')
                        class="mt-0.5 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                    <span>Jadikan kurikulum ini sebagai format buku induk siswa yang diimpor
                        <span class="block text-xs text-slate-400">Siswa yang belum memiliki buku induk selalu memakai kurikulum yang dipilih.</span>
                    </span>
                </label>

                <div class="flex flex-col-reverse gap-3 pt-5 mt-5 border-t sm:flex-row sm:items-center sm:justify-between border-slate-100">
                    <button type="button"
                        @click="if (! $refs.kelas.value) { alert('Pilih kelas terlebih dahulu.'); $refs.kelas.focus(); return; }
                                window.location = @js(route('buku-induk.import.template')) + '?' + new URLSearchParams({ kurikulum, kelas_id: $refs.kelas.value, semester: $refs.semester.value })"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Unduh Template
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import Nilai
                    </button>
                </div>
            </x-card>
        </form>

        <div class="space-y-6">
            <x-card title="Langkah Import">
                <ol class="space-y-3 text-sm list-decimal list-inside text-slate-600">
                    <li>Pilih kurikulum, kelas, dan semester, lalu klik <span class="font-medium text-slate-800">Unduh Template</span>. Template sudah berisi daftar siswa &amp; mata pelajaran (atau nilai yang pernah diisi).</li>
                    <li>Isi sheet <span class="font-medium text-slate-800">Nilai</span> dan <span class="font-medium text-slate-800">Rekap</span>. Jangan ubah judul kolom &amp; sheet Petunjuk.</li>
                    <li>Unggah file dengan pilihan yang sama, lalu klik <span class="font-medium text-slate-800">Import Nilai</span>.</li>
                    <li>Seluruh file diperiksa terlebih dahulu. Bila ada kesalahan, tidak ada data yang disimpan.</li>
                    <li>Import ulang semester yang sama akan menimpa nilai sebelumnya.</li>
                </ol>
            </x-card>

            @foreach (config('buku_induk.kurikulum') as $kode => $label)
                <div x-show="kurikulum === @js($kode)" @if ($kode !== old('kurikulum', $defaultKurikulum)) x-cloak @endif>
                    <x-card :title="'Kolom Template — ' . $label">
                        <p class="mb-2 text-xs font-semibold uppercase text-slate-500">Sheet Nilai</p>
                        <div class="flex flex-wrap gap-1.5 mb-4">
                            @foreach (RaporSpreadsheet::kolomNilai($kode) as $judul)
                                <x-badge color="indigo">{{ $judul }}</x-badge>
                            @endforeach
                        </div>
                        <p class="mb-2 text-xs font-semibold uppercase text-slate-500">Sheet Rekap</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach (RaporSpreadsheet::kolomRekap($kode) as $judul)
                                <x-badge>{{ $judul }}</x-badge>
                            @endforeach
                        </div>
                        @if ($kode === 'K13')
                            <p class="mt-4 text-xs text-slate-400">Predikat A–D dihitung otomatis dari KKM.</p>
                        @endif
                    </x-card>
                </div>
            @endforeach
        </div>
    </div>
@endsection
