@extends('layouts.admin')

@section('title', 'Laporan Aktivitas Guru Wali')

@php
    $cards = [
        ['label' => 'Total Aktivitas', 'value' => $ringkasan['total'], 'color' => 'bg-indigo-500', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['label' => 'Siswa Dibina', 'value' => $ringkasan['siswa'], 'color' => 'bg-sky-500', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['label' => 'Selesai', 'value' => $ringkasan['selesai'], 'color' => 'bg-emerald-500', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Proses', 'value' => $ringkasan['proses'], 'color' => 'bg-amber-500', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Perlu Tindak Lanjut', 'value' => $ringkasan['perlu'], 'color' => 'bg-red-500', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
    ];
    $maxTren = max(1, $tren->max('total'));
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
                <label for="guru_wali_id" class="block mb-1 text-xs font-medium text-slate-500">Perwalian</label>
                <select name="guru_wali_id" id="guru_wali_id" class="text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Perwalian</option>
                    @foreach ($daftarWali as $w)
                        <option value="{{ $w->id }}" @selected(request('guru_wali_id') == $w->id)>{{ $w->nama }}</option>
                    @endforeach
                </select>
            </div>
            <button class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Tampilkan</button>
        </form>
        <x-export-menu
            :excel-url="route('laporan-aktivitas-guru-wali.export', array_merge(['format' => 'excel'], request()->query()))"
            :word-url="route('laporan-aktivitas-guru-wali.export', array_merge(['format' => 'word'], request()->query()))"
            :pdf-url="route('laporan-aktivitas-guru-wali.export', array_merge(['format' => 'pdf'], request()->query()))" />
    </div>

    <p class="mb-5 text-sm text-slate-500">
        Periode <span class="font-medium text-slate-700">{{ $dari->translatedFormat('d F Y') }} &ndash; {{ $sampai->translatedFormat('d F Y') }}</span>
        @if ($waliDipilih)
            &middot; {{ $waliDipilih->nama }} ({{ $waliDipilih->guru?->nama_ptk ?? '-' }})
        @else
            &middot; seluruh perwalian
        @endif
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
        {{-- Tren bulanan --}}
        <x-card title="Tren Aktivitas per Bulan" class="lg:col-span-2">
            @if ($ringkasan['total'] > 0)
                <div class="flex items-end justify-between h-56 gap-3 pt-4">
                    @foreach ($tren as $bar)
                        <div class="flex flex-col items-center flex-1 h-full">
                            <div class="flex items-end justify-center flex-1 w-full">
                                <div class="w-full max-w-[3rem] bg-indigo-500 rounded-t-md hover:bg-indigo-600 transition-colors relative"
                                     style="height: {{ $bar['total'] > 0 ? max(4, round($bar['total'] / $maxTren * 100)) : 1 }}%">
                                    <span class="absolute left-0 right-0 text-xs font-semibold text-center -top-5 text-slate-600">{{ $bar['total'] }}</span>
                                </div>
                            </div>
                            <span class="mt-2 text-xs text-slate-500">{{ $bar['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="py-16 text-sm text-center text-slate-400">Tidak ada aktivitas pada periode ini.</p>
            @endif
        </x-card>

        {{-- Siswa paling sering dibina --}}
        <x-card title="Siswa Paling Sering Dibina">
            @forelse ($perSiswa as $s)
                <div class="flex items-center gap-3 py-2 {{ ! $loop->last ? 'border-b border-slate-100' : '' }}">
                    <span class="flex items-center justify-center text-xs font-bold rounded-full w-7 h-7 bg-slate-100 text-slate-600">{{ $loop->iteration }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate text-slate-700">{{ $s->nama }}</p>
                        <p class="text-xs text-slate-400">{{ $s->kelas }}</p>
                    </div>
                    <x-badge color="indigo">{{ $s->total }} aktivitas</x-badge>
                </div>
            @empty
                <p class="py-6 text-sm text-center text-slate-400">Belum ada data.</p>
            @endforelse
        </x-card>
    </div>

    {{-- Rekap per perwalian --}}
    <div class="mt-6">
        <x-card title="Rekap per Perwalian" flush>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Perwalian</th>
                            <th class="px-5 py-3 font-semibold">Guru Wali</th>
                            <th class="px-5 py-3 font-semibold text-right">Aktivitas</th>
                            <th class="px-5 py-3 font-semibold text-right">Siswa Dibina</th>
                            <th class="px-5 py-3 font-semibold text-right">Selesai</th>
                            <th class="px-5 py-3 font-semibold text-right">Proses</th>
                            <th class="px-5 py-3 font-semibold text-right">Perlu Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($perPerwalian as $r)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-800">{{ $r->label }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $r->keterangan }}</td>
                                <td class="px-5 py-3 text-right">{{ $r->total }}</td>
                                <td class="px-5 py-3 text-right">{{ $r->siswa_dibina }}</td>
                                <td class="px-5 py-3 text-right text-emerald-600">{{ $r->selesai }}</td>
                                <td class="px-5 py-3 text-right text-amber-600">{{ $r->proses }}</td>
                                <td class="px-5 py-3 text-right text-red-600">{{ $r->perlu }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Tidak ada aktivitas pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($perPerwalian->isNotEmpty())
                        <tfoot class="font-semibold border-t text-slate-700 bg-slate-50 border-slate-200">
                            <tr>
                                <td class="px-5 py-3" colspan="2">Total</td>
                                <td class="px-5 py-3 text-right">{{ $ringkasan['total'] }}</td>
                                <td class="px-5 py-3 text-right">{{ $ringkasan['siswa'] }}</td>
                                <td class="px-5 py-3 text-right">{{ $ringkasan['selesai'] }}</td>
                                <td class="px-5 py-3 text-right">{{ $ringkasan['proses'] }}</td>
                                <td class="px-5 py-3 text-right">{{ $ringkasan['perlu'] }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </x-card>
    </div>

    {{-- Rekap per kategori --}}
    <div class="mt-6">
        <x-card title="Rekap per Kategori Pembinaan" flush>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Kategori</th>
                            <th class="px-5 py-3 font-semibold w-1/3">Proporsi</th>
                            <th class="px-5 py-3 font-semibold text-right">Aktivitas</th>
                            <th class="px-5 py-3 font-semibold text-right">Siswa Dibina</th>
                            <th class="px-5 py-3 font-semibold text-right">Selesai</th>
                            <th class="px-5 py-3 font-semibold text-right">Proses</th>
                            <th class="px-5 py-3 font-semibold text-right">Perlu Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($perKategori as $r)
                            @php $persen = $ringkasan['total'] > 0 ? round($r->total / $ringkasan['total'] * 100) : 0; @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-800">{{ $r->label }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 rounded-full bg-slate-100">
                                            <div class="h-2 bg-indigo-500 rounded-full" style="width: {{ max(2, $persen) }}%"></div>
                                        </div>
                                        <span class="text-xs text-slate-500 w-9 text-right">{{ $persen }}%</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right">{{ $r->total }}</td>
                                <td class="px-5 py-3 text-right">{{ $r->siswa_dibina }}</td>
                                <td class="px-5 py-3 text-right text-emerald-600">{{ $r->selesai }}</td>
                                <td class="px-5 py-3 text-right text-amber-600">{{ $r->proses }}</td>
                                <td class="px-5 py-3 text-right text-red-600">{{ $r->perlu }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Tidak ada aktivitas pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection
