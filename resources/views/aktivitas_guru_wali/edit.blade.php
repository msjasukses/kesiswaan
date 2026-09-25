@extends('layouts.admin')

@section('title', 'Edit Aktivitas Guru Wali')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Ubah Aktivitas Pembinaan">
            <form method="POST" action="{{ route('aktivitas-guru-wali.update', $aktivitas) }}">
                @csrf @method('PUT')
                @include('aktivitas_guru_wali._form')
            </form>
        </x-card>
    </div>
@endsection
