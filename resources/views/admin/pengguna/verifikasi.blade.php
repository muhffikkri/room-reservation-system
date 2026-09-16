@extends('layouts.app')

@section('title', 'Verifikasi Akun')

@section('content')
    <div class="overflow-hidden rounded-2xl border border-[#E2E7FF] bg-white shadow-sm">
        <div class="border-b border-[#EEF2FF] px-6 py-5">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0051d5]">Manajemen akun</p>
            <h1 class="mt-2 text-xl font-semibold tracking-tight text-[#00236f]">Verifikasi Akun Pengguna</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Akun hasil registrasi mandiri menunggu persetujuan admin.</p>
        </div>

        <table class="w-full text-sm">
            <thead class="border-b border-[#E2E7FF] bg-[#F8FAFC] text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">NIM/NIP</th>
                    <th class="px-6 py-3">Terdaftar</th>
                    <th class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF2FF]">
                @forelse ($pendingUsers as $pendingUser)
                    <tr class="transition-colors hover:bg-[#F8FAFC]">
                        <td class="px-6 py-3 font-semibold text-[#00236f]">{{ $pendingUser->name }}</td>
                        <td class="px-6 py-3">{{ $pendingUser->email }}</td>
                        <td class="px-6 py-3">{{ $pendingUser->identity ?? '-' }}</td>
                        <td class="px-6 py-3">{{ $pendingUser->created_at->format('d-m-Y H:i') }}</td>
                        <td class="px-6 py-3">
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.pengguna.verify', $pendingUser) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                        Verifikasi
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.pengguna.reject', $pendingUser) }}"
                                      onsubmit="return confirm('Tolak akun ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-700">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">Tidak ada akun yang menunggu verifikasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
