# Changelog — Sistem Reservasi & Pelaporan Fasilitas Kampus

Format mengikuti riwayat commit tim. Tanggal terbaru di atas. Status merge: **Dev** = sudah masuk `dev`; **Branch** = masih di branch kerja anggota tim (belum di-merge ke `dev`).

> Patokan status terakhir: `dev` = `e9c591a` (2026-09-12, merge PR #36 — docs v1.1.0; pemulihan fitur reservasi masuk via `a6a5d70`).

---

## [Unreleased] — Sedang dikerjakan di branch anggota tim

_Bagian ini kosong; semua pekerjaan tim terkini sudah masuk `dev`._

> Alur reservasi pengguna (form/slot picker/riwayat/batal-BR-8) & rekap/ekspor CSV-PDF masih menunggu milestone anggota tim.

---

## v1.1.0 — 2026-09-12 (Minor, baseline `dev` saat ini)

Rilis minor non-breaking dari `v1.0.0` (`060685f`): menambahkan modul laporan pengguna & petugas serta halaman publik fasilitas, lalu menjaga seluruh fitur reservasi yang sudah ada tetap utuh. Detail lengkap: [releases/v1.1.0.md](releases/v1.1.0.md).

### Dev — penambahan fitur sejak v1.0.0
- **Halaman fasilitas publik** (merge `feat/facility-system` → `7b2a730`): `/fasilitas` (katalog + filter), `/fasilitas/{facility}` (detail tanpa data pemohon, BR-13), `/fasilitas/{facility}/jadwal` (26 slot via `scopeOverlap`, BR-1/BR-6/BR-12) — `FacilityController` + views `fasilitas/{index,show,jadwal}` + `PublicFacility{Index,Jadwal,Show}Test` (17 tes)
- **Modul laporan kerusakan pengguna & petugas** (merge `test/officer-report` → `14541ff`): pengguna (`/laporan` index/create/store/show + foto, `ReportController` + `StoreReportRequest`) + petugas (`/petugas/laporan` index/show, update status BR-10, toggle status fasilitas BR-11, `Officer\ReportController`) — view `laporan/*`, `petugas/laporan/*`, `ReportService::createReport`, tes `UserReportTest`/`OfficerReportTest`/`OfficerDashboardTest` (10 tes, Opank)
- **Pulihkan fitur reservasi** (`a6a5d70`, masuk `dev` via merge PR #36 `e9c591a`): PR #35 (`3cc9f51`, GitHub merge `feature/officer-report`) sempat membawa `Revert PR #15` yang menghapus antrian reservasi petugas → di-revert. Alur reservasi (`Officer\ReservationController`, views `petugas/reservasi/*`, request cancel/reject, `OfficerReservationTest`) kembali utuh di `dev`.

> Catatan: `9df2d0d` (branch lokal `fix/restore-officer-reservation`) adalah versi standalone dari perbaikan yang di-merge lewat `a6a5d70`; yang menjadi baseline riwayat `dev` adalah `a6a5d70` → `e9c591a`.

### Dev — dikerjakan muhffikkri (Fikri) & kontributor (basis v1.0.0)
- **Landing page** (`060685f`, `d6fe94e`): katalog fasilitas publik + filter (kata kunci/jenis/lokasi/kapasitas) + grid 26 slot 07.00–20.00 tanpa data pemohon (BR-13)
- **Admin dashboard** (`5061c7a`, `de2cbe5`): ringkasan antrian reservasi, laporan, fasilitas perbaikan, akun menunggu verifikasi
- **Dokumentasi** (`32143e0`, `f4428e3`, `bea2344`, `72f63fd`): README, feature-checklist, snapshot, release notes, konvensi commit (COMMIT.md), panduan desain (DESIGN.md)
- **Pull-only deploy + frontend container** (`cb5ecf5`): `git fetch`+`git reset --hard`, `docker compose run --rm frontend` (`npm ci && npm run build`)
- **Nginx config** (`ef22648`): `docker/nginx/default.conf`

## v1.0.0 — 2026-09-09 (Stable, tag `v1.0.0` @ `060685f`)

Rilis stabil pertama. Detail lengkap: [releases/v1.0.0.md](releases/v1.0.0.md).

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

- Rentang dok: `2026-08-30` → `2026-09-12`.
- Commit tim di luar dev yang belum terdokumentasi di release: lihat bagian [Unreleased].
- Snapshot detail per tanggal: `snapshots/`.