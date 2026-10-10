@props([
    'heading' => 'Masuk',
    'subheading' => 'Silakan masuk untuk melanjutkan.',
])

@php
    $sekolah = setting('nama_sekolah', 'Aplikasi Kesiswaan');
    // Tombol "Beranda" hanya tampil bila alamat situs sekolah diisi di Setelan.
    $beranda = setting('website');
    if ($beranda && ! preg_match('#^https?://#i', $beranda)) {
        $beranda = 'https://' . ltrim($beranda, '/');
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $heading }} &middot; {{ $sekolah }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .login-hero {
            background:
                radial-gradient(circle at 20% 20%, rgba(20, 184, 166, 0.30), transparent 50%),
                radial-gradient(circle at 80% 30%, rgba(16, 185, 129, 0.20), transparent 55%),
                radial-gradient(circle at 60% 100%, rgba(56, 189, 248, 0.14), transparent 60%),
                linear-gradient(135deg, #0f172a 0%, #062275 68%, #0d1e94 100%);
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-50 text-slate-900">
<div class="grid min-h-screen lg:grid-cols-5">

    {{-- Kiri: panel sambutan --}}
    <div class="relative flex flex-col justify-between p-8 overflow-hidden text-white sm:p-12 lg:col-span-3 login-hero">
        <div class="absolute rounded-full pointer-events-none -top-24 -right-24 w-96 h-96 bg-white/5 blur-3xl"></div>
        <div class="absolute rounded-full pointer-events-none -bottom-32 -left-12 w-96 h-96 bg-white/5 blur-3xl"></div>

        <div class="relative z-10 flex items-center justify-between gap-3">
            @if (setting('logo'))
                <img src="{{ asset('storage/' . setting('logo')) }}" alt="Logo {{ $sekolah }}"
                    class="object-contain w-12 h-12 p-1.5 shadow-lg rounded-xl bg-white/95">
            @else
                <div class="grid w-12 h-12 font-bold text-white shadow-lg place-items-center rounded-xl bg-white/20 ring-2 ring-white/40">
                    {{ mb_substr($sekolah, 0, 1) }}
                </div>
            @endif

            @if ($beranda)
                <a href="{{ $beranda }}"
                    class="text-xs font-semibold transition border rounded-full text-white/85 hover:text-white bg-white/10 hover:bg-white/15 border-white/20 px-3.5 py-2">
                    &larr; Beranda
                </a>
            @endif
        </div>

        <div class="relative z-10 max-w-xl my-12 lg:my-0">
            <h1 class="text-3xl font-bold leading-tight sm:text-4xl xl:text-5xl">
                Selamat datang di Sistem Kesiswaan.
            </h1>
            <p class="mt-4 leading-relaxed text-white/85 sm:text-lg">
                Kelola pelanggaran, ekstrakurikuler, perwalian, dan prestasi siswa dalam satu aplikasi terpadu.
            </p>
        </div>

        <div class="relative z-10 text-xs text-white/70">
            &copy; {{ date('Y') }} {{ $sekolah }} &mdash; Powered by CyberGarage
        </div>
    </div>

    {{-- Kanan: formulir --}}
    <div class="flex items-center justify-center p-6 lg:col-span-2 sm:p-12 bg-slate-50">
        <div class="w-full max-w-md">
            <h2 class="text-3xl font-bold text-slate-900">{{ $heading }}</h2>
            <p class="mt-1.5 text-sm text-slate-500">{{ $subheading }}</p>

            {{ $slot }}
        </div>
    </div>
</div>
</body>
</html>
