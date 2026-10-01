# Changelog

All notable release changes are recorded here. Commit and pull request details are available in the GitHub history.

## v1.3.0 — Pending release

### Added

- Automatically expire pending reservations that pass the approval cutoff.
- Automatically reject overlapping pending reservations after approval and notify their owners in-app.
- Add officer queue filters, sorting, and AJAX actions.
- Add live facility search and public schedule previews to the landing page.
- Show the nearest ten approved reservations requiring review when a facility enters repair, with access to the paginated queue.
- Add REKSA favicons across public, authentication, and authenticated pages.

### Changed

- Redesign public, authentication, user, officer, and admin pages with consistent REKSA clay cards, buttons, status badges, and pagination.
- Improve mobile navigation proportions, section tracking, responsive content widths, and scroll transitions.
- Use WebP illustrations and resolve background images through Vite for production builds.
- Preserve reservation form selections after validation errors and use the shared booking date window.
- Consolidate account management, role access checks, facility labels, and recap export columns.
- Bound dashboard and repair previews in SQL, remove unused recap caching, and batch notification read updates.
- Keep CSV and PDF exports required; XLSX is optional and is not included in this release.

### Fixed

- Align user cancellation checks at the inclusive one-hour cutoff, validate reasons inside services, and record the decision time.
- Allow adjacent reservation intervals, enforce the 365-day booking limit, and recheck the approval cutoff after locking.
- Scope expired reservation cleanup to the current user on user-facing pages.
- Restore facilities only when the rejected repair report owns the repair link.
- Scope the repair reservation panel to its facility and report, cap its preview, and keep reservation reads outside the status transaction.
- Reject malformed account fields and invalid phone numbers without HTTP 500 responses; share a valid HTML phone pattern across account forms.
- Sanitize text before validating its length and enforce the twenty-report daily quota atomically.
- Preserve the existing facility photo when replacement storage fails.
- Correct recap status grouping, active-facility occupancy averages, date ranges, category labels, and export filenames.
- Use semicolon-separated CSV, neutralize spreadsheet formulas, escape exported HTML, and disable PDF remote resources and JavaScript.
- Bound public location filters to fifty options, use absolute facility photo URLs, and limit descriptions to thirty words.
- Keep facility card heights stable, reuse server-rendered search cards, and hide empty pagination panels.
- Route public dashboard links to the current role's home page and require active accounts for notification actions.
- Align demo reservation dates and officer attribution, include a private report photo, and isolate photo storage in tests.

## v1.2.3 — 2026-09-23

- Pusatkan keputusan ketersediaan reservasi pada `ReservationAvailability`.
- Gunakan format waktu `H:i` secara konsisten.
- Hapus konversi WebP dan simpan upload dalam format aslinya.

## v1.2.2 — 2026-09-18

- Tambahkan rekap okupansi dan kerusakan dengan ekspor CSV/PDF.
- Terapkan lead time reservasi minimum 60 menit.

## v1.2.1 — 2026-09-18

- Tingkatkan aksesibilitas, navigasi keyboard, dialog, dan tampilan loading.
- Perbaiki beberapa masalah UX pada alur petugas dan laporan.

## v1.2.0 — 2026-09-16

- Tambahkan pembatalan reservasi pengguna dan isolasi akses berbasis role.
- Tambahkan pembuatan multi-admin dan riwayat verifikasi akun.

## v.1.1.0 — 2026-09-12

- Tambahkan alur laporan kerusakan pengguna dan petugas.
- Tambahkan halaman fasilitas publik.

## v1.0.0 — 2026-09-09

- Rilis stabil pertama untuk autentikasi, akun, fasilitas, dan reservasi.
