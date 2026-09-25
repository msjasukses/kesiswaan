@extends('layouts.admin')

@section('title', 'Tambah Akun')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Akun Pengguna Baru">
            <form method="POST" action="{{ route('akun.store') }}">
                @csrf
                @include('akun._form')
            </form>
        </x-card>
    </div>
@endsection
