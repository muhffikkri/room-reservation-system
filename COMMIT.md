# COMMIT.md — Conventional Commits Guide

> Panduan standar penulisan commit untuk proyek **Sistem Reservasi & Pelaporan Fasilitas Kampus**.
>
> Tujuan utama dokumen ini adalah menjaga riwayat Git tetap **rapi, konsisten, mudah dibaca, mudah direview, dan mudah dilacak** oleh seluruh anggota tim.

---

# 1. Format Dasar

Gunakan format berikut:

```text
<type>(<scope>): <description>
```

Contoh:

```text
feat(auth): add user registration
fix(reservation): prevent overlapping approved bookings
docs(readme): add local setup instructions
```

`scope` bersifat opsional.

Format tanpa scope:

```text
<type>: <description>
```

Contoh:

```text
feat: add facility search
fix: correct reservation validation
```

---

# 2. Quick Examples

```text
feat: new feature
fix(scope): bug in scope
feat!: breaking change
feat(scope)!: rework API
chore(deps): update dependencies
```

Contoh yang lebih relevan dengan proyek:

```text
feat(auth): add login and logout flow
feat(facility): add facility search filters
feat(reservation): add 30-minute slot picker
feat(report): add damage report submission
fix(reservation): prevent duplicate approved time slots
fix(auth): reject login for pending accounts
docs: add project installation guide
test(reservation): add overlap validation tests
refactor(report): move status logic to report service
chore(deps): update Laravel dependencies
```

---

# 3. Struktur Commit

Format lengkap Conventional Commit:

```text
<type>[optional scope][optional !]: <description>

[optional body]

[optional footer]
```

Contoh:

```text
feat(reservation): add cancellation deadline validation

Prevent users from cancelling reservations less than one hour
before the scheduled start time.

Refs: BR-8
```

---

# 4. Commit Types

## `build`

Perubahan yang memengaruhi **build system** atau dependency eksternal.

Contoh scope:
- npm
- vite
- composer
- tailwind

Contoh:

```text
build(vite): update asset build configuration
build(composer): add dompdf dependency
build(tailwind): configure content paths
```

Gunakan ketika perubahan berkaitan dengan:
- `package.json`
- `composer.json`
- Vite
- Tailwind build
- dependency yang memengaruhi proses build

---

## `ci`

Perubahan pada konfigurasi **Continuous Integration**.

Contoh:

```text
ci(github): add Laravel test workflow
ci(github): run tests on pull requests
```

Contoh area:
- GitHub Actions
- GitLab CI
- automated testing
- automated build

---

## `chore`

Perubahan pendukung yang **tidak mengubah fitur utama maupun test secara langsung**.

Contoh:

```text
chore: clean unused files
chore(deps): update dependencies
chore(git): update gitignore
chore(config): adjust local development settings
```

Gunakan untuk:
- housekeeping
- konfigurasi tooling
- dependency maintenance
- file pendukung
- cleanup repository

---

## `docs`

Perubahan **dokumentasi saja**.

Contoh:

```text
docs: add installation instructions
docs(readme): add demo account information
docs(design): update button guidelines
docs(commit): add conventional commit guide
```

Gunakan untuk:
- README
- DESIGN.md
- COMMIT.md
- dokumentasi API
- komentar dokumentatif penting
- panduan setup

---

## `feat`

Penambahan **fitur baru**.

Contoh:

```text
feat(auth): add user registration
feat(auth): add admin account verification
feat(facility): add facility listing
feat(facility): add facility search filters
feat(reservation): add reservation submission
feat(reservation): add reservation cancellation
feat(report): add damage report submission
feat(report): add report status timeline
feat(admin): add facility management
feat(admin): add user verification page
feat(officer): add reservation approval queue
feat(recap): add occupancy recap
feat(export): add CSV export
```

Gunakan `feat` jika pengguna memperoleh kemampuan baru.

