@extends('layouts.app')

@section('title', 'Kelola Akun Pengguna')

@section('content')
    <div class="space-y-5">
        <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-8" aria-labelledby="users-heading">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Manajemen akun</p>
                    <h1 id="users-heading" class="mt-0.5 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">
                        Akun Pengguna</h1>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">Seluruh pengguna beserta status akunnya. Akun
                        pending diproses di <a href="{{ route('admin.pengguna.verifikasi') }}"
                            class="font-bold text-blue-600 underline decoration-blue-200 underline-offset-2 transition hover:text-blue-700">halaman
                            verifikasi</a>.</p>
                </div>
                <a href="{{ route('admin.pengguna.create') }}"
                    class="landing-button inline-flex min-h-11 items-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-5 py-2.5 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                    Tambah Pengguna
                </a>
            </div>

            <x-admin.account-table :accounts="$users" identity-label="NIM/NIP" empty-message="Belum ada akun pengguna."
                restore-route="admin.pengguna.restore" />
        </section>
    </div>
@endsection
