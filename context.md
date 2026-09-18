# Context & Architecture Guide: Soulmate Komputer App

Sistem Manajemen Usaha Servis Elektronik (Laptop, Komputer, Printer), Kasir Point of Sale (POS), Manajemen Stok Multi-Cabang, dan Marketplace Publik Terintegrasi WhatsApp.

---

## 1. Tech Stack & Environment

- **Framework**: Laravel 12 (PHP 8.2+)
- **Full-Stack UI Component**: Livewire 3 + Alpine.js
- **Styling**: Tailwind CSS v4 (Mobile-first responsive design)
- **Database**: SQLite (Development / Testing) / MySQL (Production)
- **Containerization**: Docker Linux Container (`soulmate_apps`)
- **Realtime / WebSockets**: Laravel Reverb (Papan Display Antrean Servis)
- **Authentication**: Laravel Jetstream + Fortify (Email & Username Login, 2FA, Profile Management)

---

## 2. Struktur Role & Akses Multi-Cabang

Sistem memiliki otorisasi bertingkat dengan pemisahan konteks yang ketat:

1. **Owner (Pemilik Bisnis)**:
   - Akses global ke seluruh cabang dan **Kantor Pusat (HQ)**.
   - Mengelola master cabang, tarif PPN, data pelanggan global, dan seluruh akun staf/owner.
   - Memiliki visibilitas switcher cabang dan laporan finansial konsolidasi multi-cabang.
2. **Superadmin (Admin Cabang)**:
   - Terikat pada 1 cabang tugas.
   - Mengelola operasional cabang: Kasir POS, stok cabang, laporan cabang, dan akun staf cabangnya (`Superadmin`, `Teknisi`, `Admin`).
   - Menerima **Single-Store Experience** ("Soulmate Komputer") tanpa switcher cabang atau informasi cabang lain.
3. **Teknisi**:
   - Terikat pada 1 cabang tugas.
   - Mengelola servis masuk, pengerjaan servis, perubahan status, pemilihan jasa & sparepart servis, dan data pelanggan cabang.
4. **Admin (Kasir)**:
   - Terikat pada 1 cabang tugas.
   - Mengelola transaksi kasir POS produk, penagihan nota servis selesai, dan cetak struk thermal.

---

## 3. Domain & Pemisahan Master Data

### A. Master Produk (`/master/produk`) vs Master Servis (`/master/servis`)
- **Master Produk**:
  - Barang fisik untuk dijual di Kasir POS atau ditampilkan di website marketplace publik.
  - Katalog terpusat dengan matriks stok multi-cabang (`stoks`).
  - Mendukung barcode scanner, deteksi duplikasi instan, toggle `ready_marketplace`, dan kompresi WebP.
  - **Bebas dari istilah sparepart servis**.
- **Master Servis**:
  - Jasa perbaikan (tanpa stok fisik) & Suku Cadang pengganti (dengan stok fisik cabang & threshold alert).
  - Khusus digunakan untuk transaksi dan cetak nota servis (terpisah dari master produk).
  - Memiliki tabel tersendiri: `master_servis` dan `master_servis_stoks`.
  - Tidak pernah ditampilkan di katalog publik/marketplace.
  - Form modal terstruktur: Tipe (Jasa vs Sparepart), Identitas, Harga Modal & Jual (format rupiah), Alokasi Stok Cabang (khusus sparepart), Garansi Default (preset 0-90 hari), Catatan Teknis, dan Checklist Status Aktif di bawah Catatan.

### B. Form Servis Masuk (`/servis/masuk` / Tambah Servis)
- **Kategori Perangkat**: Urutan standar: `Laptop`, `Printer` (tanpa "/Scanner"), `Komputer` (tanpa "PC Komputer"), `Lainnya`.
- **Penyederhanaan Input**:
  - Kolom *Nomor Seri / Serial Number* dan *Kondisi Fisik / Bodi Awal* **dihapus**.
  - Pilihan Kelengkapan: `Charger`, `Box`, `Flashdisk`, `Harddisk` (dipisah), opsi `Mouse` dihapus.
  - Kolom *Catatan Servis* diposisikan di sebelah kiri dengan layout yang lebih luas dan nyaman.

### C. Kasir POS & Penagihan Servis
- **Kasir POS Produk** (`/kasir/produk`): Penjualan produk retail biasa dengan keranjang belanja, diskon, PPN, opsi Penjualan Non-Real, dan cetak struk 58mm/80mm.
- **Penagihan Servis**: Dilakukan dari Index Servis pada item berstatus `selesai` via tombol *Tagih & Cetak* (terkunci otomatis setelah dibayar).
- **Nomor Transaksi Atomik Harian**:
  - Backend Penjualan: `INV-PJ-[KODE_CABANG]-[YYMMDD]-[00001]` -> Frontend: `PJ-[YYMMDD]-[00001]`
  - Backend Servis: `INV-SV-[KODE_CABANG]-[YYMMDD]-[00001]` -> Frontend: `SV-[YYMMDD]-[00001]`
  - Cetakan Struk: Format kuantiti berspasi (`1 UNIT`).

---

## 4. Standar UI/UX & Desain (Anti-AI Slop & Anti-Mojibake)

1. **Color Palette**:
   - Primary: `#5E17EB` (Electric Purple)
   - Secondary: `#0052FF` (Tech Blue)
   - Accent: `#E6007A` (Vivid Pink / Magenta)
   - Neutral: `#F8F9FA` / `#FFFFFF` (White cards, subtle borders `border-gray-200`, no messy mesh gradients).
2. **Anti-Mojibake & Tipografi Bersih**:
   - Dilarang memakai karakter rentan encoding rusak (`•`, `—`, `✓`, raw emoji). Gunakan Clean Outline SVG atau HTML entities (`&bull;`, `&middot;`).
   - Placeholder form wajib deskriptif dan manusiawi dalam bahasa Indonesia.
3. **Standar Loading & Skeleton Loader**:
   - Tombol aksi form (Simpan, Update, Hapus): Wajib memiliki `wire:loading` spinner, teks proses deskriptif, dan `wire:loading.attr="disabled"`.
   - Data Fetching (Tabel & Grid): Wajib menampilkan `<x-table-skeleton />` shimmer `animate-pulse` yang sinkron presisi dengan lebar kolom tabel real.
4. **Universal Multi-Word Loose Search**:
   - Semua kolom pencarian menggunakan substring tokenized matching multi-kata (`explode(' ', $search)`), case-insensitive (`LOWER() LIKE ?`), non-strict per karakter.
5. **Format Rupiah Terintegrasi**:
   - Input harga menggunakan komponen Alpine.js `<x-input-rupiah>` dengan separator ribuan otomatis (`250.000`), tetap mengikat nilai integer/float ke backend.

---

## 5. Standar Eksekusi Lingkungan (Docker)

- **Artisan & Migrasi**: Seluruh perintah framework (`php artisan migrate`, `php artisan test`, `php artisan storage:link`, dll.) **WAJIB dieksekusi di dalam Docker container**:
  ```bash
  docker exec soulmate_apps php artisan ...
  ```
- **Storage Symlink**: Wajib dibuat dari dalam Linux container untuk menghindari NTFS Junction Windows yang menyebabkan error 403 Forbidden.
- **Knowledge Graph**: Jalankan `python -m graphify.cli update` setiap kali terjadi perubahan kode atau penambahan fitur.
