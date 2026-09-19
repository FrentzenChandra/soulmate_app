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

### B. Alur & Pemisahan 3 Menu Servis (`/servis/masuk`, `/servis/keluar`, `/servis/batal`)
- **1. Servis Masuk** (`/servis/masuk`):
  - Khusus unit dalam tahap pengerjaan (status: `pengecekan` dan `perbaikan`).
  - Fitur tambah item/komponen jasa & sparepart dari Master Servis, rincian masa garansi per item, dan tombol "Selesai" untuk mengubah status menjadi `selesai` serta mencetak nota tanda terima / faktur.
- **2. Servis Keluar** (`/servis/keluar`):
  - Khusus unit yang telah tuntas (status: `selesai` dan `sudah_diambil`).
  - Default sorting memprioritaskan unit yang belum diambil (`selesai` muncul di atas, `sudah_diambil` di bawah).
  - Validasi penyerahan unit & pelunasan. Unit yang berstatus `sudah_diambil` terkunci otomatis dan tidak dapat diedit lagi.
- **3. Servis Batal** (`/servis/batal`):
  - Khusus unit dengan status `batal`.
  - Dilengkapi fitur penyerahan unit batal tanpa tagihan biaya (Rp 0) serta cetak tanda terima pembatalan.

### C. Kasir POS, Invoice & Standardisasi Cetak
- **Kasir POS Produk** (`/kasir/produk`): Penjualan produk retail biasa dengan keranjang belanja, diskon, PPN, opsi Penjualan Non-Real, dan cetak invoice.
- **Nomor Transaksi Atomik Harian**:
  - Backend Penjualan: `INV-PJ-[KODE_CABANG]-[YYMMDD]-[00001]` -> Frontend: `PJ-[YYMMDD]-[00001]`
### D. Manajemen Kas Keluar & Serial Number (Superadmin)
- **Kas Keluar** (`/kas-keluar`):
  - Pencatatan pengeluaran kas cabang dengan waktu input, keterangan, total uang, sumber dana (Master Sumber Dana: Cash, BCA, TF BCA), dan kategori (Master Kategori Kas Keluar).
- **Serial Number** (`/serial-number`):
  - Registrasi & pelacakan unit/perangkat dengan kolom Nama, Merk Tipe Spek, Serial Number (SN), dan Distributor.

### E. Modul Transaksi Berbasis Tabulator.js (Transaksi FULL & Transaksi Harian)
- **1. Transaksi FULL** (`/transaksi/full` - Superadmin):
  - Menggunakan library **Tabulator.js**.
  - Rekapitulasi: Penjualan Real (Cash) + Penjualan Servis Selesai/Diambil (Cash) - Kas Keluar (Cash).
  - Kolom Data: Modal (HPP), Harga Jual, Status Pembayaran (Lunas, Bon), Transaksi (Penjualan / Servis auto-detect), Pajak (PPN atau `-`), Metode Pembayaran, Teknisi (pengguna role teknisi di cabang terkait).
- **2. Transaksi Harian** (`/transaksi/harian` - Admin & Superadmin):
  - Menggunakan library **Tabulator.js**.
  - Histori transaksi real hari ini saja (*today only*): Penjualan produk & Servis diambil.
  - Kolom Data: Modal, Harga Jual, Nett (Harga Jual - Modal), Pajak, Metode Pembayaran, Keterangan.
- **3. Modal Awal & Rekonsiliasi Saldo Kas Harian** (`/modal-awal`):
  - Pencatatan kas modal awal kasir untuk memastikan kesesuaian fisik uang cash:
    $$\text{Total Cash Sistem} = \text{Modal Awal (Cash)} + \text{Penjualan Cash} + \text{Servis Cash} - \text{Kas Keluar Cash}$$
  - Deteksi status cash harian (klop, minus, atau surplus).
- **4. Tutup Kasir & Riwayat Tutup Kas** (`/tutup-kas`):
  - Fitur closing kasir di akhir hari/shift pada halaman Transaksi Harian untuk mencocokkan fisik uang tunai laci dengan hitungan sistem.
  - Kasir menginput **Kas Fisik** (hasil hitung uang riil di laci).
  - Sistem menghitung selisih:
    $$\text{Selisih} = \text{Kas Fisik} - \text{Kas Sistem}$$
    - `Rp 0` (Klop / Cocok)
    - Negatif (Minus / Tekor)
    - Positif (Surplus / Lebih)
  - Riwayat tersimpan pada tabel `tutup_kas` dan ditampilkan di menu **Tutup Kas** lengkap dengan rincian modal awal, omzet cash, kas keluar, kas fisik, selisih, dan catatan kasir.

### F. Metrik Pendapatan Topbar Superadmin
- Header navbar khusus Superadmin menampilkan ringkasan performa finansial cabang secara real-time:
  - Total Penjualan Produk, Total Servis, HPP (Total Modal Barang Terjual), dan Pendapatan Nett (Penjualan - HPP).

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
