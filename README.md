# Sistem Reservasi & Pelaporan Fasilitas Kampus

Aplikasi Laravel untuk reservasi fasilitas kampus dan pelaporan kerusakan. Pengguna mengajukan reservasi dan laporan, petugas menangani alur operasional, dan admin mengelola akun, fasilitas, serta rekap.

## Overview

REKSA (Reservasi dan Kerusakan Sarana Akademik) adalah aplikasi web untuk memesan
fasilitas kampus dan melapor kerusakan. Dua kebutuhan yang biasanya terpisah —
pemesan ruang dan pengaduan kerusakan — mengalir dalam satu alur dengan satu sumber
kebenaran tentang ketersediaan dan status.

- **Pengguna** mengajukan reservasi, memantau statusnya, membatalkan sendiri, dan
  melaporkan kerusakan.
- **Petugas** menjalankan antrean operasional: menyetujui atau menolak reservasi,
  memproses laporan, dan menandai fasilitas yang sedang diperbaiki.
- **Admin** menjaga gerbang masuk: memverifikasi akun, mengelola master fasilitas, dan
  membaca rekap pemakaian.
- **Pengunjung tanpa akun** tetap dapat melihat katalog fasilitas dan jadwal
  ketersediaan, tanpa identitas pemesan.

Tiga masalah yang diselesaikan:

1. **Bentrok jadwal tidak terlihat** — ketersediaan dihitung sebagai slot 30 menit dan
   bentrok diperiksa di server, baik saat pengajuan maupun saat persetujuan.
2. **Permintaan tidak berjejak** — setiap transisi status disimpan pada tabel audit
   terpisah lengkap dengan alasan dan pelaku.
3. **Kerusakan diam-diam dipakai** — laporan memiliki antrean sendiri, dan fasilitas
   yang sedang diperbaiki dikeluarkan dari pemesanan.

| Ringkasan                                 | Nilai                                     |
| ----------------------------------------- | ----------------------------------------- |
| Rilis                                     | `v1.3.0` (2026-10-01)                     |
| Route aplikasi                            | 62                                        |
| View Blade                                | 53                                        |
| Tabel domain                              | 7                                         |
| Business rules                            | 20 (BR-1 sampai BR-20)                    |
| Service, Form Request, Policy, Middleware | 7, 8, 2, 3                                |
| Test otomatis                             | 362 test PHP (Pest) dan 5 test JavaScript |

### Stack

| Komponen    | Teknologi                                             |
| ----------- | ----------------------------------------------------- |
| Backend     | Laravel 13, PHP 8.3+                                  |
| Database    | MySQL 8                                               |
| Frontend    | Blade, Tailwind CSS 4, Vite 8                         |
| Ekspor PDF  | dompdf                                                |
| Test        | Pest 5, Node test runner                              |
| Format kode | Laravel Pint                                          |
| Container   | Docker Compose (nginx, app, MySQL, Cloudflare Tunnel) |

## Fitur

### Publik (tanpa login)

- **Landing page** dengan ringkasan fasilitas pilihan dan pencarian langsung.
- **Katalog fasilitas** dengan filter tipe, lokasi, dan kapasitas.
- **Detail fasilitas** dan **grid ketersediaan** harian: 26 slot 30 menit pada pukul
  07.00 sampai 20.00, hanya menampilkan status terisi atau tersedia.
- **Registrasi mandiri** dan login. Akun baru berstatus `pending` sampai diverifikasi
  admin.

### Pengguna

- **Dashboard** ringkasan reservasi, laporan, dan notifikasi.
- **Pengajuan reservasi** dengan validasi penuh di server: kelipatan 30 menit, jam
  operasional, durasi 30 menit sampai 4 jam, mulai minimal 1 jam dari sekarang,
  maksimal 365 hari ke depan, maksimal 2 reservasi `pending` per hari, dan bebas
  bentrok.
- **Riwayat dan detail reservasi** lengkap dengan alasan penolakan atau pembatalan.
- **Pembatalan mandiri** sampai 1 jam sebelum waktu mulai, dengan alasan wajib.
- **Laporan kerusakan** per kategori dengan foto bukti opsional, maksimal 20 laporan
  per hari.
- **Notifikasi in-app**, termasuk saat reservasinya otomatis ditolak karena bentrok.

### Petugas

- **Dashboard antrean** berisi jumlah antrean dan pratinjau terbaru.
- **Antrean reservasi** dengan filter status, tanggal, dan fasilitas, pengurutan bebas,
  serta tab yang dimuat lewat AJAX.
- **Setujui, tolak, atau batalkan** reservasi. Menyetujui satu reservasi otomatis
  menolak `pending` lain yang benar-benar overlap di fasilitas sama dan memberi tahu
  pemiliknya.
- **Antrean laporan kerusakan**: `baru` → `diproses` → `selesai` atau `ditolak`, dengan
  catatan resolusi wajib saat laporan ditutup.
- **Status fasilitas** `perbaikan` dan pengembalian ke `aktif`, disertai daftar
  reservasi terpengaruh yang perlu ditinjau.
- **Kedaluwarsa otomatis**: `pending` yang lewat 60 menit sebelum waktu mulai
  dibatalkan sistem.

### Admin

- **Verifikasi, tolak, atau pulihkan** akun pendaftar beserta riwayat aksi.
- **Pembuatan akun** admin, petugas, dan pengguna.
- **CRUD master fasilitas**: nama, tipe, lokasi, kapasitas, deskripsi, foto, dan
  status.
- **Rekap okupansi dan kerusakan** per rentang tanggal, dengan ekspor CSV dan PDF.
- **Dashboard agregat** yang read-only dan tidak membuka aksi petugas.

### Lintas fitur

- **Akses berbasis role**; `admin` tidak mewarisi hak `petugas`.
- **Jejak audit** pada setiap transisi status reservasi, laporan, dan verifikasi akun.
- **Penanganan balapan** pada approve, verifikasi, pembuatan laporan, dan pengajuan
  reservasi memakai transaksi dan penguncian baris.
- **Keamanan**: password bcrypt, header keamanan, rate limit, foto laporan pada disk
  privat yang hanya dapat diakses lewat route terproteksi.
- **Tampilan responsif** dengan gaya claymorphism yang konsisten di halaman publik,
  autentikasi, pengguna, petugas, dan admin.

### Matriks hak akses

| Fitur                                 | Pengunjung | Pengguna | Petugas | Admin |
| ------------------------------------- | :--------: | :------: | :-----: | :---: |
| Lihat katalog dan jadwal ketersediaan |     Ya     |    Ya    |   Ya    |  Ya   |
| Registrasi akun mandiri               |     Ya     |    -     |  Tidak  | Tidak |
| Ajukan dan batalkan reservasi         |   Tidak    |    Ya    |  Tidak  | Tidak |
| Lapor kerusakan                       |   Tidak    |    Ya    |  Tidak  | Tidak |
| Proses antrean reservasi dan laporan  |   Tidak    |  Tidak   |   Ya    | Tidak |
| Verifikasi akun dan kelola fasilitas  |   Tidak    |  Tidak   |  Tidak  |  Ya   |
| Rekap dan ekspor                      |   Tidak    |  Tidak   |  Tidak  |  Ya   |

## Struktur folder

```
room-reservation-system/
├── app/
│   ├── Console/Commands/        # reports:protect-photos untuk migrasi foto privat
│   ├── Http/Controllers/        # Controller tipis: Auth, halaman publik, pengguna,
│   │   └── Admin, Officer/      # petugas, dan admin
│   ├── Http/Middleware/         # EnsureRole, EnsureAccountActive, AddSecurityHeaders
│   ├── Http/Requests/           # Validasi sisi server (8 Form Request)
│   ├── Models/                  # Relasi, scope, dan satu-satunya pemilik label status
│   ├── Notifications/           # Notifikasi in-app (mis. reservasi otomatis ditolak)
│   ├── Observers/               # UserObserver untuk akun dan verifikasi
│   ├── Policies/                # Otorisasi per objek
│   ├── Rules/                   # Rule khusus, mis. MaxWords
│   ├── Services/                # Seluruh logika bisnis (7 service)
│   ├── Providers/               # AppServiceProvider
│   └── Support/                 # AccountAttributes untuk field akun bersama
├── bootstrap/                   # Bootstrap Laravel
├── config/                      # Konfigurasi aplikasi
├── database/
│   ├── factories/               # Factory untuk test
│   ├── migrations/              # 16 migrasi, sumber kebenaran skema database
│   └── seeders/                 # Data contoh dan akun demo
├── docker/nginx/                # Konfigurasi Nginx untuk container
├── docs/                        # Spesifikasi, laporan audit, panduan kode, screenshot
├── public/                      # Aset publik dan entry point
├── resources/
│   ├── css/app.css              # Sumber gaya Tailwind
│   ├── js/                      # app.js dan reservation-form.js (AJAX, slot picker)
│   └── views/                   # 53 Blade: landing, auth, dashboard, reservasi,
│       └── components/          # laporan, fasilitas, petugas, admin, dan komponen UI
├── routes/web.php               # 62 route aplikasi
├── scripts/                     # create-database.php
├── skills/                      # Panduan kerja repository untuk agen
├── storage/                     # Log, cache, dan disk privat foto laporan
├── tests/
│   ├── Feature/                 # 35 file alur end-to-end
│   ├── Unit/                    # 4 file aturan murni
│   └── *.test.mjs               # 3 file test JavaScript
├── .github/workflows/           # lint.yml (Pint) dan deploy.yml (nonaktif)
├── Dockerfile, docker-compose.yml
├── vite.config.js, phpunit.xml, pint.json
└── composer.json, package.json, .env.example
```

