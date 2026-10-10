@php
    $state = [
        'kurikulum' => old('kurikulum', $semester->kurikulum),
        'nilai' => array_values(old('nilai', $nilai)),
        'ekskul' => array_values(old('ekstrakurikuler', $semester->ekstrakurikuler ?? [])),
        'projek' => array_values(old('projek', $semester->projek ?? [])),
        'templates' => $templates,
        'labels' => config('buku_induk.kurikulum'),
    ];
    $cell = 'w-full px-2 py-1.5 text-sm rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500';
    $tingkat = (int) old('tingkat', $semester->tingkat);
@endphp

<script>
    function bukuIndukSemesterForm(state) {
        return {
            ...state,
            get isK13() { return this.kurikulum === 'K13'; },
            gantiKurikulum() {
                if (confirm('Ganti daftar mata pelajaran dengan template ' + this.labels[this.kurikulum] + '? Isian nilai pada tabel akan dikosongkan.')) {
                    this.nilai = JSON.parse(JSON.stringify(this.templates[this.kurikulum]));
                }
            },
            tambahMapel() {
                const last = this.nilai[this.nilai.length - 1];
                this.nilai.push({ kelompok: last ? last.kelompok : '', nama_mapel: '', kkm: this.isK13 ? (last?.kkm ?? 70) : null });
            },
            predikat(nilai, kkm) {
                if (nilai === '' || nilai === null || nilai === undefined) return '-';
                nilai = Number(nilai);
                kkm = Number(kkm) || 70;
                const i = (100 - kkm) / 3;
                if (nilai < kkm) return 'D';
                if (nilai < kkm + i) return 'C';
                if (nilai < kkm + 2 * i) return 'B';
                return 'A';
            },
        };
    }
</script>

<datalist id="dl-keputusan">
    @if ($tingkat)
        <option value="Naik ke kelas {{ $tingkat + 1 }}">
        <option value="Tinggal di kelas {{ $tingkat }}">
    @endif
    <option value="Lulus">
    <option value="Tidak Lulus">
</datalist>

