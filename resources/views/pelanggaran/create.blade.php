@extends('layouts.admin')

@section('title', 'Catat Pelanggaran')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Formulir Pencatatan Pelanggaran">
            <form method="POST" action="{{ route('pelanggaran.store') }}">
                @csrf
                @include('pelanggaran._form')
            </form>
        </x-card>
    </div>
@endsection
