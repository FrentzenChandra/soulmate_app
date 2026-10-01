# Konteks Sistem: Aplikasi Servis, Kasir & Marketplace Multi-Cabang

Saya sedang membangun sebuah sistem internal untuk usaha servis elektronik (misal servis HP/laptop) yang punya **banyak cabang**. Sistem ini mencakup: manajemen servis masuk/keluar, kasir (POS), penjualan produk, modul transaksi Tabulator.js, manajemen kas, dan performance board target bulanan. Berikut kebutuhan lengkap dan logika bisnis yang sudah disepakati.

---

## 1. Gambaran Umum & Role

Sistem digunakan oleh banyak cabang sekaligus, jadi ada **data master Cabang**. Setiap User terikat ke 1 cabang tertentu (kecuali **Owner** yang bisa akses semua cabang dan beralih konteks antara Kantor Pusat/HQ dan Cabang).

**Role & Hierarki Hak Akses:**
- **Owner (Pemilik Bisnis)** — Memegang otoritas tertinggi (level Organisasi/Pusat). Memiliki akses ke **HQ Context** dan seluruh cabang. Berhak menambah dan mengelola akun semua peran (**Owner baru**, **Superadmin**, **Teknisi**, **Admin**, **Bendahara**) di seluruh cabang maupun HQ, mengelola **Cabang baru**, mengatur **Tarif PPN**, serta melihat konsolidasi finansial/laporan performa seluruh cabang.
- **Superadmin (Admin Cabang)** — Administrator operasional cabang. Akses dibatasi ketat **hanya pada 1 cabang yang dipegangnya**. Dapat mengelola transaksi kasir, katalog produk cabang, stok, laporan cabang, dan **mengelola/membuat akun staf di cabangnya sendiri** (role **Superadmin**, **Teknisi**, **Admin**, dan **Bendahara** khusus untuk cabangnya). Superadmin **tidak bisa** menambah cabang baru, beralih ke cabang lain, mengelola akun Owner, atau mengelola staf di cabang lain.
- **Teknisi** — Akses menu servis (servis masuk, servis keluar, ubah status pengerjaan) serta pencatatan data pelanggan pada cabang yang ditugaskan.
- **Admin (Staf POS / Kasir)** — Akses menu kasir POS/penjualan produk, penagihan servis, riwayat transaksi, dan data pelanggan pada cabang yang ditugaskan.
- **Bendahara** — Akses pencatatan keuangan dan kas operasional cabang.

---

## 2. Fitur Customer (Publik)

- **Menu publik cek status servis** — customer bisa cek status servisnya (pengecekan / pengerjaan / selesai) tanpa login, misal pakai No. Servis.
- **Menu marketplace/pembelian produk** — customer browsing produk yang memang sengaja ditampilkan ke publik, masukkan ke keranjang, lalu pesan lewat WhatsApp (bukan checkout online biasa, tapi generate rincian pesanan yang dikirim ke WA).

### Logika marketplace multi-cabang (penting):
- Produk adalah **katalog global** (1 produk = 1 baris data, dipakai semua cabang) — bukan produk per cabang, supaya tidak duplikat & mudah dikelola.
- Produk punya kolom `ready_marketplace` (true/false) — keputusan admin pusat, produk mana yang boleh tampil di web publik.
- Stok tiap produk **berbeda-beda per cabang**, dicatat di tabel stok terpisah yang menghubungkan produk + cabang + jumlah + status keaktifan (`is_aktif`).
- Saat customer lihat produk di web, sistem menampilkan **ketersediaan stok per cabang** (misal: "Ready di Cabang Pangkalpinang: 5 pcs", "Cabang Sungailiat: 0 pcs").
- **Customer wajib memilih cabang** sebelum kirim pesan ke WA. Setelah pilih cabang, sistem generate link WA ke **nomor WA milik cabang tersebut**, berisi rincian pesanan.
- Hanya menampilkan stok dari **cabang yang berstatus aktif** dan produk dengan `is_aktif = true`.

---

## 3. Fitur Servis Masuk

