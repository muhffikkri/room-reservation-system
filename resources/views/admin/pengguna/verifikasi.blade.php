@extends('layouts.app')

@section('title', 'Verifikasi Akun')

@section('content')
    @php
        $clayTableHead = 'border-b border-blue-100/80 bg-blue-50/50 px-5 py-3.5 text-[11px] font-extrabold uppercase tracking-[0.14em] text-blue-700';
        $clayTableCell = 'px-5 py-4 align-middle';
        // Aksi baris memakai token clay yang sama dengan halaman antrean
        // petugas: Verifikasi biru (positif), Tolak merah (destructive).
        $chipLayout = 'inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full px-4 text-xs font-bold';
        $approveChip = "landing-button {$chipLayout} bg-gradient-to-r from-blue-600 to-blue-500 text-white";
        $dangerChip = "clay-button-danger {$chipLayout}";
    @endphp

    <div class="space-y-5">
        <section class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-8" aria-labelledby="verification-heading">
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Manajemen akun</p>
            <h1 id="verification-heading" class="mt-0.5 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">
                Verifikasi Akun Pengguna</h1>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">Akun hasil registrasi mandiri menunggu persetujuan
                admin.</p>

            <div class="clay-inset mt-6 overflow-hidden rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr>
                                <th scope="col" class="{{ $clayTableHead }}">Nama</th>
                                <th scope="col" class="{{ $clayTableHead }}">Email</th>
                                <th scope="col" class="{{ $clayTableHead }}">NIM/NIP</th>
                                <th scope="col" class="{{ $clayTableHead }}">Terdaftar</th>
                                <th scope="col" class="{{ $clayTableHead }} text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-100/70">
                            @forelse ($pendingUsers as $pendingUser)
                                <tr class="transition-colors hover:bg-white/70">
                                    <td class="{{ $clayTableCell }} font-bold text-[#10264a]">{{ $pendingUser->name }}</td>
                                    <td class="{{ $clayTableCell }}">{{ $pendingUser->email }}</td>
                                    <td class="{{ $clayTableCell }}">{{ $pendingUser->identity ?? '-' }}</td>
                                    <td class="{{ $clayTableCell }} whitespace-nowrap text-xs text-slate-500">
                                        {{ $pendingUser->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                    </td>
                                    <td class="{{ $clayTableCell }}">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.pengguna.verify', $pendingUser) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="{{ $approveChip }}">Verifikasi</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.pengguna.reject', $pendingUser) }}"
                                                data-confirm-message="Tolak akun ini?">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="{{ $dangerChip }}">Tolak</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-14 text-center">
                                        <span
                                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-white bg-white text-2xl text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.15)]">
                                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                        <p class="mt-4 text-base font-bold text-slate-800">Tidak ada akun yang menunggu
                                            verifikasi.</p>
                                        <p class="mt-1 text-sm text-slate-500">Semua registrasi mandiri sudah ditinjau
                                            admin.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection
