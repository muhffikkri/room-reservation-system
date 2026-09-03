---
name: git-conventions
description: Konvensi git proyek sistem-reservasi — nama branch konvensional Bahasa Inggris, conventional commits, verifikasi-sebelum-push, PR via gh CLI, sinkronisasi pasca-merge.
---

# Git Conventions

## Branch

- Selalu buat branch baru dari `main` terbaru untuk setiap milestone/fitur. JANGAN commit langsung di `main`.
- Nama branch: `<type>/<deskripsi-singkat-english>`, contoh:
  - `feat/data-foundation`
  - `feat/auth-and-roles`
  - `fix/overlap-race-condition`
- Buat dengan `git checkout -b <nama>`; push pertama dengan `git push -u origin <nama>`.
- Sebelum membuat branch: `git checkout dev && git pull origin dev` agar titik awal selalu terbaru.
- Branch yang dilindungi (tidak boleh commit langsung): `dev`, `staging`, `prod`, `main`.

## Conventional Commits (Bahasa Inggris, pesan padat & natural)

Format: `<type>: <imperative summary>`

- `feat:` fitur baru — `feat: add role and account status to users table`
- `fix:` perbaikan bug — `fix: prevent duplicate approval on overlapping slots`
- `docs:` dokumentasi — `docs: add technical specification for the reservation system (Laravel 13 + MySQL)`
- `chore:` perawatan — `chore: update project name in package-lock`
- `refactor:`, `test:`, `style:` sesuai kebutuhan.

Aturan:
- 1 commit = 1 unit kerja logis (jangan campur skema + seeder + docs dalam satu commit).
- Commit dilakukan per file/file-group dengan `git add <path>` eksplisit — hindari `git add -A`
  kecuali yakin seluruh working tree adalah satu unit kerja.
- Deskripsi Bahasa Inggris; komentar/jelaskan ke user dalam Bahasa Indonesia.

## Verifikasi sebelum push (HARD RULE)

Sebelum `git push`, selalu tampilkan ke user:

1. `git log --oneline origin/main..HEAD` — daftar commit yang akan ter-push.
2. Ringkasan file per commit bila relevan.
3. Konfirmasi `.env` / `vendor/` / `node_modules/` tidak ikut (`git status` bersih selain yang sengaja).

Push hanya setelah user menyetujui. Setelah push, verifikasi dengan
`git status -sb` (sinkron) atau `git ls-remote origin <branch>`.

## Pull Request (gh CLI)

```bash
gh pr create --base main --head <branch> --title "<conventional title>" --body-file <file.md>
gh pr view <nomor> --json title,state,headRefName,url,commits   # verifikasi
```

- Jika `gh` tidak dikenali PATH: `& "C:\Program Files\GitHub CLI\gh.exe" ...`
- Body PR ditulis ke file sementara lalu `--body-file` (hindari masalah quoting PowerShell).
- Struktur body: `## Summary`, `## What is included`, `## Verification`, `## Commits`.
- Merge dilakukan USER secara manual di GitHub (agent tidak merge tanpa perintah eksplisit).

## Pasca-merge

```bash
git checkout main
git pull origin main
git log --oneline -5        # pastikan merge commit ada
```

Bersihkan branch yang sudah ter-merge (tanyakan user dulu):
`git branch -d <branch>` dan/atau `git push origin --delete <branch>`.

- Sebelum menghapus branch: sebutkan nama branch satu per satu dan minta persetujuan
  eksplisit (`oke hapus`). Jangan menebak branch mana yang dimaksud user.
