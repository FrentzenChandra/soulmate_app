# Project Guidelines (Soulmate App)

## 1. UI & Design Standards (Anti-AI Slop & Anti-Mojibake)
- Dilarang keras memakai gradient acak / multi-color mesh gradient (AI slop) kecuali diminta eksplisit.
- Gunakan solid modern color palette: Brand Purple (`#5E17EB`), Tech Blue (`#0052FF`), Accent Pink (`#E6007A`), neutral slate/gray, white cards, clean borders, subtle shadows.
- **Zero Placeholder Standard:** Seluruh inputan form, filter, textarea, modal, dan kolom pencarian dilarang menggunakan atribut `placeholder`.
- **Red Asterisk Standard:** Tanda bintang wajib isi wajib berwarna merah: `<span class="text-rose-500 font-bold">*</span>`.
- **Loading Kasir Standard:** Seluruh tombol aksi form (POST/PUT/DELETE) wajib menggunakan SVG Heroicons spinner (`wire:loading wire:target="..."`), `wire:loading.attr="disabled"`, dan teks proses deskriptif bertransisi.
- **Skeleton Loader:** Tabel dan katalog grid wajib menampilkan shimmer `<x-table-skeleton />` / `<x-grid-skeleton />` saat query/filter.
- **Sidebar Auto-Scroll:** `<nav>` sidebar otomatis memusatkan link menu aktif via Alpine `x-init` & `scrollIntoView({ block: 'center', behavior: 'smooth' })`.
- **Universal Table Font Size:** `<x-table-size-selector />` di seluruh tabel index (S: 11px, M: 12px, L: 13px) dengan `localStorage`.

## 2. Global Multi-Word Loose Search Standard
- Semua input pencarian wajib multi-kata tokenized (`explode(' ', $search)`), case-insensitive (`LOWER() LIKE ?`), non-strict per huruf/kata across relevant columns.

## 3. Branch Obfuscation & Role Isolation
- Non-owner roles (Superadmin, Admin, Teknisi) melihat aplikasi sebagai 1 toko mandiri ("Soulmate Komputer").
- Sembunyikan label cabang, switcher, dan badge cabang di navbar, header, notifikasi untuk non-owner. Hanya Owner yang punya akses multi-cabang / HQ.

## 4. Simplified Customer Data & Print / Portrait Standard
- Data pelanggan operasional: Murni Nama (`nama`) dan No. WhatsApp/HP (`no_hp`).
- Invoice number harian: Backend `INV-PJ-[KODE_CABANG]-[YYMMDD]-[00001]`, frontend `PJ-[YYMMDD]-[00001]`.
- **Standar Cetak Portrait:** Orientasi cetak **WAJIB PORTRAIT** (`@page { size: portrait; margin: 3mm 6mm; }`, dilarang landscape), font universal Arial, proporsional tinggi konten ~12 cm tanpa merusak layout. Format kuantiti berspasi (`1 UNIT`).
- **Grandtotal Standard:** Seluruh label total akhir pada seluruh nota/cetakan transaksi, faktur servis, dan POS wajib menggunakan teks `Grandtotal` (Title Case persis `Grandtotal`, dilarang `GRANDTOTAL`, `Grand Total`, atau `TOTAL TAGIHAN`).

## 5. Docker & Backend Execution
- Host OS: Windows. Application runtime: Docker Linux (`soulmate_apps`).
- Selalu eksekusi perintah backend/filesystem (`php artisan storage:link`, `php artisan migrate`, `php artisan optimize:clear`, dsb.) di dalam Docker: `docker exec soulmate_apps php artisan ...`.
- Windows Junction ≠ Linux Symlink. `storage:link` wajib dibuat di dalam container Linux agar tidak menghasilkan 403 Forbidden.
- Gunakan `Storage::disk('public')` dan URL relatif `storage/...`.

## 6. Komunikasi & Active Skills
- Jelaskan istilah teknis IT dengan analogi sederhana dan ramah pemula sebelum detail teknis.
- Mode komunikasi: ringkas, padat, hemat token (Caveman mode).
- Skill **Frontend UI Engineering** (`frontend-ui-engineering`) dan **Ponytail** (`ponytail`) WAJIB selalu aktif setiap saat di semua percakapan.

## 7. Graphify Auto-Update
- Gunakan `graphify query` untuk menelusuri relasi kode, god nodes, dan dependensi sebelum melakukan refaktor.

## 8. Targeted & Scoped Testing Standard
- Eksekusi pengujian secara tertarget per modul yang diubah (`docker exec soulmate_apps php artisan test --filter <TestClass>` atau path `tests/Feature/...`) untuk memangkas waktu tunggu. Dilarang full test di setiap iterasi kecil.