### Struktur logika bisnis

| Lapisan          | Isi                             | Aturan                                                       |
| ---------------- | ------------------------------- | ------------------------------------------------------------ |
| **View (Blade)** | Markup dan tampilan             | Tidak menjalankan aturan bisnis                              |
| **JavaScript**   | Interaksi ringan dan AJAX       | Tidak mengulang aturan server; server tetap sumber kebenaran |
| **Controller**   | Adaptor HTTP                    | Tipis: validasi, panggil service, kembalikan response        |
| **Form Request** | Validasi input                  | Menolak sebelum menyentuh service                            |
| **Policy**       | Otorisasi per objek             | Contoh: laporan hanya dilihat pemiliknya atau petugas        |
| **Service**      | Seluruh logika bisnis           | Satu-satunya pemilik aturan; dipakai banyak controller       |
| **Model**        | Relasi, scope, dan label domain | Tidak memuat aturan bisnis                                   |

## Dokumentasi

- [Spesifikasi sistem](docs/spesifikasi-sistem-reservasi.md) — kebutuhan, skema domain, business rules, workflow, dan kriteria penerimaan.
- [Changelog](CHANGELOG.md) — perubahan pada setiap rilis.
- [Panduan agen](AGENTS.md) — aturan kerja dan sumber informasi proyek.

Status pekerjaan berjalan dicatat di GitHub Issues dan Pull Requests. Route aktif dapat dilihat dengan `php artisan route:list --except-vendor`; skema database yang dijalankan berada di `database/migrations/`.

## Kebutuhan

- PHP 8.3 atau lebih baru dengan `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, dan `zip`
- Composer 2
- MySQL 8
- Node.js 20 atau lebih baru dan npm

## Setup lokal

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi MySQL di `.env`, lalu isi password seeder lokal berikut dengan nilai unik minimal 12 karakter:

```dotenv
DB_DATABASE=reservasi_kampus
SEED_ADMIN_PASSWORD=
SEED_OFFICER_PASSWORD=
SEED_USER_PASSWORD=
SEED_PENDING_PASSWORD=
```

Siapkan database dan aplikasi:

```bash
php scripts/create-database.php
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Aplikasi lokal tersedia di `http://localhost:8000`.

Untuk development frontend, gunakan `npm run dev`. Alternatif container tersedia melalui:

```bash
docker compose up -d --build
```

## Menjalankan test

Test menggunakan database MySQL `reservasi_kampus_testing` yang dikonfigurasi di `phpunit.xml`.

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS reservasi_kampus_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan test --compact
```

## Akun demo

Seeder membuat akun berikut. Password dibaca dari environment lokal dan tidak disimpan di repository.

| Role     | Email                      | Environment password    | Status  |
| -------- | -------------------------- | ----------------------- | ------- |
| Admin    | `admin@kampus.test`        | `SEED_ADMIN_PASSWORD`   | aktif   |
| Petugas  | `petugas@kampus.test`      | `SEED_OFFICER_PASSWORD` | aktif   |
| Pengguna | `budi@student.kampus.test` | `SEED_USER_PASSWORD`    | aktif   |
| Pengguna | `sari@dosen.kampus.test`   | `SEED_USER_PASSWORD`    | aktif   |
| Pengguna | `pending@kampus.test`      | `SEED_PENDING_PASSWORD` | pending |

## Upgrade dari versi lama

Setelah membuat backup storage, pindahkan foto laporan lama dari disk public ke private:

```bash
php artisan reports:protect-photos --delete-public
```

Perintah ini hanya diperlukan untuk instalasi yang pernah menyimpan foto laporan pada disk public.
