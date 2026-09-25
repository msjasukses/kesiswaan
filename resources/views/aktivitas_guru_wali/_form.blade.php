@php
    $waliLama = (string) old('guru_wali_id', $aktivitas->guru_wali_id);
    $siswaLama = (string) old('siswa_id', $aktivitas->siswa_id);
@endphp

<div x-data="{ wali: @js($waliLama), siswa: '', siswaPerWali: @js($siswaPerWali) }"
     x-init="$nextTick(() => siswa = @js($siswaLama))"
     class="grid grid-cols-1 gap-5 md:grid-cols-2">

    <div class="md:col-span-2">
        <x-select name="guru_wali_id" label="Perwalian" required x-model="wali" @change="siswa = ''">
            <option value="">-- Pilih Perwalian --</option>
            @foreach ($daftarWali as $w)
                <option value="{{ $w->id }}">{{ $w->nama }}</option>
            @endforeach
        </x-select>
    </div>

    <x-field name="tanggal" label="Tanggal" type="date"
        :value="old('tanggal', optional($aktivitas->tanggal)->format('Y-m-d'))" required />

    <div>
        <label for="siswa_id" class="block mb-1 text-sm font-medium text-slate-700">
            Pilih Siswa Binaan<span class="text-red-500"> *</span>
        </label>
        <select name="siswa_id" id="siswa_id" x-model="siswa" required
            class="w-full text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">-- Pilih Siswa --</option>
            <template x-for="s in (siswaPerWali[wali] ?? [])" :key="s.id">
                <option :value="s.id" x-text="s.label"></option>
            </template>
        </select>
        <p class="mt-1 text-xs text-slate-400" x-show="! wali">Pilih perwalian terlebih dahulu.</p>
        @error('siswa_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <x-select name="kategori" label="Kategori Pembinaan" required>
        <option value="">-- Pilih Kategori --</option>
        @foreach (\App\Http\Controllers\AktivitasGuruWaliController::KATEGORI as $k)
            <option value="{{ $k }}" @selected(old('kategori', $aktivitas->kategori) === $k)>{{ $k }}</option>
        @endforeach
    </x-select>

    <x-select name="status" label="Status" required>
        @foreach (\App\Http\Controllers\AktivitasGuruWaliController::STATUS as $s)
            <option value="{{ $s }}" @selected(old('status', $aktivitas->status ?? 'Selesai') === $s)>{{ $s }}</option>
        @endforeach
    </x-select>

    <div class="md:col-span-2">
        <x-textarea name="aktivitas" label="Aktivitas / Kegiatan" :value="old('aktivitas', $aktivitas->aktivitas)" rows="4"
            placeholder="Contoh: Bimbingan belajar kelompok untuk persiapan ulangan harian" required />
    </div>

    <div class="md:col-span-2">
        <x-textarea name="catatan" label="Catatan Guru Wali" :value="old('catatan', $aktivitas->catatan)" rows="3" />
    </div>

    <div class="md:col-span-2">
        <x-textarea name="tindak_lanjut" label="Tindak Lanjut" :value="old('tindak_lanjut', $aktivitas->tindak_lanjut)" rows="3" />
    </div>

    <div class="md:col-span-2">
        <x-textarea name="hasil" label="Hasil / Perkembangan Siswa" :value="old('hasil', $aktivitas->hasil)" rows="3" />
    </div>
</div>

<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('aktivitas-guru-wali.index') }}" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Kembali</a>
    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan Aktivitas</button>
</div>
