# Checklist Fitur — Sistem Reservasi & Pelaporan Fasilitas Kampus

| Meta | Nilai |
|---|---|
| Tanggal pembaruan | 2026-09-10 |
| Referensi commit | `14541ff` (2026-09-10, merge `test/officer-report` — modul laporan) |
| Dokumen sempai dasar | [spesifikasi-sistem-reservasi.md](spesifikasi-sistem-reservasi.md) |
| Status publikasi | Checklist mengikuti **§16** dan **§8** dokumen spesifikasi |

> Dokumen ini menandai fitur yang **sudah** dan **belum** tersedia sampai commit tercantum di atas.
> Status merujuk **`dev`** (commit `14541ff`). Fitur yang selesai di **branch kerja tim** tetapi belum masuk `dev` ditandai **Di Branch** pada kolom Catatan.
> Legenda: ✔ = Selesai · ◐ = Sebagian · ✗ = Belum.

---

## 1. Checklist Deliverables (§16 spesifikasi)

| # | Item (§16) | Status | Catatan & Artefak |
|---|---|---|---|
| 1 | Registrasi mandiri (role `pengguna`, status `pending`), login, logout | ✔ Selesai | `Auth\RegisterController` (hanya pengguna), `LoginController` (throttle `10,1`, blokir akun non-aktif), `LogoutController`. View `auth/register|login`. |
| 2 | Verifikasi/tolak akun oleh admin; petugas dibuat admin; pengguna bisa dibuat admin | ✔ Selesai | `Admin\AccountVerificationController` (verify/reject); `OfficerAccountController` + `UserAccountController` berbagi `BaseAccountController`; akun buatan admin langsung `aktif` (BR-14). |
| 3 | Daftar fasilitas publik + filter tipe/lokasi/kapasitas + grid ketersediaan **tanpa data pemohon** | ✔ Selesai | Landing page publik (`App\Http\Controllers\HomeController` + `resources/views/landing/index`): katalog fasilitas + filter (`q`, `type`, `location`, `capacity`) + grid 26 slot 07.00–20.00 (BR-1) + status `aktif/perbaikan/nonaktif` + akses login/daftar. Halaman khusus publik (merge `7b2a730`): `/fasilitas` (index), `/fasilitas/{facility}` (show-detail), `/fasilitas/{facility}/jadwal` (jadwal slot) — `FacilityController` + view `fasilitas/{index,show,jadwal}` + tes `PublicFacility*Test`. Identitas pemohon & tujuan tidak pernah dirender di keduanya (BR-13). |
| 4 | Ajukan reservasi (tujuan wajib) + validasi slot server & client | ◐ Sebagian | Mesin reservasi lengkap & teruji: `ReservationService::create()` + 5 Rule (`SlotTimeValid`, `BookingLeadTime`, `PendingQuota`, `NoApprovedOverlap`, `FacilityBookable` — BR-1..BR-6). Komponen UI `components/reservation/slot-picker` sudah dibuat. **Belum**: halaman/form `/reservasi/baru` untuk pengguna + validasi client-nya. |
| 5 | Riwayat, detail, dan pembatalan reservasi milik sendiri (batas waktu) | ✗ Belum | Alur pengguna belum ada; BR-8 (batal min 1 jam sebelum mulai, hanya milik sendiri) belum diterapkan di UI/endpoint. |
| 6 | Dashboard antrian petugas (reservasi + laporan) | ✔ Selesai | `Officer\DashboardController`: ringkasan reservasi pending + laporan `baru`/`diproses` + fasilitas perbaikan + daftar antrian. Alur laporan petugas tersedia (`/petugas/laporan`, `Officer\ReportController`) — merge `14541ff`. |
| 7 | Approve/reject/cancel reservasi; anti-bentrok saat approve | ✔ Selesai | `Officer\ReservationController` + `ReservationService` (transaksi + `lockForUpdate`, 409 saat bentrok — BR-7), reject/cancel wajib alasan min 10 (BR-9), guard fasilitas non-aktif (BR-12). Detail + dialog konfirmasi di `petugas/reservasi/*`. |
| 8 | Laporan kerusakan (kategori, deskripsi, foto) + status laporan untuk pelapor | ✔ Selesai | `ReportController` (pengguna) + `StoreReportRequest` + `ReportService::createReport` (foto ke `storage/public/reports`) + view `laporan/{create,index,show}` (route `laporan.*`) — merge `14541ff` (Opank). |
| 9 | Transisi status laporan + catatan resolusi + riwayat | ✔ Selesai | `ReportService::transition` (state machine §9.2 + audit `report_updates`, BR-10) diformat ke UI: `Officer\ReportController@updateStatus` + `PATCH petugas.laporan.status` + view `petugas/laporan/{index,show}` — merge `14541ff`. Tes unit & fitur (BR-10) hijau. |
| 10 | Status fasilitas `perbaikan` ↔ `aktif` dari alur laporan | ✔ Selesai | `Officer\ReportController@toggleFacilityStatus` (BR-11): `PATCH petugas.laporan.fasilitas-status`→ `markFacilityForRepair`/`restoreFacilityToActive` — merge `14541ff`. |
| 11 | CRUD fasilitas (tambah/edit/nonaktifkan) | ✔ Selesai | `Admin\FacilityController` + `FacilityRequest`; destroy = soft-disable via `nonaktifkan`/`aktifkan`; foto upload ke `storage/public/facilities`; preview gambar di `app.js`. |
| 12 | Rekap okupansi & frekuensi kerusakan + ekspor CSV & PDF | ✗ Belum | `RecapService`, `Admin\RecapController`, view `admin/rekap/*` belum dibuat. Ditunda ke milestone v1. |
| 13 | Validasi server & client pada semua form penting | ◐ Sebagian | Server ✔ (FormRequest + Rule pada semua form yang ada). Client sebagian: atribut HTML5, dialog konfirmasi, preview gambar fasilitas; `resources/js/validation.js` (mirror frontend) belum lengkap untuk form reservasi/laporan yang belum ada. |
| 14 | Seeder akun demo berjalan (`php artisan migrate:fresh --seed`) | ✔ Selesai | `UserSeeder`, `FacilitySeeder` (5 fasilitas sesuai §15), `ReservationSeeder`, `ReportSeeder`; akun demo §5.3. |
| 15 | README berisi setup + informasi login | ✔ Selesai | README.md diperbarui 2026-09-09 (setup, arsitektur, akun demo, struktur folder, daftar snapshot). |

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
| BR-8 | Cancel pengguna (milik sendiri, min 1 jam sebelum mulai) | ✗ | Belum ada alur pengguna |
| BR-9 | Petugas cancel/reject wajib alasan min 10 | ✔ | `RejectReservationRequest`, `CancelReservationOfficerRequest` |
| BR-10 | Transisi laporan + `resolution_note` + audit | ✔ | `ReportService::transition` + `Officer\ReportController@updateStatus` + tes (BR-10) |
| BR-11 | Fasilitas `perbaikan` ↔ `aktif` dari alur laporan | ✔ | `Officer\ReportController@toggleFacilityStatus` → `markFacilityForRepair`/`restoreFacilityToActive` |
| BR-12 | Fasilitas non-aktif tak dapat direservasi/di-approve | ✔ | `FacilityBookable` + guard approve + halaman fasilitas publik menampilkan `inactive` |
| BR-13 | Publik lihat fasilitas tanpa data pemohon | ✔ | Landing page & halaman `/fasilitas` hanya merender status slot (BR-13) |
| BR-14 | Akun registrasi `pending` → login ditolak; akun admin langsung `aktif` | ✔ | `AccountStatusGate` + `EnsureAccountActive` |
| BR-15 | Petugas tidak registrasi mandiri | ✔ | Registrasi dibatasi role `pengguna` |
| BR-16 | Reservasi approved di fasilitas `perbaikan` dibatalkan petugas | ◐ | Aksi cancel petugas tersedia; otomasi/alur penandaan selesai via laporan perbaikan (petugas dapat toggle fasilitas & membatalkan reservasi) |

