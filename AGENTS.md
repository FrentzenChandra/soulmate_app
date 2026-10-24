# Workspace Agent Guidelines

## 1. Graphify Auto-Update
<!-- - Ini TIDAK USAH Jalankan auto-update graphify (via `python -m graphify.cli update`) setiap kali terjadi perubahan kode, penambahan fitur, atau commit di project. -->
- Gunakan `graphify query` untuk menelusuri relasi kode, god nodes, dan dependensi sebelum melakukan refaktor.

## 2. UI & Design Standards (Anti-AI Slop, Anti-Mojibake & Loading/Skeleton)
- **Dilarang keras memakai gradient berlebihan / multi-color mesh gradient (AI slop)** kecuali diperintahkan secara eksplisit oleh user.
- Gunakan solid clean modern color palette: Brand Purple (`#5E17EB`), Tech Blue (`#0052FF`), Accent Pink (`#E6007A`), dan netral gray/slate dengan white cards, clean crisp borders, dan subtle shadows.
- **Anti-Mojibake & Tipografi Bersih:** Dilarang menggunakan karakter UTF-8 rentan encoding rusak seperti raw bullet (`•`), em-dash (`—`), checkmark mentah (`✓`), atau emoji di kode Blade. Gunakan HTML entity (`&bull;`, `&middot;`), SVG icon, atau Tailwind CSS round dots (`<span class="inline-block w-1 h-1 rounded-full bg-gray-300"></span>`).
- **Dilarang Menggunakan Placeholder (Zero Placeholder Standard):** Seluruh inputan form, filter, textarea, modal, dan kolom pencarian di seluruh aplikasi **TIDAK BOLEH** menggunakan atribut `placeholder` (wajib dikosongkan/dihilangkan full). Gunakan label form yang tegas dan jelas di atas/samping input.
- **Standar Loading & Skeleton Universal:**
  * **Upload Foto/Media:** Loading indicator WAJIB terpusat di tengah parent (`flex-col items-center justify-center`) dengan spinner dan progress bar animasi di bagian tengah container.
  * **Aksi Form & Tombol (POST, PUT/PATCH, DELETE, EDIT, Modal Actions):** Semua tombol aksi dan form WAJIB menggunakan **Standar Loading Kasir Produk**:
    - Animasi spinner SVG Heroicons (`<svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>`) dipicu via `wire:loading wire:target="..."`.
    - Status disabled dinamis (`wire:loading.attr="disabled" disabled:opacity-50 cursor-not-allowed`).
    - Teks proses deskriptif bertransisi (`wire:loading.remove` &rarr; `wire:loading`, contoh: `Simpan` &rarr; `Menyimpan...`, `Hapus` &rarr; `Menghapus...`).
  * **Query / Data Fetching (GET, Search, Filter, Sort):** Tabel dan katalog grid WAJIB menampilkan **Skeleton Loader** (`<x-table-skeleton />` / `<x-grid-skeleton />`) dengan efek shimmer `animate-pulse` saat request query berlangsung.
- **Standar Label Tunggal & Tegas (Anti-Ambiguitas):**
  * Dilarang menggunakan label majemuk bergaris miring (`/`) atau ampersand (`&` / `&amp;`) pada UI frontend seperti nama kolom tabel, header modal, tombol aksi, dan cetakan nota (contoh DILARANG: `Unit & Keluhan`, `Hp/WA`, `Teknisi / Kasir`, `Servis & Kelengkapan`, `Status & Waktu Ambil`).
  * WAJIB menggunakan **SATU NAMA TUNGGAL** yang spesifik dan jelas (contoh: `Perangkat`, `WhatsApp`, `Petugas`, `Tindakan`, `Status`, `Pelunasan`).
- **Standar Tanda Bintang Wajib Isi Merah (Red Asterisk Standard):**
  * Seluruh simbol bintang asteris (`*`) yang menandakan bahwa suatu kolom input / form / modal WAJIB diisi **HARUS BERWARNA MERAH** menggunakan styling Tailwind: `<span class="text-rose-500">*</span>` (atau `<span class="text-rose-500 font-bold">*</span>`).
  * Dilarang keras menulis tanda bintang mentah tanpa tag warna merah (contoh DILARANG: `Nama *`, `Harga Satuan *`, `Tanggal *`).
