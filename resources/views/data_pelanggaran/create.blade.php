@extends('layouts.admin')

@section('title', 'Tambah Data Pelanggaran')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Butir Pelanggaran Baru">
            <form method="POST" action="{{ route('data-pelanggaran.store') }}">
                @csrf
                @include('data_pelanggaran._form')
            </form>
        </x-card>
    </div>
@endsection
