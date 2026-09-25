@extends('layouts.admin')

@section('title', 'Edit Data Pelanggaran')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Edit Butir Pelanggaran">
            <form method="POST" action="{{ route('data-pelanggaran.update', $data) }}">
                @csrf @method('PUT')
                @include('data_pelanggaran._form')
            </form>
        </x-card>
    </div>
@endsection
