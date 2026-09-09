@extends('layouts.app')

@section('title', 'Kelola Akun Petugas')

@section('content')
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <div>
                <h1 class="text-lg font-semibold">Akun Petugas</h1>
                <p class="text-sm text-slate-600">Petugas hanya dibuat oleh admin, tidak bisa registrasi mandiri.</p>
            </div>
            <a href="{{ route('admin.petugas.create') }}"
               class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Tambah Petugas
            </a>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">NIP</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($officers as $officer)
                    <tr>
                        <td class="px-6 py-3">{{ $officer->name }}</td>
                        <td class="px-6 py-3">{{ $officer->email }}</td>
                        <td class="px-6 py-3">{{ $officer->identity ?? '-' }}</td>
                        <td class="px-6 py-3">{{ $officer->account_status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-6 text-center text-slate-500">Belum ada akun petugas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
