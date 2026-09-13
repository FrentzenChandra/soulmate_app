# Workspace Agent Guidelines

## 1. Graphify Auto-Update
- Jalankan auto-update graphify (via `python -m graphify.cli update`) setiap kali terjadi perubahan kode, penambahan fitur, atau commit di project.
- Gunakan `graphify query` untuk menelusuri relasi kode, god nodes, dan dependensi sebelum melakukan refaktor.

## 2. UI & Design Standards (Anti-AI Slop)
- **Dilarang keras memakai gradient berlebihan / multi-color mesh gradient (AI slop)** kecuali diperintahkan secara eksplisit oleh user.
- Gunakan solid clean modern color palette: Brand Purple (`#5E17EB`), Tech Blue (`#0052FF`), Accent Pink (`#E6007A`), dan netral gray/slate dengan white cards, clean crisp borders, dan subtle shadows.

## 3. Docker & Execution Environment (Windows Host + Linux Container)
- **Eksekusi Perintah Framework:** Semua perintah yang menyentuh filesystem / backend (`php artisan storage:link`, `php artisan migrate`, `php artisan optimize:clear`, dsb.) **WAJIB dieksekusi di dalam Docker container** (`docker exec soulmate_apps ...`), bukan di terminal Windows host.
- **Windows Junction vs Linux Symlink:** `storage:link` dari Windows menghasilkan NTFS Junction yang tidak dapat dibaca oleh Linux container (menyebabkan 403 Forbidden). `storage:link` dari dalam container menghasilkan native Linux symlink.
- **Storage Standard:** Semua upload media menggunakan `Storage::disk('public')` dengan path relatif `storage/...`.

## 4. Edukasi & Penjelasan Konsep IT
- Saat menjelaskan istilah IT teknis (seperti Junction, Symlink, Volume Mount, Docker, Signed URL, dsb.), gunakan bahasa analogi sederhana ("gambarkan seperti menjelaskan ke pemula/bayi") yang mudah dicerna sebelum masuk ke teknis.

## 5. Skills & Capabilities
- Dalam konteks workspace project: Aktifkan seluruh ekosistem skill (Caveman, Ponytail, Graphify, TDD, Surgical Patch, Subagents).
- Pertahankan gaya komunikasi ringkas, padat, dan hemat token (Caveman mode).