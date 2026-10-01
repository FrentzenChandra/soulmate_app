# Context & Architecture Guide: Soulmate Komputer App

Sistem Manajemen Usaha Servis Elektronik (Laptop, Komputer, Printer), Kasir Point of Sale (POS), Manajemen Stok Multi-Cabang, Rekonsiliasi Kas, Modul Transaksi Spreadsheet Tabulator.js, dan Performance Board Target Bulanan.

---

## 1. Tech Stack & Environment

- **Framework**: Laravel 12 (PHP 8.2+)
- **Full-Stack UI Component**: Livewire 3 + Alpine.js
- **Styling**: Tailwind CSS v4 (Mobile-first responsive design)
- **Database**: SQLite (Development / Testing) / MySQL (Production)
- **Containerization**: Docker Linux Container (`soulmate_apps`)
- **Realtime / WebSockets**: Laravel Reverb (Papan Display Antrean Servis)
- **Authentication**: Laravel Jetstream + Fortify (Email & Username Login, 2FA, Profile Management)
- **Spreadsheet Data Grid**: Tabulator.js (Dense mode, font warna biaya ungu `#5E17EB`, pagination options `[200, 1000, 2000, 10000]`)

---

## 2. Struktur Role & Akses Multi-Cabang

Sistem memiliki otorisasi bertingkat dengan pemisahan konteks yang ketat:

1. **Owner (Pemilik Bisnis)**:
   - Akses global ke seluruh cabang dan **Kantor Pusat (HQ)**.
   - Mengelola master cabang, tarif PPN, data pelanggan global, dan seluruh akun staf/owner.
   - Memiliki visibilitas switcher cabang dan laporan finansial konsolidasi multi-cabang.
2. **Superadmin (Admin Cabang)**:
   - Terikat pada 1 cabang tugas.
   - Mengelola operasional cabang: Kasir POS, stok cabang, laporan cabang, dan akun staf cabangnya (`Superadmin`, `Teknisi`, `Admin`, `Bendahara`).
   - Menerima **Single-Store Experience** ("Soulmate Komputer") tanpa switcher cabang atau informasi cabang lain.
3. **Teknisi**:
   - Terikat pada 1 cabang tugas.
   - Mengelola servis masuk, pengerjaan servis, perubahan status, pemilihan jasa & sparepart servis, dan data pelanggan cabang.
4. **Admin (Kasir)**:
   - Terikat pada 1 cabang tugas.
   - Mengelola transaksi kasir POS produk, penagihan nota servis selesai, dan cetak struk thermal.
5. **Bendahara**:
   - Terikat pada 1 cabang tugas untuk pencatatan keuangan dan kas.

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

### B. Master Pegawai & Bagian Dinamis (`/master/pegawai`)
- Pengelolaan daftar pegawai operasional toko/cabang yang terhubung ke transaksi dan performa.
- **Terminologi Bagian (Bukan Role):** Menggunakan istilah **"Bagian"** untuk divisi teknis dan administrasi toko.
- **7 Bagian Default Baseline:** `Teknisi Laptop`, `Teknisi Printer`, `Teknisi Komputer`, `Bendahara`, `Admin Pajak`, `Admin Stok`, `Admin Konten`.
- **Select Distinct Dinamis (Zero Overhead):** Pilihan Bagian ditarik secara dinamis dari `SELECT DISTINCT role FROM pegawais` digabung dengan 7 bagian baseline.
- **Input Bagian Baru Fleksibel:** Form modal menyediakan opsi `+ Tambah Bagian Baru...` dengan input teks tambahan. Saat disimpan, bagian baru tersebut otomatis masuk ke opsi pilihan dan filter.
- **Pewarnaan Badge Cerdas:** Badge bagian otomatis memiliki styling warna Tailwind berbasis kata kunci (Laptop: Biru, Printer: Amber, Komputer: Purple, Bendahara: Emerald, Pajak: Rose, Stok: Cyan, Konten: Indigo, default: Slate/Gray).

### C. Alur & Pemisahan 3 Menu Servis (`/servis/masuk`, `/servis/keluar`, `/servis/batal`)
- **1. Servis Masuk** (`/servis/masuk`): Khusus unit dalam tahap pengerjaan (status: `pengecekan` dan `perbaikan`).
- **2. Servis Keluar** (`/servis/keluar`): Khusus unit yang telah tuntas (status: `selesai` dan `sudah_diambil`).
- **3. Servis Batal** (`/servis/batal`): Khusus unit dengan status `batal` dengan biaya Rp 0.

