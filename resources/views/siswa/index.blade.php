@extends('layouts.admin')

@section('title', 'Data Siswa')

@section('content')
    <div class="flex flex-col gap-3 mb-5 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / NIS / NISN..."
                class="text-sm rounded-lg shadow-sm w-full sm:w-56 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <select name="kelas_id" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Kelas</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama_rombel }}</option>
                @endforeach
            </select>
            <select name="status" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                @foreach (['Aktif', 'Lulus', 'Keluar'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="px-3 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Filter</button>
        </form>
        <div class="flex items-center gap-3">
            <p class="hidden text-xs text-slate-400 sm:block">Sumber: Data Center &middot; T.A. {{ optional(current_tahun_ajaran())->nama_tahun_ajaran ?? '-' }}</p>
            <x-export-menu
                :excel-url="route('siswa.export', array_merge(['format' => 'excel'], request()->query()))"
                :word-url="route('siswa.export', array_merge(['format' => 'word'], request()->query()))"
                :pdf-url="route('siswa.export', array_merge(['format' => 'pdf'], request()->query()))" />
        </div>
    </div>

    <x-card flush>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 font-semibold">NIS</th>
                        <th class="px-5 py-3 font-semibold">Nama</th>
                        <th class="px-5 py-3 font-semibold">Kelas</th>
                        <th class="px-5 py-3 font-semibold">L/P</th>
                        <th class="px-5 py-3 font-semibold text-center">Poin</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($siswa as $s)
                        @php
                            $statusColor = ['Aktif' => 'green', 'Lulus' => 'blue', 'Keluar' => 'red'][$s->status_siswa] ?? 'slate';
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $s->nis ?? '-' }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $s->nama_siswa }}</td>
                            <td class="px-5 py-3">{{ $s->kelas?->nama_rombel ?? '-' }}</td>
                            <td class="px-5 py-3">{{ $s->jenis_kelamin }}</td>
                            <td class="px-5 py-3 text-center">
                                <x-badge :color="$s->total_poin > 0 ? 'red' : 'slate'">{{ (int) $s->total_poin }}</x-badge>
                            </td>
                            <td class="px-5 py-3"><x-badge :color="$statusColor">{{ $s->status_siswa }}</x-badge></td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('siswa.show', $s) }}" class="text-xs font-medium text-indigo-600 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Tidak ada siswa pada tahun ajaran ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($siswa->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $siswa->links() }}</div>
        @endif
    </x-card>
@endsection
