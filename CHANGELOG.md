# Changelog — Sistem Reservasi & Pelaporan Fasilitas Kampus

Format mengikuti riwayat commit tim. Tanggal terbaru di atas. Status merge: **Dev** = sudah masuk `dev`; **Branch** = masih di branch kerja anggota tim (belum di-merge ke `dev`).

> Patokan status terakhir: `v1.2.2` = `ebc4967` (2026-09-18, merge PR #56 — v1.2.2 release; baseline sebelumnya `dc7e1a4` v1.2.1). Kandidat terbaru: `v1.2.3` (2026-09-23, branch `refactor/reservation-availability`).

---

## [Unreleased] — Sedang dikerjakan di branch anggota tim

### fix/recap-count-session-logout — Guard count() Recap, Back-button Logout, & Halaman 500 Ramah (fix/recap-count-session-logout)

- **Recap cache tahan korupsi**: `RecapService` tidak lagi memakai `Cache::remember` mentah — cache divalidasi (`isValidRecap()`) dan otomatis dihitung ulang bila bukan struktur array murni (mis. `__PHP_Incomplete_Class` sisa serialisasi lama yang membuat `count()` melempar TypeError), lalu disimpan ulang. Data rekap kini di-cache sebagai array biasa (`$data->all()`), bukan Collection.
- **Guard `count()`**: 6 panggilan `count($recap['data'])` di log `RecapController` diganti `facilitiesCount()` (memakai `is_countable`); log ekspor HTML `RecapService` ikut dijaga.
- **Tombol back setelah logout aman**: `AddSecurityHeaders` mengirim `Cache-Control: no-store, no-cache, must-revalidate, private` + `Pragma`/`Expires` untuk respons halaman user login — back browser memicu request baru sehingga sesi yang sudah dibuang berujung redirect 302 ke login (ritual `invalidate()` + `regenerateToken()` di `AccountStatusGate` tetap jadi pemilik tunggal).
- **Logout tangguh**: `LogoutController` membungkus ritual terminasi dalam try/catch + `Log::warning` — kegagalan teknis sesi tidak pernah menggagalkan redirect ke login (respons tetap 302).
- **Halaman 500 ramah**: view baru `resources/views/errors/500.blade.php` ("Maaf, ada kesalahan teknis", halaman mandiri tanpa Vite) + render callback di `bootstrap/app.php` yang menangkap semua exception tak terduga saat `APP_DEBUG=false` tanpa membocorkan stack trace; HttpException (404/403/419) tetap memakai alur bawaan Laravel.
- **Test**: `AdminRecapTest` (4: render okupansi + cache array murni, heal cache rusak okupansi & damage, ekspor CSV), `ErrorHandlingTest` (2: 500 ramah tanpa trace + pesan exception tersembunyi, 404 tetap), `AuthFlowTest` +2 (back-button dashboard setelah logout → 302 login, logout ganda tanpa error), `SecurityHardeningTest` +1 (no-store hanya untuk respons terautentikasi, halaman publik tetap cacheable). Pest **228/230** (2 pre-existing GD); pint ✓.

### feat/reservation-overlap-auto-reject — Auto-reject Overlap + Notifikasi In-App (Dev — merge 9ee28d1, PR #80)

- **Overlap interval tertutup (BR-6)**: `Reservation::scopeOverlap` & `ReservationAvailability::hasApprovedOverlap` kini memakai `start_time <= end AND end_time >= start` — interval bersinggungan (09.00-11.00 vs 11.00-12.00) ikut dianggap bentrok di grid jadwal (landing/jadwal publik/form booking), penolakan saat submit, dan guard approve.
- **Pesan penolakan sesuai AC**: `overlapError()` dan conflict `create()` menjawab "Maaf, fasilitas ini sudah dipesan pada jam yang sama (atau overlap). Permohonan Anda ditolak." — tampil inline pada field fasilitas di form dan sebagai flash error.
- **Status baru `rejected_by_system`**: migrasi `add_rejected_by_system_status_to_reservations_table`; label **"Ditolak oleh Sistem"** di semua view pengguna & petugas (badge violet, terpisah dari `rejected` merah). `STATUSES`/`TABS['selesai']`/`STATUS_ORDER` petugas, filter opsi antrean, `reservasi/show` (label "Alasan Penolakan Otomatis oleh Sistem"), badge component, dan `getStatusBadge` di `app.js` ikut diperbarui.
- **Auto-reject saat approve**: `ReservationService::rejectOverlappingPendings()` berjalan di dalam transaksi `approve()` — seluruh `pending` pada fasilitas sama yang overlap (identik maupun bersinggungan) ditolak jadi `rejected_by_system` + `reject_reason` otomatis + `decided_at`; jumlahnya diakses controller lewat `autoRejectedOnApprove()`.
- **Fitur notifikasi baru (database channel)**: migrasi `create_notifications_table`, `App\Notifications\ReservationOverlapRejected` (sinkron di dalam transaksi, ikut ter-rollback bila approve gagal), **bell notifikasi** di `layouts/app.blade.php` (badge unread + panel 10 notifikasi terbaru + "Tandai semua dibaca"), route `notifications.read` & `notifications.read-all` (`NotificationController`).
- **Flash petugas**: approve sukses menyertakan "N reservasi lain otomatis ditolak karena overlap." bila ada yang ter-auto-reject (tanpa perubahan pesan saat tidak ada).
- **Test baru `ReservationOverlapRejectTest`** (11 test): adjacency saat submit dengan pesan AC persis, jarak 30 menit tetap diterima, auto-reject identik & bersinggungan, baris notifikasi DB + isinya, label per peran (pengguna vs petugas), flash jumlah untuk petugas, non-overlap & fasilitas lain tidak tersentuh + tanpa notifikasi, mark-read & read-all. **4 test lama disesuaikan** (kasus 4, BR-7 officer, grid landing, proyeksi jadwal). Pest **219/221** (2 pre-existing GD); `npm run build` ✓.

### feat/reservation-auto-expire — Auto-cancel Reservasi & Status "Gagal" (Dev — merge e3314dd, PR #79)

- **Status baru `cancelled_by_system`**: migrasi `add_cancelled_by_system_status_to_reservations_table` menambah nilai enum `cancelled_by_system` (label **"Gagal"** di sisi pengguna, **"Dibatalkan oleh Sistem"** di sisi petugas; badge violet).
- **`ReservationService::expireStale(?User $viewer)`**: menkonversi bulk reservasi `pending` dengan `start_time < now() + 60 menit` (lead time BR-3) menjadi `cancelled_by_system` + `cancel_reason` otomatis + `decided_at`. Mengembalikan jumlah kedaluwarsa milik viewer untuk flash.
- **Lazy check di semua akses**: dashboard & riwayat & detail pengguna (`DashboardController`, `ReservationController@index/@show`), dashboard & antrean & AJAX & detail petugas (`Officer\DashboardController`, `Officer\ReservationController@index/@ajaxIndex/@show`) — status terkini langsung tersimpan saat halaman dibuka.
- **Blokir keputusan lewat batas**: `ReservationService::approve()`/`reject()` menjalankan `expireStale()` lebih dulu sehingga reservasi lewat batas menjawab 409 (tidak bisa lagi disetujui/ditolak petugas). `create()` juga mengecek agar pending basi tidak memakan kuota pending. Jalur cancel manual (BR-8/BR-16) tidak diubah.
- **Flash `info`**: layout `layouts/app.blade.php` mendapat blok notifikasi `session('info')` (sky) — tampil saat ada reservasi yang otomatis dibatalkan, teks berbeda untuk pengguna ("N reservasi Anda...") dan petugas ("N reservasi...").
- **Label per peran**: view pengguna (`dashboard/index`, `reservasi/index`, `reservasi/show` termasuk blok "Alasan Pembatalan Otomatis oleh Sistem", `components/ui/badge`) menampilkan "Gagal"; view petugas (`petugas/reservasi/index` filter+badge, `petugas/reservasi/show`, `app.js getStatusBadge`) menampilkan "Dibatalkan oleh Sistem". `STATUSES`/`TABS['selesai']`/`STATUS_ORDER` officer ikut diperbarui.
- **Test**: `ReservationExpiryTest` (11 test: flip di dashboard/detail/riwayat pengguna, dashboard & halaman petugas, approve diblokir 409, approve dalam jendela aman tetap bisa, expiry saat `create()`, filter status + AJAX queue, reservasi jauh dari batas tidak terpengaruh).

### feat/dashboard-petugas-sorting-filter — Sorting, Tab, & Filter Dashboard Petugas (Dev — merge 74033fb, PR #78)

- **Sorting antrian reservasi (sesuai AC)**: `Officer\ReservationController` (`index` + `ajaxIndex`) mengurutkan `created_at` desc → `start_time` asc → prioritas status (pending → approved → rejected → cancelled_by_user → cancelled_by_officer). Konstanta `TABS`/`STATUS_ORDER` dipakai bersama oleh view dan endpoint AJAX.
- **Tab rekapitulasi reservasi**: Tab "Menunggu Approval" (`pending`) dan "Selesai" (`approved`/`rejected`/`cancelled_by_user`/`cancelled_by_officer`) di `petugas/reservasi/index.blade.php`. Query param `tab` didukung `index` + `ajaxIndex`; filter status menimpa tab; fetch AJAX meneruskan `tab` dari URL aktif.
- **Tab rekapitulasi laporan diperbaiki**: `menunggu` = baru/diproses, `selesai` = selesai/ditolak (sebelumnya memakai status `cancelled_by_*` yang tidak ada di enum laporan dan menaruh `ditolak` di tab menunggu). Default tanpa tab menampilkan semua laporan, diurutkan baru/diproses di atas lalu selesai/ditolak di bawah (`CASE status` + `latest()`). Kartu filter status asli tetap ada dan dikombinasikan dengan tab.
- **Sorting dashboard petugas**: Antrian reservasi pending diurutkan `created_at` desc → `start_time` asc; daftar laporan dashboard kini menampilkan semua status dengan urutan aktif (baru/diproses) di atas lalu tertutup (selesai/ditolak), badge status dinamis (baru/diproses/selesai/ditolak), variabel `newReports` → `queueReports`.
- **Kolom "Waktu Diajukan"**: Kolom `created_at` ditambahkan ke tabel antrian reservasi dan laporan petugas.
- **Dialog konfirmasi Setujui**: Tombol "Setujui" pada antrian reservasi kini membuka `<dialog>` konfirmasi (sebelumnya submit langsung), konsisten dengan Tolak/Batalkan. Dialog AJAX ditandai `data-ajax-dialog` dan dibersihkan sebelum tiap fetch.
- **Fix filter AJAX petugas**: URL fetch di `resources/js/app.js` sebelumnya memakai sintaks Blade `{{ route(...) }}` yang tidak diproses di file JS hasil bundling Vite (URL literal rusak) — diganti path statis `/petugas/reservasi/data`. Token CSRF kini diambil dari `<meta name="csrf-token">` yang ditambahkan ke `layouts/app.blade.php`. Empty-state `colspan` disesuaikan jadi 6.
- **Test**: 10 test (sorting reservasi 3 level, tab reservasi menunggu/selesai/status-menimpa-tab, kolom Waktu Diajukan + dialog konfirmasi, tab laporan menunggu/selesai, urutan laporan aktif-di-atas, urutan laporan dashboard, kolom Waktu Diajukan laporan).

### feat/live-search-filter-dashboard — Live Search Filter di Dashboard Petugas (feat/live-search-filter-dashboard)

- **Filter AJAX live search**: Menghapus `method="GET"` dari form filter di halaman antrian reservasi petugas (`resources/views/petugas/reservasi/index.blade.php`). Pencarian otomatis berjalan saat input berubah dengan debounce ±300ms tanpa reload halaman.
- **Tombol Reset Filter**: Tombol "Filter" diganti "Reset Filter" yang mengosongkan semua input dan menampilkan data default.
- **Loading indicator**: Menampilkan animasi loading saat fetch data berlangsung.
- **AJAX endpoint**: `GET /petugas/reservasi/data` → `OfficerReservationController@ajaxIndex` mengembalikan JSON dengan data reservasi terfilter.
- **Kombinasi filter**: Status (`status`) dan Tanggal (`date`) menyaring hasil dengan benar.
- **JavaScript**: Menambahkan `fetch()` dengan debounce di `resources/js/app.js` untuk update tabel secara dinamis tanpa reload.

### feat/live-search-filter-dashboard — Live Search Filter Landing Page + Schedule AJAX (feat/live-search-filter-dashboard)
- **Landing page AJAX live search**: Mengganti form filter `method="GET"` dengan AJAX live search pada `resources/views/landing/index.blade.php`. Fasilitas kartu diperbarui secara dinamis saat input pencarian, jenis, lokasi, atau kapasitas berubah (debounce ±300ms) tanpa reload halaman.
- **Reset Filter**: Tombol "Terapkan Filter" diganti "Reset Filter" yang mengosongkan semua input dan mengembalikan data default.
- **Loading indicator**: Menampilkan animasi loading `#landing-loading` saat fetch data berlangsung.
- **AJAX endpoint**: `GET /home/facilities` → `HomeController@ajaxFacilities` mengembalikan JSON dengan data fasilitas terfilter dan grid ketersediaan. Mendukung parameter `facility_id` dan `date` untuk schedule preview update.
- **Container grid fixed height**: Grid kartu fasilitas menggunakan `max-h-[500px] overflow-y-auto` untuk mencegah layout shift.
- **Schedule date picker**: Menambahkan input `type="date"` di section "Pratinjau Ketersediaan Jadwal" untuk memilih tanggal berbeda. Container fixed height pada grid jadwal.
- **JavaScript**: Menambahkan `fetch()` dengan debounce di `resources/js/app.js` untuk update grid fasilitas dan jadwal secara dinamis.

### feat/landing-page-features — Grid Fasilitas Maksimal 9 + Live Search AJAX + View All + Pratinjau Jadwal Tanggal (feat/landing-page-features)
- **Grid kartu fasilitas maksimal 9**: Menampilkan maksimal 9 kartu (3 baris × 3 kolom). Jika hasil > 9, muncul link "Lihat Semua (N)" yang mengarah ke halaman `/fasilitas` menampilkan seluruh fasilitas. Penampungan hasil pada grid konsisten dengan hasil filter live.
- **Container grid fixed height**: Grid kartu fasilitas ditempatkan di dalam container dengan `max-h-[500px] overflow-y-auto` untuk mencegah layout shift saat data sedikit atau kosong. State kosong menampilkan di dalam container tersebut.
- **Section "Pratinjau Ketersediaan Jadwal"**: Menambahkan input tanggal untuk memilih tanggal berbeda (default: hari ini). Judul section tanpa "(Hari Ini: ...)". Menggunakan AJAX untuk mengambil grid ketersediaan per tanggal lewat `ReservationAvailability::publicScheduleSlots`. Tab/kartu fasilitas menggunakan container terpisah dengan ukuran fixed agar result kosong tidak menggeser komponen di bawahnya. Ketika jumlah fasilitas sangat banyak, tampilkan dalam carousel.
- **HomeController**: Ditambah metode `ajaxFacilities()` untuk endpoint live search AJAX. Fitur filter quantity limit 9 fasilitas untuk grid.

### feat/image-webp-conversion — Media WebP (Dev — merge `b21af99`, PR #60)
- ~~**Dependencies**: `intervention/image ^4.0`~~
- ~~**Media**: foto fasilitas & laporan dikonversi otomatis ke WebP (kualitas 80, maks 1920px) via `Image::fromUpload(...)->toWebp()`~~
- **Status**: Fitur WebP conversion dihapus. Upload kini menyimpan file asli tanpa konversi. `intervention/image` tetap ada sebagai dependency untuk backward compatibility.

### refactor/reservation-availability — Keputusan Ketersediaan Slot di Satu Modul (`60cde7e`, belum di-merge ke `dev`)

- **Modul baru `ReservationAvailability`** (BR-1..BR-6, BR-12): konstanta 07:00–20:00 / 30 menit / 26 slot / maks 8 slot (240 menit) / lead time 60 menit / format `H:i`; predikat `isFacilityBookable`, `leadTimeCutoff`/`isWithinLeadTime`, `pendingQuotaError`, `hasApprovedOverlap`/`hasBlockingOverlap`; query `approvedForDay`; proyeksi `publicScheduleSlots` (publik, tanpa lead time) & `bookingSlots` (form); `dayStart`/`dayEnd`/`slotsForDay`/`timeOptions`
- **Adapters**: `HomeController` (landing → `bookingSlots`), `FacilityController` (jadwal publik → `publicScheduleSlots`), `ReservationController` (`timeOptions` + `maxDurationSlots` ke view), `reservation-form.js` memakai `data-max-duration-slots`; konstanta/helper jam operasional duplikat di controller dihapus; format grid `H.i` → `H:i`; inline script di `reservasi/create` dipindah ke file JS
- **Internalize checks**: `ReservationService::create()` menjalankan cek slot/lead/kuota/overlap/kelayakan di dalam transaksi via modul (`assertSlotShape`, `assertAvailability`); `approve()` → `ConflictHttpException` saat `hasBlockingOverlap`; `App\Rules\*` (5 kelas) dihapus
- **Tests**: `ReservationSlotTest` (unit, interface modul), `ReservationSlotDepthTest` (fitur), `ReservationAvailabilityProjectionTest` (kesepakatan proyeksi + lead time hanya booking), update `ReservationApprovalTest` (`isValidSlot`) & `LandingPageTest` (`07:00 - 07:30`)
- **Docs**: spesifikasi §3/§7.1/§7.3/BR-3 (30→60 menit)/§14.1; CONTEXT.md; README; feature-checklist; changelog; snapshot 2026-09-23; release v1.2.3
- **Verifikasi**: 186 tes / 758 assertions hijau (2026-09-23, MySQL) · `pint --dirty` bersih · `npm run build` sukses

### feat/image-webp-conversion — Media WebP (Dev — merge `b21af99`, PR #60)
- ~~**Dependencies**: `intervention/image ^4.0`~~
- ~~**Media**: foto fasilitas & laporan dikonversi otomatis ke WebP (kualitas 80, maks 1920px) via `Image::fromUpload(...)->toWebp()`~~
- ~~**Tests**: upload tersimpan `.webp` < 500KB (`AdminFacilityTest`, `UserReportTest`)~~
- ⚠️ **Removed**: Fitur WebP conversion dihapus pada `feat/landing-page-features`. Upload kini menyimpan file asli tanpa konversi. Dependensi `intervention/image` tetap ada untuk backward compatibility.

---

## v1.2.2 — 2026-09-18 (Minor)

Rilis minor dari `v1.2.1` (`dc7e1a4`): rekap okupansi & frekuensi kerusakan dengan ekspor CSV/PDF (BR-12) serta lead time pemesanan minimum 1 jam (BR-3). Detail lengkap: [releases/v1.2.2.md](releases/v1.2.2.md).

### Rekap Okupansi & Frekuensi Kerusakan + Ekspor CSV/PDF (BR-12)
- **Service layer**: `RecapService` dengan `getOccupancyRecap()` (per fasilitas: total reservasi, jam disetujui, max jam operasional, tingkat okupansi) dan `getDamageRecap()` (per fasilitas & kategori: baru/diproses/selesai/ditolak)
- **Controller**: `Admin\RecapController` dengan halaman `occupancy` & `damage` + filter tanggal, ekspor CSV & PDF (via HTML untuk PDF)
- **Views**: `admin/rekap/occupancy.blade.php` & `admin/rekap/damage.blade.php` — tabel ringkasan, kartu metrik, tombol ekspor, navigasi admin
- **Routes**: `/admin/rekap/okupansi`, `/admin/rekap/kerusakan` + endpoint ekspor CSV/PDF
- **Navigation**: Link "Rekap Okupansi" & "Rekap Kerusakan" ditambahkan ke sidebar admin (desktop & mobile)

### Minimum 1 Hour Booking Lead Time (BR-3)
- **Backend validation**: `BookingLeadTime` rule diperbarui dari 30 menit ke 60 menit (1 jam)
- **Slot grid (create form)**: `ReservationController::determineSlotState()` menandai slot < 1 jam sebagai `inactive`
- **Public landing page**: `HomeController::slotState()` menandai slot < 1 jam sebagai `past`
- **Frontend validation**: Client-side JS di form reservasi create menonaktifkan kombinasi tanggal/waktu < 1 jam dari sekarang
- **UI copy updated**: Legend text "Tidak Aktif (< 1 Jam / Lewat)", help text "Waktu mulai minimal: 1 jam dari waktu saat ini"
- **Public facility pages**: "Batas Pengajuan" dari 30 menit → 1 jam di `fasilitas/show.blade.php`
- **Tests updated**: `ReservationSlotDepthTest`, `ReservationApprovalTest`, `LandingPageTest` expectations untuk ambang 60 menit

---

## v1.2.1 — 2026-09-18 (Minor)

Rilis minor dari `v1.2.0-dev`: perbaikan UI accessibility (a11y), komponen skeleton loading, lazy-load images, password toggle, perbaikan dialog & navigasi keyboard, serta perbaikan minor UX petugas & laporan. Detail lengkap: [releases/v1.2.1.md](releases/v1.2.1.md).

### Dev — penambahan fitur & perbaikan (PR #51 + perbaikan terkait)
- **UI Accessibility (a11y) & Polish** (PR #51 `feat/ui-accessibility` → `1e5097c`): skip-to-content link di layout publik & auth (`4cf42dc`); schedule tabs keyboard-navigable di landing (`1af05e9`); submit-button loading states + lazy image fade-in (`b62ad14`); reusable skeleton loading component (`54bace9`); lazy-load images below the fold (`e9c8af3`); center dialogs, labels, autofocus, reduced motion support (`352c217`); show/hide password toggle di login & register (`a9f40ba`); hapus referensi internal dari teks terlihat (`2144273`).
- **Petugas UX** (merge `df541a9`): item antrian laporan di dashboard petugas kini terhubung ke detail laporan.
- **Laporan pengguna** (merge `c4251be`): tambah aksi "Lapor kerusakan lain" di halaman detail laporan.
- **Admin facility form** (merge `32d0977`): konsistensi tinggi & padding input pada create/edit fasilitas admin.
- **Fasilitas publik minor** (merge `c4692a3`, `352c217`): perbaikan layout jadwal & show fasilitas publik.
- **Landing page facility count fix** (merge `dc7e1a4` PR #52): perbaikan hitungan total fasilitas di landing page agar konsisten dengan filter.

---

## [v1.2.0-dev] — Perkembangan `dev` sejak v1.1.0 (`e9c591a` → `e03df57`, 2026-09-15)

Fitur berikut sudah masuk `dev` tetapi belum dipromosikan ke `staging`/`prod`. Release notes: [releases/v1.2.0.md](releases/v1.2.0.md) · snapshot 2026-09-15 · suite terverifikasi **168 tes / 627 assertions hijau** (MySQL, 2026-09-16).

### Dev — penambahan fitur (PR #37–#45)
- **Dokumentasi sinkron v1.1.0** (PR #37 `docs/project-information` → `6ac099e`): baseline `dev` yang benar (`e9c591a`), snapshot 2026-09-12, perbaikan setup README (nama DB & seed demo).
- **Alur reservasi pengguna** (PR #39 `feat/user-reservation-create` → `509585a`): `/reservasi` (riwayat), `/reservasi/baru` (form + slot picker), `/reservasi/{reservation}` (detail), `POST /reservasi` (store) — `ReservationController` + views `reservasi/*` + `UserReservationCreateTest`; aturan BR-1..BR-6, BR-12 diterapkan saat pengajuan.
- **Pembatalan reservasi pengguna** (PR #38 `feat/reservation-cancellation` → `92309b9`): `DELETE /reservasi/{reservation}` — hanya milik sendiri, min 1 jam sebelum mulai, 403 untuk reservasi orang lain (BR-8) — `ReservationCancellationTest`.
- **Isolasi role & auth hardening** (PR #41 `feat/auth-admin-isolation` → `e4de698`): grup route dipisah tegas `role:pengguna` / `role:petugas` / `role:admin`; redirect dashboard per role pasca-login (`08fc860`); view operasional dibatasi petugas via `ReportPolicy`/`ReservationPolicy` (`14655e0`); kelola & buat akun admin (`AdminAccountController` + `AdminAccountRequest`); pulihkan akun ditolak (`/admin/pengguna/{user}/pulihkan`); tolak perubahan status fasilitas saat `perbaikan` (`3349980`); dashboard admin read-only agregat (`028d6e0`); registrasi di-throttle `10,1`; `identity` & `phone` wajib unik pada registrasi mandiri (`6b67630` + migration `add_unique_identity_phone_to_users_table`); invalidasi sesi saat role/status berubah (`307ec70` via `UserObserver`); `AccountAttributes` helper; tes `RoleIsolationTest` (22 tes).
- **Riwayat verifikasi akun** (PR #45 `feat/account-verification-history` → `e03df57`): `AccountVerificationAction` + tabel `account_verification_actions` + `AccountVerificationService` + kolom audit verifikasi di `users` (migration `add_verification_audit_to_users_table`) — `AccountVerificationHistoryTest`.
- **Perapian UI & branding** (PR #40 `fix/landing-nav-active-state` → `8c854d0`): state nav aktif sinkron dengan section terlihat, layout navigasi responsif bermerek, dashboard pengguna berisi ringkasan aktivitas (`bea6f78`), penyelarasan tema login/register/landing/fasilitas/laporan/reservasi/petugas/admin.
- **Docs spesifikasi** (PR #43 `docs/clarify-spec-after-auth-admin` → `0a2b6c8`): `docs/spesifikasi-sistem-reservasi.md` disinkronkan dengan isolasi role, audit verifikasi, dan BR-17 (multi-admin).

---

## v1.1.0 — 2026-09-12 (Minor)

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

- Rentang dok: `2026-08-30` → `2026-09-23`.
- Commit tim di luar dev yang belum terdokumentasi di release: lihat bagian [Unreleased].
- Snapshot detail per tanggal: `snapshots/`.