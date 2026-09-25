@extends('layouts.admin')

@section('title', 'Laporan Jurnal Ekstrakurikuler')

@php
    $cards = [
        ['label' => 'Total Pertemuan', 'value' => $ringkasan['pertemuan'], 'color' => 'bg-indigo-500', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['label' => 'Ekskul Terlaksana', 'value' => $ringkasan['ekskul'], 'color' => 'bg-emerald-500', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0'],
        ['label' => 'Siswa Hadir', 'value' => $ringkasan['hadir'], 'color' => 'bg-sky-500', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Siswa Tidak Hadir', 'value' => $ringkasan['tidak_hadir'], 'color' => 'bg-red-500', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Rata-rata Kehadiran', 'value' => $ringkasan['persen'] . '%', 'color' => 'bg-amber-500', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
    ];
    $maxTren = max(1, $perBulan->max('pertemuan'));
@endphp

@section('content')
    {{-- Filter periode --}}
    <div class="flex flex-col gap-3 mb-5 lg:flex-row lg:items-end lg:justify-between">
        <form method="GET" class="flex flex-wrap items-end gap-2">
            <div>
                <label for="dari" class="block mb-1 text-xs font-medium text-slate-500">Dari Tanggal</label>
                <input type="date" name="dari" id="dari" value="{{ $dari->format('Y-m-d') }}"
                    class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="sampai" class="block mb-1 text-xs font-medium text-slate-500">Sampai Tanggal</label>
                <input type="date" name="sampai" id="sampai" value="{{ $sampai->format('Y-m-d') }}"
                    class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="ekstrakurikuler_id" class="block mb-1 text-xs font-medium text-slate-500">Ekstrakurikuler</label>
                <select name="ekstrakurikuler_id" id="ekstrakurikuler_id" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Ekskul</option>
                    @foreach ($daftarEkskul as $e)
                        <option value="{{ $e->id }}" @selected(request('ekstrakurikuler_id') == $e->id)>{{ $e->nama }}</option>
                    @endforeach
                </select>
            </div>
            <button class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Tampilkan</button>
        </form>
        <x-export-menu
            :excel-url="route('laporan-jurnal-ekstrakurikuler.export', array_merge(['format' => 'excel'], request()->query()))"
            :word-url="route('laporan-jurnal-ekstrakurikuler.export', array_merge(['format' => 'word'], request()->query()))"
            :pdf-url="route('laporan-jurnal-ekstrakurikuler.export', array_merge(['format' => 'pdf'], request()->query()))" />
    </div>

    <p class="mb-5 text-sm text-slate-500">
        Periode <span class="font-medium text-slate-700">{{ $dari->translatedFormat('d F Y') }} &ndash; {{ $sampai->translatedFormat('d F Y') }}</span>
        &middot; {{ $ekskulDipilih?->nama ?? 'seluruh ekstrakurikuler' }}
    </p>

    {{-- Ringkasan --}}
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
        {{-- Tren pertemuan --}}
        <x-card title="Tren Pertemuan per Bulan" class="lg:col-span-2">
            @if ($ringkasan['pertemuan'] > 0)
                <div class="flex items-end justify-between h-56 gap-3 pt-4">
                    @foreach ($perBulan as $bar)
                        <div class="flex flex-col items-center flex-1 h-full">
                            <div class="flex items-end justify-center flex-1 w-full">
                                <div class="w-full max-w-[3rem] bg-indigo-500 rounded-t-md hover:bg-indigo-600 transition-colors relative"
                                     style="height: {{ $bar['pertemuan'] > 0 ? max(4, round($bar['pertemuan'] / $maxTren * 100)) : 1 }}%">
                                    <span class="absolute left-0 right-0 text-xs font-semibold text-center -top-5 text-slate-600">{{ $bar['pertemuan'] }}</span>
                                </div>
                            </div>
                            <span class="mt-2 text-xs text-slate-500">{{ $bar['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="py-16 text-sm text-center text-slate-400">Tidak ada kegiatan pada periode ini.</p>
            @endif
        </x-card>

        {{-- Kehadiran tertinggi --}}
        <x-card title="Kehadiran Tertinggi">
            @forelse ($kehadiranTertinggi as $e)
                <div class="flex items-center gap-3 py-2 {{ ! $loop->last ? 'border-b border-slate-100' : '' }}">
                    <span class="flex items-center justify-center text-xs font-bold rounded-full w-7 h-7 bg-slate-100 text-slate-600">{{ $loop->iteration }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate text-slate-700">{{ $e->label }}</p>
                        <p class="text-xs text-slate-400">{{ $e->pertemuan }} pertemuan</p>
                    </div>
                    <x-badge :color="$e->persen >= 80 ? 'green' : ($e->persen >= 60 ? 'yellow' : 'red')">{{ $e->persen }}%</x-badge>
                </div>
            @empty
                <p class="py-6 text-sm text-center text-slate-400">Belum ada data.</p>
            @endforelse
        </x-card>
    </div>

    {{-- Rekap per ekstrakurikuler --}}
    <div class="mt-6">
        <x-card title="Rekap per Ekstrakurikuler" flush>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Ekstrakurikuler</th>
                            <th class="px-5 py-3 font-semibold">Pembina</th>
                            <th class="px-5 py-3 font-semibold text-right">Pertemuan</th>
                            <th class="px-5 py-3 font-semibold text-right">Hadir</th>
                            <th class="px-5 py-3 font-semibold text-right">Tidak Hadir</th>
                            <th class="px-5 py-3 font-semibold w-48">Kehadiran</th>
                            <th class="px-5 py-3 font-semibold">Pertemuan Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($perEkskul as $r)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-800">{{ $r->label }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $r->pembina }}</td>
                                <td class="px-5 py-3 text-right">{{ $r->pertemuan }}</td>
                                <td class="px-5 py-3 text-right text-emerald-600">{{ $r->hadir }}</td>
                                <td class="px-5 py-3 text-right text-red-600">{{ $r->tidak_hadir }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 rounded-full bg-slate-100">
                                            <div class="h-2 rounded-full {{ $r->persen >= 80 ? 'bg-emerald-500' : ($r->persen >= 60 ? 'bg-amber-500' : 'bg-red-500') }}"
                                                 style="width: {{ max(2, $r->persen) }}%"></div>
                                        </div>
                                        <span class="text-xs text-slate-500 w-9 text-right">{{ $r->persen }}%</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-slate-600">{{ $r->terakhir_label }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Tidak ada kegiatan pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($perEkskul->isNotEmpty())
                        <tfoot class="font-semibold border-t text-slate-700 bg-slate-50 border-slate-200">
                            <tr>
                                <td class="px-5 py-3" colspan="2">Total</td>
                                <td class="px-5 py-3 text-right">{{ $ringkasan['pertemuan'] }}</td>
                                <td class="px-5 py-3 text-right">{{ $ringkasan['hadir'] }}</td>
                                <td class="px-5 py-3 text-right">{{ $ringkasan['tidak_hadir'] }}</td>
                                <td class="px-5 py-3">{{ $ringkasan['persen'] }}%</td>
                                <td class="px-5 py-3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </x-card>
    </div>

    {{-- Rekap per bulan --}}
    <div class="mt-6">
        <x-card title="Rekap per Bulan" flush>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Bulan</th>
                            <th class="px-5 py-3 font-semibold text-right">Pertemuan</th>
                            <th class="px-5 py-3 font-semibold text-right">Hadir</th>
                            <th class="px-5 py-3 font-semibold text-right">Tidak Hadir</th>
                            <th class="px-5 py-3 font-semibold text-right">Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($perBulan as $r)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-800">{{ $r['label'] }}</td>
                                <td class="px-5 py-3 text-right">{{ $r['pertemuan'] }}</td>
                                <td class="px-5 py-3 text-right text-emerald-600">{{ $r['hadir'] }}</td>
                                <td class="px-5 py-3 text-right text-red-600">{{ $r['tidak_hadir'] }}</td>
                                <td class="px-5 py-3 text-right">{{ $r['pertemuan'] > 0 ? $r['persen'] . '%' : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection
