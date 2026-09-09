# Changelog — Sistem Reservasi & Pelaporan Fasilitas Kampus

Format mengikuti riwayat commit tim. Tanggal terbaru di atas. Status merge: **Dev** = sudah masuk `dev`; **Branch** = masih di branch kerja anggota tim (belum di-merge ke `dev`).

> Patokan status terakhir: `origin/dev` = `db2abd7` (2026-09-09, PR #29 feat/cicd).

---

## [Unreleased] — Sedang dikerjakan di branch anggota tim

### `feat/facility-system` — Katalog fasilitas publik (Branch, author unknown)
Struktur route publik yang belum masuk `dev`:
- `GET /fasilitas` → `FacilityController@index` (`fasilitas.index`) — katalog publik
- `GET /fasilitas/{facility}` → `FacilityController@show` (`fasilitas.show`) — detail fasilitas
- `GET /fasilitas/{facility}/jadwal` → `FacilityController@jadwal` (`fasilitas.jadwal`) — jadwal/ketersediaan slot
- View: `resources/views/fasilitas/{index,jadwal,show}.blade.php`; tes: `PublicFacilityIndexTest`, `PublicFacilityJadwalTest`, `PublicFacilityShowTest`.
- Commit: `47e3185`, `3668275`, `f494c46` (2026-09-09).

> Catatan: konten ini adalah rework dari PR #20 (`1121df0`, 2026-09-07) yang menggabungkan `101ba6d` (core blade components + slot picker). Versi final berada di `f494c46`.

### `feature/officer-report` — Modul laporan kerusakan pengguna & petugas (Branch, Opank)
Modul lengkap alur laporan, belum masuk `dev`:
- **Pengguna**: `GET /laporan` (`laporan.index`), `GET /laporan/baru` (`laporan.create`), `POST /laporan` (`laporan.store`), `GET /laporan/{report}` (`laporan.show`) — `ReportController` + `StoreReportRequest`
- **Petugas**: `GET /petugas/laporan` (`petugas.laporan.index`), `GET /petugas/laporan/{report}` (`petugas.laporan.show`), `PATCH .../status` (`petugas.laporan.status`), `PATCH .../fasilitas-status` (`petugas.laporan.fasilitas-status`) — `Officer\ReportController`
- Perluasan `Officer\DashboardController` (ringkasan laporan) + `ReportService`
- View: `resources/views/laporan/{create,index,show}.blade.php` & `resources/views/petugas/laporan/{index,show}.blade.php`
- Tes: `UserReportTest`, `OfficerReportTest`, `OfficerDashboardTest`
- Commit: `8cdfea4` (2026-09-09).

---

## v1.0.0 — 2026-09-09 (Stable, tag `v1.0.0` @ `060685f`)

Rilis stabil pertama. Detail lengkap: [releases/v1.0.0.md](releases/v1.0.0.md).

### Dev — dikerjakan muhffikkri (Fikri) & kontributor
- **Landing page** (`060685f`, `d6fe94e`): katalog fasilitas publik + filter (kata kunci/jenis/lokasi/kapasitas) + grid 26 slot 07.00–20.00 tanpa data pemohon (BR-13)
- **Admin dashboard** (`5061c7a`, `de2cbe5`): ringkasan antrian reservasi, laporan, fasilitas perbaikan, akun menunggu verifikasi
- **Dokumentasi** (`32143e0`, `f4428e3`, `bea2344`, `72f63fd`): README, feature-checklist, snapshot, release notes, konvensi commit (COMMIT.md), panduan desain (DESIGN.md)
- **Pull-only deploy + frontend container** (`cb5ecf5`): `git fetch`+`git reset --hard`, `docker compose run --rm frontend` (`npm ci && npm run build`)
- **Nginx config** (`ef22648`): `docker/nginx/default.conf`

### 2026-09-08 (Dev)
- **Docker** (`b7d9042`): `Dockerfile`, `docker-compose.yml`
- **CI/deploy** (`8cd2da9` dkk): GitHub Actions `deploy.yml` (Tailscale + SSH → VPS, trigger push `dev`)

### 2026-09-07 (Dev)
- **PR #20 feat/facility-system** (`1121df0`): commit awal `101ba6d` — core Blade components + reservation slot picker
- Merge dev/staging (`756d633` dst)

### 2026-09-06 (Dev)
- **Reservasi & petugas** (`PR #15` → `2df47f1`, dilanjutkan #23): dashboard antrian petugas + `ReservationController` (approve/reject/cancel), dialog konfirmasi `1ac94cf`, tes approve/reject `ea58323`.
  > Terdapat `f94c40a` *Revert* sementara atas PR #15; alur akhirnya terintegrasi ke baseline dev.
- **CRUD fasilitas admin** (`4860a5a`): pengelolaan fasilitas lengkap (dilanjutkan final di `5061c7a`)

### 2026-09-04 (Dev)
- **Refactor slot & overlap** (`e72c79c` PR #13): `NoApprovedOverlap` jadi satu-satunya pemilik BR-6 (`0e1dd20`), validasi slot pakai Carbon di `create` (`43fc0b0`), tes blocking overlap/split slot (`f8563f3`)
- **Guard approve fasilitas non-aktif** (`b88b04a` PR #14): yang menolak approve pada fasilitas `perbaikan`/`nonaktif` (`b00f64f`), perjelas dokumen spec (`a0a8dca`)
- **UI foundation** (`101ba6d`): core Blade components + reservation slot picker

### 2026-09-03 (Dev)
- **Slots & approval transactional** (`36f3a62` PR #3): `SlotTimeValid`, `BookingLeadTime`, `PendingQuota`, `NoApprovedOverlap`, `FacilityBookable`, `ReservationService@approve` (transaksi + anti-race), timezone `Asia/Jakarta`, factory slot-aligned
- **Laporan state machine** (`cf09b13` PR #6): `ReportService` transisi status + audit `report_updates`, `StoreUpdateReportStatusRequest`, seeder audit, tes transisi
- **Kelola akun petugas** (`dd30236` PR #7): `Admin\OfficerAccountController`, view index/create, tes
- **Kelola akun pengguna** (`8d29fb5` PR #8): `Admin\UserAccountController`, view index/create, tes
- **Refactor akun admin** (`8c5b82d` PR #9): ekstrak base controller & base request (`ee779d6`, `7d43625`, `b5d1522`, `76393fb`), scope `pending`, tes gate
- **Refactor auth logout** (`1f78431` PR #10): `Auth\LogoutController` terpisah dari login
- **Dokumentasi** (PR #4 & #5): komentar Indonesia pada kode dasar; DESIGN.md + COMMIT.md + konvensi scope commit (`5e05584`)

### 2026-08-30 — Fondasi (Dev)
- **PR #1 feat/data-foundation** (`d56c673`): inisialisasi Laravel, spesifikasi teknis `docs/spesifikasi-sistem-reservasi.md`, skema DB (`users` role+account_status, `facilities`, `reservations`, `reports`, `report_updates`), model + relasi + overlap scopes, seeder demo
- **PR #2 feat/auth-and-roles** (`534afac`): login/logout + rate limiting (`40ccb01`), registrasi mandiri dengan role `pengguna` paksa + status `pending` (`caa229c`), verifikasi akun admin, middleware role & account-activation, layout & navigasi dasar, dashboard placeholder

---

## Catatan Método

- Rentang dok: `2026-08-30` → `2026-09-09`.
- Commit tim di luar dev yang belum terdokumentasi di release: lihat bagian [Unreleased].
- Snapshot detail per tanggal: `snapshots/`.