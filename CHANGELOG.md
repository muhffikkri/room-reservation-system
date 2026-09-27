# Changelog

Perubahan penting pada setiap rilis dicatat di sini. Detail commit dan pull request tersedia di riwayat GitHub.

## Unreleased

### Added

- Kedaluwarsa otomatis reservasi pending yang melewati batas persetujuan.
- Penolakan otomatis dan notifikasi untuk reservasi pending yang overlap setelah reservasi lain disetujui.
- Filter dan pengurutan antrian reservasi petugas.
- Pencarian fasilitas dan pratinjau jadwal pada landing page.

### Fixed

- Samakan batas pembatalan BR-8 pada tepat 60 menit sebelum waktu mulai.
- Tolak field akun berbentuk array melalui validasi tanpa menghasilkan HTTP 500.
- Pertahankan foto fasilitas lama ketika penyimpanan pengganti gagal.
- Izinkan reservasi bersebelahan tanpa dianggap overlap.
- Hitung status penolakan dan pembatalan sistem dengan benar pada rekap.
- Pulihkan fasilitas terkait ketika laporan kerusakan ditolak.

## v1.2.3 — 2026-09-23

- Pusatkan keputusan ketersediaan reservasi pada `ReservationAvailability`.
- Gunakan format waktu `H:i` secara konsisten.
- Hapus konversi WebP dan simpan upload dalam format aslinya.

## v1.2.2 — 2026-09-18

- Tambahkan rekap okupansi dan kerusakan dengan ekspor CSV/PDF.
- Terapkan lead time reservasi minimum 60 menit.

## v1.2.1 — 2026-09-18

- Tingkatkan aksesibilitas, navigasi keyboard, dialog, dan tampilan loading.
- Perbaiki beberapa masalah UX pada alur petugas dan laporan.

## v1.2.0 — 2026-09-16

- Tambahkan pembatalan reservasi pengguna dan isolasi akses berbasis role.
- Tambahkan pembuatan multi-admin dan riwayat verifikasi akun.

## v.1.1.0 — 2026-09-12

- Tambahkan alur laporan kerusakan pengguna dan petugas.
- Tambahkan halaman fasilitas publik.

## v1.0.0 — 2026-09-09

- Rilis stabil pertama untuk autentikasi, akun, fasilitas, dan reservasi.