- **Sidebar Auto-Scroll Active Menu Standard:**
  * Container `<nav>` sidebar (desktop & mobile drawer) WAJIB mengimplementasikan auto-scroll vertikal (`scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'smooth' })`) ke elemen tautan menu yang sedang aktif saat halaman dimuat atau saat drawer mobile dibuka (`$watch('sidebarOpen')`).
  * Deteksi elemen aktif menargetkan selector warna brand solid aktif (`a.bg-[#5E17EB]`, `a.bg-[#0052FF]`, `a.bg-brand-purple`, `a.bg-brand-blue`, `[data-sidebar-active]`).

## 3. Global Multi-Word Loose Search Standard
- **Universal Non-Strict Multi-Word Search:** Semua fitur pencarian di seluruh project WAJIB menggunakan tokenized substring matching multi-kata (`explode(' ', $search)`), *case-insensitive* (`LOWER(col) LIKE ?`), dan tidak *strict* per huruf/kata. Pencarian harus mencocokkan setiap kata kunci di berbagai kolom relevan (nama, barcode, no hp, username, kategori, dll).
- Berlaku di Kasir POS, Master Produk, Stok Cabang, Master Pelanggan, Master User, Master Kategori, dsb.

## 4. Branch Obfuscation for Non-Owner Roles
- **Single-Store Experience untuk Staf:** Pengguna dengan peran non-owner (**Superadmin**, **Admin**, **Teknisi**) WAJIB melihat aplikasi seolah-olah hanya 1 toko mandiri ("Soulmate Komputer").
- Dilarang menampilkan label cabang, switcher cabang, tag nama cabang, atau breadcrumb cabang pada navbar, header, notifikasi, dan katalog bagi non-owner.
- Hanya role **Owner** yang memiliki akses ke switcher cabang, konteks Kantor Pusat (HQ), dan visibilitas multi-cabang.

## 5. Simplified Customer Data & Selection Standard
- **Field Ringkas & Fokus Operasional:** Data pelanggan operasional harian disederhanakan murni menjadi **Nama Pelanggan** (`nama`) dan **Nomor WhatsApp / HP** (`no_hp`). Hilangkan kolom foto, alamat rumit, dan tipe keanggotaan/badge di alur cepat kasir, transaksi, & master pelanggan harian.
- **Standar Alur Input & Pemilihan Pelanggan (Pola Servis Masuk):**
  * **Toggle Switcher:** Dua mode jelas &rarr; `Pilih Terdaftar` vs `+ Pelanggan Baru`.
  * **Autocomplete Terdaftar:** Pencarian multi-kata tokenized substring matching dengan dropdown saran instan.
  * **Kartu Pelanggan Terpilih:** Menampilkan nama & nomor kontak pelanggan yang dipilih dengan tombol aksi `Ganti` / reset.
  * **Registrasi Pelanggan Cepat:** Input `Nama Lengkap Pelanggan` & `WhatsApp`, otomatis tersimpan dengan `cabang_id` aktif dan langsung terpilih pada transaksi/tiket yang sedang dibuka.
  * **Standar Tombol Loading:** Animasi SVG Heroicons spinner, transisi teks status (`Simpan` &rarr; `Menyimpan...`), dan proteksi `wire:loading.attr="disabled"`.
  * **Zero Placeholder Standard:** Seluruh input form pelanggan tanpa atribut `placeholder`.

## 6. Daily Invoice Sequence, Continuous Form & Grandtotal Standard
- **Format Backend:** `INV-PJ-[KODE_CABANG]-[YYMMDD]-[00001]` (counter atomik di-reset setiap hari per cabang).
- **Format Frontend:** `PJ-[YYMMDD]-[00001]` (ditampilkan via accessor `no_transaksi_frontend`).
- **Format Servis:** `INV-SV-[KODE_CABANG]-[YYMMDD]-[00001]` / `SV-[YYMMDD]-[00001]`.
- **Cetakan Nota & Continuous Form (Portrait Standard):**
  * Orientasi cetak **WAJIB PORTRAIT** (`@page { size: portrait; margin: 4mm 15mm; }`, dilarang landscape).
  * Font universal **Arial** (`Arial, Helvetica, sans-serif`).
  * Proporsional tinggi konten ~12 cm tanpa mengubah layout struktur asli.
  * Format kuantiti dan unit wajib berjarak (`{{ $qty }} UNIT`, bukan `1UNIT`).
