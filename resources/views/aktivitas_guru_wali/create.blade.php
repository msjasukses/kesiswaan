@extends('layouts.admin')

@section('title', 'Tambah Aktivitas Guru Wali')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Aktivitas Pembinaan Baru">
            <form method="POST" action="{{ route('aktivitas-guru-wali.store') }}">
                @csrf
                @include('aktivitas_guru_wali._form')
            </form>
        </x-card>
    </div>
@endsection
