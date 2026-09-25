@extends('layouts.admin')

@section('title', 'Edit Prestasi')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Edit Prestasi">
            <form method="POST" action="{{ route('prestasi.update', $prestasi) }}">
                @csrf @method('PUT')
                @include('prestasi._form')
            </form>
        </x-card>
    </div>
@endsection
