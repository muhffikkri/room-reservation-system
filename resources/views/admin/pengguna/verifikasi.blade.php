@extends('layouts.app')

@section('title', 'Verifikasi Akun')

@section('content')
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
            <h1 class="text-lg font-semibold">Verifikasi Akun Pengguna</h1>
            <p class="text-sm text-slate-600">Akun hasil registrasi mandiri menunggu persetujuan admin.</p>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">NIM/NIP</th>
                    <th class="px-6 py-3">Terdaftar</th>
                    <th class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($pendingUsers as $pendingUser)
                    <tr>
                        <td class="px-6 py-3">{{ $pendingUser->name }}</td>
                        <td class="px-6 py-3">{{ $pendingUser->email }}</td>
                        <td class="px-6 py-3">{{ $pendingUser->identity ?? '-' }}</td>
                        <td class="px-6 py-3">{{ $pendingUser->created_at->format('d-m-Y H:i') }}</td>
                        <td class="px-6 py-3">
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.pengguna.verify', $pendingUser) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="rounded bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700">
                                        Verifikasi
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.pengguna.reject', $pendingUser) }}"
                                      onsubmit="return confirm('Tolak akun ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="rounded bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-slate-500">Tidak ada akun yang menunggu verifikasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
