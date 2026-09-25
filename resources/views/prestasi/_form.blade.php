<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-select name="siswa_id" label="Siswa" required>
            <option value="">- Pilih Siswa -</option>
            @foreach ($daftarSiswa as $s)
                <option value="{{ $s->id }}" @selected(old('siswa_id', $prestasi->siswa_id) == $s->id)>
                    {{ $s->nama }} — {{ $s->kelas?->nama ?? 'tanpa kelas' }}
                </option>
            @endforeach
        </x-select>
    </div>

    <div class="md:col-span-2">
        <x-field name="nama_prestasi" label="Nama Prestasi" :value="old('nama_prestasi', $prestasi->nama_prestasi)" placeholder="Contoh: Juara Olimpiade Matematika" required />
    </div>

    <x-select name="bidang" label="Bidang" required>
        @foreach (['Akademik', 'Non-Akademik'] as $b)
            <option value="{{ $b }}" @selected(old('bidang', $prestasi->bidang ?? 'Akademik') === $b)>{{ $b }}</option>
        @endforeach
    </x-select>

    <x-select name="tingkat" label="Tingkat" required>
        @foreach (['Sekolah', 'Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Nasional', 'Internasional'] as $t)
            <option value="{{ $t }}" @selected(old('tingkat', $prestasi->tingkat ?? 'Sekolah') === $t)>{{ $t }}</option>
        @endforeach
    </x-select>

    <x-field name="peringkat" label="Peringkat" :value="old('peringkat', $prestasi->peringkat)" placeholder="Contoh: Juara 1, Harapan 2" />
    <x-field name="tanggal" label="Tanggal" type="date" :value="old('tanggal', optional($prestasi->tanggal)->format('Y-m-d'))" />

    <div class="md:col-span-2">
        <x-field name="penyelenggara" label="Penyelenggara" :value="old('penyelenggara', $prestasi->penyelenggara)" />
    </div>

    <div class="md:col-span-2">
        <x-textarea name="keterangan" label="Keterangan" :value="old('keterangan', $prestasi->keterangan)" rows="2" />
    </div>
</div>

<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('prestasi.index') }}" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Batal</a>
    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan</button>
</div>