<div x-data="bukuIndukSemesterForm(@js($state))" class="space-y-6">
    @if ($errors->any())
        <div class="p-4 text-sm text-red-700 border border-red-200 rounded-lg bg-red-50">
            <ul class="space-y-1 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-card title="Kurikulum & Identitas Rapor">
        <div class="grid grid-cols-1 gap-3 mb-5 sm:grid-cols-2">
            @foreach (config('buku_induk.kurikulum') as $kode => $label)
                <label class="flex items-start gap-3 p-4 border rounded-lg cursor-pointer"
                    :class="kurikulum === @js($kode) ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-slate-200 hover:bg-slate-50'">
                    <input type="radio" name="kurikulum" value="{{ $kode }}" x-model="kurikulum" @change="gantiKurikulum()"
                        class="mt-0.5 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                    <span>
                        <span class="block text-sm font-semibold text-slate-800">{{ $label }}</span>
                        <span class="block text-xs text-slate-500">{{ config("buku_induk.keterangan.{$kode}") }}</span>
                    </span>
                </label>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <x-field name="tahun_pelajaran" label="Tahun Pelajaran" :value="old('tahun_pelajaran', $semester->tahun_pelajaran)" placeholder="2026/2027" required />
            <x-select name="semester" label="Semester" required>
                <option value="1" @selected(old('semester', $semester->semester) == 1)>1 (Ganjil)</option>
                <option value="2" @selected(old('semester', $semester->semester) == 2)>2 (Genap)</option>
            </x-select>
            <x-field name="tanggal_rapor" label="Tanggal Rapor" type="date" :value="old('tanggal_rapor', optional($semester->tanggal_rapor)->format('Y-m-d'))" />
            <x-field name="kelas" label="Kelas" :value="old('kelas', $semester->kelas)" placeholder="Contoh: 7A" required />
            <x-field name="tingkat" label="Tingkat" type="number" min="1" max="13" :value="old('tingkat', $semester->tingkat)" />
            <x-field name="wali_kelas" label="Wali Kelas" :value="old('wali_kelas', $semester->wali_kelas)" />
        </div>
    </x-card>

    <x-card title="Nilai Mata Pelajaran" flush>
        <x-slot:actions>
            <button type="button" @click="tambahMapel()" class="px-3 py-1.5 text-xs font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">+ Tambah Mapel</button>
        </x-slot:actions>

        <p class="px-5 pt-4 text-xs text-slate-500" x-show="isK13">Predikat dihitung otomatis dari KKM: rentang di atas KKM dibagi tiga (C, B, A); di bawah KKM = D.</p>
        <p class="px-5 pt-4 text-xs text-slate-500" x-show="!isK13" x-cloak>Isi nilai akhir (0–100) dan deskripsi capaian kompetensi tiap mata pelajaran.</p>

        <div class="overflow-x-auto">
            <table class="w-full mt-3 text-sm text-left">
                <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                    <tr>
                        <th class="px-3 py-3 font-semibold">No</th>
                        <th class="px-2 py-3 font-semibold">Kelompok</th>
                        <th class="px-2 py-3 font-semibold">Mata Pelajaran</th>
                        <th class="px-2 py-3 font-semibold text-center" x-show="isK13">KKM</th>
                        <th class="px-2 py-3 font-semibold text-center" x-show="isK13">Pengetahuan</th>
                        <th class="px-2 py-3 font-semibold" x-show="isK13">Deskripsi Pengetahuan</th>
                        <th class="px-2 py-3 font-semibold text-center" x-show="isK13">Keterampilan</th>
                        <th class="px-2 py-3 font-semibold" x-show="isK13">Deskripsi Keterampilan</th>
                        <th class="px-2 py-3 font-semibold text-center" x-show="!isK13" x-cloak>Nilai Akhir</th>
                        <th class="px-2 py-3 font-semibold" x-show="!isK13" x-cloak>Capaian Kompetensi</th>
                        <th class="px-2 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(row, i) in nilai" :key="i">
                        <tr class="align-top">
                            <td class="px-3 py-2 pt-4 text-slate-400" x-text="i + 1"></td>
                            <td class="px-2 py-2"><input type="text" :name="`nilai[${i}][kelompok]`" x-model="row.kelompok" class="{{ $cell }} min-w-[9rem]"></td>
                            <td class="px-2 py-2"><input type="text" :name="`nilai[${i}][nama_mapel]`" x-model="row.nama_mapel" required class="{{ $cell }} min-w-[14rem]"></td>

                            <td class="px-2 py-2" x-show="isK13"><input type="number" min="0" max="100" :name="`nilai[${i}][kkm]`" x-model="row.kkm" class="{{ $cell }} w-16 text-center"></td>
                            <td class="px-2 py-2" x-show="isK13">
                                <div class="flex items-center gap-1">
                                    <input type="number" min="0" max="100" :name="`nilai[${i}][nilai_pengetahuan]`" x-model="row.nilai_pengetahuan" class="{{ $cell }} w-16 text-center">
                                    <span class="w-5 text-xs font-semibold text-center text-slate-500" x-text="predikat(row.nilai_pengetahuan, row.kkm)"></span>
                                </div>
                            </td>
                            <td class="px-2 py-2" x-show="isK13"><textarea rows="2" :name="`nilai[${i}][deskripsi_pengetahuan]`" x-model="row.deskripsi_pengetahuan" class="{{ $cell }} min-w-[14rem]"></textarea></td>
                            <td class="px-2 py-2" x-show="isK13">
                                <div class="flex items-center gap-1">
                                    <input type="number" min="0" max="100" :name="`nilai[${i}][nilai_keterampilan]`" x-model="row.nilai_keterampilan" class="{{ $cell }} w-16 text-center">
                                    <span class="w-5 text-xs font-semibold text-center text-slate-500" x-text="predikat(row.nilai_keterampilan, row.kkm)"></span>
                                </div>
                            </td>
                            <td class="px-2 py-2" x-show="isK13"><textarea rows="2" :name="`nilai[${i}][deskripsi_keterampilan]`" x-model="row.deskripsi_keterampilan" class="{{ $cell }} min-w-[14rem]"></textarea></td>

                            <td class="px-2 py-2" x-show="!isK13"><input type="number" min="0" max="100" :name="`nilai[${i}][nilai_akhir]`" x-model="row.nilai_akhir" class="{{ $cell }} w-20 text-center"></td>
                            <td class="px-2 py-2" x-show="!isK13"><textarea rows="2" :name="`nilai[${i}][capaian_kompetensi]`" x-model="row.capaian_kompetensi" class="{{ $cell }} min-w-[20rem]"></textarea></td>

                            <td class="px-2 py-2 pt-3">
                                <button type="button" @click="nilai.splice(i, 1)" title="Hapus baris" class="p-1 text-slate-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!nilai.length"><td colspan="11" class="px-5 py-6 text-center text-slate-400">Belum ada mata pelajaran.</td></tr>
                </tbody>
            </table>
        </div>
    </x-card>

    <div x-show="isK13">
        <x-card title="Sikap">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                @foreach (['spiritual' => 'Sikap Spiritual', 'sosial' => 'Sikap Sosial'] as $k => $label)
                    <div class="space-y-3">
                        <x-select name="sikap_{{ $k }}_predikat" :label="$label">
                            <option value="">- Predikat -</option>
                            @foreach (config('buku_induk.predikat_sikap') as $o)
                                <option value="{{ $o }}" @selected(old("sikap_{$k}_predikat", $semester->{"sikap_{$k}_predikat"}) === $o)>{{ $o }}</option>
                            @endforeach
                        </x-select>
                        <x-textarea name="sikap_{{ $k }}_deskripsi" rows="2" placeholder="Deskripsi" :value="old('sikap_' . $k . '_deskripsi', $semester->{'sikap_' . $k . '_deskripsi'})" />
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    <x-card title="Ekstrakurikuler">
        <x-slot:actions>
            <button type="button" @click="ekskul.push({ nama: '', predikat: '', keterangan: '' })" class="px-3 py-1.5 text-xs font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">+ Tambah</button>
        </x-slot:actions>
        <div class="space-y-2">
            <template x-for="(e, i) in ekskul" :key="i">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-12">
                    <input type="text" :name="`ekstrakurikuler[${i}][nama]`" x-model="e.nama" placeholder="Nama ekstrakurikuler" class="{{ $cell }} sm:col-span-4">
                    <select :name="`ekstrakurikuler[${i}][predikat]`" x-model="e.predikat" class="{{ $cell }} sm:col-span-3">
                        <option value="">- Predikat -</option>
                        @foreach (config('buku_induk.predikat_ekskul') as $o)
                            <option value="{{ $o }}">{{ $o }}</option>
                        @endforeach
                    </select>
                    <input type="text" :name="`ekstrakurikuler[${i}][keterangan]`" x-model="e.keterangan" placeholder="Keterangan" class="{{ $cell }} sm:col-span-4">
                    <button type="button" @click="ekskul.splice(i, 1)" class="text-xs font-medium text-red-600 sm:col-span-1 hover:underline">Hapus</button>
                </div>
            </template>
            <p x-show="!ekskul.length" class="text-sm text-slate-400">Belum ada ekstrakurikuler.</p>
        </div>
    </x-card>

    <div x-show="!isK13" x-cloak>
        <x-card title="Projek Penguatan Profil Pelajar Pancasila / Kokurikuler">
            <x-slot:actions>
                <button type="button" @click="projek.push({ tema: '', capaian: '', catatan: '' })" class="px-3 py-1.5 text-xs font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">+ Tambah</button>
            </x-slot:actions>
            <div class="space-y-2">
                <template x-for="(p, i) in projek" :key="i">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-12">
                        <input type="text" :name="`projek[${i}][tema]`" x-model="p.tema" placeholder="Tema / judul projek" class="{{ $cell }} sm:col-span-4">
                        <select :name="`projek[${i}][capaian]`" x-model="p.capaian" class="{{ $cell }} sm:col-span-3">
                            <option value="">- Capaian -</option>
                            @foreach (config('buku_induk.capaian_p5') as $kode => $label)
                                <option value="{{ $kode }}">{{ $kode }} — {{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="text" :name="`projek[${i}][catatan]`" x-model="p.catatan" placeholder="Catatan proses" class="{{ $cell }} sm:col-span-4">
                        <button type="button" @click="projek.splice(i, 1)" class="text-xs font-medium text-red-600 sm:col-span-1 hover:underline">Hapus</button>
                    </div>
                </template>
                <p x-show="!projek.length" class="text-sm text-slate-400">Belum ada projek.</p>
            </div>
        </x-card>
    </div>

    <x-card title="Ketidakhadiran, Catatan & Keputusan">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <x-field name="sakit" label="Sakit (hari)" type="number" min="0" :value="old('sakit', $semester->sakit)" />
            <x-field name="izin" label="Izin (hari)" type="number" min="0" :value="old('izin', $semester->izin)" />
            <x-field name="alpa" label="Tanpa Keterangan (hari)" type="number" min="0" :value="old('alpa', $semester->alpa)" />
            <div class="md:col-span-3">
                <x-textarea name="catatan_wali_kelas" label="Catatan Wali Kelas" rows="2" :value="old('catatan_wali_kelas', $semester->catatan_wali_kelas)" />
            </div>
            <x-field name="keputusan" label="Keputusan" list="dl-keputusan" :value="old('keputusan', $semester->keputusan)" help="Umumnya diisi pada semester genap." />
        </div>
    </x-card>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('buku-induk.show', $siswa) }}" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Batal</a>
        <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan</button>
    </div>
</div>