### D. Kasir POS, Penomoran Invoice Harian & Standar Cetakan (24x14 cm)
- **Kasir POS Produk** (`/kasir/produk`): Penjualan produk retail biasa dengan keranjang belanja, diskon, PPN, opsi Penjualan Non-Real, dan cetak invoice.
- **Nomor Transaksi Atomik Harian**:
  - Backend Penjualan: `INV-PJ-[KODE_CABANG]-[YYMMDD]-[00001]` -> Frontend: `PJ-[YYMMDD]-[00001]`
  - Backend Servis: `INV-SV-[KODE_CABANG]-[YYMMDD]-[00001]` -> Frontend: `SV-[YYMMDD]-[00001]`
- **Standar Cetakan Continuous Form (24 cm x 14 cm)**:
  - Ukuran universal `@page { size: 240mm 140mm; margin: 3mm 6mm; }` (orientasi continuous form horizontal).
  - Tipografi universal **Arial** (`Arial, Helvetica, sans-serif`), proporsional ~12 cm tinggi kertas 1 halaman pas.
  - **Grandtotal Standard:** Seluruh label total akhir pada seluruh nota/cetakan penjualan, faktur servis, POS, dan PDF WAJIB bertuliskan **`Grandtotal`** (Title Case, dilarang `GRANDTOTAL`, `Grand Total`, atau `TOTAL TAGIHAN`).
  - Format kuantiti berspasi (`1 UNIT`).

### E. Manajemen Pengguna (Master User & Soft Delete — `/master/user`)
- Pengelolaan akun sistem / autentikasi (`Owner`, `Superadmin`, `Teknisi`, `Admin`, `Bendahara`).
- **Soft Delete Mechanism**: Model `User` mengimplementasikan `SoftDeletes` (`deleted_at`).
- **Proteksi Hapus**: Dilarang menghapus diri sendiri (*self-lockout guard*), dilarang menghapus akun Owner terakhir (*minimal 1 owner guard*), serta Superadmin terisolasi hanya bisa menghapus staf non-owner di cabangnya sendiri.
- **Integritas Relasi Historis**: Seluruh relasi `belongsTo(User::class)` pada model transaksi, kas, servis, audit, dan serial number menggunakan `->withTrashed()` sehingga nota dan log masa lalu tetap menampilkan nama staf aslinya.

### F. Modul Transaksi Berbasis Tabulator.js (Transaksi FULL & Transaksi Harian)
- **1. Transaksi FULL** (`/transaksi/full` - Superadmin & Owner):
  - Menggunakan library **Tabulator.js** dengan opsi pagination `[200, 1000, 2000, 10000]` (default 200).
  - Rekapitulasi finansial multi-dimensi (Modal HPP, Harga Jual, Laba Bersih, Status Pembayaran, Metode Pembayaran, Pajak PPN, Pegawai).
  - Status pembayaran: Hanya 2 status valid (**`Lunas`** dan **`BON`**).
  - Row-by-Row 1-to-1 Item Breakdown (Item, Biaya, Modal, Laba, Pajak). Kolom Pajak tampil murni `PPN` atau `Non PPN`.
  - Modal Form Sizing: Ukuran form modal transaksi menggunakan batas maksimal `sm:max-w-2xl`.
  - Isolasi Modal Form: Komponen Livewire dilarang keras men-dispatch event tabel Tabulator ke frontend saat modal form sedang terbuka.
  - Dropdown & Kartu Pelanggan: Hanya menampilkan nama pelanggan tanpa nomor telepon.
- **2. Transaksi Harian** (`/transaksi/harian` - Admin & Superadmin):
  - Khusus operasional staf & kasir hari ini.
  - **Transaksi Harian Zero Laba Standard:** SEMUA INFORMASI LABA (PROFIT / MARGIN) DILARANG KERAS DITAMPILKAN pada tabel, modal input/edit, modal detail, dan kartu metrik Transaksi Harian.
  - Status pembayaran: Hanya **`Lunas`** dan **`BON`**.
- **3. Modal Awal & Rekonsiliasi Saldo Kas Harian** (`/modal-awal`):
  - Pencatatan kas modal awal kasir untuk memastikan kesesuaian fisik uang cash.
  - Notifikasi Lonceng (> 10:00 Alert) otomatis jika cabang belum mencatat modal awal.
- **4. Cek Kas Cepat / Audit Laci (Snapshot Kasir)**:
  - Menggantikan Tutup Kas konvensional. Audit kas fisik kapan saja tanpa mengunci transaksi harian.

### G. Modul Saldo Rekening Standalone (`/saldo`)
- Pencatatan saldo kas/rekening bank murni catatan mandiri (*pure record keeping*).
- Model & Tabel: `sumber_saldos` & `saldo_rekenings`.