## 9. User Soft Delete & Historical Relations Standard
- Model `User` menggunakan `SoftDeletes`.
- Seluruh relasi `belongsTo(User::class)` pada model transaksi, kas, servis, audit (`TransaksiFull`, `ServisMasuk`, `KasKeluar`, `ModalAwal`, `CekKasLog`, `TutupKas`, `Penjualan`, `SerialNumber`, `ServisStatusLog`) WAJIB menggunakan `->withTrashed()` untuk menjaga keutuhan riwayat masa lalu.

## 10. Modul Transaksi (Transaksi FULL & Transaksi Harian)
- **Hanya 2 Status Valid:** `Lunas` dan `BON`. Status `Belum Lunas` dan `Batal` ditiadakan.
- **Transaksi Harian Zero Laba Standard:** Dilarang menampilkan informasi laba (profit/margin) dalam bentuk apapun pada modul Transaksi Harian (khusus operasional staf & kasir). Laba hanya bisa diakses pada Transaksi FULL oleh Superadmin & Owner.
- **Row-by-Row 1-to-1 Item Breakdown:** Rincian Item, Biaya, Modal, Laba, dan Pajak dijabarkan per baris 1-to-1. Kolom Pajak tampil murni `PPN` atau `Non PPN`.
- **Ukuran Modal Form:** `$modalFormOpen` menggunakan batas maksimal `sm:max-w-2xl`.
- **Isolasi Modal Form:** Dilarang men-dispatch event tabel Tabulator ke frontend saat modal form sedang dibuka.

## 11. Target Bulanan (Performance Board 12 Bulan — `/target-bulanan`)
- **Tabel Divisi (Laptop, Printer, Komputer, Admin):** Target diatur per tahun via modal 5-in-1 di toolbar atas (berlaku merata Jan-Des). Kolom ke-2 menampilkan nilai target tahunan. 12 Sel Bulan (Jan-Des) HANYA menampilkan angka realisasi riil yang bersih (tanpa teks `/ target` dan tanpa badge persentase `%`). Kolom Total YTD menampilkan total realisasi, perbandingan target tahunan berjalan (`/ total_target_ytd`), dan badge persentase YTD. Tanpa baris footer Total Divisi. Penyesuaian realisasi manual tetap didukung via klik per sel bulan.
- **Tabel Pegawai (Staf):** Realisasi murni 100% ditarik otomatis dari `transaksi_fulls` berdasarkan `pegawai_id`. Read-only performance summary tanpa kolom target, tanpa modal edit per cell, dan tanpa badge status merah.
- Slogan motivasi: *"Target Bukan Untuk Ditakuti, Tapi Untuk Dikalahkan!"*.

## 12. Master Pegawai & Bagian Dinamis (`/master/pegawai`)
- Menggunakan istilah **"Bagian"** (menggantikan "Role").
- 7 Bagian Default Baseline: `Teknisi Laptop`, `Teknisi Printer`, `Teknisi Komputer`, `Bendahara`, `Admin Pajak`, `Admin Stok`, `Admin Konten`.
- Dinamis via Select Distinct: Nilai dari database (`pegawais.role`) digabung dengan 7 bagian baseline.
- Form modal menyediakan opsi `+ Tambah Bagian Baru...` dengan input teks tambahan.
- Pewarnaan badge cerdas otomatis berdasarkan jenis bagian (Laptop: Biru, Printer: Amber, Komputer: Purple, Bendahara: Emerald, Pajak: Rose, Stok: Cyan, Konten: Indigo, default: Slate/Gray).

## 13. Universal SweetAlert Mutation Standard (POST, PUT, DELETE)
- **Wajib SweetAlert2:** Seluruh aksi mutasi data (POST / Simpan Baru, PUT / Update/Edit, DELETE / Hapus) di seluruh modul WAJIB menggunakan dialog konfirmasi SweetAlert (`window.swalConfirm` / `Swal.fire`) sebelum request diproses, dan notifikasi SweetAlert Toast hijau (`swal:toast` / `window.swalToast`) saat berhasil.
- **Konfirmasi POST:** Teks *"Simpan [Nama Data]? Pastikan rincian data sudah lengkap dan benar."* dengan tombol ungu `#5E17EB` (*"Ya, Simpan"*).
- **Konfirmasi PUT:** Teks *"Perbarui [Nama Data]? Apakah Anda yakin ingin memperbarui data ini?"* dengan tombol ungu `#5E17EB` (*"Ya, Perbarui"*).
- **Konfirmasi DELETE:** Teks bahaya *"Hapus [Nama Data]? Data akan dihapus secara permanen."* dengan tombol merah rose `#e11d48` (*"Ya, Hapus"*).
- **Feedback:** Dispatch `$this->dispatch('swal:toast', ['type' => 'success', 'title' => '...', 'message' => '...'])` di backend Livewire setelah eksekusi berhasil.

