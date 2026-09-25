<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi in-app (channel database) ketika reservasi pemohon otomatis
 * ditolak sistem karena overlap dengan reservasi lain yang disetujui petugas.
 *
 * Sengaja tidak di-queue: pengiriman berlangsung di dalam transaksi approve()
 * agar ikut ter-rollback bila persetujuan gagal.
 */
class ReservationOverlapRejected extends Notification
{
    public const MESSAGE = 'Maaf, fasilitas ini sudah dipesan pada jam yang sama (atau overlap). Permohonan Anda ditolak.';

    /**
     * @param  Reservation  $reservation  reservasi milik penerima yang ditolak sistem
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reservation_overlap_rejected',
            'message' => self::MESSAGE,
            'reservation_id' => $this->reservation->id,
            'url' => route('reservasi.show', $this->reservation),
        ];
    }
}
