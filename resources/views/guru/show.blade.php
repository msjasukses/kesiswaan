@extends('layouts.admin')

@section('title', 'Detail Guru')

@section('content')
    <div class="mb-5">
        <a href="{{ route('guru.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    <div class="max-w-2xl">
        <x-card>
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-20 h-20 text-2xl font-bold rounded-full text-emerald-600 bg-emerald-100">
                    {{ strtoupper(substr($guru->nama_ptk, 0, 1)) }}
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">{{ $guru->nama_ptk }}</h3>
                    <p class="text-sm text-slate-500">{{ $guru->jabatan ?? 'Guru' }}</p>
                    <div class="mt-1">
                        <x-badge :color="$guru->is_aktif ? 'green' : 'slate'">{{ $guru->is_aktif ? 'Aktif' : 'Nonaktif' }}</x-badge>
                    </div>
                </div>
            </div>

            <dl class="grid grid-cols-1 gap-x-6 gap-y-2 pt-5 mt-5 text-sm border-t sm:grid-cols-2 border-slate-100">
                <div class="flex justify-between"><dt class="text-slate-500">NIP</dt><dd class="font-medium text-slate-700">{{ $guru->nip ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Jenis Kelamin</dt><dd class="font-medium text-slate-700">{{ $guru->jenis_kelamin_label }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Mata Pelajaran</dt><dd class="font-medium text-slate-700">{{ $guru->mataPelajaran?->nama_mapel ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Status Kepegawaian</dt><dd class="font-medium text-slate-700">{{ $guru->status_kepegawaian ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">No. HP</dt><dd class="font-medium text-slate-700">{{ $guru->nomor_hp ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd class="font-medium text-slate-700">{{ $guru->email ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Tempat, Tgl Lahir</dt><dd class="font-medium text-right text-slate-700">{{ $guru->tempat_lahir ?? '-' }}{{ $guru->tanggal_lahir ? ', ' . $guru->tanggal_lahir->translatedFormat('d M Y') : '' }}</dd></div>
                <div class="flex justify-between sm:col-span-2"><dt class="text-slate-500">Alamat</dt><dd class="font-medium text-right text-slate-700">{{ $guru->alamat ?? '-' }}</dd></div>
            </dl>
        </x-card>
    </div>
@endsection
