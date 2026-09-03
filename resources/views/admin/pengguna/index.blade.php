@extends('layouts.app')

@section('title', 'Kelola Akun Pengguna')

@section('content')
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <div>
                <h1 class="text-lg font-semibold">Akun Pengguna</h1>
                <p class="text-sm text-slate-600">Seluruh pengguna beserta status akunnya. Akun pending diproses di <a href="{{ route('admin.pengguna.verifikasi') }}" class="text-blue-600 hover:underline">halaman verifikasi</a>.</p>
            </div>
            <a href="{{ route('admin.pengguna.create') }}"
               class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Tambah Pengguna
            </a>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">NIM/NIP</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-6 py-3">{{ $user->name }}</td>
                        <td class="px-6 py-3">{{ $user->email }}</td>
                        <td class="px-6 py-3">{{ $user->identity ?? '-' }}</td>
                        <td class="px-6 py-3">{{ $user->account_status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-6 text-center text-slate-500">Belum ada akun pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
