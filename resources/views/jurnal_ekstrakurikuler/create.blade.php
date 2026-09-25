@extends('layouts.admin')

@section('title', 'Tambah Jurnal Ekstrakurikuler')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Jurnal Kegiatan Baru">
            <form method="POST" action="{{ route('jurnal-ekstrakurikuler.store') }}" enctype="multipart/form-data">
                @csrf
                @include('jurnal_ekstrakurikuler._form')
            </form>
        </x-card>
    </div>
@endsection