- **Standardisasi Label Grandtotal:**
  * Seluruh label total akhir pada seluruh nota/cetakan (Sales Invoice, Faktur Servis, Kasir POS, dan PDF) **WAJIB menggunakan teks `Grandtotal`** (Title Case persis `Grandtotal`, dilarang `GRANDTOTAL`, `Grand Total`, atau `TOTAL TAGIHAN`).

## 7. Docker & Execution Environment (Windows Host + Linux Container)
- **Eksekusi Perintah Framework:** Semua perintah yang menyentuh filesystem / backend (`php artisan storage:link`, `php artisan migrate`, `php artisan optimize:clear`, dsb.) **WAJIB dieksekusi di dalam Docker container** (`docker exec soulmate_apps ...`), bukan di terminal Windows host.
- **Windows Junction vs Linux Symlink:** `storage:link` dari Windows menghasilkan NTFS Junction yang tidak dapat dibaca oleh Linux container (menyebabkan 403 Forbidden). `storage:link` dari dalam container menghasilkan native Linux symlink.
- **Storage Standard:** Semua upload media menggunakan `Storage::disk('public')` dengan path relatif `storage/...`.

## 8. Edukasi & Penjelasan Konsep IT
- Saat menjelaskan istilah IT teknis (seperti Junction, Symlink, Volume Mount, Docker, Signed URL, dsb.), gunakan bahasa analogi sederhana ("gambarkan seperti menjelaskan ke pemula/bayi") yang mudah dicerna sebelum masuk ke teknis.

## 10. Modular Menu Daily Logging Standard
- **Global Logging Middleware:** Terpasang global via `bootstrap/app.php` (`App\Http\Middleware\ApiMenuLogger`).
- **File Format:** Terorganisir di dalam subfolder per nama menu/modul per hari (`storage/logs/{menu}/menu-{menu}-YYYY-MM-DD.log`). Auto-create direktori jika belum tersedia.
- **Pembersihan Log Otomatis:** Driver `daily` dengan retensi otomatis `LOG_MENU_DAYS` (default 5 hari) dan permission `0666` untuk mencegah disk membengkak.
- **Log Format:** Single-line string tokenized `STATUS | METHOD /path | User: ID (Name) | IP: ... | DURATIONms | Payload: {...} | Error: ...`.
- **Sensitivitas Keamanan:** Masking otomatis recursive untuk password, token, key, dan pin.

## 11. Universal Table Font Size Standard
- **Komponen Standar:** `<x-table-size-selector />` di toolbar filter semua tabel index.
- **Ukuran:** 3 level font sizing solid: `S` (Compact, 11px), `M` (Default/Normal, 12px), `L` (Comfortable, 13px).
- **Persistensi State:** Disimpan otomatis via `localStorage('soulmate_table_size')` dan disinkronkan ke seluruh tabel melalui Alpine event `table-size-changed`.

## 12. Modal Awal Kasir & Notification Alert Rules
- **Aturan Input Admin / Kasir:**
  * Wajib input nominal > 0 (`min:1`).
  * Tanggal terkunci otomatis untuk hari ini (`now()->toDateString()`). Admin dilarang keras backdate / future-date.
  * Proteksi dobel input: Admin hanya boleh input 1x per hari.
- **Hak Akses Khusus Superadmin & Owner:**
  * Hanya Superadmin & Owner yang diizinkan melakukan koreksi / edit dan hapus data modal awal.
  * Superadmin & Owner diizinkan melakukan backdate untuk input data susulan.
- **Cek Kas Cepat / Audit Laci (Snapshot Kasir):**
  * Menggantikan Tutup Kas konvensional. Kasir/Owner/Supervisor dapat melakukan audit kas fisik kapan saja tanpa mengunci transaksi harian.
- **Notifikasi Lonceng (> 10:00 Alert):**
  * Jika waktu lokal telah melewati jam 10:00 pagi (`now()->hour >= 10`) dan cabang belum mencatat modal awal kasir, navbar lonceng notifikasi otomatis memicu badge merah berkedip (`animate-pulse`) dan card peringatan prioritas tinggi dengan tombol aksi langsung `Input Modal Awal`.
  * Pada konteks Owner HQ, sistem memantau seluruh cabang aktif yang belum mengisi modal awal.

