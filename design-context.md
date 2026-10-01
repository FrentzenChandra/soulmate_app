# Design System & UI/UX Guidelines: Soulmate Komputer

Panduan konteks desain UI/UX lengkap (*Design System & Theme Guide*) antarmuka aplikasi **Soulmate Komputer**.

---

## 1. Brand & Design Principles

Kamu adalah Product Designer untuk Soulmate Komputer — partner teknologi yang membangun kepercayaan pelanggan dan efisiensi kerja staf melalui transparansi, kesederhanaan, dan kecepatan.

### Prinsip Utama:
1. **Contextual:** Setiap elemen memiliki alasan dan tujuan yang jelas.
2. **Humane & Clean:** Hindari *dark patterns*, gunakan microcopy yang lugas dan ramah.
3. **Opinionated:** Tampilan tegas, solid, modern, dan bebas dari *AI slop*.
4. **Ergonomic & Fast:** Alur cepat, keyboard-friendly, dan zero-delay.

---

## 2. Palette Warna (Color Palette)

- **Primary (Utama):** `#5E17EB` (Electric Purple) &mdash; Header utama, tombol Aksi Utama (CTA), logo, dan aksen aktif.
- **Secondary (Sekunder):** `#0052FF` (Tech Blue) &mdash; Badge status, navigasi aktif, dan tombol penunjang.
- **Accent (Aksen):** `#E6007A` (Vivid Pink / Magenta) &mdash; Notifikasi penting, alert prioritas tinggi.
- **Neutral (Netral):**
  * Latar Belakang: `#F8F9FA` (Off-White) & `#FFFFFF` (Pure White untuk card/kontainer).
  * Teks Utama: `#1A1D20` / `#111827` (Dark Charcoal) untuk keterbacaan optimal.
  * Border: `border-gray-200` / `border-gray-100` crisp dan bersih.
  * Footer: `#0E1726` (Deep Dark Blue).

### Indikator Status:
- **Success / Lunas / Selesai:** Emerald Hijau (`#10B981` / `bg-emerald-50 text-emerald-700 border-emerald-200`)
- **Process / Diagnosa / Pengecekan:** Tech Blue (`#0052FF` / `bg-blue-50 text-blue-700 border-blue-200`)
- **Warning / BON / Kritis:** Amber / Kuning (`#F59E0B` / `bg-amber-50 text-amber-800 border-amber-200`)
- **Danger / Batal / Minus:** Rose Merah (`#E11D48` / `bg-rose-50 text-rose-700 border-rose-200`)

---

## 3. Sistem Tipografi

- **Font Family:** `Inter`, `Plus Jakarta Sans`, atau sans-serif modern.
- **Hierarki Visual:**
  * **Hero / Title:** *Extra Bold / Black* (24px &ndash; 32px)
  * **Section Header:** *Bold / Black* (16px &ndash; 20px)
  * **Body Text:** *Medium / Regular* (12px &ndash; 14px)
  * **Caption / Label:** *Bold / Black* (10px &ndash; 11px)

---

## 4. Larangan Mutlak & Anti-AI Slop

❌ **Dilarang Keras Gradient Berlebihan (AI Slop):** Utamakan warna solid modern dengan kontras tinggi di atas latar putih/abu bersih.
❌ **Anti-Mojibake & Karakter Rusak:** Dilarang keras memakai karakter berisiko encoding rusak seperti bullet point mentah (`•`), checkmark liar (`✓`), em-dash (`—`), atau emoji mentah di kode Blade template. Gunakan Clean Outline SVG atau HTML entities (`&bull;`, `&middot;`).
❌ **Dilarang Menggunakan Placeholder (Zero Placeholder Standard):** Seluruh form input, filter, modal, dan kolom pencarian di seluruh aplikasi **TIDAK BOLEH** menggunakan atribut `placeholder` (wajib dikosongkan/dihilangkan full). Gunakan label form yang tegas dan jelas di atas/samping input.
❌ **Standar Tanda Bintang Wajib Isi Merah:** Seluruh simbol bintang asteris (`*`) yang menandakan input wajib diisi **HARUS BERWARNA MERAH**: `<span class="text-rose-500 font-bold">*</span>`.
❌ **Standar Label Tunggal & Tegas:** Dilarang menggunakan label majemuk bergaris miring (`/`) atau ampersand (`&`) pada UI (contoh DILARANG: `Unit & Keluhan`, `Hp/WA`, `Teknisi / Kasir`). Wajib menggunakan **SATU NAMA TUNGGAL** yang spesifik (contoh: `Perangkat`, `WhatsApp`, `Petugas`, `Tindakan`).

---

