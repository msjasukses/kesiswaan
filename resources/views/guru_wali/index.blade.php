@extends('layouts.admin')

@section('title', 'Guru Wali')

@section('content')
    <div class="flex flex-col gap-3 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari perwalian / guru..."
                class="w-full text-sm rounded-lg shadow-sm sm:w-64 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <button class="px-3 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Cari</button>
        </form>
        <div class="flex items-center gap-2">
            <x-export-menu
                :excel-url="route('guru-wali.export', array_merge(['format' => 'excel'], request()->query()))"
                :word-url="route('guru-wali.export', array_merge(['format' => 'word'], request()->query()))"
                :pdf-url="route('guru-wali.export', array_merge(['format' => 'pdf'], request()->query()))" />
            <a href="{{ route('guru-wali.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Guru Wali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($guruWali as $w)
            <div class="flex flex-col p-5 bg-white border shadow-sm rounded-xl border-slate-200">
                <div class="flex items-start justify-between">
                    <div class="flex items-center justify-center w-11 h-11 text-white rounded-lg bg-sky-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <x-badge color="blue">{{ $w->siswa_count }} siswa asuh</x-badge>
                </div>
                <h3 class="mt-3 font-semibold text-slate-800">{{ $w->nama }}</h3>
                <dl class="mt-2 space-y-1 text-sm text-slate-500">
                    <p>Guru Wali: <span class="text-slate-700">{{ $w->guru?->nama_ptk ?? '-' }}</span></p>
                    <p>Jadwal: <span class="text-slate-700">{{ $w->jadwal ?? '-' }}</span></p>
                </dl>
                <div class="flex items-center gap-2 pt-4 mt-auto border-t border-slate-100">
                    <a href="{{ route('guru-wali.show', $w) }}" class="flex-1 px-3 py-1.5 text-sm font-medium text-center text-indigo-600 rounded-lg bg-indigo-50 hover:bg-indigo-100">Kelola Siswa Asuh</a>
                    <a href="{{ route('guru-wali.edit', $w) }}" class="p-2 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-50" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form method="POST" action="{{ route('guru-wali.destroy', $w) }}" onsubmit="return confirm('Hapus perwalian {{ $w->nama }}?')">
                        @csrf @method('DELETE')
                        <button class="p-2 rounded-lg text-slate-500 hover:text-red-600 hover:bg-slate-50" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="py-10 text-sm text-center col-span-full text-slate-400">Belum ada guru wali.</div>
        @endforelse
    </div>

    @if ($guruWali->hasPages())
        <div class="mt-5">{{ $guruWali->links() }}</div>
    @endif
@endsection
