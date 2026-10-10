@extends('layouts.admin')

@section('title', 'Buku Induk')

@section('content')
    <div class="flex flex-col gap-3 mb-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / NIS / NISN..."
                class="w-full text-sm rounded-lg shadow-sm sm:w-56 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <select name="kelas_id" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Kelas</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama_rombel }}</option>
                @endforeach
            </select>
            <select name="kurikulum" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Kurikulum</option>
                @foreach (config('buku_induk.kurikulum') as $kode => $label)
                    <option value="{{ $kode }}" @selected(request('kurikulum') === $kode)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="px-3 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Filter</button>
        </form>
        <div class="flex items-center gap-3">
            <p class="hidden text-xs text-slate-400 sm:block">
                T.A. {{ optional(current_tahun_ajaran())->nama_tahun_ajaran ?? '-' }} &middot; Default: {{ config("buku_induk.kurikulum.{$defaultKurikulum}") }}
            </p>
            <a href="{{ route('buku-induk.import') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import Nilai
            </a>
            <x-export-menu
                :excel-url="route('buku-induk.export', array_merge(['format' => 'excel'], request()->query()))"
                :word-url="route('buku-induk.export', array_merge(['format' => 'word'], request()->query()))"
                :pdf-url="route('buku-induk.export', array_merge(['format' => 'pdf'], request()->query()))" />
        </div>
    </div>

    <x-card flush>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold">No</th>
                        <th class="px-4 py-3 font-semibold">NIS / NISN</th>
                        <th class="px-4 py-3 font-semibold">Nama Siswa</th>
                        <th class="px-4 py-3 font-semibold">L/P</th>
                        <th class="px-4 py-3 font-semibold">Kelas</th>
                        <th class="px-4 py-3 font-semibold">Kurikulum</th>
                        <th class="px-4 py-3 font-semibold text-center">Semester Tercatat</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($siswa as $s)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-400">{{ $siswa->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $s->nis ?? '-' }}<br>{{ $s->nisn }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-slate-800">{{ $s->nama_siswa }}</span>
                                @unless ($s->bukuInduk->exists)
                                    <span class="block text-xs text-slate-400">Belum diisi</span>
                                @endunless
                            </td>
                            <td class="px-4 py-3">{{ $s->jenis_kelamin }}</td>
                            <td class="px-4 py-3">{{ $s->kelas?->nama_rombel ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <x-badge :color="$s->bukuInduk->isK13() ? 'yellow' : 'indigo'">{{ $s->bukuInduk->kurikulum_label }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-center">{{ (int) $s->bukuInduk->semester_count }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <x-action-button :href="route('buku-induk.show', $s)" icon="book">Buka</x-action-button>
                                    <x-action-button :href="route('buku-induk.pdf', $s)" variant="secondary" icon="printer" target="_blank">Cetak</x-action-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-10 text-center text-slate-400">Tidak ada siswa untuk kriteria ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($siswa->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $siswa->links() }}</div>
        @endif
    </x-card>
@endsection