## 13. Universal Date & Period Filter Standard (Format Transaksi FULL)
- **Standar Filter Periode Global:** Seluruh modul tabel/index dan laporan di aplikasi yang memiliki filter tanggal WAJIB mengikuti format dan arsitektur filter **Transaksi FULL**:
  * **Tombol Switcher Tab Mode:**
    - `Semua` (`setPeriodeMode('semua')`) &rarr; Menampilkan seluruh data tanpa batasan tanggal (`startDate = ''; endDate = '';`).
    - `Hari Ini` (`setPeriodeMode('hari_ini')`) &rarr; Mengunci tanggal hari ini (`startDate = now()->toDateString(); endDate = now()->toDateString();`).
    - `Rentang Tanggal` (`setPeriodeMode('range')`) &rarr; Rentang awal bulan sampai hari ini (`startDate = now()->startOfMonth()->toDateString(); endDate = now()->toDateString();`).
    - `Bulanan` (`setPeriodeMode('bulanan')`) &rarr; Filter dinamis berdasarkan `filterBulan` dan `filterTahun`.
    - `Tahunan` (`setPeriodeMode('tahunan')`) &rarr; Filter dinamis berdasarkan `filterTahun`.
  * **Input Tanggal Dinamis (UI Blade):**
    - Posisi input dinamis WAJIB diletakkan di sisi kanan otomatis (`sm:ml-auto` / `auto-right`) setelah tombol mode periode.
    - Saat mode `range` / `hari_ini`: Tampilkan input `startDate` dan `endDate` dengan pemisah teks `s/d`.
    - Saat mode `bulanan`: Tampilkan dropdown `filterBulan` (`1`..`12`) dan dropdown `filterTahun`.
    - Saat mode `tahunan`: Tampilkan dropdown `filterTahun`.
  * **Sinkronisasi Query Backend:**
    - Gunakan helper tunggal `applyDateFilter($query)` yang memfilter kolom timestamp/tanggal (`whereBetween('created_at', [$start, $end])` atau `whereBetween('tanggal', [$start, $end])`).
    - Filter berlaku serentak pada metrik/ringkasan card di atas tabel dan pagination query tabel.

## 14. Skills & Capabilities
- Dalam konteks workspace project: Aktifkan seluruh ekosistem skill (Caveman, Ponytail, Frontend UI Engineering, Graphify, TDD, Surgical Patch, Subagents).
- Skill **Frontend UI Engineering** (`frontend-ui-engineering`) dan **Ponytail** (`ponytail`) WAJIB selalu aktif setiap saat di seluruh conversation/turn.
- Pertahankan gaya komunikasi ringkas, padat, dan hemat token (Caveman mode).

## 15. Proactive Anomaly & Discrepancy Reporting
- **Wajib Lapor Anomali / Ketidakwajaran:** Setiap kali agen menemukan kejanggalan struktural di codebase (misal: rute/komponen aktif yang tidak ada di menu navigasi, dead code/link putus, inkonsistensi permission/role antar halaman, data filter yang bocor antar cabang, query rawan bug/N+1, atau fitur gantung), agen **WAJIB langsung proaktif memberitahu user** secara lugas tanpa menunggu ditanya.

## 16. Targeted & Scoped Testing Standard
- **Larangan Full Test Acak:** Dilarang menjalankan full test suite (`php artisan test`) pada setiap conversation/turn kecuali diminta secara eksplisit oleh user untuk release gate.
- **Pengujian Tertarget & Cepat:** Setiap kali melakukan perubahan kode atau verifikasi, eksekusi test WAJIB diarahkan secara spesifik pada modul yang diubah dan modul yang berhubungan langsung:
  * Gunakan flag `--filter`: `docker exec soulmate_apps php artisan test --filter <TestClass>` (contoh: `--filter ServisMasukTest`, `--filter KasirPosTest`, `--filter ProductAndStockTest`).
  * Atau targetkan file spesifik: `docker exec soulmate_apps php artisan test tests/Feature/<NamaTest>.php`.

## 17. User Soft Delete & Historical Data Integrity Standard
- **Soft Delete Wajib untuk User:** Model `User` menggunakan `Illuminate\Database\Eloquent\SoftDeletes`. Pengguna yang dihapus tidak dihapus permanen dari database melainkan diberi timestamp `deleted_at`.
- **Proteksi Hapus Pengguna:**
  * Pengguna dilarang menghapus akunnya sendiri (*self-lockout guard*).
  * Sistem wajib mempertahankan minimal 1 akun Owner aktif (*minimal 1 owner guard*).
  * Superadmin hanya berhak menghapus akun staf non-owner di cabangnya sendiri.
