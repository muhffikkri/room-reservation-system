@extends('layouts.app')

@section('title', 'Kelola Akun Admin')

@section('content')
    <div class="space-y-5">
        <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-8" aria-labelledby="admins-heading">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Manajemen akun</p>
                    <h1 id="admins-heading" class="mt-0.5 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">
                        Akun Admin</h1>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">Admin baru hanya dibuat oleh admin aktif, tidak
                        bisa registrasi mandiri.</p>
                </div>
                <a href="{{ route('admin.admin.create') }}"
                    class="landing-button inline-flex min-h-11 items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-2.5 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                    Tambah Admin
                </a>
            </div>

            <x-admin.account-table :accounts="$admins" empty-message="Belum ada akun admin." />
        </section>
    </div>
@endsection
