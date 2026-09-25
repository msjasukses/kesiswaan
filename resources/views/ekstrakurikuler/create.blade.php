@extends('layouts.admin')

@section('title', 'Tambah Ekstrakurikuler')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Ekstrakurikuler Baru">
            <form method="POST" action="{{ route('ekstrakurikuler.store') }}">
                @csrf
                @include('ekstrakurikuler._form')
            </form>
        </x-card>
    </div>
@endsection
