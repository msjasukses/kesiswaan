@extends('layouts.admin')

@section('title', 'Ubah Identitas Buku Induk')

@php
    $v = fn ($f) => old($f, $bukuInduk->{$f});
    $d = fn ($f) => old($f, optional($bukuInduk->{$f})->format('Y-m-d'));
@endphp

@section('content')
    <div class="mb-5">
        <a href="{{ route('buku-induk.show', $siswa) }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Buku Induk
        </a>
    </div>

    <datalist id="dl-pendidikan">
        @foreach (['Tidak Sekolah', 'SD/Sederajat', 'SMP/Sederajat', 'SMA/SMK/Sederajat', 'D1', 'D2', 'D3', 'D4/S1', 'S2', 'S3'] as $o)<option value="{{ $o }}">@endforeach
    </datalist>
    <datalist id="dl-penghasilan">
        @foreach (['Tidak Berpenghasilan', '< Rp 1.000.000', 'Rp 1.000.000 - Rp 2.999.999', 'Rp 3.000.000 - Rp 4.999.999', 'Rp 5.000.000 - Rp 9.999.999', '>= Rp 10.000.000'] as $o)<option value="{{ $o }}">@endforeach
    </datalist>
    <datalist id="dl-tinggal">
        @foreach (['Orang Tua', 'Wali', 'Saudara', 'Asrama', 'Kos', 'Panti Asuhan'] as $o)<option value="{{ $o }}">@endforeach
    </datalist>

    <form method="POST" action="{{ route('buku-induk.update', $siswa) }}" class="max-w-5xl space-y-6"
        x-data="{ kurikulum: @js(old('kurikulum', $bukuInduk->kurikulum)) }">
        @csrf @method('PUT')

        <x-card title="Kurikulum">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach (config('buku_induk.kurikulum') as $kode => $label)
                    <label class="flex items-start gap-3 p-4 border rounded-lg cursor-pointer"
                        :class="kurikulum === @js($kode) ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-slate-200 hover:bg-slate-50'">
                        <input type="radio" name="kurikulum" value="{{ $kode }}" x-model="kurikulum" class="mt-0.5 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">{{ $label }}</span>
                            <span class="block text-xs text-slate-500">{{ config("buku_induk.keterangan.{$kode}") }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-slate-400">Menentukan format lembar buku induk. Setiap catatan semester menyimpan kurikulumnya sendiri, sehingga masa transisi tetap tercatat.</p>
        </x-card>

        <x-card title="Data dari Data Center">
            <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                <div><dt class="text-slate-500">Nama</dt><dd class="font-medium text-slate-700">{{ $siswa->nama_siswa }}</dd></div>
                <div><dt class="text-slate-500">NIS / NISN</dt><dd class="font-medium text-slate-700">{{ $siswa->nis ?? '-' }} / {{ $siswa->nisn }}</dd></div>
                <div><dt class="text-slate-500">Jenis Kelamin</dt><dd class="font-medium text-slate-700">{{ $siswa->jenis_kelamin_label }}</dd></div>
                <div><dt class="text-slate-500">Tempat, Tgl Lahir</dt><dd class="font-medium text-slate-700">{{ $siswa->tempat_lahir ?? '-' }}{{ $siswa->tanggal_lahir ? ', ' . $siswa->tanggal_lahir->translatedFormat('d F Y') : '' }}</dd></div>
                <div><dt class="text-slate-500">Agama</dt><dd class="font-medium text-slate-700">{{ $siswa->agama ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">Orang Tua</dt><dd class="font-medium text-slate-700">{{ $siswa->nama_ayah ?? '-' }} / {{ $siswa->nama_ibu ?? '-' }}</dd></div>
            </dl>
            <p class="mt-3 text-xs text-slate-400">Data di atas dikelola di aplikasi Data Center dan tidak dapat diubah dari sini.</p>
        </x-card>

        <x-card title="A. Keterangan Tentang Diri Siswa">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <x-field name="nama_panggilan" label="Nama Panggilan" :value="$v('nama_panggilan')" />
                <x-field name="kewarganegaraan" label="Kewarganegaraan" :value="$v('kewarganegaraan')" placeholder="Indonesia" />
                <x-field name="bahasa_sehari_hari" label="Bahasa Sehari-hari" :value="$v('bahasa_sehari_hari')" placeholder="Bahasa Indonesia" />
                <x-field name="anak_ke" label="Anak ke-" type="number" min="1" :value="$v('anak_ke')" />
                <x-select name="status_anak" label="Status Anak">
                    <option value="">- Pilih -</option>
                    @foreach (['Lengkap', 'Yatim', 'Piatu', 'Yatim Piatu'] as $o)
                        <option value="{{ $o }}" @selected($v('status_anak') === $o)>{{ $o }}</option>
                    @endforeach
                </x-select>
                <div></div>
                <x-field name="jumlah_saudara_kandung" label="Saudara Kandung" type="number" min="0" :value="$v('jumlah_saudara_kandung')" />
                <x-field name="jumlah_saudara_tiri" label="Saudara Tiri" type="number" min="0" :value="$v('jumlah_saudara_tiri')" />
                <x-field name="jumlah_saudara_angkat" label="Saudara Angkat" type="number" min="0" :value="$v('jumlah_saudara_angkat')" />
            </div>
        </x-card>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-card title="B. Keterangan Tempat Tinggal">
                <div class="grid grid-cols-1 gap-5">
                    <x-field name="tinggal_dengan" label="Tinggal Bersama" list="dl-tinggal" :value="$v('tinggal_dengan')" />
                    <x-field name="jarak_ke_sekolah" label="Jarak ke Sekolah" :value="$v('jarak_ke_sekolah')" placeholder="Contoh: 2 km" />
                </div>
            </x-card>

            <x-card title="C. Keterangan Kesehatan">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <x-select name="golongan_darah" label="Gol. Darah">
                        <option value="">-</option>
                        @foreach (['A', 'B', 'AB', 'O'] as $o)
                            <option value="{{ $o }}" @selected($v('golongan_darah') === $o)>{{ $o }}</option>
                        @endforeach
                    </x-select>
                    <x-field name="tinggi_badan" label="Tinggi (cm)" type="number" :value="$v('tinggi_badan')" />
                    <x-field name="berat_badan" label="Berat (kg)" type="number" :value="$v('berat_badan')" />
                    <div class="sm:col-span-3"><x-field name="riwayat_penyakit" label="Penyakit yang Pernah Diderita" :value="$v('riwayat_penyakit')" /></div>
                    <div class="sm:col-span-3"><x-field name="kelainan_jasmani" label="Kelainan Jasmani" :value="$v('kelainan_jasmani')" /></div>
                </div>
            </x-card>
        </div>

        <x-card title="D. Keterangan Pendidikan">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <x-select name="status_masuk" label="Status Masuk">
                    <option value="">- Pilih -</option>
                    @foreach (['Siswa Baru', 'Pindahan'] as $o)
                        <option value="{{ $o }}" @selected($v('status_masuk') === $o)>{{ $o }}</option>
                    @endforeach
                </x-select>
                <x-field name="diterima_di_kelas" label="Diterima di Kelas" :value="$v('diterima_di_kelas')" placeholder="Contoh: VII" />
                <x-field name="tanggal_masuk" label="Tanggal Diterima" type="date" :value="$d('tanggal_masuk')" />
                <x-field name="asal_sekolah" label="Asal Sekolah" :value="$v('asal_sekolah')" />
                <div class="md:col-span-2"><x-field name="alamat_asal_sekolah" label="Alamat Asal Sekolah" :value="$v('alamat_asal_sekolah')" /></div>
                <x-field name="nomor_ijazah_asal" label="Nomor Ijazah Sekolah Asal" :value="$v('nomor_ijazah_asal')" />
                <x-field name="tanggal_ijazah_asal" label="Tanggal Ijazah Sekolah Asal" type="date" :value="$d('tanggal_ijazah_asal')" />
                <div></div>
                <x-field name="pindahan_dari" label="Pindahan dari Sekolah" :value="$v('pindahan_dari')" help="Diisi bila siswa pindahan." />
                <div class="md:col-span-2"><x-field name="alasan_pindah" label="Alasan Pindah" :value="$v('alasan_pindah')" /></div>
            </div>
        </x-card>

        <x-card title="E. Keterangan Orang Tua">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <x-field name="pendidikan_ayah" label="Pendidikan Ayah" list="dl-pendidikan" :value="$v('pendidikan_ayah')" />
                <x-field name="pekerjaan_ayah" label="Pekerjaan Ayah" :value="$v('pekerjaan_ayah')" />
                <x-field name="penghasilan_ayah" label="Penghasilan Ayah" list="dl-penghasilan" :value="$v('penghasilan_ayah')" />
                <x-field name="pendidikan_ibu" label="Pendidikan Ibu" list="dl-pendidikan" :value="$v('pendidikan_ibu')" />
                <x-field name="pekerjaan_ibu" label="Pekerjaan Ibu" :value="$v('pekerjaan_ibu')" />
                <x-field name="penghasilan_ibu" label="Penghasilan Ibu" list="dl-penghasilan" :value="$v('penghasilan_ibu')" />
                <div class="md:col-span-3"><x-textarea name="alamat_orang_tua" label="Alamat Orang Tua" rows="2" :value="$v('alamat_orang_tua')" /></div>
            </div>
        </x-card>

        <x-card title="F. Keterangan Wali">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <x-field name="nama_wali" label="Nama Wali" :value="$v('nama_wali')" />
                <x-field name="hubungan_wali" label="Hubungan dengan Siswa" :value="$v('hubungan_wali')" placeholder="Contoh: Paman" />
                <x-field name="telepon_wali" label="Telepon" :value="$v('telepon_wali')" />
                <x-field name="pendidikan_wali" label="Pendidikan" list="dl-pendidikan" :value="$v('pendidikan_wali')" />
                <x-field name="pekerjaan_wali" label="Pekerjaan" :value="$v('pekerjaan_wali')" />
                <div></div>
                <div class="md:col-span-3"><x-textarea name="alamat_wali" label="Alamat Wali" rows="2" :value="$v('alamat_wali')" /></div>
            </div>
        </x-card>

        <x-card title="G. Kegemaran">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-field name="hobi_kesenian" label="Kesenian" :value="$v('hobi_kesenian')" />
                <x-field name="hobi_olahraga" label="Olahraga" :value="$v('hobi_olahraga')" />
                <x-field name="hobi_organisasi" label="Organisasi / Kemasyarakatan" :value="$v('hobi_organisasi')" />
                <x-field name="hobi_lainnya" label="Lain-lain" :value="$v('hobi_lainnya')" />
            </div>
        </x-card>

        <x-card title="H. Keterangan Meninggalkan Sekolah">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <x-field name="tanggal_lulus" label="Tanggal Lulus" type="date" :value="$d('tanggal_lulus')" />
                <x-field name="nomor_ijazah" label="Nomor Ijazah" :value="$v('nomor_ijazah')" />
                <div x-show="kurikulum === 'K13'" x-cloak>
                    <x-field name="nomor_skhun" label="Nomor SKHUN" :value="$v('nomor_skhun')" help="Khusus Kurikulum 2013." />
                </div>
                <x-field name="melanjutkan_ke" label="Melanjutkan ke" :value="$v('melanjutkan_ke')" />
                <x-field name="tanggal_keluar" label="Tanggal Keluar / Pindah" type="date" :value="$d('tanggal_keluar')" />
                <x-field name="alasan_keluar" label="Alasan Keluar / Pindah" :value="$v('alasan_keluar')" />
            </div>
        </x-card>

        <x-card title="Catatan">
            <x-textarea name="catatan" rows="3" :value="$v('catatan')" />
        </x-card>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('buku-induk.show', $siswa) }}" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Batal</a>
            <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan</button>
        </div>
    </form>
@endsection
