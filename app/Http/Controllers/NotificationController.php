<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Notifikasi in-app pengguna (channel database): menampilkan daftar lengkap,
 * menandai sudah dibaca, dan mengarahkan ke reservasi terkait.
 */
class NotificationController extends Controller
{
    /**
     * Jumlah notifikasi yang ditampilkan di dropdown header.
     *
     * Pemilik tunggal: dipakai view layout dan endpoint read-on-open, supaya
     * yang ditandai "sudah dibaca" persis sama dengan yang terlihat.
     */
    public const DROPDOWN_LIMIT = 5;

    /**
     * Halaman notifikasi lengkap dengan paginasi.
     */
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(10),
        ]);
    }

    /**
     * Tandai satu notifikasi milik pengguna yang sedang login sebagai sudah
     * dibaca, lalu arahkan ke reservasi yang dimaksud.
     */
    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $stored = $request->user()->notifications()->findOrFail($notification);

        if ($stored->read_at === null) {
            $stored->markAsRead();
        }

        return redirect($stored->data['url'] ?? url('/'));
    }

    /**
     * Tandai notifikasi yang tampil di dropdown sebagai sudah dibaca saat
     * dropdown dibuka, lalu kembalikan sisa unread agar badge dapat
     * diperbarui tanpa reload.
     *
     * Hanya baris yang terlihat ($limit teratas) yang disentuh; notifikasi
     * lama di halaman lengkap tetap bisa dibaca satu per satu.
     */
    public function markOpened(Request $request): JsonResponse
    {
        $visible = $request->user()->notifications()
            ->latest()
            ->limit(self::DROPDOWN_LIMIT)
            ->pluck('id');

        if ($visible->isNotEmpty()) {
            $request->user()->notifications()
                ->whereIn('id', $visible)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json([
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Tandai seluruh notifikasi milik pengguna yang sedang login.
     *
     * Satu operasi update, bukan satu tulis per notifikasi: collections
     * markAsRead() menyimpan tiap baris satu per satu.
     */
    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