---

## `fix`

Perbaikan **bug** atau perilaku yang tidak sesuai.

Contoh:

```text
fix(auth): prevent pending users from logging in
fix(reservation): prevent approved booking overlap
fix(reservation): correct maximum duration validation
fix(report): require resolution note on completed reports
fix(facility): disable reservation for inactive facilities
```

Gunakan ketika memperbaiki perilaku yang sebelumnya salah.

---

## `perf`

Perubahan yang meningkatkan **performance** tanpa mengubah fungsi utama.

Contoh:

```text
perf(facility): optimize facility search query
perf(reservation): reduce schedule query count
perf(recap): optimize occupancy aggregation
```

Gunakan jika fokus perubahan adalah:
- query lebih cepat
- response lebih cepat
- penggunaan memory lebih efisien
- pengurangan query database

---

## `refactor`

Perubahan struktur kode yang **tidak menambah fitur dan tidak memperbaiki bug secara langsung**.

Contoh:

```text
refactor(reservation): move booking logic to service
refactor(report): extract status transition service
refactor(auth): simplify login validation flow
refactor(admin): split account controllers
```

Gunakan ketika:
- memindahkan logic
- memecah class
- merapikan arsitektur
- meningkatkan maintainability

---

## `revert`

Membatalkan perubahan commit sebelumnya.

Contoh:

```text
revert: revert facility status workflow
```

Jika memungkinkan:

```text
revert: revert "feat(report): auto activate repaired facility"
```

---

## `style`

Perubahan yang **tidak mengubah arti atau perilaku kode**.

Contoh:

```text
style(blade): format reservation table markup
style(css): normalize component spacing
style: fix code indentation
```

Contoh perubahan:
- whitespace
- indentasi
- formatting
- urutan import
- missing semicolon
- formatting Blade/PHP

Jangan gunakan `style` untuk perubahan UI baru.

Jika desain UI berubah secara fungsional atau terlihat sebagai fitur baru, gunakan:

```text
feat(ui): ...
```

atau jika hanya memperbaiki visual yang salah:

```text
fix(ui): ...
```

---

## `test`

Menambah atau memperbaiki test.

Contoh:

```text
test(auth): add pending account login test
test(reservation): add slot overlap test
test(report): add status transition test
test(facility): add search filter test
```

Gunakan untuk:
- unit test
- feature test
- integration test

---

# 5. Recommended Scopes

Gunakan scope yang jelas dan singkat.

## Core scopes

```text
auth
dashboard
facility
reservation
report
recap
export
user
officer
admin
ui
database
```

## Technical scopes

```text
routes
middleware
policy
service
validation
migration
seeder
blade
tailwind
vite
composer
npm
config
tests
```

## Documentation scopes

```text
readme
design
commit
docs
```

---

# 6. Scope Guidelines

Scope harus menunjukkan **bagian utama yang berubah**, bukan nama file secara sembarang.

Baik:

```text
feat(reservation): add slot picker
fix(auth): correct account status validation
refactor(report): extract report service
```

Kurang baik:

```text
feat(controller): add feature
fix(file): fix bug
update(code): update code
```

Scope harus membantu reviewer langsung memahami area perubahan.

---

# 7. Description Rules

Description adalah ringkasan singkat setelah `:`.

Gunakan:

```text
feat(auth): add user registration
```

Bukan:

```text
feat(auth): Added user registration.
```

## Aturan description

- gunakan huruf kecil di awal
- singkat dan spesifik
- gunakan kata kerja aktif
- tidak perlu titik di akhir
- idealnya maksimal sekitar 72 karakter
- jelaskan **apa yang berubah**, bukan proses pengerjaannya

Baik:

```text
feat(reservation): add cancellation validation
```

Kurang baik:

```text
feat(reservation): work on reservation
```

Kurang baik:

```text
fix: fix bug
```

Lebih baik:

