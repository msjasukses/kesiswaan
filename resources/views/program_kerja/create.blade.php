@extends('layouts.admin')

@section('title', 'Tambah Program Kerja')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Program Kerja Baru">
            <form method="POST" action="{{ route('program-kerja.store') }}">
                @csrf
                @include('program_kerja._form')
            </form>
        </x-card>
    </div>
@endsection
