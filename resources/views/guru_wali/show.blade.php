@extends('layouts.admin')

@section('title', 'Siswa Asuh ' . $guruWali->nama)

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <a href="{{ route('guru-wali.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke daftar guru wali
        </a>
        <a href="{{ route('aktivitas-guru-wali.create', ['guru_wali_id' => $guruWali->id]) }}"
            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Aktivitas
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Info + tambah siswa asuh --}}
        <div class="space-y-6">
            <x-card :title="$guruWali->nama">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Guru Wali</dt><dd class="font-medium text-slate-700">{{ $guruWali->guru?->nama_ptk ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Jadwal</dt><dd class="font-medium text-slate-700">{{ $guruWali->jadwal ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Tempat</dt><dd class="font-medium text-slate-700">{{ $guruWali->tempat ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Jumlah Siswa Asuh</dt><dd class="font-medium text-slate-700">{{ $anggota->count() }}</dd></div>
                </dl>
                @if ($guruWali->deskripsi)
                    <p class="pt-3 mt-3 text-sm border-t text-slate-600 border-slate-100">{{ $guruWali->deskripsi }}</p>
                @endif
            </x-card>

            <x-card title="Tambah Siswa Asuh">
                <form method="POST" action="{{ route('guru-wali.siswa.store', $guruWali) }}" class="space-y-4">
                    @csrf
                    <x-select name="siswa_id" label="Siswa" required>
                        <option value="">- Pilih Siswa -</option>
                        @foreach ($calonAnggota as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_siswa }} — {{ $s->kelas?->nama_rombel ?? 'tanpa kelas' }}</option>
                        @endforeach
                    </x-select>
                    <x-select name="status" label="Status Bimbingan" required>
                        @foreach (\App\Http\Controllers\GuruWaliController::STATUS as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </x-select>
                    <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700" @disabled($calonAnggota->isEmpty())>
                        Tambahkan
                    </button>
                    @if ($calonAnggota->isEmpty())
                        <p class="text-xs text-center text-slate-400">Semua siswa pada tahun ajaran ini sudah menjadi siswa asuh.</p>
                    @endif
                </form>
            </x-card>
        </div>

        {{-- Daftar siswa asuh --}}
        <div class="lg:col-span-2">
            <x-card title="Daftar Siswa Asuh" flush>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama</th>
                                <th class="px-5 py-3 font-semibold">Kelas</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($anggota as $s)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3 font-medium text-slate-800">{{ $s->nama_siswa }}</td>
                                    <td class="px-5 py-3">{{ $s->kelas?->nama_rombel ?? '-' }}</td>
                                    <td class="px-5 py-3">
                                        <x-badge :color="$s->status_bimbingan === 'Aktif' ? 'slate' : 'yellow'">{{ $s->status_bimbingan }}</x-badge>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <form method="POST" action="{{ route('guru-wali.siswa.destroy', [$guruWali, $s->id]) }}" onsubmit="return confirm('Keluarkan {{ $s->nama_siswa }} dari perwalian ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-xs font-medium text-red-600 hover:underline">Keluarkan</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Belum ada siswa asuh.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection
