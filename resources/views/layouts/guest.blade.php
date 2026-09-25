<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Masuk &middot; {{ setting('nama_sekolah', config('app.name')) }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900">
        <div class="flex flex-col items-center justify-center min-h-screen px-4 py-8 bg-slate-100">
            <div class="flex flex-col items-center mb-6">
                @if (setting('logo'))
                    <img src="{{ asset('storage/' . setting('logo')) }}" alt="Logo" class="object-contain w-16 h-16 mb-3 bg-white rounded-lg">
                @else
                    <span class="flex items-center justify-center mb-3 text-2xl font-bold text-white bg-indigo-600 w-14 h-14 rounded-xl">K</span>
                @endif
                <h1 class="text-lg font-bold text-center text-slate-800">{{ setting('nama_sekolah', 'Aplikasi Kesiswaan') }}</h1>
                <p class="text-sm text-slate-500">Sistem Informasi Kesiswaan</p>
            </div>

            <div class="w-full px-6 py-6 overflow-hidden bg-white shadow-md sm:max-w-md rounded-xl">
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-slate-400">&copy; {{ date('Y') }} {{ setting('nama_sekolah', 'Aplikasi Kesiswaan') }}</p>
        </div>
    </body>
</html>
