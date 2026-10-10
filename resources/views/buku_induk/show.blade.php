@extends('layouts.admin')

@section('title', 'Buku Induk Siswa')

@section('content')
    <div class="flex flex-col gap-3 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('buku-induk.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('buku-induk.pdf', $siswa) }}" target="_blank" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Cetak PDF</a>
            <a href="{{ route('buku-induk.edit', $siswa) }}" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Ubah Identitas</a>
            <a href="{{ route('buku-induk.semester.create', $siswa) }}" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">+ Hasil Belajar Semester</a>
        </div>
    </div>

    {{-- Ringkasan --}}
    <x-card class="mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <div class="flex items-center justify-center w-16 h-16 text-2xl font-bold text-indigo-600 bg-indigo-100 rounded-full shrink-0">
                {{ strtoupper(substr($siswa->nama_siswa, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-lg font-semibold text-slate-800">{{ $siswa->nama_siswa }}</h2>
                <p class="text-sm text-slate-500">
                    NIS {{ $siswa->nis ?? '-' }} &middot; NISN {{ $siswa->nisn }} &middot; {{ $siswa->kelas?->nama_rombel ?? 'Tanpa kelas' }}
                </p>
            </div>
            <div class="text-left sm:text-right">
                <x-badge :color="$bukuInduk->isK13() ? 'yellow' : 'indigo'">{{ $bukuInduk->kurikulum_label }}</x-badge>
                @unless ($bukuInduk->exists)
                    <p class="mt-1 text-xs text-slate-400">Buku induk belum diisi (memakai kurikulum default).</p>
                @endunless
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        @foreach ($identitas as $judul => $baris)
            <x-card :title="$judul">
                <dl class="space-y-2 text-sm">
                    @foreach ($baris as $label => $nilai)
                        <div class="grid grid-cols-5 gap-3">
                            <dt class="col-span-2 text-slate-500">{{ $label }}</dt>
                            <dd class="col-span-3 font-medium text-slate-700">{{ filled($nilai) ? $nilai : '-' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-card>
        @endforeach
    </div>

    @if ($bukuInduk->catatan)
        <x-card title="Catatan" class="mt-6">
            <p class="text-sm whitespace-pre-line text-slate-700">{{ $bukuInduk->catatan }}</p>
        </x-card>
    @endif

    <div class="grid grid-cols-1 gap-6 mt-6 xl:grid-cols-2">
        <x-card title="Riwayat Kelas" flush>
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Tahun Ajaran</th>
                        <th class="px-5 py-3 font-semibold">Kelas</th>
                        <th class="px-5 py-3 font-semibold">Wali Kelas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($riwayatKelas as $r)
                        <tr>
                            <td class="px-5 py-3">{{ $r->tahunAjaran?->nama_tahun_ajaran ?? '-' }}</td>
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $r->kelas?->nama_rombel ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $r->kelas?->wali_kelas_nama ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-6 text-center text-slate-400">Belum ada riwayat kelas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>

        <x-card title="Prestasi" flush>
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Prestasi</th>
                        <th class="px-5 py-3 font-semibold">Tingkat</th>
                        <th class="px-5 py-3 font-semibold">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($prestasi as $p)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $p->nama_prestasi }}@if ($p->peringkat) <span class="text-slate-500">({{ $p->peringkat }})</span>@endif</td>
                            <td class="px-5 py-3">{{ $p->tingkat }}</td>
                            <td class="px-5 py-3">{{ $p->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-6 text-center text-slate-400">Belum ada prestasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>

    {{-- Hasil belajar per semester --}}
    <h2 class="mt-8 mb-3 text-base font-semibold text-slate-800">Laporan Perkembangan Hasil Belajar</h2>
    <div class="space-y-6">
        @forelse ($semester as $sem)
            @include('buku_induk._semester', ['sem' => $sem])
        @empty
            <x-card>
                <p class="py-4 text-sm text-center text-slate-400">
                    Belum ada hasil belajar yang dicatat.
                    <a href="{{ route('buku-induk.semester.create', $siswa) }}" class="font-medium text-indigo-600 hover:underline">Tambah sekarang</a>.
                </p>
            </x-card>
        @endforelse
    </div>
@endsection