- **Integritas Relasi Historis:** Semua relasi `belongsTo(User::class)` pada model transaksi, servis, kas, dan audit log (`TransaksiFull`, `ServisMasuk`, `KasKeluar`, `ModalAwal`, `CekKasLog`, `TutupKas`, `ServisStatusLog`, `Penjualan`, `SerialNumber`) **WAJIB menggunakan `->withTrashed()`** agar riwayat dan nota masa lalu tetap menampilkan nama staf pembuat aslinya tanpa menghasilkan error null.
- **Validasi Unik Email & Username:** Validasi unik pada form pengguna wajib mengabaikan akun yang telah di-soft-delete (`Rule::unique('users', 'email')->whereNull('deleted_at')`).

## 18. Target Bulanan (Performance Board 12 Bulan) Standard
- **Tabel Divisi (Laptop, Printer, Komputer, Admin):**
  * Target diatur per tahun via modal "Target Tahunan Divisi" di toolbar atas (nilai target yang diinput berlaku merata untuk setiap bulan Jan &ndash; Des, misal input 200 maka target bulanan tiap bulan adalah 200 transaksi).
  * Kolom ke-2 tabel divisi menampilkan nilai target tahunan divisi tersebut.
  * 12 Sel Bulan (Jan &ndash; Des) menampilkan angka realisasi riil dan badge persentase status pencapaian (`≥ 150%` Over Target / Tech Blue, `100% - 149%` Target Tercapai / Emerald, `< 100%` Belum Tercapai / Rose Merah).
  * Kolom Total YTD menampilkan total realisasi tahun berjalan dan badge persentase YTD: `≥ 150%` (Over Target / Tech Blue), `100% - 149%` (Target Tercapai / Emerald), `< 100%` (Belum Tercapai / Rose Merah).
  * Tanpa baris ringkasan footer `TOTAL DIVISI` (tabel bersih dan padat).
  * Penyesuaian realisasi manual per bulan tetap didukung secara independen melalui klik per sel bulan.
- **Tabel Staf (Individu):**
  * Realisasi murni 100% ditarik otomatis dari `transaksi_fulls` berdasarkan `pegawai_id`.
  * Tanpa kolom target/persyaratan, tanpa badge status merah, dan tanpa modal edit per sel (read-only real-time performance summary).
- **Hak Akses Set Target:** Hanya role **Owner** dan **Superadmin** yang berwenang mengatur target tahunan dan mengklik sel target divisi.
- **Slogan Motivasi:** Header menampilkan slogan penyemangat: *"Target Bukan Untuk Ditakuti, Tapi Untuk Dikalahkan!"*.

## 19. Simplified Transaction Status Standard (Lunas & BON Only)
- **Hanya 2 Status Valid:** Modul Transaksi (Transaksi FULL dan Transaksi Harian) HANYA mengenal 2 status pembayaran:
  * **`Lunas`**
  * **`BON`**
- **Dilarang Status Lain:** Dilarang keras menambahkan atau menampilkan status `Belum Lunas` dan `Batal` pada form modal input transaksi, inline editing status Tabulator, filter status, maupun badge status transaksi.

## 20. Single-Line Control Bar & Toolbar Standard
- **Tata Letak 1 Baris Terpadu:** Toolbar atas pada modul analitik/dashboard (seperti Target Bulanan) WAJIB disusun rapi dalam 1 baris horizontal (`flex items-center justify-between`):
  * **Sisi Kiri:** Judul modul dengan aksen brand + slogan motivasi.
  * **Sisi Kanan:** Filter dropdown tahun, selector cabang (Owner HQ), dan tombol aksi modal.
- Mencegah penumpukan vertikal yang memakan ruang layar kerja.

## 21. Transaction Item Breakdown & Modal Sizing Standard
- **Row-by-Row 1-to-1 Item Breakdown:**
  * Pada modul Transaksi (Transaksi FULL dan Transaksi Harian), rincian kolom **Item**, **Biaya**, **Modal**, **Laba**, dan **Pajak** WAJIB dijabarkan baris per baris per item secara sejajar (1-to-1).
  * **Kolom Pajak:** Tampilkan teks murni `PPN` atau `NON` tanpa badge background, tanpa persen, dan tanpa nominal.
  * **Pewarnaan Kolom Metode Pembayaran:**
    - `Cash` / Tunai: **Hijau** (`bg-emerald-50 text-emerald-700 border-emerald-300`)
    - `Gopay`: **Biru** (`bg-blue-50 text-[#0052FF] border-blue-300`)
    - Personal Rekening (`BCA Sri Listiani`, `MANDIRI Delly Sarli`, `BRI Razil Geo Taza`): **Pink** (`bg-pink-50 text-[#E6007A] border-pink-300`)
    - Rekening CV (`BRI CV`, `BCA CV`, `SUMSEL CV`): **Oren** (`bg-orange-50 text-orange-700 border-orange-300`)
    - Mesin EDC (`EDC BRI`, `EDC BCA`, `EDC MANDIRI`): **Coklat Tua** (`bg-[#542A0C] text-white border-[#3B1D08]`)
  * **Anti-Duplikasi Total:** Dilarang menampilkan baris total tambahan di dalam sel tabel masing-masing kolom (total transaksi sudah tertera di card metrik atau kolom ringkasan).
