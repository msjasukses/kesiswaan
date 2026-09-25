@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $cards = [
            ['label' => 'Siswa Aktif', 'value' => $stats['siswa'], 'color' => 'bg-indigo-500', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['label' => 'Pelanggaran Bulan Ini', 'value' => $stats['pelanggaran_bulan_ini'], 'color' => 'bg-red-500', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            ['label' => 'Total Prestasi', 'value' => $stats['prestasi'], 'color' => 'bg-amber-500', 'icon' => 'M12 15a4 4 0 004-4V5H8v6a4 4 0 004 4zm0 0v3m-3 0h6'],
            ['label' => 'Ekstrakurikuler', 'value' => $stats['ekstrakurikuler'], 'color' => 'bg-emerald-500', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0'],
            ['label' => 'Program Berjalan', 'value' => $stats['program_berjalan'] . ' / ' . $stats['program_kerja'], 'color' => 'bg-sky-500', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
        ];
        $maxChart = max(1, $chart->max('total'));
    @endphp

    {{-- Sambutan --}}
    <div class="p-5 mb-6 text-white bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-xl">
        <h2 class="text-xl font-bold">Selamat datang, {{ auth()->user()->name }} 👋</h2>
        <p class="mt-1 text-sm text-indigo-100">
            {{ setting('nama_sekolah', 'Aplikasi Kesiswaan') }}
            @if (setting('tahun_ajaran')) &middot; T.A. {{ setting('tahun_ajaran') }} @if(setting('semester')) ({{ setting('semester') }}) @endif @endif
        </p>
    </div>

    {{-- Kartu statistik --}}
    <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-3 xl:grid-cols-5">
        @foreach ($cards as $c)
            <div class="p-4 bg-white border shadow-sm rounded-xl border-slate-200">
                <div class="flex items-center justify-center w-10 h-10 mb-3 text-white rounded-lg {{ $c['color'] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $c['icon'] }}"/></svg>
                </div>
                <p class="text-2xl font-bold text-slate-800">{{ $c['value'] }}</p>
                <p class="text-xs text-slate-500">{{ $c['label'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Grafik pelanggaran --}}
        <x-card title="Tren Pelanggaran (6 Bulan Terakhir)" class="lg:col-span-2">
            <div class="flex items-end justify-between h-56 gap-3 pt-4">
                @foreach ($chart as $bar)
                    <div class="flex flex-col items-center flex-1 h-full">
                        <div class="flex items-end justify-center flex-1 w-full">
                            <div class="w-full max-w-[3rem] bg-indigo-500 rounded-t-md hover:bg-indigo-600 transition-colors relative"
                                 style="height: {{ $bar['total'] > 0 ? max(4, round($bar['total'] / $maxChart * 100)) : 1 }}%">
                                <span class="absolute left-0 right-0 text-xs font-semibold text-center -top-5 text-slate-600">{{ $bar['total'] }}</span>
                            </div>
                        </div>
                        <span class="mt-2 text-xs text-slate-500">{{ $bar['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>

        {{-- Top pelanggar --}}
        <x-card title="Poin Pelanggaran Tertinggi">
            @forelse ($topPelanggar as $siswa)
                <div class="flex items-center gap-3 py-2 {{ ! $loop->last ? 'border-b border-slate-100' : '' }}">
                    <span class="flex items-center justify-center text-xs font-bold rounded-full w-7 h-7 bg-slate-100 text-slate-600">{{ $loop->iteration }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate text-slate-700">{{ $siswa->nama }}</p>
                        <p class="text-xs text-slate-400">{{ $siswa->kelas ?? '-' }}</p>
                    </div>
                    <x-badge color="red">{{ $siswa->total_poin }} poin</x-badge>
                </div>
            @empty
                <p class="py-6 text-sm text-center text-slate-400">Belum ada data pelanggaran.</p>
            @endforelse
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-2">
        {{-- Pelanggaran terbaru --}}
        <x-card title="Pelanggaran Terbaru" flush>
            <div class="divide-y divide-slate-100">
                @forelse ($pelanggaranTerbaru as $p)
                    <div class="flex items-center justify-between gap-3 px-5 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium truncate text-slate-700">{{ $p->siswa->nama }}</p>
                            <p class="text-xs text-slate-400">{{ $p->dataPelanggaran->nama }} &middot; {{ $p->tanggal?->translatedFormat('d M Y') }}</p>
                        </div>
                        <x-badge color="red">+{{ $p->poin }}</x-badge>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-center text-slate-400">Belum ada pencatatan pelanggaran.</p>
                @endforelse
            </div>
        </x-card>

        {{-- Prestasi terbaru --}}
        <x-card title="Prestasi Terbaru" flush>
            <div class="divide-y divide-slate-100">
                @forelse ($prestasiTerbaru as $pr)
                    <div class="flex items-center justify-between gap-3 px-5 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium truncate text-slate-700">{{ $pr->nama_prestasi }}</p>
                            <p class="text-xs text-slate-400">{{ $pr->siswa->nama }} &middot; {{ $pr->siswa->kelas?->nama ?? '-' }}</p>
                        </div>
                        <x-badge color="yellow">{{ $pr->tingkat }}</x-badge>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-center text-slate-400">Belum ada data prestasi.</p>
                @endforelse
            </div>
        </x-card>
    </div>
@endsection
