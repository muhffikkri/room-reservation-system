# Checklist Fitur — Sistem Reservasi & Pelaporan Fasilitas Kampus

| Meta | Nilai |
|---|---|
| Tanggal pembaruan | 2026-09-23 |
| Referensi commit | `refactor/reservation-availability` (`60cde7e`) — keputusan ketersediaan slot disentralkan ke satu modul `ReservationAvailability` |
| Dokumen sempai dasar | [spesifikasi-sistem-reservasi.md](spesifikasi-sistem-reservasi.md) |
| Status publikasi | Checklist mengikuti **§16** dan **§8** dokumen spesifikasi — **BR-13 Selesai (semua deliverable §16 terpenuhi)** |

> Dokumen ini menandai fitur yang **sudah** dan **belum** tersedia sampai commit tercantum di atas.
> Status merujuk **`dev`** (commit `b21af99` — setelah webp ter-merge). Refactor `refactor/reservation-availability` masih **Di Branch** dan belum masuk `dev`.
> Legenda: ✔ = Selesai · ◐ = Sebagian · ✗ = Belum.

---

## 1. Checklist Deliverables (§16 spesifikasi)

| # | Item (§16) | Status | Catatan & Artefak |
|---|---|---|---|
| 1 | Registrasi mandiri (role `pengguna`, status `pending`), login, logout | ✔ Selesai | `Auth\RegisterController` (hanya pengguna), `LoginController` (throttle `10,1`, blokir akun non-aktif + validasi `AccountStatusGate`), `LogoutController`. Registrasi juga mengecek `identity`/`phone` unik (migration `add_unique_identity_phone_to_users_table`). Sesi di-invalidasi saat role/status berubah (`UserObserver`). View `auth/register\|login`. |
| 2 | Verifikasi/tolak akun oleh admin; petugas dibuat admin; pengguna/admin bisa dibuat admin (BR-17) | ✔ Selesai | `Admin\AccountVerificationController` (verify/reject + riwayat `account_verification_actions`, `AccountVerificationService`); `OfficerAccountController` + `UserAccountController` berbagi `BaseAccountController` — akun buatan admin langsung `aktif` (BR-14); `AdminAccountController` mengelola & membuat admin baru (BR-17); pulihkan akun ditolak (`PATCH /admin/pengguna/{user}/pulihkan`). |
| 3 | Daftar fasilitas publik + filter tipe/lokasi/kapasitas + grid ketersediaan **tanpa data pemohon** | ✔ Selesai | Landing page publik (`App\Http\Controllers\HomeController` + `resources/views/landing/index`): katalog fasilitas + filter (`q`, `type`, `location`, `capacity`) + grid 26 slot 07:00–20:00 (BR-1) + status `aktif/perbaikan/nonaktif` + akses login/daftar. Halaman khusus publik: `/fasilitas` (index), `/fasilitas/{facility}` (show-detail), `/fasilitas/{facility}/jadwal` (jadwal slot) — `FacilityController` + view `fasilitas/{index,show,jadwal}` + tes `PublicFacility*Test`. Grid memakai proyeksi publik `ReservationAvailability::publicScheduleSlots` (tanpa lead time). Identitas pemohon & tujuan tidak pernah dirender di keduanya (BR-13). |
| 4 | Ajukan reservasi (tujuan wajib) + validasi slot server & client | ✔ Selesai | `ReservationController::create` + `StoreReservationRequest` — form + slot picker (`components/reservation/slot-picker.blade.php`) + validasi server via modul `ReservationAvailability` (slot BR-1/BR-2, lead time BR-3, kuota BR-4, fasilitas BR-5, overlap BR-6) dieksekusi di dalam transaksi `ReservationService::create`; client menerima `maxDurationSlots` dari server (`data-max-duration-slots`) + `timeOptions`. View `reservasi/create` (tanpa inline script) + tes `UserReservationCreateTest`. |
| 5 | Riwayat, detail, dan pembatalan reservasi milik sendiri (batas waktu) | ✔ Selesai | `ReservationController@index\|show\|destroy` — indeks riwayat, detail, `DELETE /reservasi/{reservation}` (min 1 jam sebelum mulai, hanya milik sendiri — BR-8) + tes `ReservationCancellationTest` (8 tes). |
| 6 | Dashboard antrian petugas (reservasi + laporan) | ✔ Selesai | `Officer\DashboardController`: ringkasan reservasi pending + laporan `baru`/`diproses` + fasilitas perbaikan + daftar antrian. Alur laporan petugas tersedia (`/petugas/laporan`, `Officer\ReportController`) — merge `14541ff`. |
| 7 | Approve/reject/cancel reservasi; anti-bentrok saat approve | ✔ Selesai | `Officer\ReservationController` + `ReservationService` (transaksi + `lockForUpdate`, 409 saat bentrok — BR-7), reject/cancel wajib alasan min 10 (BR-9), guard fasilitas non-aktif (BR-12). Detail + dialog konfirmasi di `petugas/reservasi/*`. |
| 8 | Laporan kerusakan (kategori, deskripsi, foto) + status laporan untuk pelapor | ✔ Selesai | `ReportController` (pengguna) + `StoreReportRequest` + `ReportService::createReport` (foto ke `storage/app/private/reports`, otomatis dikonversi WebP) + `ReportPhotoController` (policy-protected) + view `laporan/{create,index,show}` (route `laporan.*`). |
| 9 | Transisi status laporan + catatan resolusi + riwayat | ✔ Selesai | `ReportService::transition` (state machine §9.2 + audit `report_updates`, BR-10) diformat ke UI: `Officer\ReportController@updateStatus` + `PATCH petugas.laporan.status` + view `petugas/laporan/{index,show}` — merge `14541ff`. Tes unit & fitur (BR-10) hijau. |
| 10 | Status fasilitas `perbaikan` ↔ `aktif` dari alur laporan | ✔ Selesai | `Officer\ReportController@toggleFacilityStatus` (BR-11): `PATCH petugas.laporan.fasilitas-status`→ `markFacilityForRepair`/`restoreFacilityToActive` — merge `14541ff`. |
| 11 | CRUD fasilitas (tambah/edit/nonaktifkan) | ✔ Selesai | `Admin\FacilityController` + `FacilityRequest`; destroy = soft-disable via `nonaktifkan`/`aktifkan`; foto upload ke `storage/public/facilities` (otomatis dikonversi WebP, kualitas 80 maks 1920px); preview gambar di `app.js`. |
| 12 | Rekap okupansi & frekuensi kerusakan + ekspor CSV & PDF | ✔ Selesai | `RecapService` (`getOccupancyRecap`, `getDamageRecap`, ekspor CSV/HTML), `Admin\RecapController` (`occupancy`, `damage`, `export*Csv`, `export*Pdf`), view `admin/rekap/{occupancy,damage}` + navigasi admin. Filter tanggal, kartu metrik, tabel per fasilitas, rincian kategori kerusakan. |
| 13 | Validasi server & client pada semua form penting | ◐ Sebagian | Server ✔ (FormRequest + modul `ReservationAvailability` pada semua form yang ada). Client sebagian: atribut HTML5, dialog konfirmasi, preview gambar fasilitas; slot picker & helper client untuk reservasi ada (durasi dari `data-max-duration-slots`), namun validasi client mirror untuk form laporan belum menyeluruh. |
| 14 | Seeder akun demo berjalan (`php artisan migrate:fresh --seed`) | ✔ Selesai | `UserSeeder`, `FacilitySeeder` (5 fasilitas sesuai §15), `ReservationSeeder`, `ReportSeeder`; credential seeder wajib melalui `SEED_*_PASSWORD` lokal dan tidak disimpan di repo. |
| 15 | README berisi setup + informasi login | ✔ Selesai | README.md diperbarui: setup, arsitektur, nama akun demo tanpa password plaintext, storage private, command migrasi attachment, dan catatan hardening. |

