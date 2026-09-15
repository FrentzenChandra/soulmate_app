# Project Guidelines (Soulmate App)

## 1. UI & Design Standards (Anti-AI Slop)
- Dilarang keras memakai gradient acak / multi-color mesh gradient (AI slop) kecuali diminta eksplisit.
- Gunakan solid modern color palette: Brand Purple (`#5E17EB`), Tech Blue (`#0052FF`), Accent Pink (`#E6007A`), neutral slate/gray, white cards, clean borders, subtle shadows.

## 2. Global Multi-Word Loose Search Standard
- Semua input pencarian wajib multi-kata tokenized (`explode(' ', $search)`), case-insensitive (`LOWER() LIKE ?`), non-strict per huruf/kata across relevant columns.

## 3. Branch Obfuscation & Role Isolation
- Non-owner roles (Superadmin, Kasir, Teknisi) melihat aplikasi sebagai 1 toko mandiri ("Soulmate Komputer").
- Sembunyikan label cabang, switcher, dan badge cabang di navbar, header, notifikasi untuk non-owner. Hanya Owner yang punya akses multi-cabang / HQ.

## 4. Simplified Customer Data & POS Specs
- Data pelanggan: Murni Nama (`nama`) dan No. WhatsApp/HP (`no_hp`).
- Invoice number harian: Backend `INV-PJ-[KODE_CABANG]-[YYMMDD]-[00001]`, frontend `PJ-[YYMMDD]-[00001]`.
- Cetakan nota: Format kuantiti berspasi (`1 UNIT`).

## 5. Docker & Backend Execution
- Host OS: Windows. Application runtime: Docker Linux (`soulmate_apps`).
- Selalu eksekusi perintah backend/filesystem (`php artisan storage:link`, `php artisan migrate`, `php artisan optimize:clear`, dsb.) di dalam Docker: `docker exec soulmate_apps php artisan ...`.
- Windows Junction ≠ Linux Symlink. `storage:link` wajib dibuat di dalam container Linux agar tidak menghasilkan 403 Forbidden.
- Gunakan `Storage::disk('public')` dan URL relatif `storage/...`.

## 6. Komunikasi & Edukasi Konsep
- Jelaskan istilah teknis IT dengan analogi sederhana dan ramah pemula sebelum detail teknis.
- Mode komunikasi: ringkas, padat, hemat token (Caveman mode).

## 7. Graphify Auto-Update
- Jalankan `python -m graphify.cli update` setiap selesai melakukan perubahan kode.
