@extends('layouts.admin')

@section('title', 'Edit Pelanggaran')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Edit Catatan Pelanggaran">
            <form method="POST" action="{{ route('pelanggaran.update', $pelanggaran) }}">
                @csrf @method('PUT')
                @include('pelanggaran._form')
            </form>
        </x-card>
    </div>
@endsection
