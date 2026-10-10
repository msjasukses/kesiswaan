@extends('layouts.admin')

@section('title', 'Tambah Hasil Belajar')

@section('content')
    <div class="mb-5">
        <a href="{{ route('buku-induk.show', $siswa) }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Buku Induk
        </a>
        <p class="mt-2 text-sm text-slate-500">Siswa: <span class="font-medium text-slate-700">{{ $siswa->nama_siswa }}</span> &middot; NISN {{ $siswa->nisn }}</p>
    </div>

    <form method="POST" action="{{ route('buku-induk.semester.store', $siswa) }}">
        @csrf
        @include('buku_induk.semester._form')
    </form>
@endsection
