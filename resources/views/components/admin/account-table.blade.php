@props([
    'accounts',
    'emptyMessage',
    'identityLabel' => 'Identitas',
    'restoreRoute' => null,
])

{{-- Satu pemilik tabel akun admin/petugas/pengguna. Kolom "Aksi" hanya
     dirender bila restoreRoute diisi, karena hanya akun pengguna yang punya
     aksi kembalikan ke pending. --}}
@php
    $clayTableHead = 'border-b border-blue-100/80 bg-blue-50/50 px-5 py-3.5 text-[11px] font-extrabold uppercase tracking-[0.14em] text-blue-700';
    $clayTableCell = 'px-5 py-4 align-middle';
    $columnCount = $restoreRoute === null ? 4 : 5;
@endphp

<div class="clay-inset mt-6 overflow-hidden rounded-2xl">
    <div class="overflow-x-auto">
        <table class="w-full min-w-full text-left text-sm text-slate-600">
            <thead>
                <tr>
                    <th scope="col" class="{{ $clayTableHead }}">Nama</th>
                    <th scope="col" class="{{ $clayTableHead }}">Email</th>
                    <th scope="col" class="{{ $clayTableHead }}">{{ $identityLabel }}</th>
                    <th scope="col" class="{{ $clayTableHead }}">Status</th>
                    @if ($restoreRoute !== null)
                        <th scope="col" class="{{ $clayTableHead }} text-right">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-blue-100/70">
                @forelse ($accounts as $account)
                    <tr class="transition-colors hover:bg-white/70">
                        <td class="{{ $clayTableCell }} font-bold text-[#10264a]">{{ $account->name }}</td>
                        <td class="{{ $clayTableCell }}">{{ $account->email }}</td>
                        <td class="{{ $clayTableCell }}">{{ $account->identity ?? '-' }}</td>
                        <td class="{{ $clayTableCell }}">
                            <x-ui.badge :status="$account->account_status" dot />
                        </td>
                        @if ($restoreRoute !== null)
                            <td class="{{ $clayTableCell }} text-right">
                                @if ($account->account_status === 'ditolak')
                                    <form method="POST" action="{{ route($restoreRoute, $account) }}"
                                        data-confirm-message="Kembalikan akun ini ke pending?">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-full border border-amber-200 bg-gradient-to-br from-amber-50 to-white px-4 text-xs font-bold text-amber-700 transition hover:from-amber-100 hover:to-white">
                                            Kembalikan ke pending
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $columnCount }}" class="px-5 py-14 text-center">
                            <span
                                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-white bg-white text-2xl text-blue-600 shadow-[0_4px_10px_rgba(59,130,246,0.15)]">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 19v-1.5a4 4 0 00-4-4H6a4 4 0 00-4 4V19m8-9a4 4 0 110-8 4 4 0 010 8zm12 9v-1.5a4 4 0 00-3-3.87M16 6.13A4 4 0 0119.5 9" />
                                </svg>
                            </span>
                            <p class="mt-4 text-base font-bold text-slate-800">{{ $emptyMessage }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $accounts->links() }}</div>