### H. Modul Target Bulanan (`/target-bulanan`)
- **Performance Board 12 Bulan (Januari &ndash; Desember)**:
  - **Tabel 1: Target Per Divisi** (Laptop, Printer, Komputer, Admin):
    * Target diatur per tahun via modal 5-in-1 "Target Tahunan Divisi" di toolbar atas (nilai target yang diinput berlaku merata untuk setiap bulan Jan &ndash; Des, misal input 200 maka target bulanan tiap bulan adalah 200 transaksi).
    * Kolom ke-2 tabel divisi menampilkan nilai target divisi tersebut.
    * Penyesuaian realisasi manual per bulan tetap didukung secara independen melalui klik per sel bulan.
    * Indikator status pencapaian: `≥ 150%` (Over Target / Tech Blue), `100% - 149%` (Target Tercapai / Emerald), `< 100%` (Belum Tercapai / Rose).
  - **Tabel 2: Target Per Pegawai (Staf Individu)**:
    * Realisasi murni 100% ditarik otomatis dari `transaksi_fulls` berdasarkan `pegawai_id`.
    * Tanpa kolom target/persyaratan, tanpa badge status merah, dan tanpa modal edit per sel (read-only real-time performance summary).
- **Hak Akses Set Target:** Khusus role **Owner** dan **Superadmin**.
- **Slogan Motivasi & Single-Line Control Bar:** Header 1 baris terpadu dengan slogan *"Target Bukan Untuk Ditakuti, Tapi Untuk Dikalahkan!"*.

---

## 4. Standar UI/UX & Desain (Anti-AI Slop, Zero Placeholder & Anti-Mojibake)

1. **Color Palette**:
   - Primary: `#5E17EB` (Electric Purple)
   - Secondary: `#0052FF` (Tech Blue)
   - Accent: `#E6007A` (Vivid Pink / Magenta)
   - Neutral: `#F8F9FA` / `#FFFFFF` (White cards, subtle borders `border-gray-200`, no messy mesh gradients).
2. **Anti-Mojibake & Tipografi Bersih**:
   - Dilarang memakai karakter rentan encoding rusak (`•`, `—`, `✓`, raw emoji). Gunakan Clean Outline SVG atau HTML entities (`&bull;`, `&middot;`).
   - **Zero Placeholder Standard**: Seluruh form input, filter, modal, dan search bar TIDAK BOLEH menggunakan atribut `placeholder`. Gunakan label form yang tegas.
3. **Standar Tanda Bintang Wajib Isi Merah**:
   - Tanda bintang wajib isi wajib berwarna merah: `<span class="text-rose-500 font-bold">*</span>`.
4. **Standar Loading & Skeleton Loader**:
   - **Aksi Form & Tombol (Simpan, Update, Hapus, dsb.)**: Wajib menggunakan standar Kasir Produk: SVG spinner `animate-spin` Heroicons, `wire:loading wire:target="..."`, `disabled:opacity-50`, dan teks transisi proses deskriptif.
   - **Data Fetching (Tabel & Grid)**: Wajib menampilkan `<x-table-skeleton />` / `<x-grid-skeleton />` shimmer `animate-pulse`.
5. **Universal Multi-Word Loose Search**:
   - Semua kolom pencarian menggunakan substring tokenized matching multi-kata (`explode(' ', $search)`), case-insensitive (`LOWER() LIKE ?`), non-strict per karakter.
6. **Sidebar Auto-Scroll Menu Aktif**:
   - Navigasi sidebar (`<nav>`) otomatis melakukan scroll vertikal (`scrollIntoView({ block: 'center', behavior: 'smooth' })`) memusatkan menu yang sedang aktif.
7. **Universal Table Font Size Standard**:
   - Komponen `<x-table-size-selector />` di toolbar filter semua tabel index (S: 11px, M: 12px, L: 13px) dengan `localStorage`.
8. **Universal Date & Period Filter Standard (Format Transaksi FULL)**:
   - Tombol tab mode periode: `Semua`, `Hari Ini`, `Rentang Tanggal`, `Bulanan`, `Tahunan` dengan input tanggal dinamis di sisi kanan otomatis.

---

## 5. Standar Eksekusi Lingkungan (Docker)

- **Artisan & Migrasi**: Seluruh perintah framework (`php artisan migrate`, `php artisan test`, `php artisan storage:link`, dll.) **WAJIB dieksekusi di dalam Docker container**:
  ```bash
  docker exec soulmate_apps php artisan ...
  ```
- **Storage Symlink**: Wajib dibuat dari dalam Linux container untuk menghindari NTFS Junction Windows yang menyebabkan error 403 Forbidden.
- **Pengujian Tertarget**: Eksekusi pengujian secara spesifik per modul yang diubah (`docker exec soulmate_apps php artisan test --filter <TestClass>`).
