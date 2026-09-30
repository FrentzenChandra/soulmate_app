# Soulmate Komputer App

Sistem Manajemen Operasional & Finansial Multi-Cabang untuk Usaha Servis Elektronik (Laptop, Komputer, Printer), Kasir Point of Sale (POS), Manajemen Stok Terpadu, Rekonsiliasi Kas, dan Performance Board Target Bulanan.

---

## 🚀 Fitur Utama & Modul Sistem

### 1. Manajemen Servis Multi-Divisi
- **Pemisahan Alur Servis**: Servis Masuk (`/servis/masuk`), Servis Keluar / Selesai (`/servis/keluar`), Servis Batal (`/servis/batal`), dan Servis Non Real (`/servis/non-real`).
- **Sub-Caption No. Servis Penginput**: Sub-caption No. Servis menampilkan nama user/staf yang menginput tiket pengerjaan.
- **4 Kategori Perangkat**: `Laptop`, `Printer`, `Komputer`, dan `Lainnya`.
- **Master Servis Khusus**: Jasa perbaikan & suku cadang (`master_servis`) dengan alokasi stok cabang & threshold alert terpisah dari produk retail.
- **Papan Display Antrean Smart TV**: Display antrean real-time WebSocket (`/display/{kode_cabang}`).

### 2. Kasir POS & Transaksi Tabulator.js
- **Kasir Produk POS (`/kasir/produk`)**: Penjualan produk retail biasa, barcode scanner live, diskon, PPN, opsi Penjualan Non-Real, cetak nota thermal (58mm / 80mm), dan **Single-Input Customer Autocomplete**.
- **Transaksi FULL (`/transaksi/full`)**: Rekapitulasi finansial multi-dimensi (Modal HPP, Harga Jual, Status Pembayaran, Metode Pembayaran, Pajak PPN, Pegawai/Teknisi) berbasis **Tabulator.js**.
- **Status Transaksi Ringkas**: Hanya menggunakan 2 status pembayaran (**`Lunas`** dan **`BON`**).
- **Transaksi Harian (`/transaksi/harian`)**: Audit transaksi real khusus hari ini (*today only*).

### 3. Target Bulanan (Performance Board 12 Bulan — `/target-bulanan`)
- Matriks evaluasi 12 bulan (Januari s/d Desember) untuk 4 Divisi (`Laptop`, `Printer`, `Komputer`, `Admin`) dan per Staf (`User`).
- Realisasi otomatis terhubung ke `transaksi_fulls` dan pengelolaan per sel mandiri.
- **Kolom Total Persyaratan 1 Tahun**: Akumulasi total persyaratan tahunan di sisi kanan tabel.
- Input target & realisasi oleh **Owner** dan **Superadmin**.
- Status visual interaktif: Over Target (`≥150%`), Target Tercapai (`100-149%`), Belum Tercapai (`<100%`).
- Slogan motivasi: *"Target Bukan Untuk Ditakuti, Tapi Untuk Dikalahkan!"*.

### 4. Manajemen Kas & Keuangan
- **Modal Awal Kasir (`/modal-awal`)**: Pencatatan kas buka toko harian dengan proteksi duplikasi & alert notifikasi lonceng > 10:00.
- **Cek Kas Cepat / Audit Laci**: Snapshot audit fisik laci kasir kapan saja tanpa mengunci transaksi.
- **Kas Keluar (`/kas-keluar`)**: Pencatatan beban pengeluaran dengan Master Sumber Dana & Kategori Kas.
- **Saldo Rekening Standalone (`/saldo`)**: Pencatatan rekening bank dan saldo mandiri.
- **Serial Number Tracker (`/serial-number`)**: Pelacakan serial number perangkat & distributor.
- **Modular Menu Daily Logging**: Log terstruktur per modul di `storage/logs/{menu}/menu-{menu}-YYYY-MM-DD.log`.

### 5. Multi-Cabang & Hak Akses Berjenjang
- **Owner**: Akses global seluruh cabang & Kantor Pusat (HQ), kelola PPN, kelola master cabang, dan switcher multi-cabang.
- **Superadmin**: Single-store experience toko mandiri untuk mengelola operasional cabang dan akun staf cabangnya.
- **Teknisi & Admin**: Akses operasional harian sesuai jobdesk (servis & kasir POS).
- **User Soft Deletes & Integrity**: Perlindungan data historis menggunakan `SoftDeletes` dan `->withTrashed()`.

---

## 🛠️ Tech Stack & Ekosistem

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend / Fullstack**: Livewire 3, Alpine.js, Tailwind CSS v4
- **Spreadsheet Data Grid**: Tabulator.js
- **Database**: SQLite (Dev / Testing) / PostgreSQL / MySQL (Production)
- **Container**: Docker Linux (`soulmate_apps`)
- **Realtime**: Laravel Reverb

---

## 🐳 Panduan Eksekusi Docker

Seluruh perintah framework dan pengujian **WAJIB** dijalankan di dalam Linux container:

```bash
# Migrasi Database
docker exec soulmate_apps php artisan migrate

# Clear Cache & Optimize
docker exec soulmate_apps php artisan optimize:clear

# Menjalankan Test Tertarget
docker exec soulmate_apps php artisan test --filter TargetBulananTest
docker exec soulmate_apps php artisan test --filter TransaksiFullTest
```
