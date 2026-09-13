# Project Guidelines (Soulmate App)

## 1. UI & Design Standards (Anti-AI Slop)
- Dilarang keras memakai gradient acak / multi-color mesh gradient (AI slop) kecuali diminta eksplisit.
- Gunakan solid modern color palette: Brand Purple (`#5E17EB`), Tech Blue (`#0052FF`), Accent Pink (`#E6007A`), neutral slate/gray, white cards, clean borders, subtle shadows.

## 2. Docker & Backend Execution
- Host OS: Windows. Application runtime: Docker Linux (`soulmate_apps`).
- Selalu eksekusi perintah backend/filesystem (`php artisan storage:link`, `php artisan migrate`, `php artisan optimize:clear`, dsb.) di dalam Docker: `docker exec soulmate_apps php artisan ...`.
- Windows Junction ≠ Linux Symlink. `storage:link` wajib dibuat di dalam container Linux agar tidak menghasilkan 403 Forbidden.
- Gunakan `Storage::disk('public')` dan URL relatif `storage/...`.

## 3. Komunikasi & Edukasi Konsep
- Jelaskan istilah teknis IT dengan analogi sederhana dan ramah pemula sebelum detail teknis.
- Mode komunikasi: ringkas, padat, hemat token (Caveman mode).

## 4. Graphify Auto-Update
- Jalankan `python -m graphify.cli update` setiap selesai melakukan perubahan kode.
