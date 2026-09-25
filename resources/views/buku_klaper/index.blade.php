@extends('layouts.admin')

@section('title', 'Buku Klaper')

@section('content')
    <div class="flex flex-col gap-3 mb-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="huruf" value="{{ request('huruf') }}">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / NIS / NISN..."
                class="w-full text-sm rounded-lg shadow-sm sm:w-56 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <select name="kelas_id" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Kelas</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama_rombel }}</option>
                @endforeach
            </select>
            <button class="px-3 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Filter</button>
        </form>
        <div class="flex items-center gap-3">
            <p class="hidden text-xs text-slate-400 sm:block">Sumber: Data Center &middot; T.A. {{ optional(current_tahun_ajaran())->nama_tahun_ajaran ?? '-' }}</p>
            <x-export-menu
                :excel-url="route('buku-klaper.export', array_merge(['format' => 'excel'], request()->query()))"
                :word-url="route('buku-klaper.export', array_merge(['format' => 'word'], request()->query()))"
                :pdf-url="route('buku-klaper.export', array_merge(['format' => 'pdf'], request()->query()))" />
        </div>
    </div>

    {{-- Indeks abjad --}}
    @php $qBase = request()->except(['huruf', 'page']); @endphp
    <div class="flex flex-wrap gap-1 mb-5">
        <a href="{{ route('buku-klaper.index', $qBase) }}"
            class="px-2.5 py-1 text-xs font-medium rounded {{ ! request('huruf') ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">Semua</a>
        @foreach ($abjad as $h)
            <a href="{{ route('buku-klaper.index', array_merge($qBase, ['huruf' => $h])) }}"
                class="w-7 py-1 text-xs font-medium text-center rounded {{ request('huruf') === $h ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">{{ $h }}</a>
        @endforeach
    </div>

    <x-card flush>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 font-semibold">No</th>
                        <th class="px-4 py-3 font-semibold">NIS</th>
                        <th class="px-4 py-3 font-semibold">Nama Siswa</th>
                        <th class="px-4 py-3 font-semibold">L/P</th>
                        <th class="px-4 py-3 font-semibold">Tempat, Tgl Lahir</th>
                        <th class="px-4 py-3 font-semibold">Nama Orang Tua</th>
                        <th class="px-4 py-3 font-semibold">Kelas</th>
                        <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($siswa as $s)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-400">{{ $siswa->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $s->nis ?? '-' }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $s->nama_siswa }}</td>
                            <td class="px-4 py-3">{{ $s->jenis_kelamin }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ collect([$s->tempat_lahir, optional($s->tanggal_lahir)->translatedFormat('d M Y')])->filter()->implode(', ') ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ collect([$s->nama_ayah, $s->nama_ibu])->filter()->implode(' / ') ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $s->kelas?->nama_rombel ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('siswa.show', $s) }}" class="text-xs font-medium text-indigo-600 hover:underline">Detail</a>
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
