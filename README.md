# Sistem Reservasi & Pelaporan Fasilitas Kampus

Aplikasi web berbasis **Laravel 13** untuk mengelola penggunaan fasilitas kampus (ruang kelas, aula, laboratorium, alat, lapangan) dalam satu platform:

- **Alur Reservasi** — pengguna mengecek ketersediaan slot waktu, mengajukan reservasi dengan tujuan penggunaan; petugas menyetujui/menolak/membatalkan.
- **Alur Pelaporan** — pengguna melaporkan kerusakan fasilitas (kategori, deskripsi, foto); petugas memproses hingga selesai dan memperbarui status ketersediaan fasilitas.

Dokumen acuan:

- Spesifikasi teknis: [docs/spesifikasi-sistem-reservasi.md](docs/spesifikasi-sistem-reservasi.md)
- Checklist fitur vs spesifikasi: [docs/feature-checklist.md](docs/feature-checklist.md)
- Ringkasan konteks teknis: [CONTEXT.md](CONTEXT.md)
- Riwayat perubahan (changelog): [CHANGELOG.md](CHANGELOG.md)

---

## Fitur

### Sudah tersedia
- Landing page publik (`/`): katalog fasilitas + filter (kata kunci, jenis, lokasi, kapasitas) + grid ketersediaan 26 slot (BR-1, BR-13)
- Halaman fasilitas publik (`/fasilitas`): katalog, detail, dan jadwal slot 26 slot tanpa data pemohon (BR-13)
- Autentikasi custom (session-based): registrasi mandiri role `pengguna` (di-throttle, `identity`/`phone` unik), login dengan throttle, logout, dashboard role-aware
- Siklus akun: registrasi berstatus `pending`, admin memverifikasi/menolak, akun yang dibuat admin langsung `aktif`, akun ditolak bisa dipulihkan admin, riwayat verifikasi tersimpan
- Kelola akun admin: admin dapat membuat akun petugas, pengguna, dan admin (BR-17)
- Alur reservasi pengguna (`/reservasi`): ajukan reservasi (form + slot picker), riwayat & detail, batalkan milik sendiri min 1 jam sebelum mulai (BR-8)
- CRUD fasilitas admin: tambah/edit/nonaktifkan/aktifkan, dengan upload foto (simpan file asli)
- Admin dashboard (agregat read-only): ringkasan antrian reservasi, laporan, fasilitas perbaikan, dan akun menunggu verifikasi
- Antrian reservasi petugas: daftar + filter status/tanggal, detail, setujui/tolak/batalkan dengan alasan (konfirmasi via dialog)
- Laporan kerusakan pengguna (`/laporan`): buat laporan (kategori, deskripsi, foto), daftar & detail laporan milik sendiri
- Antrian laporan petugas (`/petugas/laporan`): filter status, transisi `baru → diproses → selesai/tolak` dengan catatan resolusi, tandai fasilitas `perbaikan` ↔ `aktif` (BR-10, BR-11)
- **Rekap okupansi & frekuensi kerusakan admin (`/admin/rekap/okupansi`, `/admin/rekap/kerusakan`): filter tanggal, ringkasan metrik, tabel per fasilitas, ekspor CSV & PDF (BR-12)**
- Mesin ketersediaan reservasi (`App\Services\ReservationAvailability`): slot 30 menit (07:00–20:00, 26 slot), kuota pending, lead time 60 menit, anti-bentrok approved, proyeksi jadwal publik & pemesanan, approve dengan kunci transaksi — format waktu kanonik `H:i`
- Seeder akun demo + fasilitas + data uji (password hanya dari environment lokal)
- 186 tes Pest — 758 assertions terverifikasi hijau (`php artisan test`, MySQL; 2026-09-23)

Status per fitur & business rules lengkap: [docs/feature-checklist.md](docs/feature-checklist.md).

---

## Arsitektur

