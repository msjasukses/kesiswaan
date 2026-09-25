<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <x-field name="name" label="Nama Lengkap" :value="old('name', $user->name)" required />
    <x-field name="email" label="Email" type="email" :value="old('email', $user->email)" required />

    <x-select name="role" label="Peran" required>
        <option value="guru" @selected(old('role', $user->role ?? 'guru') === 'guru')>Guru / Pembina</option>
        <option value="admin" @selected(old('role', $user->role ?? 'guru') === 'admin')>Admin</option>
    </x-select>
    <x-field name="jabatan" label="Jabatan" :value="old('jabatan', $user->jabatan)" placeholder="Contoh: Wakasek Kesiswaan, Wali Kelas" />

    <x-field name="telepon" label="No. Telepon" :value="old('telepon', $user->telepon)" />
    <div class="flex items-end">
        <label class="inline-flex items-center gap-2 mb-2.5">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))
                class="text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
            <span class="text-sm text-slate-700">Akun aktif</span>
        </label>
    </div>

    <x-field name="password" label="{{ $user->exists ? 'Password Baru (kosongkan jika tidak diubah)' : 'Password' }}" type="password" :required="! $user->exists" autocomplete="new-password" />
    <x-field name="password_confirmation" label="Konfirmasi Password" type="password" autocomplete="new-password" />
</div>

<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('akun.index') }}" class="px-4 py-2 text-sm font-medium border rounded-lg text-slate-600 border-slate-300 hover:bg-slate-50">Batal</a>
    <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan</button>
</div>
