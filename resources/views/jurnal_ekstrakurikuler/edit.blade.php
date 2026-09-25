@extends('layouts.admin')

@section('title', 'Edit Jurnal Ekstrakurikuler')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Ubah Jurnal Kegiatan">
            <form method="POST" action="{{ route('jurnal-ekstrakurikuler.update', $jurnal) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                @include('jurnal_ekstrakurikuler._form')
            </form>
        </x-card>
    </div>
@endsection
