@extends('layouts.admin')

@section('title', 'Tambah Jenis Pelanggaran')

@section('content')
    <div class="max-w-2xl">
        <x-card title="Kategori Pelanggaran Baru">
            <form method="POST" action="{{ route('jenis-pelanggaran.store') }}">
                @csrf
                @include('jenis_pelanggaran._form')
            </form>
        </x-card>
    </div>
@endsection
