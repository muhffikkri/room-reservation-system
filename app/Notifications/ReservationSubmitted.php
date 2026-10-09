<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi in-app (channel database) untuk setiap petugas aktif ketika
 * pengguna mengajukan reservasi baru yang menunggu persetujuan.
 *
 * Sengaja tidak di-queue: pengiriman berlangsung di dalam transaksi create()
 * agar ikut ter-rollback bila pengajuan gagal.
 */
class ReservationSubmitted extends Notification
{
    public const MESSAGE_TEMPLATE = 'Pengajuan reservasi baru #:id dari :name menunggu persetujuan Anda.';

    /**
     * @param  Reservation  $reservation  reservasi yang baru diajukan
     */
    public function __construct(public readonly Reservation $reservation) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Pesan yang ditampilkan ke petugas penerima.
     */
    public function message(): string
    {
        return strtr(self::MESSAGE_TEMPLATE, [
            ':id' => (string) $this->reservation->id,
            ':name' => $this->reservation->user->name,
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reservation_submitted',
            'message' => $this->message(),
            'reservation_id' => $this->reservation->id,
            'url' => route('petugas.reservasi.show', $this->reservation),
        ];
    }
}
