# DESIGN SYSTEM — Sistem Reservasi & Pelaporan Fasilitas Kampus

> **Dokumen:** `design.md`  
> **Tujuan:** Menjadi acuan tunggal tampilan UI sebelum development Laravel Blade + Tailwind CSS.  
> **Arah visual:** Bersih, modern, profesional, akademik, mudah dipahami, dan konsisten.  
> **Font utama:** **Figtree**  
> **Target:** Desktop-first untuk dashboard operasional, tetap responsive dan nyaman di mobile.

---

## 1. Design Principles

Design system menggunakan prinsip berikut:

1. **Clarity over decoration**  
   Setiap elemen harus membantu pengguna memahami informasi, status, dan aksi berikutnya.

2. **Clean & modern**  
   Menggunakan latar terang, whitespace cukup, border halus, shadow minimal, serta warna biru tua sebagai identitas utama.

3. **Status must be obvious**  
   Status reservasi, laporan, fasilitas, dan akun tidak boleh hanya dibedakan melalui teks. Gunakan kombinasi warna, label, dan ikon.

4. **Consistent interaction**  
   Tombol, form, badge, modal, tabel, flash message, dan navigasi harus memiliki pola visual dan perilaku yang konsisten.

5. **Role-aware interface**  
   Pengguna, petugas, dan admin menggunakan fondasi visual yang sama, tetapi navigasi dan prioritas informasinya berbeda.

6. **Accessible by default**  
   Fokus keyboard terlihat, warna teks memiliki kontras tinggi, target klik cukup besar, dan informasi tidak disampaikan melalui warna saja.

---

# 2. Brand & Color Palette

## 2.1 Core Color Palette

| Token | Hex | Tailwind Reference | Penggunaan |
|---|---|---|---|
| `primary-900` | `#1E3A8A` | `blue-900` | Header, sidebar, primary navigation, aksi utama |
| `primary-800` | `#1E40AF` | `blue-800` | Hover primary dark |
| `primary-700` | `#1D4ED8` | `blue-700` | Active state / selected navigation |
| `primary-600` | `#2563EB` | `blue-600` | Secondary interactive action, link utama |
| `primary-500` | `#3B82F6` | `blue-500` | Focus ring, selected slot |
| `primary-100` | `#DBEAFE` | `blue-100` | Light highlight |
| `primary-50` | `#EFF6FF` | `blue-50` | Selected/active surface ringan |
| `accent-500` | `#F59E0B` | `amber-500` | Pending, perhatian, highlight |
| `accent-600` | `#D97706` | `amber-600` | Hover accent |
| `background` | `#F8FAFC` | `slate-50` | Background halaman |
| `surface` | `#FFFFFF` | `white` | Card, tabel, form, modal |
| `surface-soft` | `#F1F5F9` | `slate-100` | Panel sekunder, hover lembut |
| `border` | `#E2E8F0` | `slate-200` | Border standar |
| `border-strong` | `#CBD5E1` | `slate-300` | Border input aktif / separator |
| `text-primary` | `#0F172A` | `slate-900` | Judul dan teks utama |
| `text-secondary` | `#475569` | `slate-600` | Deskripsi / metadata |
| `text-muted` | `#94A3B8` | `slate-400` | Placeholder / teks non-prioritas |
| `text-disabled` | `#CBD5E1` | `slate-300` | Disabled text |

### Rekomendasi role warna

- **Primary:** `#1E3A8A`
- **Secondary:** `#2563EB`
- **Accent:** `#F59E0B`
- **Neutral Base:** Slate
- **Surface:** White

Alasan:
- Biru tua memberi kesan stabil, formal, terpercaya, dan sesuai konteks institusi.
- Biru medium digunakan untuk elemen interaktif agar primary dark tidak terlalu dominan.
- Amber dipakai sebagai accent dan warning tanpa mengurangi kesan profesional.

---

# 3. Semantic Color System

Warna semantik harus konsisten di seluruh aplikasi.

## 3.1 General Semantic Tokens

| State | Main | Soft Background | Border | Text |
|---|---|---|---|---|
| Success | `#16A34A` | `#F0FDF4` | `#BBF7D0` | `#166534` |
| Processing | `#2563EB` | `#EFF6FF` | `#BFDBFE` | `#1E40AF` |
| Pending | `#F59E0B` | `#FFFBEB` | `#FDE68A` | `#92400E` |
| Warning | `#EA580C` | `#FFF7ED` | `#FED7AA` | `#9A3412` |
| Danger / Rejected | `#DC2626` | `#FEF2F2` | `#FECACA` | `#991B1B` |
| Info | `#0891B2` | `#ECFEFF` | `#A5F3FC` | `#155E75` |
| Neutral | `#64748B` | `#F8FAFC` | `#E2E8F0` | `#475569` |

---

# 4. Status Color Mapping

## 4.1 Status Reservasi

| Database Status | Label UI | Badge |
|---|---|---|
| `pending` | Menunggu Persetujuan | Amber |
| `approved` | Disetujui | Green |
| `rejected` | Ditolak | Red |
| `cancelled_by_user` | Dibatalkan Pengguna | Slate |
| `cancelled_by_officer` | Dibatalkan Petugas | Orange/Slate |

### Class recommendation

```html
<!-- Pending -->
<span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
    Menunggu Persetujuan
</span>

<!-- Approved -->
<span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-200">
    Disetujui
</span>

<!-- Rejected -->
<span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200">
    Ditolak
</span>
```

---

## 4.2 Status Laporan

| Database Status | Label UI | Warna |
|---|---|---|
| `baru` | Baru | Cyan / Info |
| `diproses` | Diproses | Blue |
| `selesai` | Selesai | Green |
| `ditolak` | Ditolak | Red |

### Prinsip

- `baru` = laporan baru yang belum diambil petugas.
- `diproses` = biru agar berbeda dengan `pending`.
- `selesai` = hijau sebagai outcome positif.
- `ditolak` = merah sebagai outcome negatif.

---

## 4.3 Status Fasilitas

| Database Status | Label UI | Warna |
|---|---|---|
| `aktif` | Aktif | Green |
| `perbaikan` | Dalam Perbaikan | Orange |
| `nonaktif` | Nonaktif | Slate |

---

## 4.4 Status Akun

| Database Status | Label UI | Warna |
|---|---|---|
| `pending` | Menunggu Verifikasi | Amber |
| `aktif` | Aktif | Green |
| `ditolak` | Ditolak | Red |

---

# 5. CSS Variables / Design Tokens

Disarankan menyimpan token inti sehingga mudah diubah di satu tempat.

```css
:root {
    --color-primary: #1E3A8A;
    --color-primary-hover: #1E40AF;
    --color-secondary: #2563EB;
    --color-secondary-hover: #1D4ED8;
    --color-accent: #F59E0B;

    --color-bg: #F8FAFC;
    --color-surface: #FFFFFF;
    --color-surface-soft: #F1F5F9;

    --color-text: #0F172A;
    --color-text-secondary: #475569;
    --color-text-muted: #94A3B8;

    --color-border: #E2E8F0;
    --color-border-strong: #CBD5E1;

    --color-success: #16A34A;
    --color-info: #0891B2;
    --color-warning: #EA580C;
    --color-danger: #DC2626;

    --radius-sm: 6px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --radius-xl: 16px;

    --shadow-card: 0 1px 2px rgba(15, 23, 42, 0.04),
                   0 1px 3px rgba(15, 23, 42, 0.06);
}
```

---

# 6. Typography

## 6.1 Font Family

**Font:** Figtree

```css
font-family: "Figtree", ui-sans-serif, system-ui, sans-serif;
```

Fallback:
```css
font-family:
    "Figtree",
    Inter,
    ui-sans-serif,
    system-ui,
    -apple-system,
    BlinkMacSystemFont,
    "Segoe UI",
    sans-serif;
```

---

## 6.2 Font Weights

| Weight | Penggunaan |
|---|---|
| 400 | Body text |
| 500 | Label, navigation, secondary button |
| 600 | Button, card title, table header |
| 700 | Page heading, statistik penting |

Hindari penggunaan `800/900` secara berlebihan karena desain ditujukan bersih dan profesional.

---

## 6.3 Type Scale

| Style | Size | Line Height | Weight | Tailwind |
|---|---:|---:|---:|---|
| Display | 32px | 40px | 700 | `text-3xl font-bold` |
| H1 | 28px | 36px | 700 | `text-[28px] font-bold` |
| H2 | 24px | 32px | 700 | `text-2xl font-bold` |
| H3 | 20px | 28px | 600 | `text-xl font-semibold` |
| H4 | 18px | 26px | 600 | `text-lg font-semibold` |
| Body Large | 16px | 26px | 400 | `text-base` |
| Body | 14px | 22px | 400 | `text-sm` |
| Label | 14px | 20px | 500/600 | `text-sm font-medium` |
| Caption | 12px | 18px | 400/500 | `text-xs` |

---

## 6.4 Text Color Hierarchy

### Primary Text

```text
#0F172A / slate-900
```

Digunakan untuk:
- Page title
- Card title
- Nama fasilitas
- Nama pengguna
- Data utama
- Nilai statistik

### Secondary Text

```text
#475569 / slate-600
```

Digunakan untuk:
- Deskripsi
- Metadata
- Lokasi
- Tanggal
- Informasi pendukung

### Muted Text

```text
#94A3B8 / slate-400
```

Digunakan untuk:
- Placeholder
- Helper text
- Informasi yang tidak menjadi prioritas

Jangan menggunakan muted text untuk informasi penting.

---

# 7. Spacing System

Gunakan kelipatan **4px**.

| Token | Value |
|---|---:|
| `space-1` | 4px |
| `space-2` | 8px |
| `space-3` | 12px |
| `space-4` | 16px |
| `space-5` | 20px |
| `space-6` | 24px |
| `space-8` | 32px |
| `space-10` | 40px |
| `space-12` | 48px |
| `space-16` | 64px |

### Default spacing rules

- Icon ↔ text: `8px`
- Label ↔ input: `6–8px`
- Input ↔ helper/error: `6px`
- Antar field: `20–24px`
- Card padding mobile: `16px`
- Card padding desktop: `20–24px`
- Section gap: `24–32px`

---

# 8. Border Radius

| Element | Radius |
|---|---|
| Small badge | `6px` atau full pill |
| Input | `8px` |
| Button | `8px` |
| Card | `12px` |
| Modal | `16px` |
| Large media/photo | `12px` |

Rekomendasi utama:
```text
Button/Input = 8px
Card = 12px
Modal = 16px
Badge = 9999px
```

---

# 9. Borders & Shadows

## Border

Standard:
```text
1px solid #E2E8F0
```

Strong:
```text
1px solid #CBD5E1
```

## Card Shadow

Gunakan shadow minimal:

```css
box-shadow:
    0 1px 2px rgba(15, 23, 42, 0.04),
    0 1px 3px rgba(15, 23, 42, 0.06);
```

Tailwind:
```text
shadow-sm
```

Hindari shadow besar pada setiap komponen.

---

# 10. Page Layout System

## 10.1 Global Application Shell

Untuk halaman setelah login:

```text
┌──────────────────────────────────────────────────────────┐
│ Sidebar  │                Main Area                     │
│          │ ┌──────────────────────────────────────────┐ │
│          │ │ Topbar                                   │ │
│          │ ├──────────────────────────────────────────┤ │
│          │ │ Breadcrumb                               │ │
│          │ │ Page Header                              │ │
│          │ │ Content                                  │ │
│          │ │                                          │ │
│          │ └──────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────┘
```

### Desktop

- Sidebar: `264px`
- Collapsed sidebar: `72px`
- Topbar: `64px`
- Main content max width: `1440px`
- Horizontal padding: `32px`
- Vertical content gap: `24px`

### Tablet

- Sidebar menjadi drawer atau collapsed.
- Horizontal content padding: `24px`.

### Mobile

- Sidebar tidak permanen.
- Gunakan topbar + hamburger drawer.
- Horizontal content padding: `16px`.
- Hindari layout lebih dari satu kolom untuk form utama.

---

# 11. Public Layout

Halaman publik:
- Daftar fasilitas
- Detail fasilitas
- Jadwal fasilitas
- Login
- Registrasi

Gunakan:
- Top navigation horizontal
- Logo kiri
- Menu tengah/kanan
- Tombol Login / Daftar di kanan
- `max-w-7xl mx-auto`
- Tidak menggunakan dashboard sidebar

Contoh:

```html
<div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            ...
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        ...
    </main>
</div>
```

---

# 12. Dashboard Layout

## 12.1 Main Shell — Flexbox

Gunakan Flexbox untuk struktur besar sidebar + main.

```html
<div class="flex min-h-screen bg-slate-50">
    <aside class="hidden w-64 shrink-0 lg:block">
        ...
    </aside>

    <div class="min-w-0 flex-1">
        <header>...</header>
        <main>...</main>
    </div>
</div>
```

### Mengapa Flexbox?

Flexbox ideal untuk:
- Sidebar + content
- Navbar
- Toolbar
- Button group
- Alignment icon dan text
- Header card

---

# 13. Grid System

## 13.1 12-Column Desktop Grid

Untuk halaman kompleks:

```html
<div class="grid grid-cols-12 gap-6">
    <section class="col-span-12 lg:col-span-8">
        ...
    </section>

    <aside class="col-span-12 lg:col-span-4">
        ...
    </aside>
</div>
```

Gunakan 12 kolom ketika membutuhkan komposisi:
- `8 + 4`
- `9 + 3`
- `6 + 6`
- `4 + 4 + 4`

---

## 13.2 Dashboard Statistics

```html
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    ...
</div>
```

Rekomendasi:
- Mobile: 1 kolom
- Tablet: 2 kolom
- Desktop besar: 4 kolom

---

## 13.3 Facility Cards

```html
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
    ...
</div>
```

Jika monitor sangat lebar:
```text
2xl:grid-cols-4
```

---

## 13.4 Form Grid

Untuk form admin:

```html
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    ...
</div>
```

Field panjang seperti:
- deskripsi
- tujuan reservasi
- alamat/lokasi detail
- file upload

gunakan:
```text
md:col-span-2
```

---

# 14. Breakpoints

Gunakan breakpoint Tailwind standar.

| Breakpoint | Width | Penggunaan |
|---|---:|---|
| Base | `<640px` | Mobile |
| `sm` | `640px` | Large phone |
| `md` | `768px` | Tablet |
| `lg` | `1024px` | Desktop |
| `xl` | `1280px` | Wide desktop |
| `2xl` | `1536px` | Very wide screen |

### Rule

- Mobile-first class.
- Sidebar permanent mulai `lg`.
- Form dua kolom mulai `md`.
- Dashboard 4 statistic cards mulai `xl`.

---

# 15. Container Width

### Public Content

```text
max-w-7xl
```

### Dashboard Content

Dapat menggunakan:
```text
max-w-[1440px]
```

Contoh:
```html
<main class="mx-auto w-full max-w-[1440px] px-4 py-6 sm:px-6 lg:px-8">
```

---

# 16. Sidebar

## Visual

- Background: `#1E3A8A`
- Text default: `blue-100`
- Text active: `white`
- Active background: `rgba(255,255,255,0.12)`
- Hover: `rgba(255,255,255,0.08)`
- Width: `264px`

## Item

```text
Height: 44px
Padding horizontal: 12px
Gap icon-text: 12px
Radius: 8px
Font: 14px / 500
```

### Recommended Tailwind

```html
<a class="
    flex h-11 items-center gap-3 rounded-lg px-3
    text-sm font-medium text-blue-100
    transition
    hover:bg-white/10 hover:text-white
">
```

Active:

```text
bg-white/15 text-white
```

---

# 17. Topbar

Height:
```text
64px
```

Style:
```text
bg-white
border-b border-slate-200
```

Isi:
- Sidebar trigger
- Page context
- Optional global search
- Notification
- User profile dropdown

Gunakan `sticky top-0 z-30` jika halaman panjang.

---

# 18. Page Header

Struktur:

```text
Breadcrumb
↓ 8px
Title + Description              Primary Action
```

Desktop:
```html
<div class="flex items-start justify-between gap-4">
```

Mobile:
```text
flex-col
```

### Example

```html
<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">
            Reservasi
        </h1>
        <p class="mt-1 text-sm text-slate-600">
            Kelola reservasi fasilitas kampus Anda.
        </p>
    </div>

    <button>Ajukan Reservasi</button>
</div>
```

---

# 19. Buttons

## 19.1 Button Hierarchy

Urutan prioritas:

1. Primary
2. Secondary
3. Outline
4. Ghost
5. Danger

Dalam satu area, idealnya hanya terdapat **satu primary button**.

---

## 19.2 Primary Button

Use:
- Simpan
- Ajukan Reservasi
- Approve
- Buat Fasilitas
- Verifikasi

Colors:
```text
bg #1E3A8A
hover #1E40AF
text white
```

Tailwind:

```html
<button class="
    inline-flex h-10 items-center justify-center gap-2
    rounded-lg bg-blue-900 px-4
    text-sm font-semibold text-white
    transition-colors
    hover:bg-blue-800
    focus-visible:outline-none
    focus-visible:ring-2
    focus-visible:ring-blue-500
    focus-visible:ring-offset-2
    disabled:pointer-events-none
    disabled:opacity-50
">
```

---

## 19.3 Secondary Button

Untuk aksi pendamping positif:

```text
bg blue-50
text blue-800
hover blue-100
```

---

## 19.4 Outline Button

Untuk:
- Batal
- Kembali
- Filter
- Export secondary
- Detail

```text
bg white
border slate-300
text slate-700
hover bg-slate-50
```

---

## 19.5 Ghost Button

Untuk:
- Icon action
- Toolbar
- Dropdown
- Small inline actions

```text
bg transparent
hover bg-slate-100
```

---

## 19.6 Danger Button

Untuk:
- Tolak
- Nonaktifkan
- Hapus logically
- Cancel by officer

```text
bg red-600
hover red-700
text white
```

---

## 19.7 Button Sizes

| Size | Height | Padding | Font |
|---|---:|---|---|
| Small | 32px | 12px | 12–14px |
| Default | 40px | 16px | 14px |
| Large | 44px | 20px | 14–16px |

Default action:
```text
h-10 px-4
```

---

# 20. Icon Button

Minimum clickable target:
```text
40 × 40px
```

Icon:
```text
18–20px
```

Gunakan tooltip untuk tombol ikon tanpa label.

---

# 21. Forms

## 21.1 Input

Default:

```text
Height: 40px
Radius: 8px
Border: slate-300
Background: white
Text: slate-900
Placeholder: slate-400
```

Focus:

```text
border blue-500
ring 3px blue-100
```

Error:

```text
border red-500
ring red-100
```

Disabled:

```text
bg slate-100
text slate-500
cursor-not-allowed
```

---

## 21.2 Input Tailwind

```html
<input class="
    h-10 w-full rounded-lg border border-slate-300
    bg-white px-3 text-sm text-slate-900
    placeholder:text-slate-400
    transition
    focus:border-blue-500
    focus:outline-none
    focus:ring-4
    focus:ring-blue-100
    disabled:cursor-not-allowed
    disabled:bg-slate-100
    disabled:text-slate-500
">
```

---

## 21.3 Label

```text
14px
font-medium
slate-700
```

Required field:
```text
* = red-500
```

---

## 21.4 Helper Text

```text
12px
slate-500/600
margin-top: 6px
```

---

## 21.5 Error Text

```text
12px
red-600
margin-top: 6px
```

Tambahkan ikon alert kecil bila relevan.

---

## 21.6 Textarea

- Min height: `120px`
- Resize vertical
- Style sama dengan input

---

## 21.7 Select

Gunakan style input yang sama.

Pastikan icon chevron tidak terlalu dominan.

---

# 22. File Upload

Untuk laporan kerusakan:

### Default Dropzone

```text
Border: 1px dashed slate-300
Background: white
Radius: 12px
Padding: 24px
```

Hover:
```text
border blue-400
bg blue-50/30
```

Selected image:
- Preview thumbnail
- File name
- Size
- Remove button

---

# 23. Cards

## Basic Card

```html
<div class="rounded-xl border border-slate-200 bg-white shadow-sm">
```

Padding:
```text
p-5 sm:p-6
```

Structure:

```text
Card Header
Card Body
Card Footer (optional)
```

### Card Header

Title:
```text
text-base font-semibold text-slate-900
```

Description:
```text
text-sm text-slate-500
```

---

# 24. Statistic Cards

Untuk dashboard Admin/Petugas/Pengguna.

Struktur:

```text
Label
Large number
Trend/info
Icon
```

Number:
```text
text-2xl / text-3xl
font-bold
text-slate-900
```

Card jangan seluruhnya diberi warna kuat.

Gunakan warna semantik hanya pada:
- icon background
- small indicator
- badge

---

# 25. Tables

Tabel digunakan untuk:
- Reservasi
- Laporan
- User
- Fasilitas
- Rekap

## Table Container

```html
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
```

Untuk mobile:

```html
<div class="overflow-x-auto">
```

---

## Table Header

```text
bg slate-50
text slate-600
font-semibold
text-xs / uppercase optional
```

Disarankan tidak terlalu banyak uppercase.

---

## Table Row

Default:
```text
bg white
border-b slate-100
```

Hover:
```text
bg slate-50
```

Height:
```text
52–60px
```

---

## Sticky Header

Untuk tabel panjang:

```text
sticky top-0 z-10
```

Jika topbar sticky:
sesuaikan offset.

---

# 26. Mobile Table Strategy

Jangan memaksa semua kolom tetap terlihat.

Prioritas:
1. Nama/data utama
2. Status
3. Tanggal
4. Aksi

Metadata sekunder dapat:
- disembunyikan di mobile
- ditampilkan pada detail
- diubah menjadi stacked row/card

---

# 27. Badge

Badge status:

```text
Height: 24–26px
Padding: 8–10px horizontal
Radius: full
Font: 12px / 600
```

Gunakan bentuk:

```html
<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold">
```

Optional:
- tambahkan dot `6px`
- tambahkan ikon status `14px`

---

# 28. Alerts / Flash Messages

## Success

```text
bg green-50
border green-200
text green-800
```

## Error

```text
bg red-50
border red-200
text red-800
```

## Warning

```text
bg amber-50
border amber-200
text amber-800
```

## Info

```text
bg blue-50
border blue-200
text blue-800
```

Struktur:
```text
Icon + title + description + close button
```

---

# 29. Toast

Gunakan untuk aksi singkat seperti:
- Reservasi berhasil diajukan
- Status berhasil diperbarui
- Data berhasil disimpan

Desktop:
```text
top-right
```

Mobile:
```text
top-center atau bottom-center
```

Width:
```text
320–420px
```

Auto-dismiss:
```text
4–6 detik
```

Error penting sebaiknya tidak auto-dismiss terlalu cepat.

---

# 30. Modal / Dialog

Digunakan untuk:
- Tolak reservasi
- Batalkan reservasi
- Nonaktifkan fasilitas
- Konfirmasi aksi destruktif

Width:
```text
sm: max-w-md
form complex: max-w-lg
```

Overlay:
```text
bg-slate-950/40
```

Modal:
```text
bg-white
rounded-2xl
shadow-xl
```

Footer:
```text
Cancel = outline
Confirm destructive = danger
```

---

# 31. Confirmation Pattern

Aksi destruktif wajib menjelaskan dampak.

Contoh:

```text
Batalkan reservasi?

Reservasi Aula Terpadu pada 10 September 2026,
09.00–11.00 akan dibatalkan.

[ Kembali ] [ Batalkan Reservasi ]
```

Jangan menggunakan teks konfirmasi generik seperti hanya:
```text
"Apakah Anda yakin?"
```

---

# 32. Navigation

## Sidebar Groups

### Pengguna
- Dashboard
- Fasilitas
- Reservasi Saya
- Laporan Saya

### Petugas
- Dashboard
- Antrian Reservasi
- Antrian Laporan
- Status Fasilitas

### Admin
- Dashboard
- Fasilitas
- Pengguna
- Petugas
- Verifikasi Akun
- Rekap & Ekspor

Group label:
```text
text-xs font-semibold uppercase tracking-wider
```

Gunakan secara terbatas.

---

# 33. Breadcrumb

Contoh:

```text
Dashboard / Reservasi / Detail
```

Style:
- Parent: slate-500
- Current: slate-900
- Size: 14px

Mobile:
Boleh hanya menampilkan satu level sebelumnya.

---

# 34. Pagination

Gunakan:
- Previous
- Number
- Next

Active:
```text
bg blue-900
text white
```

Inactive:
```text
bg white
border slate-300
text slate-700
```

Minimum target:
```text
36 × 36px
```

---

# 35. Search & Filter Toolbar

Struktur desktop:

```text
[ Search........................ ] [ Tipe ] [ Lokasi ] [ Status ] [ Filter ]
```

Mobile:

```text
[ Search........................ ]
[ Filter ]
```

Filter lanjutan dapat dibuka melalui:
- drawer
- popover
- collapsible panel

Gunakan `flex-wrap`.

```html
<div class="flex flex-wrap items-end gap-3">
```

---

# 36. Empty State

Gunakan ketika tidak ada data.

Contoh:
```text
[icon]
Belum ada reservasi
Reservasi yang Anda buat akan tampil di sini.
[Ajukan Reservasi]
```

Rules:
- Icon ringan
- Title jelas
- Penjelasan maksimal 2 baris
- CTA jika tersedia

---

# 37. Loading State

Untuk data yang sedang dimuat:

### Button
```text
spinner + "Menyimpan..."
```

### Table/Card
Gunakan skeleton, bukan hanya spinner besar.

Skeleton:
```text
bg-slate-200 animate-pulse rounded
```

---

# 38. Error State

Untuk error API/server:
```text
Tidak dapat memuat data.
Silakan coba lagi.
[Coba Lagi]
```

Jangan tampilkan stack trace kepada user.

---

# 39. Slot Picker Reservasi

Slot merupakan komponen khusus yang sangat penting.

## Grid

Jam operasional:
```text
07.00–20.00
```

Slot:
```text
30 menit
```

Gunakan layout responsive:

```html
<div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6">
```

---

## Slot States

### Available

```text
bg white
border slate-300
text slate-700
hover border blue-500
hover bg blue-50
```

### Selected

```text
bg blue-900
border blue-900
text white
```

### Booked

```text
bg red-50
border red-200
text red-500
cursor-not-allowed
```

### Unavailable / Facility inactive

```text
bg slate-100
border slate-200
text slate-400
cursor-not-allowed
```

### Past Slot

```text
bg slate-50
text slate-300
cursor-not-allowed
```

---

# 40. Facility Card

Struktur:

```text
Photo
Badge Status
Name
Type
Location
Capacity
Next Availability
[View Schedule]
```

Image ratio:
```text
aspect-[16/9]
```

Object:
```text
object-cover
```

Hover:
- border becomes slate-300
- shadow slightly increases
- no exaggerated scale effect

---

# 41. Facility Status Visual

### Active

```text
green dot + "Aktif"
```

### Repair

```text
orange dot + "Dalam Perbaikan"
```

### Inactive

```text
gray dot + "Nonaktif"
```

Jika fasilitas tidak dapat dipesan:
CTA reservasi disabled dan tampil alasan.

---

# 42. Reservation Detail

Layout desktop:

```text
8 columns: reservation detail
4 columns: status timeline + action
```

```html
<div class="grid grid-cols-12 gap-6">
    <main class="col-span-12 lg:col-span-8">...</main>
    <aside class="col-span-12 lg:col-span-4">...</aside>
</div>
```

---

# 43. Report Detail

Layout:
- Main report
- Image evidence
- Facility
- Status
- Resolution note
- History timeline

Timeline status:
```text
dot + vertical line + status + actor + timestamp
```

Completed:
```text
green
```

Current:
```text
blue
```

Future/unused:
```text
slate
```

---

# 44. Dashboard Components

## User Dashboard

Cards:
1. Reservasi Mendatang
2. Reservasi Menunggu
3. Laporan Diproses
4. Laporan Selesai

Sections:
- Reservasi terbaru
- Laporan terbaru
- CTA cari fasilitas

---

## Petugas Dashboard

Cards:
1. Reservasi Menunggu
2. Laporan Baru
3. Laporan Diproses
4. Fasilitas Dalam Perbaikan

Priority section:
- Antrian yang perlu tindakan

---

## Admin Dashboard

Cards:
1. Akun Menunggu Verifikasi
2. Reservasi Pending
3. Laporan Aktif
4. Fasilitas Dalam Perbaikan

Section:
- Status fasilitas
- Aktivitas terbaru
- Quick actions

---

# 45. Action Priority

### Positive primary

```text
Setujui / Simpan / Verifikasi
```

### Negative

```text
Tolak / Nonaktifkan / Batalkan
```

Jangan menempatkan tombol `Setujui` dan `Tolak` dengan visual weight yang sama.

Recommended:
- Approve = primary
- Reject = danger outline atau danger
- Detail = outline/ghost

---

# 46. Dropdown Menu

Width:
```text
min-w-48
```

Style:
```text
rounded-lg
border
bg-white
shadow-lg
p-1
```

Menu item:
```text
h-9
px-3
rounded-md
text-sm
```

Danger menu:
```text
text-red-600
hover:bg-red-50
```

---

# 47. Tabs

Contoh:
```text
Semua | Pending | Disetujui | Ditolak
```

Style:
- Tab active: blue-900
- Border bottom: 2px
- Inactive: slate-500

Jangan menggunakan background heavy jika tab sudah menggunakan underline.

---

# 48. Date Picker / Calendar

- Selected date: `blue-900`
- Today: blue border / small blue indicator
- Unavailable: slate disabled
- Date with reservation: optional small dot

Past dates tidak selectable untuk reservasi.

---

# 49. Accessibility

## Color

Jangan hanya menggunakan:
```text
merah = gagal
hijau = berhasil
```

Tambahkan:
- label
- icon
- accessible text

## Focus

Semua interactive element:

```text
focus-visible:ring-2
focus-visible:ring-blue-500
focus-visible:ring-offset-2
```

## Touch Target

Minimum:
```text
40 × 40px
```

Ideal mobile:
```text
44 × 44px
```

## Form

Setiap input wajib memiliki `<label>`.

Error:
- dikaitkan melalui `aria-describedby`
- `aria-invalid="true"`

---

# 50. Responsive Rules

## Mobile `<640px`

- Padding page: 16px
- Single-column form
- Sidebar menjadi drawer
- Table horizontal scroll atau card stack
- Button penting full width bila perlu
- Modal hampir full-width dengan margin 16px

## Tablet `768–1023px`

- Form dapat 2 kolom
- Dashboard stats 2 kolom
- Sidebar collapsed/drawer
- Table tetap horizontal scroll bila banyak kolom

## Desktop `>=1024px`

- Sidebar fixed/persistent
- Main grid 12 kolom
- Multi-column dashboard
- Toolbar horizontal

---

# 51. Flexbox Usage Guide

Gunakan **Flexbox** ketika urutan komponennya satu dimensi.

### Navbar

```html
<div class="flex items-center justify-between">
```

### Icon + Label

```html
<div class="flex items-center gap-2">
```

### Action buttons

```html
<div class="flex items-center justify-end gap-2">
```

### Toolbar responsive

```html
<div class="flex flex-wrap items-center gap-3">
```

### Vertical form section

```html
<div class="flex flex-col gap-5">
```

---

# 52. Grid Usage Guide

Gunakan **CSS Grid** ketika elemen perlu memiliki struktur 2 dimensi.

Ideal untuk:
- dashboard cards
- facility cards
- form multi-column
- page content + sidebar
- slot reservation
- metric cards

### General responsive grid

```html
<div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
```

---

# 53. Grid vs Flex Decision Rule

Gunakan:

```text
Flex = alignment / distribution dalam satu arah
Grid = pembagian area / kolom / baris
```

Contoh:

| UI | Pilihan |
|---|---|
| Sidebar + Main | Flex |
| Navbar | Flex |
| Button group | Flex |
| Dashboard cards | Grid |
| Facility cards | Grid |
| Form 2 kolom | Grid |
| Slot picker | Grid |
| Filter toolbar | Flex |
| Card header | Flex |
| Modal footer | Flex |

---

# 54. Z-Index Scale

| Layer | z-index |
|---|---:|
| Normal | 0 |
| Sticky table header | 10 |
| Sticky topbar | 30 |
| Dropdown | 40 |
| Drawer | 50 |
| Modal overlay | 60 |
| Modal | 70 |
| Toast | 80 |

Tailwind dapat menggunakan:
```text
z-10 z-30 z-40 z-50
```

Untuk modal custom dapat menggunakan arbitrary value:
```text
z-[60]
```

---

# 55. Motion & Transition

Gunakan motion singkat.

Default:
```text
duration-150
```

Card/dropdown:
```text
duration-200
```

Avoid:
- bounce
- large scale
- excessive movement

Recommended:
```text
transition-colors duration-150
```

---

# 56. Icons

Recommended:
- Heroicons
- Lucide

Gunakan satu library saja.

Sizes:
- Inline: 16px
- Button: 18px
- Navigation: 20px
- Empty state: 40–48px

Stroke:
```text
1.75–2px
```

---

# 57. Recommended Component Library Structure

Blade components:

```text
resources/views/components/ui/
├── alert.blade.php
├── avatar.blade.php
├── badge.blade.php
├── breadcrumb.blade.php
├── button.blade.php
├── card.blade.php
├── checkbox.blade.php
├── dialog.blade.php
├── dropdown.blade.php
├── empty-state.blade.php
├── input.blade.php
├── label.blade.php
├── pagination.blade.php
├── select.blade.php
├── skeleton.blade.php
├── table.blade.php
├── tabs.blade.php
├── textarea.blade.php
├── toast.blade.php
└── tooltip.blade.php
```

Feature components:

```text
resources/views/components/
├── facility/
│   ├── card.blade.php
│   └── status.blade.php
├── reservation/
│   ├── status.blade.php
│   ├── slot-picker.blade.php
│   └── summary.blade.php
├── report/
│   ├── status.blade.php
│   └── timeline.blade.php
└── dashboard/
    └── metric-card.blade.php
```

---

# 58. Component Variants

## Button

```text
primary
secondary
outline
ghost
danger
```

## Badge

```text
neutral
info
pending
processing
success
warning
danger
```

## Alert

```text
success
info
warning
danger
```

---

# 59. Button Component API

Contoh Blade:

```blade
<x-ui.button variant="primary">
    Simpan
</x-ui.button>

<x-ui.button variant="outline">
    Kembali
</x-ui.button>

<x-ui.button variant="danger">
    Tolak
</x-ui.button>
```

---

# 60. Badge Component API

```blade
<x-ui.badge status="pending">
    Menunggu Persetujuan
</x-ui.badge>

<x-ui.badge status="approved">
    Disetujui
</x-ui.badge>
```

Mapping status sebaiknya terpusat di komponen, bukan ditulis ulang di setiap view.

---

# 61. Suggested Tailwind Utility Patterns

## Page

```text
min-h-screen bg-slate-50 text-slate-900
```

## Card

```text
rounded-xl border border-slate-200 bg-white shadow-sm
```

## Divider

```text
border-slate-200
```

## Body Copy

```text
text-sm leading-6 text-slate-600
```

## Heading

```text
font-semibold tracking-tight text-slate-900
```

---

# 62. Role Visual Consistency

Tidak perlu memberi warna berbeda untuk setiap role.

Semua role tetap memakai:
```text
Primary Blue
```

Perbedaannya melalui:
- menu
- halaman
- permission
- informasi dashboard

Hal ini menjaga produk terlihat sebagai satu sistem.

---

# 63. Login & Register Design

Layout desktop:

```text
Left: brand/illustration/info
Right: auth form
```

Atau simple centered card.

Recommended untuk project:
```text
Centered auth card
```

Width:
```text
max-w-md
```

Card:
```text
p-6 sm:p-8
```

Background:
```text
slate-50
```

Brand logo:
```text
blue-900
```

---

# 64. Accessibility Contrast Guidance

Untuk teks utama:
```text
#0F172A di #FFFFFF
```

Untuk text-secondary:
```text
#475569 di #FFFFFF
```

Muted `#94A3B8` hanya untuk:
- placeholder
- non-essential metadata

Jangan gunakan `slate-400` untuk body text utama.

---

# 65. Data Density

Karena aplikasi bersifat operasional:

### Desktop
Boleh cukup padat.

- Table row: 52px
- Field height: 40px
- Card padding: 20px

### Mobile
Lebihkan spacing.

- Button: 44px bila primary CTA
- Card padding: 16px
- Table dapat berubah menjadi stacked card

---

# 66. Recommended Page Composition

Setiap halaman dashboard mengikuti pola:

```text
1. Breadcrumb
2. Page title + description
3. Page actions
4. Filter / controls
5. Main content
6. Pagination
```

Jangan mengubah urutan tanpa alasan UX yang kuat.

---

# 67. Design Example — Reservation Queue

```text
Antrian Reservasi
Kelola pengajuan reservasi yang perlu ditinjau.

[Search........] [Status ▼] [Tanggal ▼]

┌────────────────────────────────────────────────────────┐
│ Pemohon │ Fasilitas │ Waktu │ Status │ Aksi           │
├────────────────────────────────────────────────────────┤
│ Budi    │ Aula      │ 09-11 │ Pending│ Detail Approve │
│ Sari    │ Lab       │ 13-15 │ Pending│ Detail Approve │
└────────────────────────────────────────────────────────┘
```

Primary action dalam row sebaiknya tidak lebih dari satu.
Aksi tambahan dapat menggunakan dropdown.

---

# 68. Design Example — Report Queue

```text
Laporan Kerusakan

[Baru 12] [Diproses 5] [Selesai 48]

┌──────────────────────────────────────────────────────────┐
│ Laporan │ Fasilitas │ Kategori │ Status │ Terakhir      │
└──────────────────────────────────────────────────────────┘
```

Gunakan tab/filter untuk mengurangi informasi terlalu padat.

---

# 69. Design Example — Admin Verification

Priority utama:
```text
Akun Pending
```

Row:
```text
Nama
Email
Identitas
Tanggal Daftar
Status
Action
```

Action:
```text
[Verifikasi] [Tolak]
```

Verifikasi:
- primary

Tolak:
- danger outline

---

# 70. Export Actions

Untuk halaman rekap:

```text
[ Export CSV ] [ Export PDF ]
```

Recommended:
- `Export CSV` = outline
- `Export PDF` = outline
- Main action tetap bukan export jika ada aksi lebih utama.

Gunakan icon download.

---

# 71. Dark Mode

Tidak wajib untuk versi pertama.

Alasannya:
- Menambah kompleksitas QA.
- Sistem target adalah aplikasi akademik/operasional.
- Light mode sudah sesuai dengan arah bersih dan modern.

Namun token warna dibuat cukup terstruktur agar dark mode dapat ditambahkan nanti.

---

# 72. Design Anti-Patterns

Hindari:

1. Semua card menggunakan warna berbeda.
2. Gradient berlebihan.
3. Shadow tebal.
4. Radius terlalu besar pada semua komponen.
5. Terlalu banyak jenis biru tanpa aturan.
6. Tombol merah untuk aksi non-destruktif.
7. Status hanya berupa teks.
8. Placeholder menggantikan label.
9. Table tanpa responsive handling.
10. Font size terlalu kecil.
11. Primary button lebih dari satu dalam satu action group.
12. Banyak animasi dekoratif.

---

# 73. UI Copy Guidelines

Gunakan istilah konsisten.

### Reservasi

Gunakan:
- `Ajukan Reservasi`
- `Menunggu Persetujuan`
- `Disetujui`
- `Ditolak`
- `Dibatalkan`

Jangan campur:
```text
Approve / Disetujui / Accepted
```

Pilih Bahasa Indonesia untuk seluruh UI.

### Laporan

Gunakan:
- `Laporan Baru`
- `Diproses`
- `Selesai`
- `Ditolak`

---

# 74. Date & Time Format

UI Indonesia:

```text
3 September 2026
```

Tanggal tabel ringkas:

```text
03 Sep 2026
```

Time:

```text
09.00–11.00 WIB
```

Gunakan `.` untuk display jam Indonesia.

Database tetap mengikuti tipe datetime standar.

---

# 75. Number Format

Capacity:
```text
40 orang
```

Count:
```text
1.250
```

Percentage:
```text
72,5%
```

Gunakan locale Indonesia pada UI.

---

# 76. Recommended Design Tokens Summary

```text
Primary        #1E3A8A
Secondary      #2563EB
Accent         #F59E0B

Background     #F8FAFC
Surface        #FFFFFF
Surface Soft   #F1F5F9

Text Primary   #0F172A
Text Secondary #475569
Text Muted     #94A3B8

Border         #E2E8F0
Border Strong  #CBD5E1

Success        #16A34A
Processing     #2563EB
Pending        #F59E0B
Warning        #EA580C
Danger         #DC2626
Info           #0891B2
Neutral        #64748B
```

---

# 77. Figma / Design Token Naming Recommendation

Jika desain dibuat di Figma:

```text
Color/Primary/900
Color/Primary/600
Color/Primary/50

Color/Accent/500

Color/Text/Primary
Color/Text/Secondary
Color/Text/Muted

Color/Surface/Default
Color/Surface/Soft

Color/Border/Default
Color/Border/Strong

Color/Semantic/Success
Color/Semantic/Processing
Color/Semantic/Pending
Color/Semantic/Warning
Color/Semantic/Danger
Color/Semantic/Info
```

Typography:

```text
Typography/Display
Typography/H1
Typography/H2
Typography/H3
Typography/Body
Typography/Body Small
Typography/Label
Typography/Caption
```

---

# 78. Tailwind Theme Recommendation

Jika ingin mendefinisikan warna custom:

```js
colors: {
    brand: {
        50: '#EFF6FF',
        100: '#DBEAFE',
        500: '#3B82F6',
        600: '#2563EB',
        700: '#1D4ED8',
        800: '#1E40AF',
        900: '#1E3A8A',
    },
    accent: {
        500: '#F59E0B',
        600: '#D97706',
    }
}
```

Jika menggunakan Tailwind default palette, sebagian besar warna sudah tersedia dan tidak perlu dibuat ulang.

---

# 79. Recommended Global Body

```html
<body class="bg-slate-50 font-sans text-slate-900 antialiased">
```

---

# 80. Recommended Base Card

```html
<section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
```

---

# 81. Recommended Section Heading

```html
<div>
    <h2 class="text-lg font-semibold text-slate-900">
        Reservasi Terbaru
    </h2>
    <p class="mt-1 text-sm text-slate-600">
        Pengajuan reservasi terbaru pada sistem.
    </p>
</div>
```

---

# 82. Final Style Direction

Website sebaiknya memiliki karakter:

```text
Modern
Clean
Academic
Professional
Trustworthy
Functional
Calm
```

Visual utama:

- Dominan putih + slate-50.
- Biru tua sebagai identitas.
- Biru medium sebagai interaction color.
- Amber hanya untuk perhatian/status menunggu.
- Semantic color hanya untuk status dan feedback.
- Shadow halus.
- Border lebih dominan daripada shadow.
- Radius medium.
- Figtree untuk seluruh interface.
- Layout menggunakan Flexbox untuk struktur satu dimensi.
- Layout menggunakan Grid untuk dashboard, cards, slot picker, dan form multi-column.
- Mobile-first responsive behavior.
- Semua status diberi badge yang konsisten.

---

# 83. Implementation Checklist

Sebelum mulai implementasi UI:

- [ ] Figtree sudah terpasang.
- [ ] Primary color menggunakan `#1E3A8A`.
- [ ] Secondary color menggunakan `#2563EB`.
- [ ] Accent menggunakan `#F59E0B`.
- [ ] Background menggunakan `#F8FAFC`.
- [ ] Semua card menggunakan surface putih.
- [ ] Sistem status memiliki mapping terpusat.
- [ ] Button component memiliki variant.
- [ ] Input component memiliki default/focus/error/disabled state.
- [ ] Sidebar responsive.
- [ ] Table responsive.
- [ ] Grid dashboard responsive.
- [ ] Slot picker memiliki seluruh state.
- [ ] Modal destructive memiliki konfirmasi jelas.
- [ ] Focus state keyboard terlihat.
- [ ] Teks sekunder dan muted memiliki hierarki konsisten.
- [ ] Mobile layout diuji minimal di 360px.
- [ ] Tablet diuji di 768px.
- [ ] Desktop diuji di 1024px dan 1440px.
- [ ] Status tidak pernah disampaikan hanya melalui warna.
- [ ] Empty, loading, dan error state tersedia.
- [ ] Setiap halaman mengikuti page composition yang sama.

---

## 84. Recommended First Components to Build

Urutan implementasi yang disarankan:

1. `button`
2. `input`, `select`, `textarea`, `label`, `form-error`
3. `badge`
4. `alert`
5. `card`
6. `table`
7. `modal/dialog`
8. `sidebar`
9. `topbar`
10. `page-header`
11. `facility-card`
12. `reservation-slot-picker`
13. `status-timeline`
14. `metric-card`
15. `empty-state`
16. `pagination`

Dengan urutan ini, halaman selanjutnya dapat dibangun dengan komponen reusable dan konsisten.

---

**End of `design.md`**
