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
### D. Manajemen Pengguna (Master User & Soft Delete)
- **Master User** (`/master/user`):
  - Pengelolaan akun pengguna (`Owner`, `Superadmin`, `Teknisi`, `Admin`).
  - **Soft Delete Mechanism**: Model `User` mengimplementasikan `SoftDeletes` (`deleted_at`).
  - **Proteksi Hapus**: Dilarang menghapus diri sendiri (*self-lockout guard*), dilarang menghapus akun Owner terakhir (*minimal 1 owner guard*), serta Superadmin terisolasi hanya bisa menghapus staf non-owner di cabangnya sendiri.
  - **Integritas Relasi Historis**: Seluruh relasi `belongsTo(User::class)` pada model transaksi, kas, servis, audit, dan serial number menggunakan `->withTrashed()` sehingga nota dan log masa lalu tetap menampilkan nama staf aslinya.

### E. Manajemen Kas Keluar & Serial Number (Superadmin)
- **Kas Keluar** (`/kas-keluar`):
  - Menggunakan library spreadsheet interaktif **Tabulator.js**.
  - Pencatatan pengeluaran kas cabang dengan waktu input, keterangan, total uang, sumber dana (Master Sumber Dana: Cash, BCA, TF BCA), dan kategori (Master Kategori Kas Keluar).
  - Dilengkapi multi-mode filter periode (Semua, Hari Ini, Rentang Tanggal, Bulanan, Tahunan), Group by Tanggal, Column Filters interaktif, dan Table Font Size Selector (S/M/L).
- **Serial Number** (`/serial-number`):
  - Registrasi & pelacakan unit/perangkat dengan kolom Nama, Merk Tipe Spek, Serial Number (SN), dan Distributor.

### F. Modul Transaksi Berbasis Tabulator.js (Transaksi FULL & Transaksi Harian)
- **1. Transaksi FULL** (`/transaksi/full` - Superadmin & Owner):
  - Menggunakan library **Tabulator.js**.
  - Rekapitulasi: Penjualan Real (Cash) + Penjualan Servis Selesai/Diambil (Cash) - Kas Keluar (Cash).
  - Kolom Data: Modal (HPP), Harga Jual, Status Pembayaran (hanya **Lunas** dan **BON**), Transaksi (Penjualan / Servis auto-detect), Pajak (PPN atau `-`), Metode Pembayaran, Teknisi (pengguna role teknisi di cabang terkait).
  - Kategori Perangkat Servis: Mendukung 4 kategori (`laptop`, `komputer`, `printer`, `lainnya`) saat tipe transaksi adalah servis. Kategori ini tersimpan di backend dan dapat dilihat di modal detail transaksi.
  - **Modal Input Transaksi Baru Terstruktur**:
    - Input waktu transaksi fokus jam (`<input type="time">`) dengan badge tanggal aktif di header modal.
    - Seleksi tipe transaksi dinamis warna (Ungu untuk Penjualan, Kuning/Amber untuk Servis).
    - Mode Pelanggan ringkas: Default `Konsumen`, input WhatsApp tersembunyi pada mode umum.
    - Baris Item penuh (Item full-width) dengan urutan kolom: Kuantitas, Biaya, Modal.
    - Baris Pembayaran & Pegawai terpisah rapi dengan custom dropdown Pegawai interaktif dan Pajak PPN bersih (`Tanpa PPN` tanpa persentase `(0%)`).
- **2. Transaksi Harian** (`/transaksi/harian` - Admin & Superadmin):
  - Menggunakan library **Tabulator.js**.
  - Histori transaksi real hari ini saja (*today only*): Penjualan produk & Servis diambil.
  - Kolom Data: Modal, Harga Jual, Nett (Harga Jual - Modal), Pajak, Metode Pembayaran, Keterangan, Status Pembayaran (**Lunas** / **BON**).
- **3. Modal Awal & Rekonsiliasi Saldo Kas Harian** (`/modal-awal`):
  - Pencatatan kas modal awal kasir untuk memastikan kesesuaian fisik uang cash:
    $$\text{Total Cash Sistem} = \text{Modal Awal (Cash)} + \text{Penjualan Cash} + \text{Servis Cash} - \text{Kas Keluar Cash}$$
  - Deteksi status cash harian (klop, minus, atau surplus).
- **4. Cek Kas Cepat / Audit Laci (Snapshot Kasir Tanpa Kunci Transaksi)**:
  - *Menggantikan fitur Tutup Kasir / Closing Kasir konvensional yang sudah tidak relevan*.
  - Kasir, Superadmin, atau Owner dapat melakukan pengecekan/audit kas fisik di laci kapan saja (pagi, siang, sore, malam) tanpa mengunci transaksi.
  - Alur cepat:
    1. Klik tombol **Cek Kas Cepat / Audit Laci**.
    2. Input nominal fisik di laci + catatan (opsional).
    3. Sistem otomatis menghitung snapshot selisih: $\text{Selisih} = \text{Kas Fisik} - \text{Kas Sistem}$.
    4. Simpan log snapshot ke tabel `cek_kas_logs` (`id`, `cabang_id`, `user_id`, `waktu_cek`, `kas_sistem`, `kas_fisik`, `selisih`, `catatan`).
    5. Riwayat audit hari ini langsung tampil di bawah modal/halaman transaksi.
  - Kasir tetap dapat melanjutkan transaksi kasir seperti biasa.

