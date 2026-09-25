@extends('layouts.admin')

@section('title', 'Tambah Prestasi')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Prestasi Baru">
            <form method="POST" action="{{ route('prestasi.store') }}">
                @csrf
                @include('prestasi._form')
            </form>
        </x-card>
    </div>
@endsection