- **MVC murni (Laravel 13)** — Model (Eloquent), View (Blade + Tailwind CSS via Vite), Controller tipis.
- **Autentikasi custom** tanpa Breeze: `Auth\*Controller` + `RateLimiter`; gate akun `AccountStatusGate` + middleware `active`; sesi di-invalidasi saat role/status akun berubah (`UserObserver`).
- **Otorisasi role**: grup route peran dipisah tegas — middleware `EnsureRole` (`role:pengguna`, `role:petugas`, `role:admin`) + kebijakan `ReportPolicy`/`ReservationPolicy`; verifikasi akun hanya admin.
- **Service layer**: `ReservationService` (mutasi reservasi + approve transaksi + `lockForUpdate`), `ReservationAvailability` (keputusan ketersediaan slot: jam operasional, slot, lead time, kuota, overlap, proyeksi publik/booking), `ReportService` (buat laporan + transisi status + audit + toggle status fasilitas), `AccountVerificationService` (audit verifikasi/pulihkan), `AccountStatusGate`, `AccountAttributes`.
- **Validasi server** via FormRequest + modul `ReservationAvailability` (slot BR-1/BR-2, lead time BR-3, kuota BR-4, fasilitas BR-5, overlap BR-6, dll.) dieksekusi di dalam transaksi `ReservationService::create`.
- **Keamanan**: password bcrypt, CSRF di semua form, Eloquent binding bebas SQLi, output ter-escape (XSS), header keamanan/CSP, otorisasi berlapis, upload dibatasi mimes+size+dimensi dan laporan disimpan private, rate limit + quota, validasi reservasi atomic.
- **Testing**: Pest (feature + unit) — 186 tes / 758 assertions hijau, termasuk regression test hardening keamanan.
- **Deploy**: GitHub Actions (`.github/workflows/deploy.yml`) mendorong ke VPS saat push ke `dev`; aplikasi dikontainerkan (`Dockerfile`, `docker-compose.yml`).

## Stack

| Komponen | Versi |
|---|---|
| PHP | >= 8.3 (pdo_mysql, mbstring, fileinfo, gd, zip) |
| Composer | >= 2 |
| MySQL | 8.x (utf8mb4_unicode_ci) |
| Node.js + npm | >= 20 |
| Laravel | 13.x |
| Frontend | Blade + Tailwind CSS (Vite) |

---

## Struktur Folder

```text
room-reservation-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/            # login, register, logout (custom session-based)
│   │   │   ├── Officer/         # dashboard + antrian reservasi & laporan petugas
│   │   │   ├── Admin/           # dashboard, akun (pengguna/petugas/admin), verifikasi, CRUD fasilitas
│   │   │   ├── ReservationController # alur reservasi pengguna (riwayat/baru/batal)
│   │   │   ├── ReportController # laporan kerusakan pengguna (CRUD)
│   │   │   ├── ReportPhotoController # serve foto laporan setelah policy authorize
│   │   │   └── FacilityController # halaman publik fasilitas (katalog/detail/jadwal)
│   │   ├── Middleware/          # EnsureRole, EnsureAccountActive
│   │   └── Requests/            # Form Request (validasi server)
│   ├── Models/                  # User, Facility, Reservation, Report, ReportUpdate, AccountVerificationAction
│   ├── Observers/               # UserObserver (invalidasi sesi saat role/status berubah)
│   ├── Policies/                # ReportPolicy, ReservationPolicy (akses lintas peran)
│   ├── Services/                # ReservationService, ReservationAvailability, ReportService, AccountVerificationService, AccountStatusGate
│   └── Support/                 # AccountAttributes
├── bootstrap/                   # konfigurasi app, alias middleware
├── config/                      # database.php, app.php (timezone Asia/Jakarta)
├── database/
│   ├── migrations/
│   └── seeders/                 # akun demo + fasilitas + data uji (secret dari env)
├── docs/
│   ├── spesifikasi-sistem-reservasi.md
│   └── feature-checklist.md
├── resources/
│   ├── views/                   # Blade: landing, fasilitas publik, auth, dashboard, laporan, reservasi, petugas, admin, components/ui
│   └── js/                      # app.js (dialog, preview gambar, tab jadwal)
├── routes/web.php
├── snapshots/                   # snapshot mingguan (lihat bagian Snapshots)
├── tests/                       # Feature + Unit (Pest)
├── Dockerfile
├── docker-compose.yml
└── .github/workflows/deploy.yml
```

---

## Cara Menjalankan

