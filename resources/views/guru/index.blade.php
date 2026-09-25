@extends('layouts.admin')

@section('title', 'Data Guru')

@section('content')
    <div class="flex flex-col gap-3 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / NIP / jabatan..."
                class="w-full text-sm rounded-lg shadow-sm sm:w-56 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <select name="status" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
            </select>
            <button class="px-3 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Filter</button>
        </form>
        <p class="text-xs text-slate-400">Sumber: Data Center</p>
    </div>

    <x-card flush>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 font-semibold">NIP</th>
                        <th class="px-5 py-3 font-semibold">Nama</th>
                        <th class="px-5 py-3 font-semibold">L/P</th>
                        <th class="px-5 py-3 font-semibold">Jabatan</th>
                        <th class="px-5 py-3 font-semibold">Mata Pelajaran</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($guru as $g)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $g->nip ?? '-' }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $g->nama_ptk }}</td>
                            <td class="px-5 py-3">{{ $g->jenis_kelamin ?? '-' }}</td>
                            <td class="px-5 py-3">{{ $g->jabatan ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $g->mataPelajaran?->nama_mapel ?? '-' }}</td>
                            <td class="px-5 py-3"><x-badge :color="$g->is_aktif ? 'green' : 'slate'">{{ $g->is_aktif ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('guru.show', $g) }}" class="text-xs font-medium text-indigo-600 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Belum ada data guru.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($guru->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $guru->links() }}</div>
        @endif
    </x-card>
@endsection
