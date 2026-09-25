<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-field name="nama" label="Nama Ekstrakurikuler" :value="old('nama', $ekskul->nama)" placeholder="Contoh: Pramuka, PMR, Futsal" required />
    </div>

    <x-field name="pembina" label="Pembina" :value="old('pembina', $ekskul->pembina)" />
    <x-field name="jadwal" label="Jadwal" :value="old('jadwal', $ekskul->jadwal)" placeholder="Contoh: Sabtu, 08.00 - 10.00" />

    <div class="md:col-span-2">
        <x-field name="tempat" label="Tempat" :value="old('tempat', $ekskul->tempat)" placeholder="Contoh: Lapangan, Aula" />
    </div>

    <div class="md:col-span-2">
        <x-textarea name="deskripsi" label="Deskripsi" :value="old('deskripsi', $ekskul->deskripsi)" rows="3" />
    </div>
</div>

<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('ekstrakurikuler.index') }}" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Batal</a>
    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan</button>
</div>
