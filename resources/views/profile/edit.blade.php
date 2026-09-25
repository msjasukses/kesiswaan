@extends('layouts.admin')

@section('title', 'Profil Saya')

@section('content')
    <div class="max-w-3xl space-y-6">
        <x-card title="Informasi Profil">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </x-card>

        <x-card title="Ubah Password">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </x-card>

        <x-card title="Hapus Akun">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </x-card>
    </div>
@endsection