```text
fix(reservation): prevent booking inactive facilities
```

---

# 8. Imperative Style

Gunakan gaya kata kerja seperti instruksi.

Recommended:

```text
add
update
remove
prevent
allow
create
refactor
optimize
validate
handle
display
restrict
replace
```

Contoh:

```text
feat(report): add image preview
fix(auth): prevent rejected account login
refactor(facility): extract search scope
```

---

# 9. Breaking Changes

Gunakan tanda `!` jika perubahan menyebabkan **breaking change**.

Format:

```text
feat!: change authentication flow
```

atau:

```text
feat(auth)!: replace account verification flow
```

Contoh:

```text
feat(reservation)!: replace hourly booking with fixed slot system
```

Breaking change juga dapat dijelaskan di footer:

```text
feat(reservation)!: change reservation time model

Replace free-form reservation time with fixed 30-minute slots.

BREAKING CHANGE: existing reservation time inputs are no longer supported.
```

---

# 10. Commit Body

Body digunakan jika description saja belum cukup menjelaskan perubahan.

Format:

```text
feat(reservation): add booking conflict validation

Check approved reservations before allowing a new booking.
Pending reservations can still coexist until officer approval.
```

Body sebaiknya menjelaskan:
- apa yang berubah
- mengapa perubahan diperlukan
- aturan bisnis penting
- dampak perubahan

Jangan menjadikan body sebagai catatan harian pengerjaan.

---

# 11. Commit Footer

Footer dapat digunakan untuk:
- issue
- ticket
- business rule
- breaking change
- referensi task

Contoh:

```text
Refs: BR-7
```

```text
Refs: #24
```

```text
Closes: #18
```

```text
BREAKING CHANGE: old reservation endpoint has been removed.
```

---

# 12. Business Rule References

Karena proyek memiliki Business Rules, referensi BR dapat dicantumkan ketika relevan.

Contoh:

```text
feat(reservation): enforce maximum booking duration

Refs: BR-2
```

```text
fix(reservation): recheck conflicts during approval

Refs: BR-7
```

```text
feat(report): enforce report status transition

Refs: BR-10
```

Hal ini membantu menghubungkan implementasi dengan spesifikasi proyek.

---

# 13. One Commit, One Purpose

Setiap commit sebaiknya memiliki **satu tujuan utama**.

Baik:

```text
feat(auth): add login throttling
```

Lalu commit berbeda:

```text
docs(readme): document demo accounts
```

Hindari:

```text
feat: add login, fix dashboard, update readme, change colors
```

Commit kecil dan fokus lebih mudah:
- direview
- direvert
- dilacak
- digabung
- dipahami anggota tim lain

---

# 14. Examples by Project Module

## Authentication

```text
feat(auth): add user registration
feat(auth): add login throttling
feat(auth): add account status validation
fix(auth): prevent pending account login
fix(auth): prevent rejected account login
refactor(auth): move active account check to middleware
test(auth): add account verification tests
```

---

## Facility

```text
feat(facility): add facility listing
feat(facility): add type and location filters
feat(facility): add availability status
feat(facility): add facility photo upload
fix(facility): prevent inactive facility reservation
refactor(facility): extract search query scope
test(facility): add facility filter tests
```

---

## Reservation

```text
feat(reservation): add reservation form
feat(reservation): add 30-minute slot picker
feat(reservation): add reservation history
feat(reservation): add user cancellation
feat(reservation): add officer approval flow
fix(reservation): prevent approved slot overlap
fix(reservation): enforce four-hour maximum duration
fix(reservation): enforce cancellation deadline
perf(reservation): optimize availability query
test(reservation): add overlapping booking tests
```

---

## Reports

```text
feat(report): add damage report form
feat(report): add photo upload
feat(report): add report status timeline
feat(report): add resolution notes
fix(report): require notes when closing reports
refactor(report): extract status transition service
test(report): add report workflow tests
```