- Form input servis baru: pilih **Customer** — bisa pilih dari data lama atau input Customer Baru.
- **Kategori Perangkat (Urutan Standar)**: `Laptop`, `Printer`, `Komputer`, `Lainnya`.
- **Penyederhanaan Kolom**:
  - Kolom *Nomor Seri / Serial Number* dan *Kondisi Fisik / Bodi Awal* **dihapus**.
  - Pilihan Kelengkapan: `Charger`, `Box`, `Flashdisk`, `Harddisk`.
  - Kolom *Catatan Servis* diposisikan di sisi kiri dengan ukuran lebih besar untuk kenyamanan teknisi mencatat riwayat keluhan.
- Setelah input, bisa **cetak Tanda Terima Servis** untuk diberikan ke customer (printer thermal/dot matrix struk).
- Status servis: `pengecekan` -> `pengerjaan` -> `selesai` (atau `batal`).
- Setiap perubahan status dicatat sebagai audit log di `servis_status_logs`.
- Servis menggunakan detail jasa & suku cadang dari **Master Servis** (`master_servis` & `master_servis_stoks`), terpisah dari Master Produk retail kasir.

---

## 4. Fitur Servis Keluar

- **Filter/list dari data Servis Masuk** yang statusnya sudah `selesai` (siap diambil/dibayar) dan `sudah_diambil`.
- Default sorting memprioritaskan unit yang belum diambil (`selesai` di atas, `sudah_diambil` di bawah).
- Ada tombol **Batal** — kalau dipencet, status servis jadi `batal` dan total biaya dikosongkan (Rp 0).
- Begitu servis sudah ditagihkan lewat kasir, servis tersebut **otomatis terkunci** agar tidak ditagih dobel.

---

## 5. Fitur Kasir Produk & Penagihan Servis

### A. Kasir Produk (menu tersendiri — `/kasir/produk`)
- Kasir cari barang -> masuk keranjang -> bayar (diskon + PPN) -> cetak nota thermal.
- Mendukung **Penjualan Non-Real (Nota Formalitas)**: mode cetak struk tanpa memotong stok dan tanpa masuk ke pembukuan finansial / dashboard.

### B. Penagihan Servis (masuk dari Index/List Servis)
- Servis yang statusnya **`selesai`** memiliki aksi **"Tagih & Cetak"** langsung di baris itu.
- Form ringkas: rincian biaya servis, diskon, PPN, lalu Bayar -> langsung cetak nota.
- Setelah dibayar, status servis terkunci otomatis.

### Penomoran Transaksi — Format Harian & Tampilan Frontend
Nomor transaksi menggunakan generator urutan harian per cabang (`YYMMDD`):
- **Backend Servis:** `INV-SV-[KODE_CABANG]-[YYMMDD]-[00001]` -> **Frontend:** `SV-[YYMMDD]-[00001]`
- **Backend Produk:** `INV-PJ-[KODE_CABANG]-[YYMMDD]-[00001]` -> **Frontend:** `PJ-[YYMMDD]-[00001]`

### Standar Cetakan Nota & Portrait Standard
- **Orientasi Portrait Wajib:** `@page { size: portrait; margin: 3mm 6mm; }` (Orientasi cetak WAJIB **PORTRAIT**, dilarang keras landscape).
- **Tipografi & Skala:** Universal font **Arial** (`Arial, Helvetica, sans-serif`), proporsional ~12 cm tinggi kertas 1 halaman pas.
- **Grandtotal Standard:** Seluruh label total akhir pada seluruh nota/cetakan penjualan, faktur servis, POS, dan PDF WAJIB bertuliskan **`Grandtotal`** (Title Case, dilarang `GRANDTOTAL`, `Grand Total`, atau `TOTAL TAGIHAN`).
- **Format Kuantiti:** Format kuantiti berspasi (`1 UNIT`).

---

## 6. Data Master yang Dibutuhkan

