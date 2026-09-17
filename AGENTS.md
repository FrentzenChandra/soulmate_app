# Workspace Agent Guidelines

## 1. Graphify Auto-Update
- Jalankan auto-update graphify (via `python -m graphify.cli update`) setiap kali terjadi perubahan kode, penambahan fitur, atau commit di project.
- Gunakan `graphify query` untuk menelusuri relasi kode, god nodes, dan dependensi sebelum melakukan refaktor.

## 2. UI & Design Standards (Anti-AI Slop, Anti-Mojibake & Loading/Skeleton)
- **Dilarang keras memakai gradient berlebihan / multi-color mesh gradient (AI slop)** kecuali diperintahkan secara eksplisit oleh user.
- Gunakan solid clean modern color palette: Brand Purple (`#5E17EB`), Tech Blue (`#0052FF`), Accent Pink (`#E6007A`), dan netral gray/slate dengan white cards, clean crisp borders, dan subtle shadows.
- **Anti-Mojibake & Tipografi Bersih:** Dilarang menggunakan karakter UTF-8 rentan encoding rusak seperti raw bullet (`•`), em-dash (`—`), checkmark mentah (`✓`), atau emoji di kode Blade. Gunakan HTML entity (`&bull;`, `&middot;`), SVG icon, atau Tailwind CSS round dots (`<span class="inline-block w-1 h-1 rounded-full bg-gray-300"></span>`).
- **Placeholder Jelas & Manusiawi:** Placeholder form input WAJIB deskriptif dalam bahasa Indonesia (contoh: `placeholder="Masukkan kata sandi saat ini..."`), BUKAN placeholder karakter dummy/bullet acak (`••••••••`).
- **Standar Loading & Skeleton Universal:**
  * **Upload Foto/Media:** Loading indicator WAJIB terpusat di tengah parent (`flex-col items-center justify-center`) dengan spinner dan progress bar animasi di bagian tengah container.
  * **Aksi Form (POST, PUT/PATCH, DELETE, EDIT):** Semua tombol aksi WAJIB dilengkapi `wire:loading` spinner, status disabled (`wire:loading.attr="disabled"`), dan teks proses deskriptif.
  * **Query / Data Fetching (GET, Search, Filter, Sort):** Tabel dan katalog grid WAJIB menampilkan **Skeleton Loader** (`<x-table-skeleton />` / `<x-grid-skeleton />`) dengan efek shimmer `animate-pulse` saat request query berlangsung.

## 3. Global Multi-Word Loose Search Standard
- **Universal Non-Strict Multi-Word Search:** Semua fitur pencarian di seluruh project WAJIB menggunakan tokenized substring matching multi-kata (`explode(' ', $search)`), *case-insensitive* (`LOWER(col) LIKE ?`), dan tidak *strict* per huruf/kata. Pencarian harus mencocokkan setiap kata kunci di berbagai kolom relevan (nama, barcode, no hp, username, kategori, dll).
- Berlaku di Kasir POS, Master Produk, Stok Cabang, Master Pelanggan, Master User, Master Kategori, dsb.

## 4. Branch Obfuscation for Non-Owner Roles
- **Single-Store Experience untuk Staf:** Pengguna dengan peran non-owner (**Superadmin**, **Admin**, **Teknisi**) WAJIB melihat aplikasi seolah-olah hanya 1 toko mandiri ("Soulmate Komputer").
- Dilarang menampilkan label cabang, switcher cabang, tag nama cabang, atau breadcrumb cabang pada navbar, header, notifikasi, dan katalog bagi non-owner.
- Hanya role **Owner** yang memiliki akses ke switcher cabang, konteks Kantor Pusat (HQ), dan visibilitas multi-cabang.

## 5. Simplified Customer Data Standard
- Data pelanggan operasional harian disederhanakan murni menjadi **Nama Pelanggan** (`nama`) dan **Nomor WhatsApp / HP** (`no_hp`).
- Hilangkan kolom foto, alamat rumit, dan tipe keanggotaan/badge di alur cepat kasir & master pelanggan harian.

## 6. Daily Invoice Sequence & Frontend Display
- **Format Backend:** `INV-PJ-[KODE_CABANG]-[YYMMDD]-[00001]` (counter atomik di-reset setiap hari per cabang).
- **Format Frontend:** `PJ-[YYMMDD]-[00001]` (ditampilkan via accessor `no_transaksi_frontend`).
- Format Servis: `INV-SV-[KODE_CABANG]-[YYMMDD]-[00001]` / `SV-[YYMMDD]-[00001]`.
- Cetakan nota: Format kuantiti dan unit wajib berjarak (`{{ $qty }} UNIT`, bukan `1UNIT`).

## 7. Docker & Execution Environment (Windows Host + Linux Container)
- **Eksekusi Perintah Framework:** Semua perintah yang menyentuh filesystem / backend (`php artisan storage:link`, `php artisan migrate`, `php artisan optimize:clear`, dsb.) **WAJIB dieksekusi di dalam Docker container** (`docker exec soulmate_apps ...`), bukan di terminal Windows host.
- **Windows Junction vs Linux Symlink:** `storage:link` dari Windows menghasilkan NTFS Junction yang tidak dapat dibaca oleh Linux container (menyebabkan 403 Forbidden). `storage:link` dari dalam container menghasilkan native Linux symlink.
- **Storage Standard:** Semua upload media menggunakan `Storage::disk('public')` dengan path relatif `storage/...`.

## 8. Edukasi & Penjelasan Konsep IT
- Saat menjelaskan istilah IT teknis (seperti Junction, Symlink, Volume Mount, Docker, Signed URL, dsb.), gunakan bahasa analogi sederhana ("gambarkan seperti menjelaskan ke pemula/bayi") yang mudah dicerna sebelum masuk ke teknis.

## 9. Skills & Capabilities
- Dalam konteks workspace project: Aktifkan seluruh ekosistem skill (Caveman, Ponytail, Graphify, TDD, Surgical Patch, Subagents).
- Pertahankan gaya komunikasi ringkas, padat, dan hemat token (Caveman mode).