---

## Admin

```text
feat(admin): add user verification
feat(admin): add officer account management
feat(admin): add facility management
feat(admin): add recap dashboard
fix(admin): prevent officer self-registration
```

---

## Petugas / Officer

```text
feat(officer): add reservation queue
feat(officer): add report queue
feat(officer): add facility repair action
fix(officer): require reason when cancelling reservation
```

---

## UI

```text
feat(ui): add dashboard sidebar
feat(ui): add reservation status badges
feat(ui): add responsive facility cards
feat(ui): add reusable modal component
fix(ui): correct mobile table overflow
style(blade): format dashboard templates
```

---

## Database

```text
feat(database): add reservations table
feat(database): add report updates audit table
fix(migration): correct reservation status enum
chore(seeder): add demo facilities
chore(seeder): add demo accounts
```

---

## Documentation

```text
docs(readme): add installation guide
docs(readme): add demo login credentials
docs(design): add UI color tokens
docs(commit): add conventional commit rules
```

---

# 15. Good Commit Examples

```text
feat(auth): add registration for regular users
```

```text
feat(admin): add pending account verification
```

```text
fix(reservation): prevent approval of overlapping bookings
```

```text
feat(report): add resolution status timeline
```

```text
refactor(reservation): move overlap logic to service
```

```text
test(reservation): cover concurrent approval conflict
```

```text
docs(readme): document local development setup
```

```text
chore(deps): update Laravel dependencies
```

---

# 16. Bad Commit Examples

Avoid:

```text
update
```

```text
fix
```

```text
changes
```

```text
final
```

```text
final fix
```

```text
final fix 2
```

```text
latest
```

```text
backup
```

```text
progress
```

```text
code update
```

```text
fix bug
```

```text
update project
```

Masalahnya adalah pesan tersebut tidak menjelaskan perubahan secara jelas.

---

# 17. Before Committing

Sebelum menjalankan commit:

```bash
git status
```

Periksa perubahan:

```bash
git diff
```

Tambahkan file yang relevan:

```bash
git add <file>
```

atau:

```bash
git add .
```

Kemudian commit:

```bash
git commit -m "feat(reservation): add slot picker"
```

---

# 18. Commit with Body

Untuk perubahan yang membutuhkan penjelasan:

```bash
git commit
```

Kemudian tulis:

```text
fix(reservation): prevent concurrent booking conflicts

Recheck approved reservations inside a database transaction
before an officer approves a pending reservation.

Refs: BR-7
```

---

# 19. Commit Message Checklist

Sebelum commit, pastikan:

- [ ] Type benar.
- [ ] Scope relevan.
- [ ] Description singkat dan jelas.
- [ ] Description menjelaskan perubahan nyata.
- [ ] Satu commit memiliki satu tujuan utama.
- [ ] Tidak menggunakan pesan seperti `update`, `fix`, atau `final`.
- [ ] Tidak menyertakan file yang tidak berhubungan.
- [ ] Test terkait sudah dijalankan jika diperlukan.
- [ ] Tidak ada debug code.
- [ ] Tidak ada password, secret, API key, atau credential sensitif.
- [ ] Breaking change menggunakan `!`.
- [ ] Business Rule dicantumkan jika perubahan terkait aturan penting.

---

# 20. Reminders

## DO

Gunakan commit seperti:

```text
feat(reservation): add slot availability grid
fix(auth): reject pending user login
refactor(report): move workflow logic to service
test(reservation): add cancellation deadline test
docs(readme): add setup instructions
```

## DON'T

Jangan gunakan:

```text
update
changes
fix
final
done
progress
test
backup
```

---

# 21. Important Reminders for Team Members

1. **Commit secara rutin.**  
   Jangan menunggu seluruh modul selesai baru melakukan satu commit besar.

2. **Commit hanya pekerjaan yang sudah masuk akal sebagai satu unit perubahan.**

