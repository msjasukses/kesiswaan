<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') &middot; {{ setting('nama_sekolah', config('app.name')) }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-800">
<div x-data="{ sidebarOpen: false }" class="min-h-screen">

    {{-- Sidebar --}}
    @include('layouts.partials.sidebar')

    {{-- Overlay (mobile) --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-slate-900/50 lg:hidden"></div>

    {{-- Konten utama --}}
    <div class="lg:pl-64">
        {{-- Topbar --}}
        <header class="sticky top-0 z-10 flex items-center justify-between h-16 px-4 bg-white border-b border-slate-200 sm:px-6">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="p-2 -ml-2 rounded-md text-slate-500 hover:bg-slate-100 lg:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-slate-800">@yield('title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-3">
                {{-- Pemilih tahun ajaran --}}
                @php $daftarTa = \App\Models\TahunAjaran::orderByDesc('is_aktif')->orderByDesc('id')->get(); @endphp
                @if ($daftarTa->isNotEmpty())
                    <form method="POST" action="{{ route('tahun-ajaran.set') }}" class="hidden sm:block">
                        @csrf
                        <div class="flex items-center gap-1.5 px-2 py-1 border rounded-lg border-slate-200 bg-slate-50">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <select name="tahun_ajaran_id" onchange="this.form.submit()" class="py-0 text-sm bg-transparent border-0 text-slate-600 focus:ring-0 pr-7">
                                @foreach ($daftarTa as $ta)
                                    <option value="{{ $ta->id }}" @selected(current_ta_id() == $ta->id)>T.A. {{ $ta->nama_tahun_ajaran }}{{ $ta->is_aktif ? ' (aktif)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                @endif

            {{-- User dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-100">
                    <span class="flex items-center justify-center w-8 h-8 text-sm font-semibold text-white rounded-full bg-indigo-600">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span class="hidden text-sm text-left sm:block">
                        <span class="block font-medium leading-tight text-slate-700">{{ auth()->user()->name }}</span>
                        <span class="block text-xs capitalize text-slate-400">{{ auth()->user()->role }}</span>
                    </span>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak @click.outside="open = false" x-transition
                     class="absolute right-0 z-20 mt-2 bg-white border rounded-lg shadow-lg w-48 border-slate-200 py-1">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Profil Saya</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 text-sm text-left text-red-600 hover:bg-red-50">Keluar</button>
                    </form>
                </div>
            </div>
            </div>
        </header>

        {{-- Page --}}
        <main class="p-4 sm:p-6">
            @if (session('success'))
                <x-flash type="success">{{ session('success') }}</x-flash>
            @endif
            @if (session('error'))
                <x-flash type="error">{{ session('error') }}</x-flash>
            @endif
            @if ($errors->any())
                <x-flash type="error">Terdapat {{ $errors->count() }} kesalahan pada isian. Silakan periksa kembali.</x-flash>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