- **Transaction Modal Form Sizing:**
  * Ukuran lebar modal input/edit transaksi (`$modalFormOpen`) di Transaksi FULL dan Transaksi Harian WAJIB menggunakan batas maksimal `sm:max-w-2xl` (bukan `sm:max-w-4xl`) agar proporsional, nyaman dilihat, dan ergonomis.

## 22. Transaksi Harian Zero Laba Standard (Sensitivitas Margin Kasir & Staf)
- **Larangan Total Informasi Laba di Transaksi Harian:** Pada modul **Transaksi Harian** (yang digunakan oleh staf operasional dan kasir), **SEMUA INFORMASI LABA (PROFIT / MARGIN) DILARANG KERAS DITAMPILKAN**:
  * Dilarang menampilkan kolom `Laba` pada tabel Tabulator Transaksi Harian.
  * Dilarang menampilkan kalkulasi/preview `Laba Bersih` pada baris modal form input/edit transaksi.
  * Dilarang menampilkan kolom `Laba` pada tabel modal detail transaksi harian.
  * Dilarang menampilkan metrik/ringkasan laba di header atau kartu metrik Transaksi Harian.
- **Hak Akses Informasi Laba:** Informasi dan analisis laba secara penuh HANYA boleh diakses melalui modul **Transaksi FULL** (khusus role Superadmin dan Owner).

## 23. Transaksi Modal Detail & Customer Form Simplification Standard
- **Urutan Kolom Tabel Modal Detail:**
  * Urutan kolom rincian barang/layanan pada modal detail transaksi WAJIB: `Item` &rarr; `Jumlah` (wajib memakai nama `Jumlah`, dilarang `Qty`) &rarr; `Harga Jual` &rarr; `Modal` (dilarang `Modal (HPP)`) &rarr; `Laba` (hanya ada di Transaksi FULL).
  * Baris footer total ringkasan tersinkronisasi 1-to-1 dengan urutan kolom di atasnya.
- **Zero Nomor Telepon pada Dropdown & Modal Detail Pelanggan:**
  * Dropdown saran pencarian pelanggan (`$customerSuggestions`) pada modal input transaksi HANYA menampilkan nama pelanggan (tanpa nomor telepon di sampingnya).
  * Kartu pelanggan terpilih pada form modal hanya menampilkan avatar inisial dan nama pelanggan.
  * Modal detail transaksi pada blok data pelanggan HANYA menampilkan nama pelanggan tanpa nomor telepon.
- **Ultra-Compact Dense Table & Slim Payload:**
  * Format tabel Transaksi FULL dan Harian menggunakan `.tabulator-dense`, font warna biaya ungu `#5E17EB`, opsi pagination `[200, 1000, 2000, 10000]` (default 200), dan slim query payload (`select` kolom spesifik) untuk menjaga performa optimal.

## 24. Modal Form Isolation & Anti-Lag Livewire Input Standard
- **Isolasi Background Table Dispatch:** Saat modal form terbuka (`$modalFormOpen === true` atau `$modalOpen === true`), komponen Livewire **DILARANG KERAS** memetakan payload tabel dan men-dispatch event tabel (`table-updated`) ke frontend. Event re-render tabel Tabulator HANYA boleh dipicu saat modal ditutup, saat data berhasil disimpan, dihapus, atau saat filter/search tabel diubah.
- **Anti Double Network Roundtrip pada Input Form:**
  * Dilarang memanggil `$wire.call(...)` manual di dalam handler `@input` jika field tersebut sudah terikat dengan `@entangle(...).live` atau `wire:model.live`.
  * Gunakan debounce proporsional (`@input.debounce.250ms`, `wire:model.live.debounce.300ms`) pada kolom pencarian dan input dinamis untuk mencegah penumpukan request HTTP yang membekukan (*freeze*) thread browser.
