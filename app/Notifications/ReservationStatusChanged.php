<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi in-app (channel database) kepada pemilik reservasi ketika
 * statusnya berubah oleh keputusan petugas (approve/reject/cancel) atau
 * dibatalkan sistem karena melewati batas persetujuan.
 *
 * Teks memakai label status kanonik Reservation::statusLabel() agar tidak
 * ada salinan label kedua. Sengaja tidak di-queue: pengiriman berlangsung di
 * dalam transaksi yang sama dengan perubahan status.
 */
class ReservationStatusChanged extends Notification
{
    public const MESSAGE_TEMPLATE = 'Reservasi Anda di :facility kini berstatus: :status.';

    /**
     * Pembatalan oleh sistem sengaja tidak memakai label kanonik
     * "Dibatalkan oleh Sistem": halaman pengguna memakai kata "Gagal" dan
     * tidak boleh menampilkan label kanonik itu (keputusan tampilan §...).
     */
    public const SYSTEM_CANCELLED_MESSAGE_TEMPLATE = 'Reservasi Anda di :facility dibatalkan sistem karena melewati batas waktu persetujuan.';

    /**
     * @param  Reservation  $reservation  reservasi milik penerima
     * @param  string  $status  salah satu status pada Reservation::LABELS
     */
    public function __construct(
        public readonly Reservation $reservation,
        public readonly string $status,
    ) {}

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
     * Pesan yang ditampilkan ke pemilik reservasi.
     */
    public function message(): string
    {
        $facility = $this->reservation->facility->name;

        if ($this->status === 'cancelled_by_system') {
            return strtr(self::SYSTEM_CANCELLED_MESSAGE_TEMPLATE, [':facility' => $facility]);
        }

        return strtr(self::MESSAGE_TEMPLATE, [
            ':facility' => $facility,
            ':status' => Reservation::statusLabel($this->status),
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
            'type' => 'reservation_status_changed',
            'message' => $this->message(),
            'reservation_id' => $this->reservation->id,
            'status' => $this->status,
            'url' => route('reservasi.show', $this->reservation),
        ];
    }
}
