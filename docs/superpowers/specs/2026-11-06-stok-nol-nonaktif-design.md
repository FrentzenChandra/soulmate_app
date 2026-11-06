# Stok 0 = Non-Aktif & Hilang dari Marketplace — Design

Tanggal: 2026-11-06
Status: menunggu persetujuan sebelum implementasi

## Intent
Menyesuaikan status ketersediaan produk secara otomatis ketika stok habis, tanpa trigger database dan tanpa kolom baru.

## Perilaku yang diminta
1. Stok suatu cabang mencapai `<= 0` -> baris `stoks.is_aktif = false`.
2. Jika **ada salah satu** baris stok produk yang `jumlah <= 0` -> `produks.ready_marketplace = false` sehingga produk hilang dari Website Marketplace.
3. Sinkronisasi **satu arah**: sistem hanya boleh menonaktifkan. Mengaktifkan kembali sepenuhnya manual lewat Edit Produk (toggle marketplace + centang status stok per cabang).
4. Filter stok "Habis" di Cabang > Stok menampilkan semua `jumlah <= 0` tanpa melihat `is_aktif`.
5. Lonceng notifikasi tidak diubah: produk non-aktif memang hilang dari lonceng.

## Non-goals
- Tidak ada trigger PostgreSQL, tidak ada event observer yang bergantung pada `increment()/decrement()`.
- Tidak ada migrasi skema atau kolom baru.
- Tidak mengubah tampilan/filter Master Produk yang sudah ada (`non_aktif` tetap).
- Tidak mengaktifkan produk otomatis saat stok masuk kembali.

## Keputusan yang sudah diambil
| Hal | Keputusan |
| --- | --- |
| Lokasi logika | Backend saja, satu method di model `Stok` |
| Reaktivasi | Manual, tidak otomatis |
| Filter "Habis" | `jumlah <= 0`, abaikan `is_aktif` |
| Marketplace | False bila ada satu cabang dengan stok 0 |
| Lonceng | Tidak berubah |
| Backfill data lama | Command artisan idempotent, satu arah (hanya menonaktifkan) |

## Pendekatan yang dipilih
Backend terpusat: satu method `Stok::syncAvailability()` dipanggil setelah setiap mutasi stok, ditambah pengaman pada query katalog (kasir & marketplace) agar flag yang basi tidak membuat produk kosong tetap terjual.

Alternatif yang ditolak:
- **Trigger database**: tidak terbawa ke test SQLite in-memory, logika tersembunyi, sulit di-rollback.
- **Flag dihapus, status dihitung saat query**: mengubah banyak titik baca dan menghilangkan kendali manual pemilik toko.

## Risiko
- Jalur tulis baru di masa depan bisa lupa memanggil method -> dimitigasi dengan pengaman query `jumlah > 0` di kasir dan marketplace.
- Aturan marketplace "salah satu cabang 0" membuat produk mudah hilang dari web bila satu cabang tidak distok. Ini keputusan pemilik toko; konsekuensinya dicatat di sini.
- `increment()/decrement()` Eloquent tidak memicu event model, jadi pemanggilan harus eksplisit di setiap jalur.
