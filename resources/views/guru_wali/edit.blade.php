@extends('layouts.admin')

@section('title', 'Edit Guru Wali')

@section('content')
    <div class="max-w-3xl">
        <x-card title="Ubah Guru Wali">
            <form method="POST" action="{{ route('guru-wali.update', $wali) }}">
                @csrf @method('PUT')
                @include('guru_wali._form')
            </form>
        </x-card>
    </div>
@endsection
