@php
    $labelClass = 'flex items-center gap-2 mb-1 text-sm font-medium text-slate-700';
@endphp

<div x-data="{ pembinaKosong: @js(! old('pembina', $jurnal->pembina)) }" class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-select name="ekstrakurikuler_id" label="Jenis Ekskul" required
            x-on:change="if (pembinaKosong) $refs.pembina.value = $event.target.selectedOptions[0].dataset.pembina ?? ''">
            <option value="">Pilih Jenis Ekskul...</option>
            @foreach ($daftarEkskul as $e)
                <option value="{{ $e->id }}" data-pembina="{{ $e->pembina }}"
                    @selected(old('ekstrakurikuler_id', $jurnal->ekstrakurikuler_id) == $e->id)>{{ $e->nama }}</option>
            @endforeach
        </x-select>
    </div>

    <x-field name="pembina" label="Nama Pembina" :value="old('pembina', $jurnal->pembina)"
        placeholder="Masukkan nama pembina" x-ref="pembina" x-on:input="pembinaKosong = ! $event.target.value" required />
    <x-field name="pelatih" label="Nama Pelatih" :value="old('pelatih', $jurnal->pelatih)"
        placeholder="Masukkan nama pelatih" required />

    <x-field name="tanggal" label="Tanggal Kegiatan" type="date"
        :value="old('tanggal', optional($jurnal->tanggal)->format('Y-m-d'))" required />
    <x-field name="materi" label="Materi Ekskul" :value="old('materi', $jurnal->materi)"
        placeholder="Contoh: Latihan Baris Berbaris" required />

    <div>
        <label for="siswa_hadir" class="{{ $labelClass }}">
            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-600">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </span>
            Siswa Hadir
        </label>
        <input type="number" name="siswa_hadir" id="siswa_hadir" min="0"
            value="{{ old('siswa_hadir', $jurnal->siswa_hadir ?? 0) }}"
            class="w-full text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('siswa_hadir')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="siswa_tidak_hadir" class="{{ $labelClass }}">
            <span class="flex items-center justify-center w-5 h-5 text-red-600 bg-red-100 rounded-full">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </span>
            Siswa Tidak Hadir
        </label>
        <input type="number" name="siswa_tidak_hadir" id="siswa_tidak_hadir" min="0"
            value="{{ old('siswa_tidak_hadir', $jurnal->siswa_tidak_hadir ?? 0) }}"
            class="w-full text-sm rounded-lg shadow-sm border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('siswa_tidak_hadir')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Dokumentasi foto: klik atau seret berkas ke area putus-putus --}}
    <div class="md:col-span-2"
         x-data="{
            preview: @js($jurnal->foto ? asset('storage/' . $jurnal->foto) : null),
            nama: null,
            seret: false,
            ambil(files) {
                const file = files[0];
                if (! file || ! file.type.startsWith('image/')) return;
                this.$refs.foto.files = files;
                this.nama = file.name;
                this.preview = URL.createObjectURL(file);
            },
         }">
        <label for="foto" class="{{ $labelClass }}">
            <span class="flex items-center justify-center w-5 h-5 text-indigo-600 rounded bg-indigo-100">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            Dokumentasi Foto @unless ($jurnal->exists)<span class="text-red-500">*</span>@endunless
        </label>

        <div @click="$refs.foto.click()"
             @dragover.prevent="seret = true" @dragleave.prevent="seret = false"
             @drop.prevent="seret = false; ambil($event.dataTransfer.files)"
             :class="seret ? 'border-indigo-500 bg-indigo-50' : 'border-indigo-200 hover:bg-slate-50'"
             class="flex flex-col items-center justify-center gap-2 px-4 py-8 text-sm text-center border-2 border-dashed rounded-lg cursor-pointer text-slate-500">
            <template x-if="preview">
                <img :src="preview" class="object-cover h-32 border rounded-lg border-slate-200">
            </template>
            <p>
                <span x-text="nama ?? 'Seret foto ke sini atau '"></span><span x-show="! nama" class="font-medium text-indigo-600">klik untuk unggah</span>
            </p>
            <p class="text-xs text-slate-400">Format gambar, maks. 2MB.</p>
        </div>

        <input type="file" name="foto" id="foto" accept="image/*" class="hidden" x-ref="foto"
            @change="ambil($event.target.files)">
        @error('foto')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('jurnal-ekstrakurikuler.index') }}" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Batal</a>
    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan</button>
</div>
