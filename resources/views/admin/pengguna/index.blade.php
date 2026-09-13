@extends('layouts.app')

@section('title', 'Kelola Akun Pengguna')

@section('content')
    <div class="overflow-hidden rounded-2xl border border-[#E2E7FF] bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-[#EEF2FF] px-6 py-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Manajemen akun</p>
                <h1 class="mt-2 text-xl font-semibold tracking-tight text-[#00236f]">Akun Pengguna</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Seluruh pengguna beserta status akunnya. Akun pending diproses di <a href="{{ route('admin.pengguna.verifikasi') }}" class="font-semibold text-[#0051d5] hover:text-[#00236f] hover:underline">halaman verifikasi</a>.</p>
            </div>
            <a href="{{ route('admin.pengguna.create') }}"
               class="rounded-lg bg-[#0051d5] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#00236f] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
                Tambah Pengguna
            </a>
        </div>

        <table class="w-full text-sm">
            <thead class="border-b border-[#E2E7FF] bg-[#F8FAFC] text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">NIM/NIP</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF2FF]">
                @forelse ($users as $user)
                    <tr class="transition-colors hover:bg-[#F8FAFC]">
                        <td class="px-6 py-3 font-semibold text-[#00236f]">{{ $user->name }}</td>
                        <td class="px-6 py-3">{{ $user->email }}</td>
                        <td class="px-6 py-3">{{ $user->identity ?? '-' }}</td>
                        <td class="px-6 py-3">{{ $user->account_status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">Belum ada akun pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
