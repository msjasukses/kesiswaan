@extends('layouts.admin')

@section('title', 'Edit Jenis Pelanggaran')

@section('content')
    <div class="max-w-2xl">
        <x-card title="Edit Kategori Pelanggaran">
            <form method="POST" action="{{ route('jenis-pelanggaran.update', $jenis) }}">
                @csrf @method('PUT')
                @include('jenis_pelanggaran._form')
            </form>
        </x-card>
    </div>
@endsection
