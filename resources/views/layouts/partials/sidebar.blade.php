@php
    $itemClass = fn ($active) => $active
        ? 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium bg-indigo-600 text-white'
        : 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white';

    $groupBtn = fn ($active) => 'flex items-center justify-between w-full gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors '
        . ($active ? 'text-white bg-slate-800' : 'text-slate-300 hover:bg-slate-800 hover:text-white');

    $subItem = fn ($active) => $active
        ? 'block px-3 py-2 rounded-lg text-sm font-medium bg-indigo-600 text-white'
        : 'block px-3 py-2 rounded-lg text-sm text-slate-400 hover:bg-slate-800 hover:text-white';

    $gMaster = request()->routeIs('kelas.*', 'siswa.*', 'guru.*', 'buku-klaper.*');
    $gPelanggaran = request()->routeIs('jenis-pelanggaran.*', 'data-pelanggaran.*', 'pelanggaran.*');
    $gKesiswaan = request()->routeIs('program-kerja.*', 'ekstrakurikuler.*', 'jurnal-ekstrakurikuler.*', 'laporan-jurnal-ekstrakurikuler.*', 'guru-wali.*', 'aktivitas-guru-wali.*', 'laporan-aktivitas-guru-wali.*', 'prestasi.*');
    $gPengaturan = request()->routeIs('setelan.*', 'akun.*');
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 transition-transform bg-slate-900">

    {{-- Brand --}}
    <div class="flex items-center h-16 gap-3 px-5 border-b border-slate-800">
        @if (setting('logo'))
            <img src="{{ asset('storage/' . setting('logo')) }}" alt="Logo" class="object-cover bg-white rounded w-9 h-9">
        @else
            <span class="flex items-center justify-center text-lg font-bold text-white bg-indigo-600 w-9 h-9 rounded-lg">K</span>
        @endif
        <div class="min-w-0">
            <p class="text-sm font-semibold leading-tight text-white truncate">{{ setting('nama_sekolah', 'Kesiswaan') }}</p>
            <p class="text-xs text-slate-400">Sistem Kesiswaan</p>
        </div>
    </div>

    {{-- Navigasi --}}
    <nav class="flex-1 px-3 pb-4 space-y-1 overflow-y-auto">
        <a href="{{ route('dashboard') }}" class="{{ $itemClass(request()->routeIs('dashboard')) }} mt-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>

        {{-- Master Data --}}
        <div x-data="{ open: {{ $gMaster ? 'true' : 'false' }} }" class="pt-2">
            <button type="button" @click="open = !open" class="{{ $groupBtn($gMaster) }}">
                <span class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 4 3 8 3s8-1 8-3V7M4 7c0 2 4 3 8 3s8-1 8-3M4 7c0-2 4-3 8-3s8 1 8 3"/></svg>
                    Master Data
                </span>
                <svg class="w-4 h-4 transition-transform" :style="open ? 'transform: rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak class="pl-4 mt-1 ml-2 space-y-1 border-l border-slate-800">
                <a href="{{ route('kelas.index') }}" class="{{ $subItem(request()->routeIs('kelas.*')) }}">Kelas</a>
                <a href="{{ route('siswa.index') }}" class="{{ $subItem(request()->routeIs('siswa.*')) }}">Data Siswa</a>
                <a href="{{ route('guru.index') }}" class="{{ $subItem(request()->routeIs('guru.*')) }}">Data Guru</a>
                <a href="{{ route('buku-klaper.index') }}" class="{{ $subItem(request()->routeIs('buku-klaper.*')) }}">Buku Klaper</a>
            </div>
        </div>

        {{-- Pelanggaran --}}
        <div x-data="{ open: {{ $gPelanggaran ? 'true' : 'false' }} }" class="pt-1">
            <button type="button" @click="open = !open" class="{{ $groupBtn($gPelanggaran) }}">
                <span class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Pelanggaran
                </span>
                <svg class="w-4 h-4 transition-transform" :style="open ? 'transform: rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak class="pl-4 mt-1 ml-2 space-y-1 border-l border-slate-800">
                <a href="{{ route('jenis-pelanggaran.index') }}" class="{{ $subItem(request()->routeIs('jenis-pelanggaran.*')) }}">Jenis Pelanggaran</a>
                <a href="{{ route('data-pelanggaran.index') }}" class="{{ $subItem(request()->routeIs('data-pelanggaran.*')) }}">Data Pelanggaran</a>
                <a href="{{ route('pelanggaran.index') }}" class="{{ $subItem(request()->routeIs('pelanggaran.*')) }}">Pencatatan Pelanggaran</a>
            </div>
        </div>

        {{-- Kesiswaan --}}
        <div x-data="{ open: {{ $gKesiswaan ? 'true' : 'false' }} }" class="pt-1">
            <button type="button" @click="open = !open" class="{{ $groupBtn($gKesiswaan) }}">
                <span class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Kesiswaan
                </span>
                <svg class="w-4 h-4 transition-transform" :style="open ? 'transform: rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak class="pl-4 mt-1 ml-2 space-y-1 border-l border-slate-800">
                <a href="{{ route('program-kerja.index') }}" class="{{ $subItem(request()->routeIs('program-kerja.*')) }}">Program Kerja</a>
                <a href="{{ route('ekstrakurikuler.index') }}" class="{{ $subItem(request()->routeIs('ekstrakurikuler.*')) }}">Ekstrakurikuler</a>
                <a href="{{ route('jurnal-ekstrakurikuler.index') }}" class="{{ $subItem(request()->routeIs('jurnal-ekstrakurikuler.*')) }}">Jurnal Ekstrakurikuler</a>
                <a href="{{ route('laporan-jurnal-ekstrakurikuler.index') }}" class="{{ $subItem(request()->routeIs('laporan-jurnal-ekstrakurikuler.*')) }}">Laporan Jurnal Ekskul</a>
                <a href="{{ route('guru-wali.index') }}" class="{{ $subItem(request()->routeIs('guru-wali.*')) }}">Guru Wali</a>
                <a href="{{ route('aktivitas-guru-wali.index') }}" class="{{ $subItem(request()->routeIs('aktivitas-guru-wali.*')) }}">Aktivitas Guru Wali</a>
                <a href="{{ route('laporan-aktivitas-guru-wali.index') }}" class="{{ $subItem(request()->routeIs('laporan-aktivitas-guru-wali.*')) }}">Laporan Aktivitas Wali</a>
                <a href="{{ route('prestasi.index') }}" class="{{ $subItem(request()->routeIs('prestasi.*')) }}">Prestasi</a>
            </div>
        </div>

        {{-- Pengaturan (admin) --}}
        @if (auth()->user()->isAdmin())
            <div x-data="{ open: {{ $gPengaturan ? 'true' : 'false' }} }" class="pt-1">
                <button type="button" @click="open = !open" class="{{ $groupBtn($gPengaturan) }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Pengaturan
                    </span>
                    <svg class="w-4 h-4 transition-transform" :style="open ? 'transform: rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak class="pl-4 mt-1 ml-2 space-y-1 border-l border-slate-800">
                    <a href="{{ route('setelan.index') }}" class="{{ $subItem(request()->routeIs('setelan.*')) }}">Setelan</a>
                    <a href="{{ route('akun.index') }}" class="{{ $subItem(request()->routeIs('akun.*')) }}">Akun Pengguna</a>
                </div>
            </div>
        @endif
    </nav>
</aside>