- **Kalkulasi Total Bersih:** Kalkulasi subtotal/total baris form diproses otomatis melalui lifecycle hook `updated()` di backend atau reaktif di frontend via Alpine tanpa merusak performa pengetikan pengguna.

## 25. Master Pegawai & Dynamic Bagian Standard
- **Terminologi Bagian (Bukan Role):** Modul Master Pegawai (`/master/pegawai`) menggunakan istilah **"Bagian"** untuk mengelompokkan divisi staf operasional/toko.
- **7 Bagian Baseline Awal:** `Teknisi Laptop`, `Teknisi Printer`, `Teknisi Komputer`, `Bendahara`, `Admin Pajak`, `Admin Stok`, `Admin Konten`.
- **Select Distinct Dinamis (Zero-Table Overhead):** Pilihan Bagian ditarik secara dinamis dari `SELECT DISTINCT role FROM pegawais` digabung dengan 7 bagian baseline.
- **Input Bagian Baru Fleksibel:** Form modal menyediakan opsi `+ Tambah Bagian Baru...` dengan input teks tambahan. Saat disimpan, bagian baru tersebut otomatis masuk ke opsi pilihan dan filter.
- **Pewarnaan Badge Cerdas:** Badge bagian otomatis memiliki styling warna Tailwind berbasis kata kunci (Laptop: Biru, Printer: Amber, Komputer: Purple, Bendahara: Emerald, Pajak: Rose, Stok: Cyan, Konten: Indigo, default: Slate/Gray).

## 26. Dynamic Role Permission Matrix Standard (Atur Akses)
- **Modul Eksklusif Owner HQ:** Pengaturan hak akses (`/master/hak-akses`) HANYA boleh diakses oleh akun **Owner** dalam konteks Kantor Pusat (HQ) dan muncul di sidebar menu "Master & Organisasi" dengan badge "Role".
- **6 Role Staf Operasional:** `Superadmin`, `Teknisi`, `Admin`, `Bendahara`, `Operasional`, `Magang`.
- **Owner 100% Global Bypass:** Akun Owner di-bypass otomatis melalui `Gate::before(fn($user) => $user->isOwner() ? true : null)` di `AppServiceProvider` tanpa perlu assignment manual di database.
- **Relasi Database Spatie:** Menggunakan schema tabel Spatie (`roles`, `permissions`, `role_has_permissions`). Saat Owner menyimpan izin:
  * Backend memanggil `$role->syncPermissions($this->selectedPermissions)`.
  * Spatie cache wajib dibersihkan seketika via `app()[PermissionRegistrar::class]->forgetCachedPermissions()`.
- **Dynamic Route Security:** Seluruh route operasional di `routes/web.php` WAJIB diproteksi menggunakan `middleware('can:<permission_name>')`. Percobaan akses langsung via URL yang tidak diizinkan otomatis menghasilkan status `403 Forbidden`.
- **Granular UI Authorization:**
  * Sidebar operasional menggunakan `@canany(...)` untuk grup dan `@can(...)` untuk masing-masing tautan menu.
  * Tombol aksi CRUD & fitur spesifik (Detail, POST, Edit, Delete, Cetak Nota/Tanda Terima, Tindakan Servis, Ubah Status) di Blade view WAJIB dibungkus `@can(...)` dan dilindungi `abort_if(!can(...), 403)` pada method Livewire terkait.
- **Master Switch & Sub-Action Matrix:** Form Atur Akses menyediakan master toggle per modul (mengaktifkan/menonaktifkan seluruh izin modul) serta checklist individu untuk setiap tombol aksi.

## 27. Native Authentication & Rate Limiting Standard (Captcha-Free & Zero Friction)
- **Zero Friction Authentication (/ponytail):**
  * Halaman login bebas dari widget captcha pihak ketiga (Google reCAPTCHA / Cloudflare Turnstile ditiadakan penuh) untuk mencegah issue HTTP 429 Too Many Requests, false positive bot fight mode, network roundtrip eksternal, dan hambatan login staf toko.
- **Bedrock Defense (Native Laravel Rate Limiting):**
  * Keamanan form autentikasi dilindungi langsung di level backend oleh mekanisme native Laravel (`RateLimiter::tooManyAttempts(...)`).
  * Batas keamanan: 20x toleransi percobaan gagal per 60 detik per identitas/IP (`Str::transliterate(Str::lower($identity) . '|' . request()->ip())`), memberikan ruang toleransi yang aman dan ramah bagi staf saat mengetik tanpa risiko brute-force.