## 2. Kepatuhan Business Rules (§8 spesifikasi)

| BR | Aturan | Status | Keterangan |
|---|---|---|---|
| BR-1 | Jam 07:00–20:00, slot 30 menit (26 slot/hari) | ✔ | `ReservationAvailability` (`slotsForDay`, `slotTimeErrors`) |
| BR-2 | `end > start`, durasi 1–8 slot | ✔ | `ReservationAvailability` (`slotTimeErrors`, maks 8 slot / 240 mnt) |
| BR-3 | Mulai min `now+1 jam` (60 menit) | ✔ | `ReservationAvailability` (`leadTimeCutoff` 60 mnt) — hanya proyeksi booking |
| BR-4 | Maks 2 reservasi `pending`/hari/pengguna | ✔ | `ReservationAvailability` (`pendingQuotaError`) |
| BR-5 | Fasilitas wajib `aktif` | ✔ | `ReservationAvailability` (`isFacilityBookable`) |
| BR-6 | Tanpa overlap dengan `approved` saat pengajuan | ✔ | `ReservationAvailability` (`hasBlockingOverlap`/`hasApprovedOverlap`) via `approvedForDay` |
| BR-7 | Approve transaksi + `lockForUpdate`, 409 bila bentrok | ✔ | `ReservationService::approve()` + `ConflictHttpException` via `hasBlockingOverlap` |
| BR-8 | Cancel pengguna (milik sendiri, min 1 jam sebelum mulai) | ✔ | `ReservationController@destroy` + `ReservationCancellationTest` |
| BR-9 | Petugas cancel/reject wajib alasan min 10 | ✔ | `RejectReservationRequest`, `CancelReservationOfficerRequest` |
| BR-10 | Transisi laporan + `resolution_note` + audit | ✔ | `ReportService::transition` + `Officer\ReportController@updateStatus` + tes (BR-10) |
| BR-11 | Fasilitas `perbaikan` ↔ `aktif` dari alur laporan | ✔ | `Officer\ReportController@toggleFacilityStatus` → `markFacilityForRepair`/`restoreFacilityToActive` |
| BR-12 | Fasilitas non-aktif tak dapat direservasi/di-approve | ✔ | `ReservationAvailability` (`bookingSlots` state `inactive`) + guard approve + halaman fasilitas publik |
| BR-12b | Rekap okupansi & frekuensi kerusakan + ekspor CSV/PDF | ✔ | `RecapService` + `Admin\RecapController` + views + routes; filter tanggal, metrik, ekspor |
| BR-13 | Publik lihat fasilitas tanpa data pemohon | ✔ | Landing page & halaman `/fasilitas` hanya merender status slot; proyeksi publik `publicScheduleSlots` menampilkan `available` walau di bawah lead time (BR-13) |
| BR-14 | Akun registrasi `pending` → login ditolak; akun admin langsung `aktif` | ✔ | `AccountStatusGate` + `EnsureAccountActive` |
| BR-15 | Petugas tidak registrasi mandiri | ✔ | Registrasi dibatasi role `pengguna` |
| BR-16 | Reservasi approved di fasilitas `perbaikan` dibatalkan petugas | ✔ | Aksi cancel petugas tersedia + alur laporan memberi flag fasilitas `perbaikan`; petugas dapat membatalkan reservasi yang bertabrakan (BR-9) |
| BR-17 | Multi-admin: admin aktif boleh membuat admin baru; tanpa registrasi mandiri admin | ✔ | `Admin\AdminAccountController` + `AdminAccountRequest`

