@extends('layouts.admin')

@section('title', 'Tambah Guru Wali')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Guru Wali Baru">
            <form method="POST" action="{{ route('guru-wali.store') }}">
                @csrf
                @include('guru_wali._form')
            </form>
        </x-card>
    </div>
@endsection
