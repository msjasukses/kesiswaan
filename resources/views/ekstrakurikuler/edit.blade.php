@extends('layouts.admin')

@section('title', 'Edit Ekstrakurikuler')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Edit Ekstrakurikuler">
            <form method="POST" action="{{ route('ekstrakurikuler.update', $ekskul) }}">
                @csrf @method('PUT')
                @include('ekstrakurikuler._form')
            </form>
        </x-card>
    </div>
@endsection
