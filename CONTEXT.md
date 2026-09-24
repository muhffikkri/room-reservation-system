# Context

## Sistem Reservasi & Pelaporan Fasilitas Kampus

Aplikasi Laravel 13 untuk peminjaman fasilitas kampus (ruang kelas, aula, laboratorium, alat, lapangan) dengan alur persetujuan petugas dan pelaporan kerusakan sarana prasarana. Pengguna publik dapat melihat ketersediaan slot tanpa login; pengguna terdaftar dapat mengajukan reservasi; petugas menyetujui/menolak/membatalkan; admin mengelola akun, fasilitas, dan rekap.

## Reservation Availability

"Reservation Availability" adalah satu konsep kepemilikan seluruh keputusan ketersediaan slot reservasi:

- jam operasional, interval dan durasi slot,
- lead time pemesanan,
- kelayakan fasilitas,
- kuota reservasi pending per hari,
- definisi overlap reservasi approved,
- state slot kanonik dan pemformatan waktu,
- proyeksi jadwal publik dan proyeksi pemesanan.

Server tetap otoritatif: controller, view, dan JavaScript hanyalah adaptor yang menerima keputusan dari modul ini. Halaman publik dan halaman pemesanan memakai fakta dasar yang sama (overlap approved, status fasilitas) namun proyeksinya bisa berbeda — lead time 60 menit hanya membatasi pemilihan pemesanan. Detail implementasi hidup di `app/Services/ReservationAvailability.php` dan dirujuk dari dokumen spesifikasi.

## Status per 2026-09-23

- Keputusan ketersediaan dipegang **satu modul** `App\Services\ReservationAvailability`; kelas `App\Rules\*` (`SlotTimeValid`, `BookingLeadTime`, `FacilityBookable`, `PendingQuota`, `NoApprovedOverlap`) dihapus. `ReservationService::create()`/`approve()` menjalankan semua cek ketersediaan di dalam transaksi via modul.
- Controller (`HomeController`, `FacilityController`, `ReservationController`), view, dan `reservation-form.js` hanya adaptor: meminta `bookingSlots`/`publicScheduleSlots`/`timeOptions` dan menerima `data-max-duration-slots` dari server.
- Format waktu kanonik **`H:i`** (bukan `H.i`) pada semua grid slot dan opsi waktu.
- Media: foto fasilitas & laporan disimpan sebagai file asli (tanpa konversi WebP). `intervention/image ^4.0` tetap ada sebagai dependency.
- Waktu kini: **v1.2.3 (candidate)** — basis v1.2.2 (`ebc4967`). Riwayat & detail: [CHANGELOG.md](CHANGELOG.md) · [docs/feature-checklist.md](docs/feature-checklist.md) · [docs/spesifikasi-sistem-reservasi.md](docs/spesifikasi-sistem-reservasi.md) · [releases/v1.2.3.md](releases/v1.2.3.md).