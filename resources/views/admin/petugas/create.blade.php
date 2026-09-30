@extends('layouts.app')

@section('title', 'Tambah Akun Petugas')

@section('content')
    <x-admin.account-form heading="Tambah Akun Petugas" :action="route('admin.petugas.store')"
        :cancel-url="route('admin.petugas.index')" submit-label="Buat Akun Petugas" identity-label="NIP" />
@endsection
