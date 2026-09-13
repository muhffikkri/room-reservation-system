@extends('layouts.app')

@section('title', 'Kelola Akun Petugas')

@section('content')
    <div class="overflow-hidden rounded-2xl border border-[#E2E7FF] bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-[#EEF2FF] px-6 py-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Manajemen akun</p>
                <h1 class="mt-2 text-xl font-semibold tracking-tight text-[#00236f]">Akun Petugas</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Petugas hanya dibuat oleh admin, tidak bisa registrasi mandiri.</p>
            </div>
            <a href="{{ route('admin.petugas.create') }}"
               class="rounded-lg bg-[#0051d5] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#00236f] focus:outline-none focus:ring-2 focus:ring-[#0051d5] focus:ring-offset-2">
                Tambah Petugas
            </a>
        </div>

        <table class="w-full text-sm">
            <thead class="border-b border-[#E2E7FF] bg-[#F8FAFC] text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">NIP</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF2FF]">
                @forelse ($officers as $officer)
                    <tr class="transition-colors hover:bg-[#F8FAFC]">
                        <td class="px-6 py-3 font-semibold text-[#00236f]">{{ $officer->name }}</td>
                        <td class="px-6 py-3">{{ $officer->email }}</td>
                        <td class="px-6 py-3">{{ $officer->identity ?? '-' }}</td>
                        <td class="px-6 py-3">{{ $officer->account_status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">Belum ada akun petugas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
