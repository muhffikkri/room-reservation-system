@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
    <div class="space-y-6">
        <div class="auth-clay-card overflow-hidden rounded-[2rem] p-5 sm:p-7 lg:p-8">
            @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
            <div class="mb-7 flex flex-col gap-4 border-b border-blue-100/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Pemberitahuan</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">Notifikasi</h1>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">
                        {{ $unreadNotifications > 0 ? $unreadNotifications.' notifikasi belum dibaca.' : 'Semua notifikasi sudah dibaca.' }}
                    </p>
                </div>
                @if ($unreadNotifications > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit"
                            class="landing-button inline-flex shrink-0 items-center justify-center rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-3 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                            Tandai semua dibaca
                        </button>
                    </form>
                @endif
            </div>

            @if ($notifications->isEmpty())
                <div class="px-4 py-14 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-600 shadow-inner">🔔</span>
                    <h3 class="mt-4 text-base font-bold text-slate-800">Belum ada notifikasi</h3>
                    <p class="mt-1 text-xs text-slate-500">Pemberitahuan tentang reservasi Anda akan tampil di sini.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($notifications as $notification)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit"
                                class="dashboard-clay-list clay-pressable flex w-full items-center justify-between gap-4 rounded-2xl p-4.5 text-left focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $notification->read_at === null ? 'bg-[#F2F3FF]' : '' }}">
                                <span class="min-w-0">
                                    <span
                                        class="block text-sm {{ $notification->read_at === null ? 'font-semibold text-[#00236f]' : 'text-slate-600' }}">{{ $notification->data['message'] ?? '' }}</span>
                                    <span class="mt-0.5 block text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                                </span>
                                <span class="shrink-0 text-xs font-bold text-blue-700">&rarr;</span>
                            </button>
                        </form>
                    @endforeach
                </div>
                @if ($notifications->hasPages())
                    <div class="mt-5 border-t border-blue-100/80 pt-4">
                        {{ $notifications->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
