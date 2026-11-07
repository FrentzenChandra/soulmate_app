# Design Doc - Faktur Pajak: Label Modal & 5 Metric Card

Tanggal: 2026-11-07
Modul: Faktur Pajak (`/faktur-pajak`)
Status: menunggu persetujuan

## 1. Intent & Why

Dua revisi dari pemilik:

1. Istilah **"DPP Lain"** membingungkan di lapangan. Nilai itu sebenarnya adalah **Modal** (harga pokok) yang dipakai sebagai dasar PPN. Istilah di UI diganti menjadi **Modal**, tanpa mengubah rumus.
2. Card ringkasan baru menampilkan **5 metrik** (Bruto, DPP, Modal, PPN, PPh) dengan warna yang sama seperti kolom di tabel, supaya mata bisa mencocokkan card dan tabel tanpa membaca ulang.

## 2. Definisi & Dokumentasi Istilah

**DPP Lain = Modal.** Ini fakta domain, bukan sekadar rename kosmetik:

- Kolom database tetap `faktur_pajaks.dpp_lain` (nama historis).
- Rumus tidak berubah: `dpp_lain = dpp × (11 / 12)`.
- Seluruh label UI, export, dan dokumentasi menyebutnya **Modal**.
- Dokumentasi ditempel di tiga tempat yang dibaca developer:
  - docblock properti di `app/Models/FakturPajak.php` (dekat `$casts`),
  - komentar di blok kalkulasi `app/Livewire/FakturPajak/Index.php`,
  - bagian ini di design doc.
- Dokumen ini juga mencatat alasan kenapa kolom DB tidak di-rename, agar developer berikutnya tidak "memperbaiki" nama kolom tanpa tahu konteksnya.

## 3. Goals

- Label `DPP Lain` hilang dari seluruh UI Faktur Pajak dan diganti `Modal`.
- 5 card metrik: Total Bruto, Total DPP, Total Modal, Total PPN, Total PPh.
- Warna card mengikuti warna kolom tabel: Bruto ungu, DPP hitam, Modal hitam, PPN biru, PPh merah.
- Angka card tetap mengikuti filter aktif (periode, status, via, kode, rekanan, pencarian, cabang).

## 4. Non-Goals

- Tidak ada perubahan rumus pajak, tipe data, atau nilai tersimpan.
- Tidak ada migrasi database.
- Tidak ada perubahan permission, route, atau alur simpan/hapus.

## 5. Approaches

### A. Rename label saja, kolom DB tetap `dpp_lain` (dipilih)

Titik sentuh: Tabulator column title, rincian kalkulasi modal form, modal detail, header export Excel, plus dokumentasi istilah.

- Trade-off: ada ketidakcocokan nama kolom DB vs label UI; ditutup dengan dokumentasi di model + komentar kalkulasi.
- Risiko: nol. Tidak menyentuh data historis, test lama, atau query.

### B. Rename kolom DB `dpp_lain` -> `modal` (ditolak)

Butuh migrasi + rewrite query, payload tabel, test, dan data lama. Manfaatnya nol untuk pengguna, sementara risiko migrasi produksi nyata. Ditolak.

## 6. Metric Card

- Query ringkasan (`Index.php`) menambah `SUM(dpp) as total_dpp` dan `SUM(dpp_lain) as total_modal`.
- Urutan card: Bruto (`purple`), DPP (`black`), Modal (`black`), PPN (`blue`), PPh (`rose`).
- Card PPh sekarang masih `pink` `#E6007A`; diganti `rose` (`text-rose-600`) agar sama dengan kolom PPh di tabel.
- Komponen `resources/views/components/metric-card.blade.php`: tambah varian warna `rose` (varian `black` sudah ada).
- Grid: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5`; skeleton loading ikut 5 kotak dengan grid yang sama.

## 7. Verifikasi

- Test tertarget: `docker exec soulmate_apps php artisan test --filter FakturPajakTest`.
- Test mengunci label 5 card dan nilai Total DPP / Total Modal.
- Cek visual: kolom tabel, rincian kalkulasi form, modal detail, export Excel.

## 8. Yang Diserahkan ke Builder

- Penamaan variabel internal (`$dppLain`) boleh tetap; tidak wajib diganti karena bukan istilah UI.
- Ikon card DPP/Modal memakai ikon fallback komponen bila tidak ada ikon khusus.
