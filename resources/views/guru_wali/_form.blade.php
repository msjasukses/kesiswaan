<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-field name="nama" label="Nama Perwalian" :value="old('nama', $wali->nama)" placeholder="Contoh: Perwalian 7A, Kelompok Bimbingan 1" required />
    </div>

    <x-select name="guru_id" label="Guru Wali" required>
        <option value="">- Pilih Guru -</option>
        @foreach ($daftarGuru as $g)
            <option value="{{ $g->id }}" @selected(old('guru_id', $wali->guru_id) == $g->id)>{{ $g->nama_ptk }}</option>
        @endforeach
    </x-select>
    <x-field name="jadwal" label="Jadwal" :value="old('jadwal', $wali->jadwal)" placeholder="Contoh: Senin, 07.00 - 07.30" />

    <div class="md:col-span-2">
        <x-field name="tempat" label="Tempat" :value="old('tempat', $wali->tempat)" placeholder="Contoh: Ruang Kelas 7A, Ruang BK" />
    </div>

    <div class="md:col-span-2">
        <x-textarea name="deskripsi" label="Deskripsi" :value="old('deskripsi', $wali->deskripsi)" rows="3" />
    </div>
</div>

<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('guru-wali.index') }}" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Batal</a>
    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan</button>
</div>