## 3. Yang Sudah Ada (ringkasan artefak — `dev` `b21af99` + branch refactor `60cde7e`)

- **50 route** (lihat `php artisan route:list`): home/landing publik, fasilitas publik (katalog/detail/jadwal), auth custom, laporan pengguna & petugas, reservasi pengguna (riwayat/baru/detail/batal), admin (dashboard, akun pengguna/petugas/admin, verifikasi + pulihkan, fasilitas CRUD), petugas (dashboard, antrian reservasi, laporan).
- **186 tes Pest / 758 assertions — terverifikasi hijau** (2026-09-23, MySQL `reservasi_kampus_testing`): auth & gate akun, verifikasi + riwayat verifikasi, isolasi role (22), akun admin/petugas, reservasi pengguna (create + cancel BR-8) & petugas (BR-7/BR-9/BR-16), CRUD fasilitas, dashboard admin & petugas, landing page, halaman fasilitas publik (BR-13), laporan (BR-10, BR-11), media WebP, aturan slot/overlap (unit + fitur terhadap interface `ReservationAvailability`), UI accessibility (a11y), skeleton loading, lazy images. Referensi sebelumnya: 168 tes / 627 assertions (v1.2.0-dev `e03df57`).
- **Reservation Availability** (branch `refactor/reservation-availability`): satu modul `app/Services/ReservationAvailability` memegang semua keputusan ketersediaan slot (jam operasional, slot, lead time, kuota, overlap, kelayakan fasilitas, format `H:i`, proyeksi publik & booking); `App\Rules\*` dihapus; validasi dijalankan di dalam transaksi `ReservationService::create()`/`approve()`.
- **Media WebP** (dev commit `b21af99`): foto fasilitas & laporan dikonversi otomatis ke WebP (kualitas 80, maks 1920px) via `intervention/image ^4.0`; tes menegaskan upload tersimpan `.webp` < 500KB.
- Isolasi peran tegas: grup route `role:pengguna`, `role:petugas`, `role:admin`; kebijakan `ReportPolicy`/`ReservationPolicy`; error 403 untuk akses lintas peran.
- Validasi server semua form lewat FormRequest + modul `ReservationAvailability`; flash `success`/`error` konsisten.
- Deploy: `Dockerfile`, `docker-compose.yml` (frontend dibangun di container via `docker compose run --rm frontend` → `npm ci && npm run build`), `deploy.yml` (GitHub Actions → VPS saat push ke `dev`; server pull-only via `git fetch` + `git reset --hard origin/dev`, host tidak pernah menjalankan `npm install`/`npm run build`).
- Riwayat lengkap seluruh commit tim: [CHANGELOG.md](../CHANGELOG.md).

## 4. Roadmap — Yang Belum (menunggu milestone anggota tim)

| Area | Pembagian tugas | Deliverable | Status branch |
|---|---|---|---|
| Rekap & ekspor CSV/PDF | (selesai v1.2.2) | `Admin\RecapController` + `RecapService` + views + routes | ✔ `feat/recap-occupancy-damage-export` |

---

*Terakhir diperbarui: 2026-09-23 · Komit referensi `refactor/reservation-availability` (`60cde7e`) · Refactor masih di branch (belum di-merge ke `dev`); seluruh fitur deliverable §16 sudah terpenuhi dan masuk `dev`.*
