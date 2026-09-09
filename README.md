# Sistem Reservasi & Pelaporan Fasilitas Kampus

Aplikasi web berbasis **Laravel 13** untuk mengelola penggunaan fasilitas kampus (ruang kelas, aula, laboratorium, alat, lapangan) dalam satu platform:

- **Alur Reservasi** — pengguna mengecek ketersediaan slot waktu, mengajukan reservasi dengan tujuan penggunaan; petugas menyetujui/menolak/membatalkan.
- **Alur Pelaporan** — pengguna melaporkan kerusakan fasilitas (kategori, deskripsi, foto); petugas memproses hingga selesai dan memperbarui status ketersediaan fasilitas.

Dokumen acuan:

- Spesifikasi teknis: [docs/spesifikasi-sistem-reservasi.md](docs/spesifikasi-sistem-reservasi.md)
- Checklist fitur vs spesifikasi: [docs/feature-checklist.md](docs/feature-checklist.md)

---

## Fitur

### Sudah tersedia
- Autentikasi custom (session-based): registrasi mandiri role `pengguna`, login dengan throttle, logout, dashboard role-aware
- Siklus akun: registrasi berstatus `pending`, admin memverifikasi/menolak; akun yang dibuat admin langsung `aktif`
- Kelola akun admin: buat akun petugas dan pengguna
- CRUD fasilitas admin: tambah/edit/nonaktifkan/aktifkan, dengan upload foto
- Admin dashboard: ringkasan antrian reservasi, laporan, fasilitas perbaikan, dan akun menunggu verifikasi
- Antrian reservasi petugas: daftar + filter status/tanggal, detail, setujui/tolak/batalkan dengan alasan (konfirmasi via dialog)
- Mesin aturan reservasi: slot 30 menit (07.00–20.00), kuota pending, lead time, anti-bentrok approved, approve dengan kunci transaksi
- Seeder akun demo + fasilitas + data uji
- 90 tes Pest hijau

Detail status per fitur (termasuk yang belum dikerjakan): lihat [docs/feature-checklist.md](docs/feature-checklist.md).

---

## Arsitektur

- **MVC murni (Laravel 13)** — Model (Eloquent), View (Blade + Tailwind CSS via Vite), Controller tipis.
- **Autentikasi custom** tanpa Breeze: `Auth\*Controller` + `RateLimiter`; gate akun `AccountStatusGate` + middleware `active`.
- **Otorisasi role**: middleware `EnsureRole` (`role:admin`, `role:petugas,admin`); verifikasi akun hanya admin.
- **Service layer**: `ReservationService` (slot, bentrok, approve transaksi + `lockForUpdate`), `ReportService` (transisi status + audit), `AccountStatusGate`.
- **Validasi server** via FormRequest + custom Rule objects (`SlotTimeValid`, `NoApprovedOverlap`, `BookingLeadTime`, `PendingQuota`, `FacilityBookable`).
- **Keamanan**: password bcrypt, CSRF di semua form, Eloquent binding bebas SQLi, output ter-escape (XSS), upload foto diverifikasi mimes+size.
- **Testing**: Pest (feature + unit), termasuk unit test aturan slot/overlap.
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
│   │   │   ├── Officer/         # dashboard + antrian reservasi petugas
│   │   │   └── Admin/           # dashboard, akun, verifikasi, CRUD fasilitas
│   │   ├── Middleware/          # EnsureRole, EnsureAccountActive
│   │   └── Requests/            # Form Request (validasi server)
│   ├── Models/                  # User, Facility, Reservation, Report, ReportUpdate
│   ├── Rules/                   # SlotTimeValid, NoApprovedOverlap, dll.
│   └── Services/                # ReservationService, ReportService, AccountStatusGate
├── bootstrap/                   # konfigurasi app, alias middleware
├── config/                      # database.php, app.php (timezone Asia/Jakarta)
├── database/
│   ├── migrations/
│   └── seeders/                 # akun demo + fasilitas + data uji
├── docs/
│   ├── spesifikasi-sistem-reservasi.md
│   └── feature-checklist.md
├── resources/
│   ├── views/                   # Blade: auth, dashboard, petugas, admin, components/ui
│   └── js/                      # app.js (dialog, preview gambar)
├── routes/web.php
├── snapshots/                   # snapshot mingguan (lihat akhir README)
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
- MySQL 8 (XAMPP/Laragon) — buat DB `reservasi_kampus`
- Node.js >= 20 + npm

### Langkah

```bash
composer install
npm install && npm run build      # atau npm run dev saat development

cp .env.example .env              # sesuaikan kredensial DB
php artisan key:generate
php artisan migrate --seed
php artisan storage:link          # agar foto fasilitas/laporan tampil
php artisan serve                 # http://localhost:8000
```

### Dengan Docker

```bash
docker compose up -d --build
```

### Menjalankan Tes

```bash
vendor/bin/pest                   # atau: php artisan test --compact
```

Konfigurasi tes memakai DB `reservasi_kampus_testing` (MySQL). Untuk run cepat tanpa MySQL, gunakan sqlite:

```bash
$env:DB_CONNECTION="sqlite"; $env:DB_DATABASE=":memory:"
vendor/bin/pest --compact
```

### Akun Demo

Disediakan oleh seeder (`php artisan db:seed`):

| Role | Email | Password | Status |
|---|---|---|---|
| Admin | admin@kampus.test | admin123 | aktif |
| Petugas | petugas@kampus.test | petugas123 | aktif |
| Pengguna | budi@student.kampus.test | user123 | aktif |
| Pengguna | sari@dosen.kampus.test | user123 | aktif |
| Pengguna | pending@kampus.test | user123 | pending (demo verifikasi admin) |

---

## Snapshots

- [Snapshot 2026-09-09](snapshots/snapshot-2026-09-09.md)