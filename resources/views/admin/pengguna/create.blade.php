@extends('layouts.app')

@section('title', 'Tambah Akun Pengguna')

@section('content')
    <x-admin.account-form heading="Tambah Akun Pengguna" :action="route('admin.pengguna.store')"
        :cancel-url="route('admin.pengguna.index')" submit-label="Buat Akun Pengguna" identity-label="NIM/NIP" />
@endsection
