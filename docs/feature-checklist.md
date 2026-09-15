# Checklist Fitur — Sistem Reservasi & Pelaporan Fasilitas Kampus

| Meta | Nilai |
|---|---|
| Tanggal pembaruan | 2026-09-15 |
| Referensi commit | `e03df57` (2026-09-15, merge PR #45 — riwayat verifikasi akun) |
| Dokumen sempai dasar | [spesifikasi-sistem-reservasi.md](spesifikasi-sistem-reservasi.md) |
| Status publikasi | Checklist mengikuti **§16** dan **§8** dokumen spesifikasi |

> Dokumen ini menandai fitur yang **sudah** dan **belum** tersedia sampai commit tercantum di atas.
> Status merujuk **`dev`** (commit `e03df57`). Fitur yang selesai di **branch kerja tim** tetapi belum masuk `dev` ditandai **Di Branch** pada kolom Catatan.
> Legenda: ✔ = Selesai · ◐ = Sebagian · ✗ = Belum.

---

## 1. Checklist Deliverables (§16 spesifikasi)

| # | Item (§16) | Status | Catatan & Artefak |
|---|---|---|---|
| 1 | Registrasi mandiri (role `pengguna`, status `pending`), login, logout | ✔ Selesai | `Auth\RegisterController` (hanya pengguna), `LoginController` (throttle `10,1`, blokir akun non-aktif + validasi `AccountStatusGate`), `LogoutController`. Registrasi juga mengecek `identity`/`phone` unik (migration `add_unique_identity_phone_to_users_table`). Sesi di-invalidasi saat role/status berubah (`UserObserver`). View `auth/register\|login`. |
| 2 | Verifikasi/tolak akun oleh admin; petugas dibuat admin; pengguna/admin bisa dibuat admin (BR-17) | ✔ Selesai | `Admin\AccountVerificationController` (verify/reject + riwayat `account_verification_actions`, `AccountVerificationService`); `OfficerAccountController` + `UserAccountController` berbagi `BaseAccountController` — akun buatan admin langsung `aktif` (BR-14); `AdminAccountController` mengelola & membuat admin baru (BR-17); pulihkan akun ditolak (`PATCH /admin/pengguna/{user}/pulihkan`). |
| 3 | Daftar fasilitas publik + filter tipe/lokasi/kapasitas + grid ketersediaan **tanpa data pemohon** | ✔ Selesai | Landing page publik (`App\Http\Controllers\HomeController` + `resources/views/landing/index`): katalog fasilitas + filter (`q`, `type`, `location`, `capacity`) + grid 26 slot 07.00–20.00 (BR-1) + status `aktif/perbaikan/nonaktif` + akses login/daftar. Halaman khusus publik (merge `7b2a730`): `/fasilitas` (index), `/fasilitas/{facility}` (show-detail), `/fasilitas/{facility}/jadwal` (jadwal slot) — `FacilityController` + view `fasilitas/{index,show,jadwal}` + tes `PublicFacility*Test`. Identitas pemohon & tujuan tidak pernah dirender di keduanya (BR-13). |
| 4 | Ajukan reservasi (tujuan wajib) + validasi slot server & client | ✔ Selesai | `ReservationController::create` + `StoreReservationRequest` — form + slot picker bawaan (`components/reservation/slot-picker.blade.php`) + validasi server: 6 Rule (`SlotTimeValid`, `BookingLeadTime`, `PendingQuota`, `NoApprovedOverlap`, `FacilityBookable` — BR-1..BR-6, BR-12) + view `reservasi/create` + tes `UserReservationCreateTest`. |
| 5 | Riwayat, detail, dan pembatalan reservasi milik sendiri (batas waktu) | ✔ Selesai | `ReservationController@index\|show\|destroy` — indeks riwayat, detail, `DELETE /reservasi/{reservation}` (min 1 jam sebelum mulai, hanya milik sendiri — BR-8) + tes `ReservationCancellationTest` (8 tes). |
| 6 | Dashboard antrian petugas (reservasi + laporan) | ✔ Selesai | `Officer\DashboardController`: ringkasan reservasi pending + laporan `baru`/`diproses` + fasilitas perbaikan + daftar antrian. Alur laporan petugas tersedia (`/petugas/laporan`, `Officer\ReportController`) — merge `14541ff`. |
| 7 | Approve/reject/cancel reservasi; anti-bentrok saat approve | ✔ Selesai | `Officer\ReservationController` + `ReservationService` (transaksi + `lockForUpdate`, 409 saat bentrok — BR-7), reject/cancel wajib alasan min 10 (BR-9), guard fasilitas non-aktif (BR-12). Detail + dialog konfirmasi di `petugas/reservasi/*`. |
| 8 | Laporan kerusakan (kategori, deskripsi, foto) + status laporan untuk pelapor | ✔ Selesai | `ReportController` (pengguna) + `StoreReportRequest` + `ReportService::createReport` (foto ke `storage/public/reports`) + view `laporan/{create,index,show}` (route `laporan.*`) — merge `14541ff` (Opank). |
| 9 | Transisi status laporan + catatan resolusi + riwayat | ✔ Selesai | `ReportService::transition` (state machine §9.2 + audit `report_updates`, BR-10) diformat ke UI: `Officer\ReportController@updateStatus` + `PATCH petugas.laporan.status` + view `petugas/laporan/{index,show}` — merge `14541ff`. Tes unit & fitur (BR-10) hijau. |
| 10 | Status fasilitas `perbaikan` ↔ `aktif` dari alur laporan | ✔ Selesai | `Officer\ReportController@toggleFacilityStatus` (BR-11): `PATCH petugas.laporan.fasilitas-status`→ `markFacilityForRepair`/`restoreFacilityToActive` — merge `14541ff`. |
| 11 | CRUD fasilitas (tambah/edit/nonaktifkan) | ✔ Selesai | `Admin\FacilityController` + `FacilityRequest`; destroy = soft-disable via `nonaktifkan`/`aktifkan`; foto upload ke `storage/public/facilities`; preview gambar di `app.js`. |
| 12 | Rekap okupansi & frekuensi kerusakan + ekspor CSV & PDF | ✗ Belum | `RecapService`, `Admin\RecapController`, view `admin/rekap/*` belum dibuat. Ditunda ke milestone v1. |
| 13 | Validasi server & client pada semua form penting | ◐ Sebagian | Server ✔ (FormRequest + Rule pada semua form yang ada). Client sebagian: atribut HTML5, dialog konfirmasi, preview gambar fasilitas; slot picker & helper client untuk reservasi ada, namun validasi client mirror untuk form laporan/reservasi belum menyeluruh. |
| 14 | Seeder akun demo berjalan (`php artisan migrate:fresh --seed`) | ✔ Selesai | `UserSeeder`, `FacilitySeeder` (5 fasilitas sesuai §15), `ReservationSeeder`, `ReportSeeder`; akun demo §5.3. |
| 15 | README berisi setup + informasi login | ✔ Selesai | README.md diperbarui (terakhir 2026-09-15): setup, arsitektur, akun demo, catatan pemecahan masalah seed, struktur folder, daftar snapshot, catatan modes tes (MySQL; sqlite in-memory tidak didukung skema). |

## 2. Kepatuhan Business Rules (§8 spesifikasi)

| BR | Aturan | Status | Keterangan |
|---|---|---|---|
| BR-1 | Jam 07.00–20.00, slot 30 menit (26 slot/hari) | ✔ | `SlotTimeValid` |
| BR-2 | `end > start`, durasi 1–8 slot | ✔ | `SlotTimeValid` |
| BR-3 | Mulai min `now+30 mnt` | ✔ | `BookingLeadTime` |
| BR-4 | Maks 2 reservasi `pending`/hari/pengguna | ✔ | `PendingQuota` |
| BR-5 | Fasilitas wajib `aktif` | ✔ | `FacilityBookable` |
| BR-6 | Tanpa overlap dengan `approved` saat pengajuan | ✔ | `NoApprovedOverlap` |
| BR-7 | Approve transaksi + `lockForUpdate`, 409 bila bentrok | ✔ | `ReservationService::approve()` |
| BR-8 | Cancel pengguna (milik sendiri, min 1 jam sebelum mulai) | ✔ | `ReservationController@destroy` + `ReservationCancellationTest` |
| BR-9 | Petugas cancel/reject wajib alasan min 10 | ✔ | `RejectReservationRequest`, `CancelReservationOfficerRequest` |
| BR-10 | Transisi laporan + `resolution_note` + audit | ✔ | `ReportService::transition` + `Officer\ReportController@updateStatus` + tes (BR-10) |
| BR-11 | Fasilitas `perbaikan` ↔ `aktif` dari alur laporan | ✔ | `Officer\ReportController@toggleFacilityStatus` → `markFacilityForRepair`/`restoreFacilityToActive` |
| BR-12 | Fasilitas non-aktif tak dapat direservasi/di-approve | ✔ | `FacilityBookable` + guard approve + halaman fasilitas publik menampilkan `inactive` |
| BR-13 | Publik lihat fasilitas tanpa data pemohon | ✔ | Landing page & halaman `/fasilitas` hanya merender status slot (BR-13) |
| BR-14 | Akun registrasi `pending` → login ditolak; akun admin langsung `aktif` | ✔ | `AccountStatusGate` + `EnsureAccountActive` |
| BR-15 | Petugas tidak registrasi mandiri | ✔ | Registrasi dibatasi role `pengguna` |
| BR-16 | Reservasi approved di fasilitas `perbaikan` dibatalkan petugas | ✔ | Aksi cancel petugas tersedia + alur laporan memberi flag fasilitas `perbaikan`; petugas dapat membatalkan reservasi yang bertabrakan (BR-9) |
| BR-17 | Multi-admin: admin aktif boleh membuat admin baru; tanpa registrasi mandiri admin | ✔ | `Admin\AdminAccountController` + `AdminAccountRequest`

## 3. Yang Sudah Ada (ringkasan artefak commit `e03df57`)

- **50 route** (lihat `php artisan route:list`): home/landing publik, fasilitas publik (katalog/detail/jadwal), auth custom, laporan pengguna & petugas, reservasi pengguna (riwayat/baru/detail/batal), admin (dashboard, akun pengguna/petugas/admin, verifikasi + pulihkan, fasilitas CRUD), petugas (dashboard, antrian reservasi, laporan).
- **168 tes Pest** terdefinisi — auth & gate akun, verifikasi + riwayat verifikasi, isolasi role (22), akun admin/petugas, reservasi pengguna (create + cancel BR-8) & petugas (BR-7/BR-9/BR-16), CRUD fasilitas, dashboard admin & petugas, landing page, halaman fasilitas publik (BR-13), laporan (BR-10, BR-11), aturan slot/overlap (unit). (Angka 120 tes / 439 assertions terverifikasi hijau pada v1.1.0 `e9c591a`; angka terbaru = deklarasi di `e03df57`.)
- Isolasi peran tegas: grup route `role:pengguna`, `role:petugas`, `role:admin`; kebijakan `ReportPolicy`/`ReservationPolicy`; error 403 untuk akses lintas peran.
- Validasi server semua form lewat FormRequest + Rule; flash `success`/`error` konsisten.
- Deploy: `Dockerfile`, `docker-compose.yml` (frontend dibangun di container via `docker compose run --rm frontend` → `npm ci && npm run build`), `deploy.yml` (GitHub Actions → VPS saat push ke `dev`; server pull-only via `git fetch` + `git reset --hard origin/dev`, host tidak pernah menjalankan `npm install`/`npm run build`).
- Riwayat lengkap seluruh commit tim: [CHANGELOG.md](../CHANGELOG.md).

## 4. Roadmap — Yang Belum (menunggu milestone anggota tim)

| Area | Pembagian tugas | Deliverable | Status branch |
|---|---|---|---|
| Rekap & ekspor CSV/PDF | (jadwal v1) | `Admin\RecapController` + `RecapService` | ✗ belum ada branch |

---

*Terakhir diperbarui: 2026-09-15 · Komit referensi `e03df57` (dev) · Status branch: seluruh fitur di atas sudah di-merge ke `dev`; branch `separate-admin-officer-roles`, `revert-30-feature/officer-report`, `test` tidak di-merge (superseded/stale).*