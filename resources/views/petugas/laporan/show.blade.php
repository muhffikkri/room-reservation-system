@extends('layouts.app')

@section('title', 'Penanganan Laporan #' . $report->id)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <a href="{{ route('petugas.laporan.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-700">
            &larr; Kembali ke antrian laporan
        </a>
        <div class="mt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h1 class="text-2xl font-bold text-slate-800">Penanganan Laporan #{{ $report->id }}</h1>
            <div>
                @php
                    $badgeClasses = match($report->status) {
                        'baru' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                        'diproses' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                        'selesai' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                        'ditolak' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                        default => 'bg-slate-50 text-slate-700 ring-slate-600/20',
                    };
                @endphp
                <span class="inline-flex items-center rounded-md px-3 py-1 text-xs font-semibold ring-1 ring-inset capitalize {{ $badgeClasses }}">
                    Status Laporan: {{ $report->status }}
                </span>
            </div>
        </div>
        <p class="text-xs text-slate-500 mt-1">Dilaporkan oleh <span class="font-medium text-slate-700">{{ $report->user->name }}</span> ({{ $report->user->email }}) pada {{ $report->created_at->format('d M Y, H:i') }} WIB</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Detail Informasi Laporan --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
                <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Informasi Kerusakan</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Fasilitas Kampus</h3>
                        <p class="mt-1 text-base font-semibold text-slate-900">{{ $report->facility->name }}</p>
                        <p class="text-xs text-slate-500">{{ $report->facility->location }}</p>
                        <div class="mt-2">
                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 border border-slate-200">
                                Status Fasilitas: <strong class="ml-1 uppercase text-slate-900">{{ $report->facility->status }}</strong>
                            </span>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kategori Kerusakan</h3>
                        <p class="mt-1 text-base font-medium text-slate-900 capitalize">{{ str_replace('_', ' ', $report->category) }}</p>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Deskripsi Laporan</h3>
                    <p class="mt-2 text-sm text-slate-700 whitespace-pre-line leading-relaxed">{{ $report->description }}</p>
                </div>

                @if($report->photo)
                    <div class="border-t border-slate-100 pt-4">
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Foto Bukti Kerusakan</h3>
                        <div class="overflow-hidden rounded-lg border border-slate-200 bg-slate-50 max-w-lg">
                            <img src="{{ asset('storage/' . $report->photo) }}" alt="Foto laporan" class="w-full object-cover max-h-80">
                        </div>
                    </div>
                @endif

                @if($report->resolution_note)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 border-l-4 border-l-indigo-500">
                        <h3 class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Catatan Resolusi / Penanganan Terakhir</h3>
                        <p class="mt-1 text-sm text-slate-800 leading-relaxed">{{ $report->resolution_note }}</p>
                        @if($report->handledBy)
                            <p class="mt-2 text-xs text-slate-500">Ditangani oleh: <span class="font-medium text-slate-700">{{ $report->handledBy->name }}</span> pada {{ $report->handled_at?->format('d M Y, H:i') }} WIB</p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Riwayat Status (Audit Trail) --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3">Riwayat Transisi Status</h2>
                @if($report->updates->isEmpty())
                    <p class="text-sm text-slate-500 italic">Belum ada riwayat transisi status.</p>
                @else
                    <ol class="relative border-l border-slate-200 ml-3 space-y-6">
                        @foreach($report->updates as $update)
                            <li class="ml-6">
                                <span class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 ring-4 ring-white text-slate-500 text-xs font-bold">
                                    {{ $loop->iteration }}
                                </span>
                                <div class="rounded-lg border border-slate-100 bg-slate-50/50 p-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="text-xs font-semibold text-slate-800">
                                            Transisi:
                                            <span class="font-bold text-slate-600">{{ $update->old_status ?: 'Baru' }}</span>
                                            &rarr;
                                            <span class="font-bold text-indigo-600 capitalize">{{ $update->new_status }}</span>
                                        </span>
                                        <time class="text-xs text-slate-400">{{ $update->created_at->format('d M Y, H:i') }} WIB</time>
                                    </div>
                                    @if($update->note)
                                        <p class="mt-2 text-xs text-slate-600 bg-white p-2 rounded border border-slate-200">{{ $update->note }}</p>
                                    @endif
                                    <p class="mt-1 text-[11px] text-slate-400">Petugas: {{ $update->user->name ?? '-' }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>

        {{-- Panel Tindakan Petugas --}}
        <div class="space-y-6">
            {{-- Form Transisi Status --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Tindakan Petugas</h2>

                @if(empty($allowedTransitions))
                    <div class="rounded-lg bg-slate-50 p-4 border border-slate-200 text-xs text-slate-600">
                        Laporan ini sudah ditutup dengan status <strong>{{ $report->status }}</strong> dan tidak dapat diubah lagi (BR-10).
                    </div>
                @else
                    <form method="POST" action="{{ route('petugas.laporan.status', $report) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="status" class="block text-xs font-semibold text-slate-700 uppercase">Ubah Status Laporan</label>
                            <select id="status" name="status" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm">
                                <option value="" disabled selected>-- Pilih Transisi Status --</option>
                                @foreach($allowedTransitions as $targetStatus)
                                    <option value="{{ $targetStatus }}" {{ old('status') === $targetStatus ? 'selected' : '' }}>
                                        Ubah Ke: {{ strtoupper($targetStatus) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="resolution_note" class="block text-xs font-semibold text-slate-700 uppercase">Catatan Resolusi / Tindakan</label>
                            <p class="text-[11px] text-slate-500 mb-1">Wajib diisi minimal 10 karakter saat menutup laporan (Selesai/Ditolak).</p>
                            <textarea id="resolution_note" name="resolution_note" rows="3" minlength="10" placeholder="Jelaskan tindakan perbaikan atau alasan penolakan..." class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 text-sm">{{ old('resolution_note') }}</textarea>
                            @error('resolution_note')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Simpan Perubahan Status
                        </button>
                    </form>
                @endif
            </div>

            {{-- Form Status Fasilitas (BR-11) --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Kelola Fasilitas terkait</h2>

                @if($report->status === 'diproses' && $report->facility->status !== 'perbaikan')
                    <p class="text-xs text-slate-600">Laporan sedang diproses. Anda dapat menandai fasilitas ini sedang dalam perbaikan (BR-11).</p>
                    <form method="POST" action="{{ route('petugas.laporan.fasilitas-status', $report) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="perbaikan">
                        <button type="submit" onclick="return confirm('Tandai fasilitas sebagai PERBAIKAN? Fasilitas tidak dapat direservasi selama perbaikan.')" class="w-full rounded-lg bg-amber-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-amber-700">
                            Tandai Fasilitas PERBAIKAN
                        </button>
                    </form>
                @elseif($report->status === 'selesai' && $report->facility->status === 'perbaikan')
                    <p class="text-xs text-slate-600">Laporan sudah selesai. Anda dapat mengembalikan fasilitas ini ke status AKTIF (BR-11).</p>
                    <form method="POST" action="{{ route('petugas.laporan.fasilitas-status', $report) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="action" value="aktif">
                        <button type="submit" onclick="return confirm('Kembalikan fasilitas ke status AKTIF?')" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700">
                            Kembalikan Fasilitas ke AKTIF
                        </button>
                    </form>
                @else
                    <p class="text-xs text-slate-500 italic">
                        @if($report->facility->status === 'perbaikan')
                            Fasilitas sedang dalam status <strong>PERBAIKAN</strong>.
                        @else
                            Status fasilitas saat ini: <strong>{{ strtoupper($report->facility->status) }}</strong>.
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
