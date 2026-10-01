# Sistem Reservasi & Pelaporan Fasilitas Kampus

Aplikasi Laravel untuk reservasi fasilitas kampus dan pelaporan kerusakan. Pengguna mengajukan reservasi dan laporan, petugas menangani alur operasional, dan admin mengelola akun, fasilitas, serta rekap.

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
SEED_VOLUME_PASSWORD=
```

`SEED_VOLUME_PASSWORD` hanya dipakai volume seeder dan boleh dikosongkan; bila
kosong, seeder memakai kembali `SEED_USER_PASSWORD`.

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

| Role | Email | Environment password | Status |
|---|---|---|---|
| Admin | `admin@kampus.test` | `SEED_ADMIN_PASSWORD` | aktif |
| Petugas | `petugas@kampus.test` | `SEED_OFFICER_PASSWORD` | aktif |
| Pengguna | `budi@student.kampus.test` | `SEED_USER_PASSWORD` | aktif |
| Pengguna | `sari@dosen.kampus.test` | `SEED_USER_PASSWORD` | aktif |
| Pengguna | `pending@kampus.test` | `SEED_PENDING_PASSWORD` | pending |

## Volume seeder

Besides the four demo accounts above, `php artisan migrate:fresh --seed` also
runs a set of volume seeders. They exist to exercise pagination, all statuses,
validation boundaries, and layout shifts; they are not fixtures you should read.

| Table | Rows | Coverage |
|---|---|---|
| `users` | 70 | pending, ditolak, restored; all three roles |
| `account_verification_actions` | 42 | verified, rejected, restored across 3 actors |
| `facilities` | 68 | all 5 types, all 3 statuses, shared and unique locations |
| `reports` | 72 | all 5 categories, all 4 statuses |
| `report_updates` | 100 | legacy rows plus full lifecycle transitions |
| `reservations` | 631 | all 7 statuses across past and future |
| `notifications` | 120 | generated only by `ReservationService::approve()` |

Notes:

- Volume seeders are **not idempotent**. They are meant to fill an empty
  database after `migrate:fresh`; running `db:seed` twice will fail on
  duplicate rows.
- `rejected_by_system` and `cancelled_by_system` are never written by hand.
  They come from real `ReservationService::approve()` and `expireStale()`
  calls, so system-owned reason text stays owned by the service.
- Every account in the volume set shares one password, read from
  `SEED_VOLUME_PASSWORD`. It is hashed once and reused for speed.

## Upgrade dari versi lama

Setelah membuat backup storage, pindahkan foto laporan lama dari disk public ke private:

```bash
php artisan reports:protect-photos --delete-public
```

Perintah ini hanya diperlukan untuk instalasi yang pernah menyimpan foto laporan pada disk public.
