<div class="space-y-5">
    <x-field name="nama" label="Nama Kategori" :value="old('nama', $jenis->nama)" placeholder="Contoh: Kedisiplinan, Kerapihan, Kelakuan" required />
    <x-textarea name="deskripsi" label="Deskripsi" :value="old('deskripsi', $jenis->deskripsi)" rows="3" />
</div>

<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('jenis-pelanggaran.index') }}" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Batal</a>
    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan</button>
</div>
