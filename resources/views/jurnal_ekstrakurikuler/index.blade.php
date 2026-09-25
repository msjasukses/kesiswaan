@extends('layouts.admin')

@section('title', 'Jurnal Ekstrakurikuler')

@section('content')
    <div class="flex flex-col gap-3 mb-5 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari materi / pembina / pelatih..."
                class="w-full text-sm rounded-lg shadow-sm sm:w-56 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <select name="ekstrakurikuler_id" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Ekskul</option>
                @foreach ($daftarEkskul as $e)
                    <option value="{{ $e->id }}" @selected(request('ekstrakurikuler_id') == $e->id)>{{ $e->nama }}</option>
                @endforeach
            </select>
            <input type="month" name="bulan" value="{{ request('bulan') }}"
                class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <button class="px-3 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Filter</button>
        </form>
        <div class="flex items-center gap-2">
            <a href="{{ route('laporan-jurnal-ekstrakurikuler.index') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Laporan
            </a>
            <x-export-menu
                :excel-url="route('jurnal-ekstrakurikuler.export', array_merge(['format' => 'excel'], request()->query()))"
                :word-url="route('jurnal-ekstrakurikuler.export', array_merge(['format' => 'word'], request()->query()))"
                :pdf-url="route('jurnal-ekstrakurikuler.export', array_merge(['format' => 'pdf'], request()->query()))" />
            <a href="{{ route('jurnal-ekstrakurikuler.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Jurnal
            </a>
        </div>
    </div>

    <x-card flush>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Foto</th>
                        <th class="px-5 py-3 font-semibold">Tanggal</th>
                        <th class="px-5 py-3 font-semibold">Ekskul &amp; Materi</th>
                        <th class="px-5 py-3 font-semibold">Pembina / Pelatih</th>
                        <th class="px-5 py-3 font-semibold">Kehadiran</th>
                        <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($jurnal as $j)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                @if ($j->foto)
                                    <a href="{{ asset('storage/' . $j->foto) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $j->foto) }}" alt="Dokumentasi {{ $j->materi }}"
                                            class="object-cover w-16 h-12 border rounded-lg border-slate-200">
                                    </a>
                                @else
                                    <span class="flex items-center justify-center w-16 h-12 rounded-lg bg-slate-100 text-slate-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">{{ $j->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <p class="font-medium text-slate-800">{{ $j->materi }}</p>
                                <p class="text-xs text-slate-400">{{ $j->ekstrakurikuler?->nama ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-slate-700">{{ $j->pembina }}</p>
                                <p class="text-xs text-slate-400">Pelatih: {{ $j->pelatih }}</p>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                <span class="text-emerald-600">{{ $j->siswa_hadir }} hadir</span>
                                <span class="text-slate-300">/</span>
                                <span class="text-red-500">{{ $j->siswa_tidak_hadir }} tidak</span>
                                @if ($j->persen_hadir !== null)
                                    <p class="text-xs text-slate-400">{{ $j->persen_hadir }}% kehadiran</p>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('jurnal-ekstrakurikuler.edit', $j) }}" class="p-1.5 rounded text-slate-500 hover:text-indigo-600 hover:bg-indigo-50" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('jurnal-ekstrakurikuler.destroy', $j) }}" onsubmit="return confirm('Hapus jurnal kegiatan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 rounded text-slate-500 hover:text-red-600 hover:bg-red-50" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Belum ada jurnal kegiatan ekstrakurikuler.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($jurnal->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $jurnal->links() }}</div>
        @endif
    </x-card>
@endsection
