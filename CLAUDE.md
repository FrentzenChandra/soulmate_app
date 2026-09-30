# Project Guidelines (Soulmate App)

## 1. UI & Design Standards (Anti-AI Slop)
- Dilarang keras memakai gradient acak / multi-color mesh gradient (AI slop) kecuali diminta eksplisit.
- Gunakan solid modern color palette: Brand Purple (`#5E17EB`), Tech Blue (`#0052FF`), Accent Pink (`#E6007A`), neutral slate/gray, white cards, clean borders, subtle shadows.
- Sidebar Auto-Scroll: `<nav>` sidebar otomatis memusatkan link menu aktif via Alpine `x-init` & `scrollIntoView({ block: 'center', behavior: 'smooth' })`.

## 2. Global Multi-Word Loose Search Standard
- Semua input pencarian wajib multi-kata tokenized (`explode(' ', $search)`), case-insensitive (`LOWER() LIKE ?`), non-strict per huruf/kata across relevant columns.

## 3. Branch Obfuscation & Role Isolation
- Non-owner roles (Superadmin, Admin, Teknisi) melihat aplikasi sebagai 1 toko mandiri ("Soulmate Komputer").
- Sembunyikan label cabang, switcher, dan badge cabang di navbar, header, notifikasi untuk non-owner. Hanya Owner yang punya akses multi-cabang / HQ.

## 4. Simplified Customer Data & Single-Input Autocomplete
- Data pelanggan form transaksi/servis/kasir: Murni 1 input Nama Pelanggan (`nama`) saja, tanpa input WhatsApp / No HP.
- Single-Input Hybrid Autocomplete: 1 input nama pelanggan untuk autocomplete terdaftar maupun custom fallback tanpa tombol tab switcher.
- Invoice number harian: Backend `INV-PJ-[KODE_CABANG]-[YYMMDD]-[00001]`, frontend `PJ-[YYMMDD]-[00001]`.
- Cetakan nota: Format kuantiti berspasi (`1 UNIT`).

## 5. Docker & Backend Execution
- Host OS: Windows. Application runtime: Docker Linux (`soulmate_apps`).
- Selalu eksekusi perintah backend/filesystem (`php artisan storage:link`, `php artisan migrate`, `php artisan optimize:clear`, dsb.) di dalam Docker: `docker exec soulmate_apps php artisan ...`.
- Windows Junction ≠ Linux Symlink. `storage:link` wajib dibuat di dalam container Linux agar tidak menghasilkan 403 Forbidden.
- Gunakan `Storage::disk('public')` dan URL relatif `storage/...`.

## 6. Komunikasi & Edukasi Konsep
- Jelaskan istilah teknis IT dengan analogi sederhana dan ramah pemula sebelum detail teknis.
- Mode komunikasi: ringkas, padat, hemat token (Caveman mode).

## 7. Graphify Auto-Update
- Jalankan `python -m graphify.cli update` setiap selesai melakukan perubahan kode.

## 8. Targeted & Scoped Testing Standard
- Eksekusi pengujian secara tertarget per modul yang diubah (`docker exec soulmate_apps php artisan test --filter <TestClass>` atau path `tests/Feature/...`) untuk memangkas waktu tunggu dari ~3 menit menjadi 2-10 detik. Dilarang full test di setiap iterasi kecil.

## 9. User Soft Delete & Historical Relations Standard
- Model `User` menggunakan `SoftDeletes`.
- Seluruh relasi `belongsTo(User::class)` pada model transaksi, kas, servis, audit (`TransaksiFull`, `ServisMasuk`, `KasKeluar`, `ModalAwal`, `CekKasLog`, `TutupKas`, `Penjualan`, `SerialNumber`, `ServisStatusLog`) WAJIB menggunakan `->withTrashed()` untuk menjaga keutuhan riwayat masa lalu.

## 10. Simplified Transaction Status Standard
- Modul Transaksi (Transaksi FULL dan Transaksi Harian) hanya memiliki 2 status valid: `Lunas` dan `BON`. Status `Belum Lunas` dan `Batal` ditiadakan.

## 11. Target Bulanan (Performance Board)
- Route: `/target-bulanan`.
- Realisasi otomatis 12 bulan dari `transaksi_fulls` (Divisi: Laptop, Printer, Komputer, Admin; serta per Staf/User).
- Kolom Total Persyaratan 1 Tahun di sisi kanan tabel.
- Input target & realisasi mandiri per sel `(Divisi, Bulan)` dan `(User, Bulan)` hanya oleh Owner & Superadmin.

## 12. Service Ticket Sub-Caption & Modular Logging
- Sub-caption No. Servis di tabel servis menampilkan nama user/staf penginput tiket (`text-gray-600 font-semibold`).
- Log harian API & menu modular tersimpan di subfolder per modul (`storage/logs/{menu}/menu-{menu}-YYYY-MM-DD.log`).



