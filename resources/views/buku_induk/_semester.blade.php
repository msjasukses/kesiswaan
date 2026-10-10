@php
    $capaianP5 = config('buku_induk.capaian_p5');
    $no = 0;
@endphp

<x-card flush>
    <div class="flex flex-col gap-3 px-5 py-4 border-b sm:flex-row sm:items-center sm:justify-between border-slate-100">
        <div>
            <h3 class="font-semibold text-slate-800">Kelas {{ $sem->kelas }} &middot; Semester {{ $sem->semester_label }}</h3>
            <p class="text-xs text-slate-500">
                Tahun Pelajaran {{ $sem->tahun_pelajaran }}@if ($sem->wali_kelas) &middot; Wali Kelas: {{ $sem->wali_kelas }}@endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <x-badge :color="$sem->isK13() ? 'yellow' : 'indigo'">{{ $sem->kurikulum_label }}</x-badge>
            <a href="{{ route('buku-induk.semester.edit', [$siswa, $sem]) }}" class="text-xs font-medium text-indigo-600 hover:underline">Ubah</a>
            <form method="POST" action="{{ route('buku-induk.semester.destroy', [$siswa, $sem]) }}" onsubmit="return confirm('Hapus hasil belajar semester ini?')">
                @csrf @method('DELETE')
                <button class="text-xs font-medium text-red-600 hover:underline">Hapus</button>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                @if ($sem->isK13())
                    <tr>
                        <th rowspan="2" class="px-4 py-2 font-semibold align-bottom">No</th>
                        <th rowspan="2" class="px-4 py-2 font-semibold align-bottom">Mata Pelajaran</th>
                        <th rowspan="2" class="px-4 py-2 font-semibold text-center align-bottom">KKM</th>
                        <th colspan="3" class="px-4 py-2 font-semibold text-center border-l border-slate-200">Pengetahuan</th>
                        <th colspan="3" class="px-4 py-2 font-semibold text-center border-l border-slate-200">Keterampilan</th>
                    </tr>
                    <tr>
                        <th class="px-4 py-2 font-semibold text-center border-l border-slate-200">Nilai</th>
                        <th class="px-4 py-2 font-semibold text-center">Pred.</th>
                        <th class="px-4 py-2 font-semibold">Deskripsi</th>
                        <th class="px-4 py-2 font-semibold text-center border-l border-slate-200">Nilai</th>
                        <th class="px-4 py-2 font-semibold text-center">Pred.</th>
                        <th class="px-4 py-2 font-semibold">Deskripsi</th>
                    </tr>
                @else
                    <tr>
                        <th class="px-4 py-3 font-semibold">No</th>
                        <th class="px-4 py-3 font-semibold">Mata Pelajaran</th>
                        <th class="px-4 py-3 font-semibold text-center">Nilai Akhir</th>
                        <th class="px-4 py-3 font-semibold">Capaian Kompetensi</th>
                    </tr>
                @endif
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($sem->nilai->groupBy(fn ($n) => $n->kelompok ?: '') as $kelompok => $rows)
                    @if ($kelompok !== '')
                        <tr class="bg-slate-50/60">
                            <td colspan="{{ $sem->isK13() ? 9 : 4 }}" class="px-4 py-2 text-xs font-semibold text-slate-600">{{ $kelompok }}</td>
                        </tr>
                    @endif
                    @foreach ($rows as $n)
                        <tr class="align-top">
                            <td class="px-4 py-2 text-slate-400">{{ ++$no }}</td>
                            <td class="px-4 py-2 font-medium text-slate-700">{{ $n->nama_mapel }}</td>
                            @if ($sem->isK13())
                                <td class="px-4 py-2 text-center">{{ $n->kkm ?? '-' }}</td>
                                <td class="px-4 py-2 text-center border-l border-slate-100">{{ $n->nilai_pengetahuan ?? '-' }}</td>
                                <td class="px-4 py-2 text-center">{{ $n->predikat_pengetahuan ?? '-' }}</td>
                                <td class="px-4 py-2 text-xs text-slate-600 min-w-[14rem]">{{ $n->deskripsi_pengetahuan ?: '-' }}</td>
                                <td class="px-4 py-2 text-center border-l border-slate-100">{{ $n->nilai_keterampilan ?? '-' }}</td>
                                <td class="px-4 py-2 text-center">{{ $n->predikat_keterampilan ?? '-' }}</td>
                                <td class="px-4 py-2 text-xs text-slate-600 min-w-[14rem]">{{ $n->deskripsi_keterampilan ?: '-' }}</td>
                            @else
                                <td class="px-4 py-2 text-center">{{ $n->nilai_akhir ?? '-' }}</td>
                                <td class="px-4 py-2 text-xs text-slate-600">{{ $n->capaian_kompetensi ?: '-' }}</td>
                            @endif
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="9" class="px-5 py-6 text-center text-slate-400">Belum ada nilai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="grid grid-cols-1 gap-5 p-5 border-t md:grid-cols-2 border-slate-100">
        @if ($sem->isK13())
            <div class="md:col-span-2">
                <h4 class="mb-2 text-xs font-semibold uppercase text-slate-500">Sikap</h4>
                <dl class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                    <div>
                        <dt class="text-slate-500">Spiritual: <span class="font-medium text-slate-700">{{ $sem->sikap_spiritual_predikat ?? '-' }}</span></dt>
                        <dd class="text-slate-600">{{ $sem->sikap_spiritual_deskripsi }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Sosial: <span class="font-medium text-slate-700">{{ $sem->sikap_sosial_predikat ?? '-' }}</span></dt>
                        <dd class="text-slate-600">{{ $sem->sikap_sosial_deskripsi }}</dd>
                    </div>
                </dl>
            </div>
        @endif

        <div>
            <h4 class="mb-2 text-xs font-semibold uppercase text-slate-500">Ekstrakurikuler</h4>
            @forelse ($sem->ekstrakurikuler ?? [] as $e)
                <p class="text-sm text-slate-700">
                    {{ $e['nama'] }}@if (! empty($e['predikat'])) — <span class="font-medium">{{ $e['predikat'] }}</span>@endif
                    @if (! empty($e['keterangan']))<span class="text-slate-500">({{ $e['keterangan'] }})</span>@endif
                </p>
            @empty
                <p class="text-sm text-slate-400">-</p>
            @endforelse
        </div>

        @unless ($sem->isK13())
            <div>
                <h4 class="mb-2 text-xs font-semibold uppercase text-slate-500">Projek Profil Pelajar Pancasila / Kokurikuler</h4>
                @forelse ($sem->projek ?? [] as $p)
                    <p class="text-sm text-slate-700">
                        {{ $p['tema'] }}@if (! empty($p['capaian'])) — <span class="font-medium">{{ $capaianP5[$p['capaian']] ?? $p['capaian'] }}</span>@endif
                    </p>
                    @if (! empty($p['catatan']))<p class="mb-1 text-xs text-slate-500">{{ $p['catatan'] }}</p>@endif
                @empty
                    <p class="text-sm text-slate-400">-</p>
                @endforelse
            </div>
        @endunless

        <div>
            <h4 class="mb-2 text-xs font-semibold uppercase text-slate-500">Ketidakhadiran</h4>
            <p class="text-sm text-slate-700">Sakit {{ $sem->sakit }} hari &middot; Izin {{ $sem->izin }} hari &middot; Tanpa keterangan {{ $sem->alpa }} hari</p>
        </div>

        <div>
            <h4 class="mb-2 text-xs font-semibold uppercase text-slate-500">Keputusan</h4>
            <p class="text-sm font-medium text-slate-700">{{ $sem->keputusan ?: '-' }}</p>
            @if ($sem->tanggal_rapor)<p class="text-xs text-slate-500">Tanggal rapor: {{ $sem->tanggal_rapor->translatedFormat('d F Y') }}</p>@endif
        </div>

        @if ($sem->catatan_wali_kelas)
            <div class="md:col-span-2">
                <h4 class="mb-2 text-xs font-semibold uppercase text-slate-500">Catatan Wali Kelas</h4>
                <p class="text-sm whitespace-pre-line text-slate-700">{{ $sem->catatan_wali_kelas }}</p>
            </div>
        @endif
    </div>
</x-card>