3. **Jangan commit file sensitif.**

   Contoh:

   ```text
   .env
   private keys
   passwords
   API credentials
   database credentials
   ```

4. **Jangan commit dependency directory jika sudah di-ignore.**

   Contoh:

   ```text
   /vendor
   /node_modules
   ```

5. **Pastikan aplikasi masih dapat dijalankan setelah commit.**

6. **Jangan mencampurkan refactor besar dengan fitur baru jika dapat dipisahkan.**

7. **Jangan melakukan formatting seluruh project bersamaan dengan bug fix kecil.**

8. **Gunakan scope yang konsisten antar anggota tim.**

9. **Gunakan bahasa Inggris untuk commit message agar format repository konsisten.**

10. **Commit message harus menjelaskan perubahan, bukan siapa yang mengerjakannya.**

---

# 22. Suggested Team Commit Convention

Untuk proyek ini, format utama yang direkomendasikan adalah:

```text
type(scope): description
```

Contoh:

```text
feat(auth): add user registration
feat(facility): add availability filters
feat(reservation): add booking submission
feat(report): add damage report form
feat(officer): add reservation approval queue
feat(admin): add user verification
fix(reservation): prevent approved slot conflict
test(report): add report transition tests
docs(readme): add project setup guide
```

---

# 23. Recommended Scope Dictionary

Agar semua anggota tim konsisten, gunakan daftar berikut terlebih dahulu sebelum membuat scope baru.

| Scope | Area |
|---|---|
| `auth` | Login, register, logout, account access |
| `facility` | Fasilitas dan ketersediaan |
| `reservation` | Reservasi |
| `report` | Laporan kerusakan |
| `officer` | Workflow petugas |
| `admin` | Fitur admin |
| `dashboard` | Dashboard umum |
| `recap` | Rekap data |
| `export` | CSV/PDF |
| `ui` | Komponen dan perilaku UI |
| `blade` | Blade templates |
| `validation` | Server/client validation |
| `middleware` | Middleware |
| `policy` | Authorization policy |
| `service` | Business service |
| `database` | Database umum |
| `migration` | Migration |
| `seeder` | Seeder |
| `routes` | Routing |
| `tests` | Test infrastructure |
| `config` | Configuration |
| `deps` | Dependencies |
| `readme` | README |
| `design` | DESIGN.md |
| `commit` | COMMIT.md |

Jangan membuat banyak variasi untuk area yang sama, misalnya:

```text
reservation
reservations
booking
bookings
```

Pilih satu:

```text
reservation
```

---

# 24. Recommended Commit Workflow

Contoh ketika membuat fitur reservasi:

```text
feat(database): add reservations table
feat(reservation): add reservation model
feat(reservation): add reservation form request
feat(reservation): add booking service
feat(reservation): add reservation submission
feat(ui): add reservation slot picker
test(reservation): add reservation creation tests
docs(readme): document reservation rules
```

Dengan pola tersebut, history repository menunjukkan perkembangan fitur secara jelas.

---

# 25. Final Convention

Format standar:

```text
type(scope): description
```

Breaking change:

```text
type(scope)!: description
```

Dengan body:

```text
type(scope): description

Explanation of the change.
```

Dengan footer:

```text
type(scope): description

Explanation of the change.

Refs: BR-X
```

Contoh final:

```text
fix(reservation): prevent overlapping approval

Recheck approved reservations inside a database transaction
before committing an officer approval.

Refs: BR-7
```

---

## Summary

Gunakan Conventional Commits untuk membuat Git history yang:

- konsisten,
- mudah direview,
- mudah dicari,
- mudah direvert,
- mudah dipahami seluruh anggota tim,
- dan mencerminkan perkembangan fitur secara sistematis.

**Default pattern:**

```text
feat(scope): description
fix(scope): description
docs(scope): description
refactor(scope): description
test(scope): description
chore(scope): description
```