## 28. Master Data Selection Standard (Pola Servis Masuk)
- **Universal Input Master Data Tanpa Tab Baru (Full Select Standard):**
  * Desain input pemilihan data master (seperti Master Produk/Barang, Pegawai, Distributor, dll pada modal form) **WAJIB mengikuti desain pola input data pelanggan pada Servis Masuk**, namun **tanpa tabulasi untuk menambah data master baru** (murni full select).
  * **State 1 - Belum Dipilih:** Menampilkan kolom input pencarian (`wire:model.live.debounce.250ms`) dengan saran dropdown instan (`$suggestions`) multi-kata tokenized substring matching dari master database. Pengguna wajib memilih salah satu item yang muncul dari saran.
  * **State 2 - Item Terpilih:** Kolom input pencarian disembunyikan penuh, digantikan oleh **Kartu Item Terpilih** berdesain seragam (card beraksen ungu brand `#5E17EB`, icon box di kiri, label tipe kecil, nama item tebal, dan tombol aksi tegas `Ganti`).
  * **Aksi Ganti:** Tombol `Ganti` mereset nilai model yang terpilih dan pencarian, memunculkan kembali input autocomplete secara instan.
  * Menjamin 100% konsistensi alur seleksi data master di seluruh form aplikasi tanpa duplikasi kode atau tabulasi berlebih.

## 29. Universal SweetAlert Mutation Standard (POST, PUT, DELETE)
- **Wajib SweetAlert pada Seluruh Aksi Mutasi Data:**
  * Seluruh aksi form dan tombol yang melakukan mutasi data (**POST / Tambah Data Baru**, **PUT/PATCH / Update/Edit Data**, dan **DELETE / Hapus Data**) di seluruh modul aplikasi **WAJIB menggunakan SweetAlert2** untuk dialog konfirmasi (*pre-action confirm*) dan notifikasi umpan balik (*post-action toast*).
- **Standar Dialog Konfirmasi (Pre-Action Confirm):**
  * **POST (Simpan Baru):** Menampilkan konfirmasi SweetAlert (`window.swalConfirm` / `Swal.fire` ikon `question`) bertema brand solid ungu `#5E17EB`:
    - Judul: *"Simpan [Nama Data]?"*
    - Teks: *"Pastikan rincian data sudah lengkap dan benar."*
    - Tombol Aksi: *"Ya, Simpan"* (Warna brand solid `#5E17EB`) dan *"Batal"*.
  * **PUT / PATCH (Perbarui / Edit):** Menampilkan konfirmasi SweetAlert (`window.swalConfirm` / `Swal.fire` ikon `question`) bertema brand solid ungu `#5E17EB`:
    - Judul: *"Perbarui [Nama Data]?"*
    - Teks: *"Apakah Anda yakin ingin memperbarui data ini?"*
    - Tombol Aksi: *"Ya, Perbarui"* (Warna brand solid `#5E17EB`) dan *"Batal"*.
  * **DELETE (Hapus Data):** Menampilkan konfirmasi bahaya SweetAlert (`window.swalConfirm` dengan `danger: true` / `Swal.fire` ikon `warning`):
    - Judul: *"Hapus [Nama Data]?"*
    - Teks: *"Data [Nama/Nomor Data] akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan."*
    - Tombol Aksi: *"Ya, Hapus"* (Warna merah rose `#e11d48`) dan *"Batal"*.
- **Standar Notifikasi Berhasil (Post-Action Feedback):**
  * Setelah aksi berhasil diproses di backend Livewire, komponen **WAJIB men-dispatch event SweetAlert Toast** hijau (`$this->dispatch('swal:toast', ['type' => 'success', 'title' => 'Berhasil [Disimpan/Diperbarui/Dihapus]', 'message' => '...'])`).
  * Notifikasi toast otomatis muncul di pojok kanan atas dengan timer 3.5 detik tanpa memblokir layar kerja pengguna.
- **Sinergi dengan Standar Loading Kasir:**
  * Setelah pengguna mengonfirmasi SweetAlert, tombol aksi form tetap wajib mempertahankan status disabled dan spinner loading Heroicons SVG (`wire:loading wire:target="..."`) hingga response Livewire selesai.






