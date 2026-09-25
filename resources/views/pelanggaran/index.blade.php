@extends('layouts.admin')

@section('title', 'Pencatatan Pelanggaran')

@section('content')
    <div class="flex flex-col gap-3 mb-5 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari siswa / NIS..."
                class="w-full text-sm rounded-lg shadow-sm sm:w-52 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <select name="kelas_id" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Kelas</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>
                @endforeach
            </select>
            <select name="bulan" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Bulan</option>
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}" @selected(request('bulan') == $m)>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <button class="px-3 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Filter</button>
        </form>
        <div class="flex items-center gap-2">
            <x-export-menu
                :excel-url="route('pelanggaran.export', array_merge(['format' => 'excel'], request()->query()))"
                :word-url="route('pelanggaran.export', array_merge(['format' => 'word'], request()->query()))"
                :pdf-url="route('pelanggaran.export', array_merge(['format' => 'pdf'], request()->query()))" />
            <a href="{{ route('pelanggaran.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Catat Pelanggaran
            </a>
        </div>
    </div>

    <x-card flush>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Tanggal</th>
                        <th class="px-5 py-3 font-semibold">Siswa</th>
                        <th class="px-5 py-3 font-semibold">Pelanggaran</th>
                        <th class="px-5 py-3 font-semibold text-center">Poin</th>
                        <th class="px-5 py-3 font-semibold">Pencatat</th>
                        <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pelanggaran as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 whitespace-nowrap">{{ $p->tanggal?->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <p class="font-medium text-slate-800">{{ $p->siswa->nama }}</p>
                                <p class="text-xs text-slate-400">{{ $p->siswa->kelas?->nama ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-slate-700">{{ $p->dataPelanggaran->nama }}</p>
                                <p class="text-xs text-slate-400">{{ $p->dataPelanggaran->jenisPelanggaran->nama }}</p>
                            </td>
                            <td class="px-5 py-3 text-center"><x-badge color="red">+{{ $p->poin }}</x-badge></td>
                            <td class="px-5 py-3 text-slate-500">{{ $p->pencatat?->name ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('pelanggaran.edit', $p) }}" class="p-1.5 rounded text-slate-500 hover:text-indigo-600 hover:bg-indigo-50" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('pelanggaran.destroy', $p) }}" onsubmit="return confirm('Hapus catatan pelanggaran ini?')">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 rounded text-slate-500 hover:text-red-600 hover:bg-red-50" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Belum ada pencatatan pelanggaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pelanggaran->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $pelanggaran->links() }}</div>
        @endif
    </x-card>
@endsection
