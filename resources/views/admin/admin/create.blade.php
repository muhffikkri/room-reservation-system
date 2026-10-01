@extends('layouts.app')

@section('title', 'Tambah Akun Admin')

@section('content')
    <x-admin.account-form heading="Tambah Akun Admin" :action="route('admin.admin.store')"
        :cancel-url="route('admin.admin.index')" submit-label="Buat Akun Admin" identity-label="NIP" />
@endsection