### Prasyarat
- PHP >= 8.3 dengan ekstensi `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `zip`
- Composer >= 2
- MySQL 8 (XAMPP/Laragon) — buat DB dan samakan namanya dengan `DB_DATABASE` di `.env` (nilai contoh yang dipakai: `reservasi-kampus`). Catatan: `.env.example` default-nya `sqlite`, jadi saat pakai MySQL pastikan `DB_CONNECTION=mysql` + `DB_*` diisi.
- Node.js >= 20 + npm

### Langkah

```bash
composer install
npm install && npm run build      # atau npm run dev saat development

cp .env.example .env              # sesuaikan kredensial DB
php artisan key:generate
php artisan migrate --seed       # isi SEED_*_PASSWORD secara lokal terlebih dahulu
php artisan storage:link          # hanya untuk foto fasilitas publik; foto laporan via route terotorisasi
php artisan serve                 # http://localhost:8000
```

> **Akun demo tidak bisa dibuat?** Pastikan `SEED_ADMIN_PASSWORD`, `SEED_OFFICER_PASSWORD`, `SEED_USER_PASSWORD`, dan `SEED_PENDING_PASSWORD` diisi secara lokal dengan secret unik minimal 12 karakter. Nilai password tidak disimpan di repository.

Untuk deployment dari versi lama, backup storage terlebih dahulu lalu pindahkan attachment laporan lama ke disk private:

```bash
php artisan reports:protect-photos --delete-public
```

### Dengan Docker

```bash
docker compose up -d --build
```

### Menjalankan Tes

```bash
vendor/bin/pest                   # atau: php artisan test --compact
```

Konfigurasi tes memakai DB `reservasi_kampus_testing` (MySQL) sesuai `phpunit.xml` — pastikan MySQL aktif dan test DB tersedia sebelum menjalankan:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS reservasi_kampus_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
vendor/bin/pest                   # atau: php artisan test --compact
```

Bundle schema memakai sintaks MySQL (`MODIFY`, `CHARACTER SET`), jadi **sqlite in-memory tidak didukung** — tes memakai MySQL. Status terverifikasi: **186 tes / 758 assertions hijau** (2026-09-23).

### Akun Demo

Disediakan oleh seeder (`php artisan db:seed`). Password dibaca dari `SEED_*_PASSWORD` di environment lokal dan tidak dicantumkan di sini:

| Role | Email | Password | Status |
|---|---|---|---|
| Admin | admin@kampus.test | `SEED_ADMIN_PASSWORD` | aktif |
| Petugas | petugas@kampus.test | `SEED_OFFICER_PASSWORD` | aktif |
| Pengguna | budi@student.kampus.test | `SEED_USER_PASSWORD` | aktif |
| Pengguna | sari@dosen.kampus.test | `SEED_USER_PASSWORD` | aktif |
| Pengguna | pending@kampus.test | `SEED_PENDING_PASSWORD` | pending (demo verifikasi admin) |

Catatan branch hardening: file Docker dan workflow deploy sengaja tidak diubah karena berada di luar scope owner branch ini; audit dan hardening Docker perlu dilakukan terpisah.

---

## Release

- [v1.2.3 — Candidate 2026-09-23](releases/v1.2.3.md)
- [v1.2.2 — Minor 2026-09-18](releases/v1.2.2.md)
- [v1.2.0 — Candidate 2026-09-16](releases/v1.2.0.md)
- [v1.1.0 — Minor 2026-09-12](releases/v1.1.0.md)
- [v1.0.0 — Stable 2026-09-09](releases/v1.0.0.md)

> Catatan versi (release notes) dirinci di `releases/`. Setiap versi menandai titik rilis stabil dengan referensi commit dan status fitur.

---

## Snapshots

- [Snapshot 2026-09-23](snapshots/snapshot-2026-09-23.md)
- [Snapshot 2026-09-15](snapshots/snapshot-2026-09-15.md)
- [Snapshot 2026-09-12](snapshots/snapshot-2026-09-12.md)
- [Snapshot 2026-09-09](snapshots/snapshot-2026-09-09.md)
- [Snapshot 2026-09-06](snapshots/snapshot-2026-09-06.md)
- [Snapshot 2026-09-03](snapshots/snapshot-2026-09-03.md)

> Snapshot adalah file mingguan yang merinci perubahan, catatan deployment, dan referensi commit pada tiap titik waktu. Lihat folder `snapshots/`.
