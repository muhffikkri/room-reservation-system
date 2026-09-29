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
- Batasi lagi opsi lokasi pada filter publik dan endpoint AJAX ke 50 nilai unik.
- Sediakan `Facility::photo_url` sebagai URL absolut agar kartu hasil pencarian langsung dan halaman publik tidak lagi memakai path relatif yang rusak.
- Arahkan tombol Dashboard pada halaman publik ke beranda milik role masing-masing lewat `User::homeRoute()` supaya admin dan petugas tidak lagi mendarat di 403.
- Batasi deskripsi fasilitas maksimal 30 kata dan tolak input admin yang lebih panjang lewat `Facility::MAX_DESCRIPTION_WORDS`.
- Beri tinggi tetap pada blok deskripsi card dan container grid landing supaya hasil pencarian kosong tidak menggeser komponen di bawahnya, dan card hasil pencarian kini dirender oleh server lewat partial yang sama dengan render awal.
- Satukan label tipe fasilitas dan gambar cadangan pada `Facility::TYPE_LABELS` serta `Facility::TYPE_FALLBACK_IMAGES` supaya label tipe tidak lagi disalin di lima tempat.

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
