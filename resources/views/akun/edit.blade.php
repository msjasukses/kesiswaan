@extends('layouts.admin')

@section('title', 'Edit Akun')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Edit Akun Pengguna">
            <form method="POST" action="{{ route('akun.update', $user) }}">
                @csrf @method('PUT')
                @include('akun._form')
            </form>
        </x-card>
    </div>
@endsection
