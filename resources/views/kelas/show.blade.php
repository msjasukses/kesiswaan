@extends('layouts.admin')

@section('title', 'Detail Kelas ' . $kelas->nama_rombel)

@section('content')
    <div class="mb-5">
        <a href="{{ route('kelas.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke daftar kelas
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card :title="'Kelas ' . $kelas->nama_rombel">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Tingkat</dt><dd class="font-medium text-slate-700">{{ $kelas->tingkat }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Jurusan</dt><dd class="font-medium text-slate-700">{{ $kelas->jurusan?->nama_jurusan ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Wali Kelas</dt><dd class="font-medium text-right text-slate-700">{{ $kelas->waliKelas?->nama_ptk ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Tahun Ajaran</dt><dd class="font-medium text-slate-700">{{ $kelas->tahunAjaran?->nama_tahun_ajaran ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Jumlah Siswa</dt><dd class="font-medium text-slate-700">{{ $anggota->count() }}</dd></div>
            </dl>
        </x-card>

        <div class="lg:col-span-2">
            <x-card title="Daftar Siswa" flush>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 font-semibold">NIS</th>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">L/P</th>
                                <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($anggota as $s)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $s->nis }}</td>
                                    <td class="px-5 py-3 font-medium text-slate-800">{{ $s->nama_siswa }}</td>
                                    <td class="px-5 py-3">{{ $s->jenis_kelamin }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('siswa.show', $s) }}" class="text-xs font-medium text-indigo-600 hover:underline">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Belum ada siswa.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection
