@props([
    'heading',
    'action',
    'cancelUrl',
    'submitLabel',
    'identityLabel' => 'NIM/NIP',
    'loadingLabel' => 'Membuat akun...',
])

{{-- Satu pemilik formulir pembuatan akun admin/petugas/pengguna: field,
     atribut validasi, dan gaya clay identik untuk ketiganya. --}}
<div class="mx-auto max-w-3xl">
    <div class="auth-clay-card rounded-[2rem] p-5 sm:p-7 lg:p-8">
        <div class="border-b border-blue-100/80 pb-6">
            <a href="{{ $cancelUrl }}"
                class="clay-pressable mb-3 inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-blue-700">
                &larr; Kembali ke daftar akun
            </a>
            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-600">Manajemen akun</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#10264a] sm:text-3xl">{{ $heading }}</h1>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">Akun langsung aktif tanpa verifikasi karena admin yang
                membuatnya.</p>
        </div>

        <form method="POST" action="{{ $action }}" data-validate class="mt-7 grid gap-5 sm:grid-cols-2">
            @csrf

            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700">Nama lengkap</label>
                <input id="name" name="name" type="text" required maxlength="100" value="{{ old('name') }}"
                    class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                @error('name')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700">Email</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}"
                    class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                @error('email')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="identity" class="block text-sm font-semibold text-slate-700">{{ $identityLabel }}</label>
                <input id="identity" name="identity" type="text" required maxlength="30" value="{{ old('identity') }}"
                    class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                @error('identity')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-semibold text-slate-700">No. HP</label>
                <input id="phone" name="phone" type="text" required maxlength="20" value="{{ old('phone') }}"
                    class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                @error('phone')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                <input id="password" name="password" type="password" required minlength="8"
                    class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                <p class="mt-1.5 text-xs text-slate-500">Minimal 8 karakter.</p>
                @error('password')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Konfirmasi
                    password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                    class="landing-input mt-1.5 block w-full rounded-2xl px-4 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-4 focus:ring-blue-500/15">
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-blue-100/80 pt-5 sm:col-span-2">
                <a href="{{ $cancelUrl }}"
                    class="clay-pressable inline-flex items-center justify-center rounded-full px-6 py-2.5 text-sm font-semibold text-blue-700">
                    Batal
                </a>
                <button type="submit" data-submit-loading data-loading-label="{{ $loadingLabel }}"
                    class="landing-button inline-flex items-center justify-center rounded-full bg-gradient-to-r from-blue-600 to-blue-500 px-7 py-2.5 text-sm font-bold text-white shadow-[0_4px_14px_rgba(37,99,235,0.25)]">
                    {{ $submitLabel }}
                </button>
            </div>
        </form>
    </div>
</div>
