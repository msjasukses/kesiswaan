@extends('layouts.admin')

@section('title', 'Edit Program Kerja')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Edit Program Kerja">
            <form method="POST" action="{{ route('program-kerja.update', $program) }}">
                @csrf @method('PUT')
                @include('program_kerja._form')
            </form>
        </x-card>
    </div>
@endsection
