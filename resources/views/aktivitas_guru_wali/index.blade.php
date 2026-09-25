@extends('layouts.admin')

@section('title', 'Aktivitas Guru Wali')

@php
    $warnaStatus = ['Selesai' => 'green', 'Proses' => 'yellow', 'Perlu Tindak Lanjut' => 'red'];
@endphp

@section('content')
    <div class="flex flex-col gap-3 mb-5 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari aktivitas / siswa..."
                class="w-full text-sm rounded-lg shadow-sm sm:w-52 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <select name="guru_wali_id" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Perwalian</option>
                @foreach ($daftarWali as $w)
                    <option value="{{ $w->id }}" @selected(request('guru_wali_id') == $w->id)>{{ $w->nama }}</option>
                @endforeach
            </select>
            <select name="kategori" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Kategori</option>
                @foreach (\App\Http\Controllers\AktivitasGuruWaliController::KATEGORI as $k)
                    <option value="{{ $k }}" @selected(request('kategori') === $k)>{{ $k }}</option>
                @endforeach
            </select>
            <select name="status" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                @foreach (\App\Http\Controllers\AktivitasGuruWaliController::STATUS as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <input type="month" name="bulan" value="{{ request('bulan') }}"
                class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <button class="px-3 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Filter</button>
        </form>
        <div class="flex items-center gap-2">
            <a href="{{ route('laporan-aktivitas-guru-wali.index') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Laporan
            </a>
            <x-export-menu
                :excel-url="route('aktivitas-guru-wali.export', array_merge(['format' => 'excel'], request()->query()))"
                :word-url="route('aktivitas-guru-wali.export', array_merge(['format' => 'word'], request()->query()))"
                :pdf-url="route('aktivitas-guru-wali.export', array_merge(['format' => 'pdf'], request()->query()))" />
            <a href="{{ route('aktivitas-guru-wali.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Aktivitas
            </a>
        </div>
    </div>

    <x-card flush>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Tanggal</th>
                        <th class="px-5 py-3 font-semibold">Siswa Binaan</th>
                        <th class="px-5 py-3 font-semibold">Kategori</th>
                        <th class="px-5 py-3 font-semibold">Aktivitas</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($aktivitas as $a)
                        @php $s = $siswa->get($a->siswa_id); @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 whitespace-nowrap">{{ $a->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <p class="font-medium text-slate-800">{{ $s?->nama_siswa ?? '-' }}</p>
                                <p class="text-xs text-slate-400">{{ $s?->kelas?->nama_rombel ?? '-' }} &middot; {{ $a->guruWali?->nama ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-3"><x-badge color="indigo">{{ $a->kategori }}</x-badge></td>
                            <td class="px-5 py-3 max-w-sm">
                                <p class="text-slate-700">{{ Str::limit($a->aktivitas, 90) }}</p>
                                @if ($a->tindak_lanjut)
                                    <p class="text-xs text-slate-400">Tindak lanjut: {{ Str::limit($a->tindak_lanjut, 60) }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3"><x-badge :color="$warnaStatus[$a->status] ?? 'slate'">{{ $a->status }}</x-badge></td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('aktivitas-guru-wali.edit', $a) }}" class="p-1.5 rounded text-slate-500 hover:text-indigo-600 hover:bg-indigo-50" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('aktivitas-guru-wali.destroy', $a) }}" onsubmit="return confirm('Hapus aktivitas ini?')">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 rounded text-slate-500 hover:text-red-600 hover:bg-red-50" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Belum ada aktivitas guru wali.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($aktivitas->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $aktivitas->links() }}</div>
        @endif
    </x-card>
@endsection
