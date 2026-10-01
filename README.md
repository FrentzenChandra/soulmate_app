# Soulmate Komputer App

Sistem Manajemen Operasional & Finansial Multi-Cabang untuk Usaha Servis Elektronik (Laptop, Komputer, Printer), Kasir Point of Sale (POS), Manajemen Stok Terpadu, Modul Transaksi Spreadsheet Tabulator.js, Rekonsiliasi Kas, dan Performance Board Target Bulanan.

---

## 🚀 Fitur Utama & Modul Sistem

### 1. Manajemen Servis Multi-Divisi
- **Pemisahan 3 Alur Servis**: Servis Masuk (`/servis/masuk`), Servis Keluar / Selesai (`/servis/keluar`), dan Servis Batal (`/servis/batal`).
- **4 Kategori Perangkat**: `Laptop`, `Printer`, `Komputer`, dan `Lainnya`.
- **Master Servis Khusus**: Jasa perbaikan & suku cadang (`master_servis`) dengan alokasi stok cabang & threshold alert terpisah dari produk retail.
- **Papan Display Antrean Smart TV**: Display antrean real-time WebSocket (`/display/{kode_cabang}`).

### 2. Kasir POS & Transaksi Tabulator.js
- **Kasir Produk POS (`/kasir/produk`)**: Penjualan produk retail biasa, barcode scanner live, diskon, PPN, opsi Penjualan Non-Real, dan cetak nota thermal (58mm / 80mm).
- **Transaksi FULL (`/transaksi/full`)**: Rekapitulasi finansial multi-dimensi (Modal HPP, Harga Jual, Laba Bersih, Status Pembayaran, Metode Pembayaran, Pajak PPN, Pegawai) berbasis **Tabulator.js**.
- **Status Transaksi Ringkas**: Hanya menggunakan 2 status pembayaran (**`Lunas`** dan **`BON`**).
- **Transaksi Harian (`/transaksi/harian`)**: Audit transaksi real khusus hari ini (*today only*) dengan **Zero Laba Standard** (informasi laba/profit/margin dilarang ditampilkan untuk staf operasional & kasir).

### 3. Target Bulanan (Performance Board 12 Bulan — `/target-bulanan`)
- **Tabel Divisi (`Laptop`, `Printer`, `Komputer`, `Admin`)**: Target diatur per tahun via modal 5-in-1 di toolbar atas (nilai target yang diinput berlaku merata untuk setiap bulan Jan &ndash; Des, misal input 200 maka target bulanan tiap bulan adalah 200 transaksi). Kolom ke-2 menampilkan nilai target tersebut.
- **Tabel Pegawai (Staf Individu)**: Realisasi murni 100% ditarik otomatis dari `transaksi_fulls` berdasarkan `pegawai_id`. Read-only performance summary tanpa kolom target dan tanpa badge status merah.
- Slogan motivasi: *"Target Bukan Untuk Ditakuti, Tapi Untuk Dikalahkan!"*.

### 4. Master Pegawai & Bagian Dinamis (`/master/pegawai`)
- Menggunakan istilah **"Bagian"** (menggantikan "Role").
- **7 Bagian Default Baseline**: `Teknisi Laptop`, `Teknisi Printer`, `Teknisi Komputer`, `Bendahara`, `Admin Pajak`, `Admin Stok`, `Admin Konten`.
- **Select Distinct Dinamis**: Pilihan bagian ditarik secara dinamis dari database digabung dengan 7 bagian baseline, dilengkapi opsi `+ Tambah Bagian Baru...` pada modal form.
- Pewarnaan badge otomatis berbasis kata kunci bagian.

### 5. Manajemen Kas & Keuangan
- **Modal Awal Kasir (`/modal-awal`)**: Pencatatan kas buka toko harian dengan proteksi duplikasi & alert notifikasi lonceng > 10:00.
- **Cek Kas Cepat / Audit Laci**: Snapshot audit fisik laci kasir kapan saja tanpa mengunci transaksi harian.
- **Kas Keluar (`/kas-keluar`)**: Pencatatan beban pengeluaran dengan Master Sumber Dana & Kategori Kas.
- **Saldo Rekening Standalone (`/saldo`)**: Pencatatan rekening bank dan saldo mandiri.
- **Serial Number Tracker (`/serial-number`)**: Pelacakan serial number perangkat & distributor.

### 6. Multi-Cabang & Hak Akses Berjenjang
- **Owner**: Akses global seluruh cabang & Kantor Pusat (HQ), kelola PPN, kelola master cabang, dan switcher multi-cabang.
- **Superadmin**: Single-store experience toko mandiri untuk mengelola operasional cabang dan akun staf cabangnya.
- **Teknisi, Admin, & Bendahara**: Akses operasional harian sesuai jobdesk.
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
docker exec soulmate_apps php artisan test --filter MasterPegawaiTest
docker exec soulmate_apps php artisan test --filter TargetBulananTest
docker exec soulmate_apps php artisan test --filter TransaksiFullTest
```
