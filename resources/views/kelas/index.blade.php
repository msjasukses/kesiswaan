@extends('layouts.admin')

@section('title', 'Data Kelas')

@section('content')
    <div class="flex flex-col gap-3 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama kelas..."
                    class="w-full text-sm rounded-lg shadow-sm sm:w-64 border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                <button class="px-3 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Cari</button>
            </form>
        </div>
        <p class="text-xs text-slate-400">Sumber: Data Center &middot; T.A. {{ optional(current_tahun_ajaran())->nama_tahun_ajaran ?? '-' }}</p>
    </div>

    <x-card flush>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Nama Kelas</th>
                        <th class="px-5 py-3 font-semibold">Tingkat</th>
                        <th class="px-5 py-3 font-semibold">Jurusan</th>
                        <th class="px-5 py-3 font-semibold">Wali Kelas</th>
                        <th class="px-5 py-3 font-semibold text-center">Jumlah Siswa</th>
                        <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($kelas as $k)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $k->nama_rombel }}</td>
                            <td class="px-5 py-3">{{ $k->tingkat }}</td>
                            <td class="px-5 py-3">{{ $k->jurusan?->nama_jurusan ?? '-' }}</td>
                            <td class="px-5 py-3">{{ $k->waliKelas?->nama_ptk ?? '-' }}</td>
                            <td class="px-5 py-3 text-center"><x-badge color="blue">{{ $k->siswa_count }}</x-badge></td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('kelas.show', $k) }}" class="text-xs font-medium text-indigo-600 hover:underline">Lihat Anggota</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Tidak ada kelas pada tahun ajaran ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kelas->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">{{ $kelas->links() }}</div>
        @endif
    </x-card>
@endsection