## 5. Komponen UI & Layout Standards

### A. Sidebar Auto-Scroll Navigation
Container `<nav>` sidebar otomatis memusatkan link menu aktif via Alpine `x-init` & `scrollIntoView({ block: 'center', behavior: 'smooth' })` saat halaman dimuat maupun saat mobile drawer dibuka.

### B. Universal Table Font Size Standard
Toolbar filter semua tabel index wajib menyediakan selector ukuran font `<x-table-size-selector />` dengan 3 ukuran solid: `S` (11px), `M` (12px), `L` (13px) tersinkronisasi via `localStorage`.

### C. Universal Date & Period Filter Standard (Format Transaksi FULL)
Switcher tab mode 5-pilihan: `Semua`, `Hari Ini`, `Rentang Tanggal`, `Bulanan`, `Tahunan` dengan input tanggal dinamis otomatis di sisi kanan.

### D. Standar Loading Kasir Produk
Semua tombol aksi form (POST/PUT/DELETE/Submit) wajib menggunakan Heroicons SVG spinner (`wire:loading wire:target="..."`), status disabled (`wire:loading.attr="disabled"`), dan teks proses bertransisi (`Simpan` &rarr; `Menyimpan...`).

### E. Skeleton Loader
Tabel data dan katalog grid menampilkan `<x-table-skeleton />` / `<x-grid-skeleton />` shimmer `animate-pulse` saat request query/filter berlangsung.

---

## 6. Standar Khusus Modul

### A. Modul Transaksi (Transaksi FULL & Harian)
- Status Pembayaran: Hanya 2 status (**`Lunas`** dan **`BON`**).
- Row-by-Row 1-to-1 Item Breakdown. Kolom Pajak tampil murni `PPN` atau `Non PPN`.
- **Transaksi Harian Zero Laba Standard:** Dilarang keras menampilkan info laba/profit/margin pada Transaksi Harian.
- Modal Form Sizing: Ukuran lebar modal transaksi menggunakan batas maksimal `sm:max-w-2xl`.
- Modal Detail Urutan Kolom: `Item` &rarr; `Jumlah` &rarr; `Harga Jual` &rarr; `Modal` &rarr; `Laba`.
- Zero nomor telepon pada dropdown dan modal detail pelanggan.

### B. Modul Target Bulanan (`/target-bulanan`)
- **Tabel Divisi (Laptop, Printer, Komputer, Admin):** Target diatur per tahun via modal 5-in-1, berlaku merata per bulan (misal input 200 maka target bulanan Jan-Des adalah 200). Kolom ke-2 menampilkan nilai target tersebut. Edit per cell hanya untuk mengisi custom realisasi (jumlah transaksi).
- **Tabel Pegawai (Staf):** Realisasi murni 100% dari `transaksi_fulls` berdasarkan `pegawai_id`. Read-only summary tanpa target dan tanpa badge merah.
- Header 1 baris terpadu dengan quote motivasi: *"Target Bukan Untuk Ditakuti, Tapi Untuk Dikalahkan!"*.

### C. Master Pegawai (`/master/pegawai`)
- Menggunakan istilah **"Bagian"** (bukan "Role").
- 7 Bagian Default Baseline: `Teknisi Laptop`, `Teknisi Printer`, `Teknisi Komputer`, `Bendahara`, `Admin Pajak`, `Admin Stok`, `Admin Konten`.
- Dinamis via Select Distinct (`SELECT DISTINCT role FROM pegawais`).
- Form modal menyediakan opsi `+ Tambah Bagian Baru...` dengan input teks dinamis.
- Pewarnaan badge cerdas otomatis (Laptop: Biru, Printer: Amber, Komputer: Purple, Bendahara: Emerald, Pajak: Rose, Stok: Cyan, Konten: Indigo).

### D. Standar Cetakan Nota & Portrait Standard
- **Orientasi Portrait Wajib:** `@page { size: portrait; margin: 3mm 6mm; }` (Orientasi cetak WAJIB **PORTRAIT**, dilarang keras landscape).
- **Tipografi:** Universal font **Arial** (`Arial, Helvetica, sans-serif`).
- **Skala Proporsional:** Mengisi proporsional ~12 cm tinggi kertas 1 halaman pas tanpa mengubah struktur layout.
- **Grandtotal Standard:** Seluruh label total akhir pada seluruh nota/cetakan penjualan, faktur servis, POS, dan PDF WAJIB bertuliskan **`Grandtotal`** (Title Case, dilarang `GRANDTOTAL`, `Grand Total`, atau `TOTAL TAGIHAN`).
- **Kuantiti:** Format kuantiti berspasi (`1 UNIT`).
