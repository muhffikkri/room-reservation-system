<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Notifikasi in-app pengguna (channel database): menandai sudah dibaca
 * dan mengarahkan ke reservasi terkait.
 */
class NotificationController extends Controller
{
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
     * Tandai seluruh notifikasi milik pengguna yang sedang login.
     */
    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
