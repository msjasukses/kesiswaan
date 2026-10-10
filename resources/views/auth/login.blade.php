<x-guest-layout heading="Login Kesiswaan" subheading="Khusus untuk pengguna terdaftar.">

    @if (session('status'))
        <div class="p-3 mt-4 text-sm border rounded-lg text-emerald-800 bg-emerald-50 border-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-3 mt-4 text-sm border rounded-lg text-amber-800 bg-amber-50 border-amber-200">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="block mb-1.5 text-sm font-semibold text-slate-700">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                placeholder="Masukkan email Anda" required autofocus autocomplete="username"
                class="w-full px-4 py-2.5 text-sm bg-white border rounded-lg shadow-sm border-slate-300 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
            @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div x-data="{ show: false }">
            <label for="password" class="block mb-1.5 text-sm font-semibold text-slate-700">Password</label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" name="password" id="password"
                    placeholder="••••••••" required autocomplete="current-password"
                    class="w-full px-4 py-2.5 pr-11 text-sm bg-white border rounded-lg shadow-sm border-slate-300 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                <button type="button" @click="show = ! show"
                    :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
                    class="absolute -translate-y-1/2 right-3 top-1/2 text-slate-400 hover:text-slate-600">
                    <svg x-show="! show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <label for="remember" class="flex items-center gap-2 text-sm cursor-pointer text-slate-600">
            <input type="checkbox" name="remember" id="remember"
                class="rounded shadow-sm border-slate-300 text-emerald-600 focus:ring-emerald-500">
            Ingat saya di perangkat ini
        </label>

        <button type="submit"
            class="flex items-center justify-center w-full gap-2 py-3 text-base font-semibold text-white transition rounded-lg shadow-sm group bg-emerald-500 hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
            Login
            <span class="transition group-hover:translate-x-1">&rarr;</span>
        </button>
    </form>

    <p class="mt-8 text-xs text-center text-slate-500">
        @if (Route::has('password.request'))
            Lupa password? <a href="{{ route('password.request') }}" class="font-medium text-emerald-600 hover:underline">Atur ulang di sini</a>
            atau hubungi administrator sistem.
        @else
            Lupa password? Hubungi administrator sistem.
        @endif
    </p>
</x-guest-layout>
