<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-field name="nama" label="Nama Program" :value="old('nama', $program->nama)" required />
    </div>

    <x-field name="bidang" label="Bidang" :value="old('bidang', $program->bidang)" placeholder="Contoh: OSIS, Keagamaan, Olahraga" />
    <x-field name="penanggung_jawab" label="Penanggung Jawab" :value="old('penanggung_jawab', $program->penanggung_jawab)" />

    <x-field name="tanggal_mulai" label="Tanggal Mulai" type="date" :value="old('tanggal_mulai', optional($program->tanggal_mulai)->format('Y-m-d'))" />
    <x-field name="tanggal_selesai" label="Tanggal Selesai" type="date" :value="old('tanggal_selesai', optional($program->tanggal_selesai)->format('Y-m-d'))" />

    <x-field name="anggaran" label="Anggaran (Rp)" type="number" min="0" step="1000" :value="old('anggaran', $program->anggaran)" />
    <x-select name="status" label="Status" required>
        @foreach (['rencana' => 'Rencana', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai', 'batal' => 'Batal'] as $val => $lbl)
            <option value="{{ $val }}" @selected(old('status', $program->status ?? 'rencana') === $val)>{{ $lbl }}</option>
        @endforeach
    </x-select>

    <div class="md:col-span-2">
        <x-textarea name="deskripsi" label="Deskripsi" :value="old('deskripsi', $program->deskripsi)" rows="3" />
    </div>
</div>

<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('program-kerja.index') }}" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Batal</a>
    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan</button>
</div>
