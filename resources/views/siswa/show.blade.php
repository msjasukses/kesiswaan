@extends('layouts.admin')

@section('title', 'Detail Siswa')

@section('content')
    <div class="mb-5">
        <a href="{{ route('siswa.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Profil --}}
        <div class="space-y-6">
            <x-card>
                <div class="flex flex-col items-center text-center">
                    <div class="flex items-center justify-center w-24 h-24 text-3xl font-bold text-indigo-600 rounded-full bg-indigo-100">
                        {{ strtoupper(substr($siswa->nama_siswa, 0, 1)) }}
                    </div>
                    <h3 class="mt-3 text-lg font-semibold text-slate-800">{{ $siswa->nama_siswa }}</h3>
                    <p class="text-sm text-slate-500">{{ $siswa->kelas?->nama_rombel ?? 'Tanpa kelas' }}</p>
                    <div class="mt-3">
                        <x-badge :color="$siswa->total_poin > 0 ? 'red' : 'green'">Total Poin: {{ (int) $siswa->total_poin }}</x-badge>
                    </div>
                </div>
                <dl class="pt-4 mt-4 space-y-2 text-sm border-t border-slate-100">
                    <div class="flex justify-between"><dt class="text-slate-500">NIS</dt><dd class="font-medium text-slate-700">{{ $siswa->nis ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">NISN</dt><dd class="font-medium text-slate-700">{{ $siswa->nisn ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Jenis Kelamin</dt><dd class="font-medium text-slate-700">{{ $siswa->jenis_kelamin_label }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Tempat, Tgl Lahir</dt><dd class="font-medium text-right text-slate-700">{{ $siswa->tempat_lahir ?? '-' }}{{ $siswa->tanggal_lahir ? ', ' . $siswa->tanggal_lahir->translatedFormat('d M Y') : '' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Agama</dt><dd class="font-medium text-slate-700">{{ $siswa->agama ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">No. HP</dt><dd class="font-medium text-slate-700">{{ $siswa->nomor_hp ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd class="font-medium text-slate-700">{{ $siswa->status_siswa }}</dd></div>
                    <div><dt class="text-slate-500">Alamat</dt><dd class="mt-1 font-medium text-slate-700">{{ $siswa->alamat ?? '-' }}</dd></div>
                    @if ($siswa->nama_ayah || $siswa->nama_ibu)
                        <div class="pt-2 border-t border-slate-100"><dt class="text-slate-500">Orang Tua</dt>
                            <dd class="mt-1 font-medium text-slate-700">
                                {{ $siswa->nama_ayah ?? '-' }} (Ayah)<br>{{ $siswa->nama_ibu ?? '-' }} (Ibu)
                            </dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            <x-card title="Ekstrakurikuler">
                @forelse ($siswa->ekstrakurikuler as $e)
                    <div class="flex items-center justify-between py-1.5 text-sm">
                        <span class="text-slate-700">{{ $e->nama }}</span>
                        <x-badge color="indigo">{{ $e->pivot->jabatan }}</x-badge>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Tidak mengikuti ekstrakurikuler.</p>
                @endforelse
            </x-card>
        </div>

        {{-- Prestasi & Pelanggaran --}}
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Prestasi" flush>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Prestasi</th>
                                <th class="px-5 py-3 font-semibold">Tingkat</th>
                                <th class="px-5 py-3 font-semibold">Peringkat</th>
                                <th class="px-5 py-3 font-semibold">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($siswa->prestasi as $pr)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-slate-700">{{ $pr->nama_prestasi }}</td>
                                    <td class="px-5 py-3"><x-badge color="yellow">{{ $pr->tingkat }}</x-badge></td>
                                    <td class="px-5 py-3">{{ $pr->peringkat ?? '-' }}</td>
                                    <td class="px-5 py-3">{{ $pr->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-6 text-center text-slate-400">Belum ada prestasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>

            <x-card title="Riwayat Pelanggaran" flush>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Tanggal</th>
                                <th class="px-5 py-3 font-semibold">Pelanggaran</th>
                                <th class="px-5 py-3 font-semibold">Kategori</th>
                                <th class="px-5 py-3 font-semibold text-center">Poin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($siswa->pelanggaran->sortByDesc('tanggal') as $p)
                                <tr>
                                    <td class="px-5 py-3">{{ $p->tanggal?->translatedFormat('d M Y') }}</td>
                                    <td class="px-5 py-3 font-medium text-slate-700">{{ $p->dataPelanggaran->nama }}</td>
                                    <td class="px-5 py-3 text-slate-500">{{ $p->dataPelanggaran->jenisPelanggaran->nama }}</td>
                                    <td class="px-5 py-3 text-center"><x-badge color="red">+{{ $p->poin }}</x-badge></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-6 text-center text-slate-400">Tidak ada pelanggaran. 👍</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection
