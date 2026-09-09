---
name: environment-setup
description: Setup lingkungan proyek sistem-reservasi (PHP 8.3+, MySQL80, Laravel 13) dan jebakan Windows/PowerShell yang sudah pernah terjadi — gunakan saat onboarding mesin baru atau debugging environment.
---

# Environment Setup

## Spesifikasi target (sesuai docs/spesifikasi-sistem-reservasi.md §2)

| Komponen | Kebutuhan | Contoh terukur (satu mesin, bukan syarat mutlak) |
|---|---|---|
| PHP | >= 8.3 (Laravel 13) | 8.4.24 via WinGet |
| Composer | >= 2.x | 2.10.3 |
| MySQL | 8.x, service `MySQL80`, database `reservasi_kampus` (utf8mb4) | berjalan |
| Node.js + npm | Node >= 20 (build Tailwind/Vite) | Node v24.20.0, npm 12.0.2 (terukur 2026-09-03) |
| Laravel | 13.x (`laravel/framework ^13.x`) | v13.29.0 |

## Cek kesehatan (definisi "setup berhasil")

```bash
php -v && composer --version && node -v && npm -v
php artisan test --compact   # suite harus hijau sebelum mulai kerja
```

## Urutan setup mesin baru

```bash
composer install
copy .env.example .env        # lalu isi DB_PASSWORD sesuai MySQL mesin tsb
php artisan key:generate
php scripts/create-database.php   # membuat DB reservasi_kampus bila belum ada
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve             # http://localhost:8000
```

- Jika layanan `MySQL80` berhenti: `net start MySQL80` **dari terminal Administrator**
  (proses biasa akan ditolak aksesnya).
- `.env` TIDAK di-commit; setiap anggota mengisi `DB_PASSWORD` sendiri.
- Akun demo hasil seeder ada di spec §5.3 (admin@kampus.test / admin123, dst).

## Jebakan Windows/PowerShell yang SUDAH PERNAH TERJADI — jangan ulangi

1. **Git push "error" palsu**: PowerShell menampilkan `NativeCommandError` untuk output stderr git,
   padahal push sukses. Verifikasi dengan: `cmd /c "git push origin <branch> 2>&1"` lalu
   `git status -sb` / `git ls-remote`.
2. **Race condition antar perintah**: beberapa perintah yang dikirim sekaligus bisa dieksekusi
   paralel (pernah membuat folder terlambat terbentuk dan `git checkout -- app/Models` merevert
   file yang baru diedit). Rantai perintah yang saling bergantung dalam SATU string dengan `;`,
   dan verifikasi hasil setelahnya. JANGAN pernah menjalankan `git checkout --` / `git restore`
   bersamaan dengan operasi lain.
3. **Quoting `php artisan tinker --execute`** merusak string bersarang (`Unexpected end of input`).
   Gunakan skrip PHP di `scripts/` dengan bootstrap Laravel (contoh: `scripts/create-database.php`).
4. **`gh` tidak dikenali** setelah instalasi baru: gunakan path penuh
   `& "C:\Program Files\GitHub CLI\gh.exe" ...` sampai shell baru dibuka.
5. **Start-Service MySQL80 ditolak**: butuh elevated (Administrator) — instruksikan user, jangan diulang tanpa alasan.
