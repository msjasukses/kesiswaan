<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <x-select name="jenis_pelanggaran_id" label="Kategori (Jenis Pelanggaran)" required>
        <option value="">- Pilih Kategori -</option>
        @foreach ($daftarJenis as $j)
            <option value="{{ $j->id }}" @selected(old('jenis_pelanggaran_id', $data->jenis_pelanggaran_id) == $j->id)>{{ $j->nama }}</option>
        @endforeach
    </x-select>

    <x-field name="poin" label="Poin" type="number" min="0" :value="old('poin', $data->poin)" required />

    <div class="md:col-span-2">
        <x-field name="nama" label="Nama Butir Pelanggaran" :value="old('nama', $data->nama)" placeholder="Contoh: Terlambat masuk sekolah" required />
    </div>

    <div class="md:col-span-2">
        <x-textarea name="deskripsi" label="Deskripsi" :value="old('deskripsi', $data->deskripsi)" rows="2" />
    </div>
</div>

@if ($daftarJenis->isEmpty())
    <p class="mt-4 text-sm text-amber-600">Belum ada kategori. Tambahkan <a href="{{ route('jenis-pelanggaran.create') }}" class="underline">Jenis Pelanggaran</a> terlebih dahulu.</p>
@endif

<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('data-pelanggaran.index') }}" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Batal</a>
    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan</button>
</div>
