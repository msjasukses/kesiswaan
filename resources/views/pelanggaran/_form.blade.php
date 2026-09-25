<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <x-select name="siswa_id" label="Siswa" required>
        <option value="">- Pilih Siswa -</option>
        @foreach ($daftarSiswa as $s)
            <option value="{{ $s->id }}" @selected(old('siswa_id', $pelanggaran->siswa_id) == $s->id)>
                {{ $s->nama }} — {{ $s->kelas?->nama ?? 'tanpa kelas' }} ({{ $s->nis }})
            </option>
        @endforeach
    </x-select>

    <x-field name="tanggal" label="Tanggal" type="date" :value="old('tanggal', optional($pelanggaran->tanggal)->format('Y-m-d') ?? now()->format('Y-m-d'))" required />

    <div class="md:col-span-2">
        <x-select name="data_pelanggaran_id" label="Jenis / Butir Pelanggaran" required>
            <option value="">- Pilih Pelanggaran -</option>
            @foreach ($daftarButir as $b)
                <option value="{{ $b->id }}" @selected(old('data_pelanggaran_id', $pelanggaran->data_pelanggaran_id) == $b->id)>
                    [{{ $b->jenisPelanggaran->nama }}] {{ $b->nama }} — {{ $b->poin }} poin
                </option>
            @endforeach
        </x-select>
    </div>

    <div class="md:col-span-2">
        <x-textarea name="keterangan" label="Keterangan / Kronologi" :value="old('keterangan', $pelanggaran->keterangan)" rows="3" />
    </div>

    <div class="md:col-span-2">
        <x-field name="tindak_lanjut" label="Tindak Lanjut" :value="old('tindak_lanjut', $pelanggaran->tindak_lanjut)" placeholder="Contoh: Teguran lisan, panggilan orang tua" />
    </div>
</div>

@if ($daftarButir->isEmpty() || $daftarSiswa->isEmpty())
    <p class="mt-4 text-sm text-amber-600">Pastikan data <a href="{{ route('siswa.index') }}" class="underline">Siswa</a> dan <a href="{{ route('data-pelanggaran.index') }}" class="underline">butir pelanggaran</a> sudah tersedia.</p>
@endif

<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('pelanggaran.index') }}" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Batal</a>
    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan</button>
</div>