### G. Modul Saldo Rekening Standalone (`/saldo`)
- Pencatatan saldo kas/rekening bank murni catatan mandiri (*pure record keeping*).
- Model & Tabel: `sumber_saldos` (Master Kategori: *Pribadi*, *CV. SM PAMEN SEJAHTERA*, dsb.) & `saldo_rekenings` (Rekening Bank, Saldo, Atas Nama, No. Rekening).
- UI 1 Tabel Terpadu dengan Dynamic Tabs filter per Sumber Saldo, Top Metric Card Total Saldo + dynamic breakdown pills, Quick Saldo update, dan Bottom summary card.

### H. Modul Target Bulanan (`/target-bulanan`)
- **Performance Board 12 Bulan (Januari - Desember)**:
  - Tabel 1: **Target Per Divisi** (4 Divisi: Laptop, Printer, Komputer, Admin).
  - Tabel 2: **Target Per Staf / User** (seluruh staf bertugas di cabang terpilih).
- **Kalkulasi Realisasi Otomatis dari Transaksi FULL**:
  - Divisi Laptop, Printer, Komputer: Realisasi ditarik dari `transaksi_fulls` di mana `tipe_transaksi = 'servis'` dan `kategori_perangkat = '<divisi>'`.
  - Divisi Admin: Realisasi ditarik dari `transaksi_fulls` di mana `tipe_transaksi = 'penjualan'`.
  - Per Individu / Staf: Realisasi ditarik dari `transaksi_fulls` di mana `created_by = <user_id>`.
- **Pengelolaan Target Tahunan**:
  - Input target pertahun per cabang melalui 2 modal terpisah (Modal Target Divisi & Modal Target Staf), otomatis dialokasikan bulanan.
  - Hak akses set target: Khusus **Owner** dan **Superadmin**.
- **Indikator Visual Pencapaian**:
  - Over Target `≥ 150%`: Badge Tech Blue / Indigo dengan icon trending-up.
  - Target Tercapai `100% - 149%`: Badge Emerald Hijau dengan icon check-circle.
  - Belum Tercapai `< 100%`: Badge Rose Merah dengan icon x-circle.
  - Bulan mendatang / belum berjalan: Tanda `-`.
- **Slogan Motivasi**: *"Target Bukan Untuk Ditakuti, Tapi Untuk Dikalahkan!"*.
- **Toolbar 1 Baris**: Menggabungkan judul, slogan, filter tahun, switcher cabang (Owner HQ), dan tombol kelola target tahunan.

### I. Metrik Pendapatan Topbar Superadmin
- Header navbar khusus Superadmin menampilkan ringkasan performa finansial cabang secara real-time:
  - Total Penjualan Produk, Total Servis, HPP (Total Modal Barang Terjual), dan Pendapatan Nett (Penjualan - HPP).

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
3. **Standar Loading & Skeleton Loader**:
   - **Aksi Form & Tombol (Simpan, Update, Hapus, dsb.)**: Wajib menggunakan standar Kasir Produk: SVG spinner `animate-spin` (`<svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>`), `wire:loading wire:target="..."`, `disabled:opacity-50`, dan teks transisi proses deskriptif.
   - **Data Fetching (Tabel & Grid)**: Wajib menampilkan `<x-table-skeleton />` / `<x-grid-skeleton />` shimmer `animate-pulse` yang sinkron presisi dengan lebar kolom tabel real.
4. **Universal Multi-Word Loose Search**:
   - Semua kolom pencarian menggunakan substring tokenized matching multi-kata (`explode(' ', $search)`), case-insensitive (`LOWER() LIKE ?`), non-strict per karakter.
5. **Format Rupiah Terintegrasi**:
   - Input harga menggunakan komponen Alpine.js `<x-input-rupiah>` dengan separator ribuan otomatis (`250.000`), tetap mengikat nilai integer/float ke backend.
6. **Sidebar Auto-Scroll Menu Aktif**:
   - Navigasi sidebar (`<nav>`) otomatis melakukan scroll vertikal (`scrollIntoView({ block: 'center', behavior: 'smooth' })`) memusatkan menu yang sedang aktif pada desktop dan saat drawer mobile dibuka.

---

## 5. Standar Eksekusi Lingkungan (Docker)

- **Artisan & Migrasi**: Seluruh perintah framework (`php artisan migrate`, `php artisan test`, `php artisan storage:link`, dll.) **WAJIB dieksekusi di dalam Docker container**:
  ```bash
  docker exec soulmate_apps php artisan ...
  ```
- **Storage Symlink**: Wajib dibuat dari dalam Linux container untuk menghindari NTFS Junction Windows yang menyebabkan error 403 Forbidden.
- **Knowledge Graph**: Jalankan `python -m graphify.cli update` setiap kali terjadi perubahan kode atau penambahan fitur.