- **Cabang** (`/master/cabang`) — Nomor WA khusus per cabang & status aktif/nonaktif. Akses: Owner.
- **Master User** (`/master/user`) — Pengelolaan akun sistem (Owner, Superadmin, Teknisi, Admin, Bendahara) dengan soft delete dan proteksi integritas historis `->withTrashed()`.
- **Master Pegawai** (`/master/pegawai`) — Daftar pegawai operasional cabang/toko dengan **Bagian Dinamis** (7 baseline awal: Teknisi Laptop, Teknisi Printer, Teknisi Komputer, Bendahara, Admin Pajak, Admin Stok, Admin Konten; Select Distinct). Akses: Owner, Superadmin, Admin.
- **Kategori Produk** (`/master/kategori`) — Pengelompokan produk dengan proteksi hapus jika memiliki relasi produk. Akses: Owner & Superadmin.
- **Master Produk** (`/master/produk`) — Katalog retail global untuk Kasir POS dan marketplace online, dengan matriks stok cabang, barcode scanner, deteksi duplikasi instan, dan kompresi WebP. Akses: Owner & Superadmin.
- **Master Servis & Sparepart** (`/master/servis`) — Layanan jasa perbaikan & suku cadang khusus servis (terpisah dari Master Produk). Mendukung stok cabang & threshold alert minimum stok sparepart, estimasi margin laba, dan preset masa garansi. Akses: Owner, Superadmin, Admin, Teknisi.
- **Master PPN** (`/master/ppn`) — Opsi tarif pajak dinamis (`PPN 11%`, `PPN 12%`, `Tanpa PPN (0%)`). Akses: Owner.
- **Master Customer / Data Pelanggan** (`/master/customer`) — Data pelanggan disederhanakan murni Nama Pelanggan dan Nomor WhatsApp/HP (`nama`, `no_hp`), terisolasi per cabang. Akses: Owner, Superadmin, Admin, Teknisi.
- **Master Sumber Dana** (`/master/sumber-dana`) & **Master Kategori Kas Keluar** (`/master/kategori-kas`).

---

## 7. Penggunaan Barcode dalam Sistem

- **Input Master Produk:** Scan barcode fisik / kamera HP, deteksi duplikasi instan.
- **Cari Produk di Kasir POS:** Scan barcode otomatis langsung memasukkan produk ke keranjang belanja.

---

## 8. Halaman Display Papan Antrean Servis (`/display/{kode_cabang}`)

Halaman antarmuka publik tanpa login untuk layar Smart TV di cabang:
- Tampilan Dark Mode Neon Accent.
- Counter per kategori perangkat (Laptop, Printer, Komputer) dan sub-status (Pengecekan, Pengerjaan, Selesai).
- Realtime WebSocket updates via Laravel Reverb.

---

## 9. Modul Kas Keluar, Sumber Dana & Kategori Kas Keluar (`/kas-keluar`)

- Menggunakan spreadsheet grid **Tabulator.js** dengan interactive column filtering, group by tanggal, dan Table Size Selector (S/M/L).
- Filter periode lengkap (Semua, Hari Ini, Rentang Tanggal, Bulanan, Tahunan).
- Pencatatan pengeluaran kas operasional cabang.

---

## 10. Modul Transaksi Berbasis Tabulator.js (`/transaksi/full` & `/transaksi/harian`)

### A. Transaksi FULL (`/transaksi/full` - Superadmin & Owner)
- Spreadsheet grid **Tabulator.js** dengan pagination `[200, 1000, 2000, 10000]` (default 200).
- Rekapitulasi finansial multi-dimensi (Modal HPP, Harga Jual, Laba Bersih, Status Pembayaran, Metode Pembayaran, Pajak PPN, Pegawai).
- **Hanya 2 Status Valid:** `Lunas` dan `BON`. Status `Belum Lunas` dan `Batal` ditiadakan.
- **Row-by-Row 1-to-1 Item Breakdown:** Rincian Item, Biaya, Modal, Laba, dan Pajak dijabarkan per baris secara sejajar (1-to-1). Kolom Pajak tampil murni `PPN` atau `Non PPN`.
- **Modal Form Sizing:** Ukuran form modal transaksi menggunakan batas maksimal `sm:max-w-2xl`.
- **Isolasi Modal Form:** Livewire dilarang men-dispatch event tabel Tabulator ke frontend saat modal form sedang dibuka.
- **Customer Form:** Dropdown pencarian dan kartu pelanggan hanya menampilkan nama pelanggan (tanpa nomor telepon).