## 3. Yang Sudah Ada (ringkasan artefak commit `14541ff`)

- **41 route** (lihat `php artisan route:list`): home/landing publik, fasilitas publik (katalog/detail/jadwal), auth custom, laporan pengguna & petugas, admin (dashboard, akun, verifikasi, fasilitas CRUD), petugas (dashboard, antrian reservasi, laporan).
- **120 tes Pest** lulus (439 assertions): auth, gate akun, verifikasi, akun admin/petugas, antrian reservasi petugas, CRUD fasilitas, dashboard admin & petugas, landing page, halaman fasilitas publik (katalog/detail/jadwal, BR-13), laporan pengguna & petugas (BR-10, BR-11), aturan slot/overlap (unit).
- Validasi server semua form lewat FormRequest + Rule; flash `success`/`error` konsisten.
- Deploy: `Dockerfile`, `docker-compose.yml` (frontend dibangun di container via `docker compose run --rm frontend` → `npm ci && npm run build`), `deploy.yml` (GitHub Actions → VPS saat push ke `dev`; server pull-only via `git fetch` + `git reset --hard origin/dev`, host tidak pernah menjalankan `npm install`/`npm run build`).
- Riwayat lengkap seluruh commit tim: [CHANGELOG.md](../CHANGELOG.md).

## 4. Roadmap — Yang Belum (menunggu milestone anggota tim)

| Area | Pembagian tugas | Deliverable | Status branch |
|---|---|---|---|
| Alur reservasi pengguna (form, slot picker, riwayat, batal/BR-8) | rofad | `/reservasi/*` + wire `slot-picker` | ✗ belum ada branch |
| Rekap & ekspor CSV/PDF | (jadwal v1) | `Admin\RecapController` + `RecapService` | ✗ belum ada branch |

---

*Terakhir diperbarui: 2026-09-10 · Komit referensi `14541ff` (dev) · Status branch: `feature/officer-report` & `feat/facility-system` sudah di-merge.*