@extends('layouts.admin')

@section('title', 'Anggota ' . $ekstrakurikuler->nama)

@section('content')
    <div class="mb-5">
        <a href="{{ route('ekstrakurikuler.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke daftar ekstrakurikuler
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Info + tambah anggota --}}
        <div class="space-y-6">
            <x-card :title="$ekstrakurikuler->nama">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Pembina</dt><dd class="font-medium text-slate-700">{{ $ekstrakurikuler->pembina ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Jadwal</dt><dd class="font-medium text-slate-700">{{ $ekstrakurikuler->jadwal ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Tempat</dt><dd class="font-medium text-slate-700">{{ $ekstrakurikuler->tempat ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Jumlah Anggota</dt><dd class="font-medium text-slate-700">{{ $anggota->count() }}</dd></div>
                </dl>
                @if ($ekstrakurikuler->deskripsi)
                    <p class="pt-3 mt-3 text-sm border-t text-slate-600 border-slate-100">{{ $ekstrakurikuler->deskripsi }}</p>
                @endif
            </x-card>

            <x-card title="Tambah Anggota">
                <form method="POST" action="{{ route('ekstrakurikuler.anggota.store', $ekstrakurikuler) }}" class="space-y-4">
                    @csrf
                    <x-select name="siswa_id" label="Siswa" required>
                        <option value="">- Pilih Siswa -</option>
                        @foreach ($calonAnggota as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_siswa }} — {{ $s->kelas?->nama_rombel ?? 'tanpa kelas' }}</option>
                        @endforeach
                    </x-select>
                    <x-select name="jabatan" label="Jabatan" required>
                        @foreach (['Anggota', 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara'] as $j)
                            <option value="{{ $j }}">{{ $j }}</option>
                        @endforeach
                    </x-select>
                    <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700" @disabled($calonAnggota->isEmpty())>
                        Tambahkan
                    </button>
                    @if ($calonAnggota->isEmpty())
                        <p class="text-xs text-center text-slate-400">Semua siswa pada tahun ajaran ini sudah menjadi anggota.</p>
                    @endif
                </form>
            </x-card>
        </div>

        {{-- Daftar anggota --}}
        <div class="lg:col-span-2">
            <x-card title="Daftar Anggota" flush>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Kelas</th>
                                <th class="px-5 py-3 font-semibold">Jabatan</th>
                                <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($anggota as $s)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3 font-medium text-slate-800">{{ $s->nama_siswa }}</td>
                                    <td class="px-5 py-3">{{ $s->kelas?->nama_rombel ?? '-' }}</td>
                                    <td class="px-5 py-3">
                                        <x-badge :color="$s->jabatan === 'Anggota' ? 'slate' : 'indigo'">{{ $s->jabatan }}</x-badge>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <form method="POST" action="{{ route('ekstrakurikuler.anggota.destroy', [$ekstrakurikuler, $s->id]) }}" onsubmit="return confirm('Keluarkan {{ $s->nama_siswa }} dari ekskul ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-xs font-medium text-red-600 hover:underline">Keluarkan</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Belum ada anggota.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection
