@extends('layouts.admin')

@section('title', 'Ekstrakurikuler')

@section('content')
    <div class="flex flex-col gap-3 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari ekskul / pembina..."
                class="w-full text-sm rounded-lg shadow-sm sm:w-64 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <button class="px-3 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Cari</button>
        </form>
        <div class="flex items-center gap-2">
            <x-export-menu
                :excel-url="route('ekstrakurikuler.export', array_merge(['format' => 'excel'], request()->query()))"
                :word-url="route('ekstrakurikuler.export', array_merge(['format' => 'word'], request()->query()))"
                :pdf-url="route('ekstrakurikuler.export', array_merge(['format' => 'pdf'], request()->query()))" />
            <a href="{{ route('ekstrakurikuler.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Ekskul
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($ekskul as $e)
            <div class="flex flex-col p-5 bg-white border shadow-sm rounded-xl border-slate-200">
                <div class="flex items-start justify-between">
                    <div class="flex items-center justify-center w-11 h-11 text-white rounded-lg bg-emerald-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <x-badge color="blue">{{ $e->siswa_count }} anggota</x-badge>
                </div>
                <h3 class="mt-3 font-semibold text-slate-800">{{ $e->nama }}</h3>
                <dl class="mt-2 space-y-1 text-sm text-slate-500">
                    <p>Pembina: <span class="text-slate-700">{{ $e->pembina ?? '-' }}</span></p>
                    <p>Jadwal: <span class="text-slate-700">{{ $e->jadwal ?? '-' }}</span></p>
                </dl>
                <div class="flex items-center gap-2 pt-4 mt-auto border-t border-slate-100">
                    <a href="{{ route('ekstrakurikuler.show', $e) }}" class="flex-1 px-3 py-1.5 text-sm font-medium text-center text-indigo-600 rounded-lg bg-indigo-50 hover:bg-indigo-100">Kelola Anggota</a>
                    <a href="{{ route('ekstrakurikuler.edit', $e) }}" class="p-2 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-50" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form method="POST" action="{{ route('ekstrakurikuler.destroy', $e) }}" onsubmit="return confirm('Hapus ekskul {{ $e->nama }}?')">
                        @csrf @method('DELETE')
                        <button class="p-2 rounded-lg text-slate-500 hover:text-red-600 hover:bg-slate-50" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="py-10 text-sm text-center col-span-full text-slate-400">Belum ada ekstrakurikuler.</div>
        @endforelse
    </div>

    @if ($ekskul->hasPages())
        <div class="mt-5">{{ $ekskul->links() }}</div>
    @endif
@endsection