### B. Transaksi Harian (`/transaksi/harian` - Admin & Superadmin)
- Audit transaksi real khusus hari ini (*today only*).
- **Transaksi Harian Zero Laba Standard:** SEMUA INFORMASI LABA (PROFIT / MARGIN) DILARANG KERAS DITAMPILKAN pada tabel Tabulator, modal form input/edit, modal detail, dan kartu metrik Transaksi Harian. Laba hanya bisa diakses pada Transaksi FULL oleh Superadmin & Owner.
- Status pembayaran: Hanya `Lunas` dan `BON`.

### C. Modal Awal Kasir (`/modal-awal`) & Cek Kas Cepat (Audit Laci)
- **Modal Awal Kasir:** Kas modal awal harian dengan proteksi dobel input dan alert lonceng > 10:00.
- **Cek Kas Cepat / Audit Laci:** Snapshot audit fisik laci kasir kapan saja tanpa mengunci transaksi harian.

---

## 11. Modul Saldo Rekening Standalone (`/saldo`)

- Pencatatan kas/rekening bank murni catatan mandiri (*pure record keeping*).
- Model `sumber_saldos` & `saldo_rekenings`. Top Metric Cards, Dynamic Tabs filter, dan summary widget.

---

## 12. Modul Target Bulanan (Performance Board 12 Bulan — `/target-bulanan`)

- **Tabel 1 — Target & Realisasi Per Divisi**:
  - 4 Divisi: **Laptop**, **Printer**, **Komputer**, dan **Admin**.
  - Target diatur per tahun via modal 5-in-1 "Target Tahunan Divisi" di toolbar atas (nilai target yang diinput berlaku merata untuk setiap bulan Jan &ndash; Des, misal input 200 maka target tiap bulan adalah 200 transaksi).
  - Kolom ke-2 tabel divisi menampilkan nilai target divisi tersebut.
  - Realisasi ditarik otomatis dari `transaksi_fulls` (atau penyesuaian manual per cell bulan jika diperlukan).
  - Indikator status pencapaian: `≥ 150%` (Over Target / Tech Blue), `100% - 149%` (Target Tercapai / Emerald), `< 100%` (Belum Tercapai / Rose).
- **Tabel 2 — Target Per Pegawai (Staf Individu)**:
  - Menampilkan seluruh pegawai yang bertugas di cabang terpilih (`pegawais` cabang).
  - Realisasi murni 100% ditarik otomatis dari `transaksi_fulls` berdasarkan `pegawai_id`.
  - Read-only performance summary tanpa kolom target, tanpa modal edit per cell, dan tanpa badge status merah.
- **Hak Akses Set Target:** Khusus role **Owner** dan **Superadmin**.
- **Slogan Motivasi & Single-Line Control Bar:** Quote *"Target Bukan Untuk Ditakuti, Tapi Untuk Dikalahkan!"* pada toolbar 1 baris terpadu.

---

## 13. Standar UI/UX & Desain Universal

1. **Color Palette:** Brand Purple (`#5E17EB`), Tech Blue (`#0052FF`), Accent Pink (`#E6007A`), neutral slate, white cards. Anti-AI slop.
2. **Zero Placeholder Standard:** Seluruh input form, filter, textarea, modal, dan kolom pencarian dilarang menggunakan atribut `placeholder`.
3. **Standar Tanda Bintang Wajib Isi Merah:** `<span class="text-rose-500 font-bold">*</span>`.
4. **Standar Loading Kasir Produk:** Spinner SVG Heroicons (`wire:loading wire:target="..."`), `wire:loading.attr="disabled"`, dan teks proses bertransisi.
5. **Skeleton Loader:** `<x-table-skeleton />` / `<x-grid-skeleton />` shimmer `animate-pulse` saat query/filter berlangsung.
6. **Sidebar Auto-Scroll:** Memusatkan link menu aktif secara vertikal via Alpine `scrollIntoView`.
7. **Universal Table Font Size:** Selector ukuran tabel index `<x-table-size-selector />` (S: 11px, M: 12px, L: 13px).
8. **Universal Date & Period Filter:** Mode tab `Semua`, `Hari Ini`, `Rentang Tanggal`, `Bulanan`, `Tahunan`.
