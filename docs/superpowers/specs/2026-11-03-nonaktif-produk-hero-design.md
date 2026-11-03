# Design: Tombol Nonaktif Produk (Stok 0 + `ready_marketplace` false) & Banner Hero

Tanggal: 2026-11-03 (revisi 1)
Status: menunggu persetujuan
Revisi: pendekatan berubah dari B (branch-scoped) ke A (global hide) atas keputusan user.

## 1. Intent

Staf yang menonaktifkan produk dari Master Produk ingin efek nyata dalam satu klik:

- stok produk di cabang itu menjadi 0 (bukan cuma ditandai nonaktif),
- produk tidak lagi muncul di marketplace publik,
- gambar produk di modal konfirmasi cukup besar untuk dikenali (gaya hero), bukan thumbnail 40px.

## 2. Fakta kode saat ini (hasil pembacaan)

| Lokasi | Kondisi sekarang |
|---|---|
| `app/Livewire/Master/Produk/Index.php:509-520` | Jalur non-HQ hanya `Stok::update(['is_aktif' => false])`. Stok tetap, `ready_marketplace` tidak disentuh. |
| `app/Livewire/Master/Produk/Index.php:522-531` | Jalur HQ Owner = `delete()` permanen (soft delete) bila tanpa riwayat; bila ada riwayat hanya flash error, tidak ada aksi. |
| `app/Livewire/Web/Produk.php:244-246` | Query marketplace hanya `where('ready_marketplace', true)`. Stok tidak memengaruhi visibilitas sama sekali. |
| `database/migrations/2026_09_11_100006_create_produks_table.php:21` | `ready_marketplace` = satu baris global untuk semua cabang. |
| Tabel `stoks` | Tidak ada tabel riwayat/audit mutasi stok. Nol-kan `jumlah` = angka lama hilang permanen. |

Kesimpulan: stok 0 **tidak** menyembunyikan produk. Saklar yang benar-benar bekerja adalah `ready_marketplace`, dan itu sudah difilter query marketplace tanpa perlu diubah.

## 3. Goals

1. Nonaktif di 1 cabang membuat stok cabang itu `0` dan `is_aktif = false`.
2. Produk langsung hilang dari marketplace publik (`ready_marketplace = false`).
3. Modal konfirmasi menampilkan banner hero foto produk + perubahan stok yang akan terjadi (`5 Unit -> 0 Unit`).
4. Modal tetap ringkas bila produk tidak punya foto.
5. Pesan sukses menjelaskan kedua efek, supaya staf tidak bingung saat produk hilang dari web.

## 4. Non-goals

- Tidak menambah tabel/kolom baru, tidak ada migrasi.
- Tidak mengubah query `Web\Produk.php` (sudah benar, `ready_marketplace` saja).
- Tidak mengubah jalur HQ Owner (`Hapus Produk Master`) — tetap soft delete seperti sekarang.
- Tidak membuat fitur "aktifkan kembali" yang memulihkan stok lama maupun flag marketplace.
- Tidak menyentuh `w-13/h-13` mati di `saldo` dan `antrian-display` (ditunda atas permintaan).
- Tidak mengubah desain halaman marketplace publik.

## 5. Anti-goals (kalau ini terjadi, hasil dianggap gagal)

- Stok cabang lain ikut menjadi 0 karena aksi 1 cabang.
- Produk terhapus permanen padahal user hanya ingin menonaktifkan.
- Modal lebih tinggi dari viewport di layar 768px.
- Produk tetap muncul di marketplace setelah nonaktif (bug utama yang dilaporkan).

## 6. Pendekatan yang dipilih

### Backend — Opsi A "global hide" (dipilih user, revisi)

Ditolak sebelumnya:
- **B (branch-scoped):** menambah syarat `whereHas('stoks', ...)` di query marketplace dan membiarkan `ready_marketplace` utuh. Lebih benar untuk multi-cabang, tapi user memilih kesederhanaan: sembunyikan sekarang.
- **C (stok saja):** tidak menyembunyikan apa pun. Tidak memenuhi Intent.

Opsi A:
1. `deleteProduk()` jalur non-HQ:
   `Stok::where(produk_id)->where(cabang_id)->update(['jumlah' => 0, 'is_aktif' => false])`
2. `$produk->update(['ready_marketplace' => false])`.
3. Flash sukses menyebut: stok cabang dinolkan, produk disembunyikan dari marketplace.

Konsekuensi yang diterima user: 1 staf cabang bisa menyembunyikan produk dari marketplace semua cabang. Ini sengaja, dicatat di bagian Risiko.

### Tampilan — Opsi H1 "banner hero" (dipilih user)

- Foto produk dirender sebagai banner di atas card snapshot: lebar penuh, `h-36` (144px), `object-contain` di atas `bg-gray-50`, `border-b`, tanpa gradient (patuh AGENTS.md Standar 2).
- Banner dibungkus tautan `target="_blank"` supaya foto bisa dibuka ukuran penuh.
- Nama, barcode, kategori, dan stok turun ke baris di bawah banner.
- Tanpa foto: banner tidak dirender, layout ikon kotak lama dipertahankan.
- Stok ditampilkan: angka lama dicoret + panah + `0 Unit` badge rose.
- Tambahan baris peringatan kecil: "Produk juga disembunyikan dari Website Marketplace".

## 7. Yang dibiarkan ke builder

- Penamaan komentar, urutan `use` statement.
- Banner memakai `<img>` polos di dalam `<a>` (tanpa komponen baru).
- Detail skeleton/spinner tetap mengikuti Standar Loading Kasir di `AGENTS.md`.

## 8. File yang disentuh

1. `app/Livewire/Master/Produk/Index.php` — ubah `deleteProduk()` jalur non-HQ (stok 0 + `ready_marketplace` false + flash).
2. `resources/views/livewire/master/produk/index.blade.php` — banner hero + preview stok `-> 0 Unit` + baris peringatan marketplace.
3. `tests/Feature/ProductAndStockTest.php` — 2 test tertarget.

`app/Livewire/Web/Produk.php` **tidak disentuh** pada revisi ini.

## 9. Test plan (tertarget, bukan full suite)

- `test_nonaktif_produk_menolkan_stok_dan_mematikan_marketplace` — stok cabang A jadi 0, `is_aktif` false, `ready_marketplace` false, stok cabang B tidak berubah.
- `test_produk_nonaktif_tidak_tampil_di_marketplace` — produk `ready_marketplace = false` tidak muncul di komponen `Web\Produk`.

Dijalankan: `docker exec soulmate_apps php artisan test --filter ProductAndStockTest`.

Baseline: 10 gagal / 11 lulus sebelum perubahan ini (403 gate + snapshot Livewire). Perbandingan sebelum vs sesudah, bukan klaim hijau.

## 10. Risiko yang diterima

- `jumlah` di-nol-kan tanpa audit trail (tabel stok tidak punya riwayat). Angka lama tidak bisa dipulihkan otomatis; hanya terlihat di preview modal sebelum dikonfirmasi. Dicatat sebagai `ponytail:` comment di kode.
- 1 staf cabang dapat menyembunyikan produk dari marketplace seluruh perusahaan. Untuk mengembalikan: buka Master Produk, nyalakan lagi "Tampilkan di Website Marketplace", lalu isi stok lewat menu Stok Cabang.
- Produk tanpa riwayat transaksi di konteks HQ tetap memakai jalur hapus permanen (tidak berubah).
