# Stok 0 = Non-Aktif & Hilang dari Marketplace — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Saat stok cabang mencapai 0, baris stok menjadi non-aktif dan produk otomatis hilang dari Website Marketplace; reaktivasi tetap manual.

**Architecture:** Satu method statis `Stok::syncAvailability(int $produkId, bool $allowReactivate = false)` sebagai choke point, dipanggil setelah setiap mutasi stok. Default satu arah (hanya menonaktifkan); reaktivasi hanya untuk retur penjualan dan pembatalan servis. Query katalog kasir dan marketplace diberi pengaman `jumlah > 0` agar flag basi tidak menjual produk kosong.

**Tech Stack:** Laravel 12, Livewire 3, PostgreSQL (produksi), SQLite in-memory (test), PHPUnit.

**Spec:** `docs/superpowers/specs/2026-11-06-stok-nol-nonaktif-design.md`

## Global Constraints
- Tanpa trigger database, tanpa migrasi skema, tanpa kolom baru, tanpa dependency baru.
- Sinkronisasi hanya boleh mengubah `true -> false`; dilarang mengaktifkan kembali secara otomatis.
- Semua perintah artisan lewat Docker: `docker exec soulmate_apps php artisan ...`.
- Runtime & test memakai folder Desktop (Opsi A). Setelah menyalin file ke Desktop: `docker exec soulmate_apps php artisan view:clear`.
- Commit atomik per task, Conventional Commits bahasa Indonesia, `git add` per path spesifik.
- Dilarang `placeholder`, gradient, atau class Tailwind baru (tidak ada perubahan UI selain query filter).

## Review Focus
- Baris stok tanpa data (produk belum punya baris `stoks`) tidak boleh bikin error dan harus dianggap tidak tersedia.
- `increment()`/`decrement()` tidak memicu event model: bila satu jalur lupa memanggil sinkronisasi, produk harus tetap tidak terjual karena pengaman query.
- Retur penjualan dan pembatalan servis **harus** mengaktifkan kembali produk; penyesuaian stok manual **tidak boleh** mengaktifkan.
- Penyesuaian manual ke angka negatif harus tetap menonaktifkan.
- Command backfill dijalankan dua kali tidak boleh mengubah apa pun pada putaran kedua.

---

### Task 1: Model — choke point `Stok::syncAvailability()`

**Files:**
- Modify: `app/Models/Stok.php`
- Test: `tests/Feature/StokStatusSyncTest.php` (baru)

**Interfaces:**
- Produces: `public static function syncAvailability(int $produkId, bool $allowReactivate = false): void`

- [ ] **Step 1: Tulis test yang gagal**

```php
public function test_sync_marks_stock_inactive_and_hides_marketplace_when_zero()
{
    $produk = Produk::create([... 'ready_marketplace' => true]);
    $stok = Stok::create(['produk_id' => $produk->id, 'cabang_id' => $this->cabang1->id, 'jumlah' => 0, ...]);

    Stok::syncAvailability($produk->id);

    $this->assertFalse($stok->fresh()->is_aktif);
    $this->assertFalse($produk->fresh()->ready_marketplace);
}

public function test_sync_does_not_reactivate_without_permission()
{
    // stok jumlah 5 tapi is_aktif false, produk ready_marketplace false
    Stok::syncAvailability($produk->id); // default: satu arah

    $this->assertFalse($stok->fresh()->is_aktif);
    $this->assertFalse($produk->fresh()->ready_marketplace);
}

public function test_sync_reactivates_stock_and_marketplace_when_allowed()
{
    // stok cabang1 jumlah 5 (is_aktif false), produk ready_marketplace false
    Stok::syncAvailability($produk->id, true);

    $this->assertTrue($stok->fresh()->is_aktif);
    $this->assertTrue($produk->fresh()->ready_marketplace);
}

public function test_sync_keeps_marketplace_off_when_another_branch_is_zero()
{
    // cabang1 jumlah 5, cabang2 jumlah 0
    Stok::syncAvailability($produk->id, true);

    $this->assertTrue($stokCabang1->fresh()->is_aktif);
    $this->assertFalse($stokCabang2->fresh()->is_aktif);
    $this->assertFalse($produk->fresh()->ready_marketplace);
}

public function test_sync_handles_product_without_stock_row()
{
    Stok::syncAvailability($produk->id); // tidak boleh error
    $this->assertFalse($produk->fresh()->ready_marketplace);
}
```

- [ ] **Step 2: Jalankan, pastikan gagal** — `docker exec soulmate_apps php artisan test --filter StokStatusSyncTest` (harus error method tidak ada).

- [ ] **Step 3: Implementasi**

```php
public static function syncAvailability(int $produkId, bool $allowReactivate = false): void
{
    self::where('produk_id', $produkId)->where('jumlah', '<=', 0)->update(['is_aktif' => false]);

    $punyaBaris = self::where('produk_id', $produkId)->exists();
    $adaKosong = self::where('produk_id', $produkId)->where('jumlah', '<=', 0)->exists();

    if ($allowReactivate) {
        self::where('produk_id', $produkId)->where('jumlah', '>', 0)->update(['is_aktif' => true]);

        if ($punyaBaris && ! $adaKosong) {
            Produk::whereKey($produkId)->update(['ready_marketplace' => true]);
        }
    }

    if (! $punyaBaris || $adaKosong) {
        Produk::whereKey($produkId)->where('ready_marketplace', true)->update(['ready_marketplace' => false]);
    }
}
```

- [ ] **Step 4: Test hijau** — `--filter StokStatusSyncTest`.
- [ ] **Step 5: Commit** — `feat(stok): sinkronkan status produk saat stok habis`.

---

### Task 2: Kasir — 2 jalur bayar + pengaman katalog

**Files:**
- Modify: `app/Livewire/Kasir/Produk/Index.php:749`, `:872`, `:944`
- Test: `tests/Feature/StokStatusSyncTest.php`

- [ ] **Step 1: Test gagal** — jual semua stok via `prosesPembayaran()` dan via `simpanSebagaiBon()`, lalu assert baris stok `is_aktif = false` + `ready_marketplace = false`.
- [ ] **Step 2: Jalankan, pastikan gagal.**
- [ ] **Step 3: Implementasi** — setelah tiap `$stok->decrement('jumlah', ...)`, tambahkan `Stok::syncAvailability($item['id']);`. Pada query katalog (~944), tambahkan `->where('jumlah', '>', 0)` di dalam `whereHas('stoks', ...)`.
- [ ] **Step 4: Test hijau.**
- [ ] **Step 5: Commit** — `feat(kasir): produk habis otomatis non-aktif setelah pembayaran`.

---

### Task 3: Cabang > Stok — penyesuaian manual + filter "Habis"

**Files:**
- Modify: `app/Livewire/Cabang/Stok/Index.php:178`, `:329-333`
- Test: `tests/Feature/StokStatusSyncTest.php`

- [ ] **Step 1: Test gagal** — set stok manual ke 0 (dan ke -1) lalu assert `is_aktif = false`; filter `habis` tetap menampilkan produk yang `is_aktif = false` dengan `jumlah <= 0`.
- [ ] **Step 2: Jalankan, pastikan gagal.**
- [ ] **Step 3: Implementasi** — setelah `updateOrCreate`, panggil `Stok::syncAvailability($this->selectedProdukId);`. Pada filter `habis`, buang syarat `->where('is_aktif', true)`.
- [ ] **Step 4: Test hijau.**
- [ ] **Step 5: Commit** — `feat(stok): penyesuaian manual menonaktifkan produk dan filter habis abaikan status aktif`.

---

### Task 4: Servis & retur penjualan — pembatalan mengaktifkan kembali

**Files:**
- Modify: `app/Livewire/Servis/Masuk/Index.php:523`, `:594`, `:747`, `:782`
- Modify: `app/Livewire/Laporan/Penjualan/Index.php:411`
- Test: `tests/Feature/StokStatusSyncTest.php`

- [ ] **Step 1: Test gagal** — pakai sparepart sampai stok 0 -> non-aktif. Lalu retur penjualan / hapus item servis / batalkan servis -> stok naik, baris stok `is_aktif = true`, dan `ready_marketplace = true` bila semua cabang bersaldo positif.
- [ ] **Step 2: Jalankan, pastikan gagal.**
- [ ] **Step 3: Implementasi** — panggil `Stok::syncAvailability($det->produk_id, true)` (retur & pembatalan) dan `Stok::syncAvailability($det->produk_id)` (pemakaian sparepart di baris ~747, tanpa reaktivasi).
- [ ] **Step 4: Test hijau.**
- [ ] **Step 5: Commit** — `feat(servis): retur dan pembatalan mengaktifkan produk kembali`.

---

### Task 5: Master Produk & Master Servis (pembuatan stok awal)

**Files:**
- Modify: `app/Livewire/Master/Produk/Index.php:423`, `:443`, `:509-524`
- Modify: `app/Livewire/Master/Servis/Index.php:298`
- Test: `tests/Feature/Menu/MasterProdukTest.php`

- [ ] **Step 1: Test gagal** — buat produk baru dengan stok 0 di semua cabang -> `is_aktif = false` dan `ready_marketplace = false`.
- [ ] **Step 2: Jalankan, pastikan gagal.**
- [ ] **Step 3: Implementasi** — setelah blok `updateOrCreate` stok pada create/edit, panggil `Stok::syncAvailability($produk->id)`. Pada `deleteProduk` non-HQ, cukup jalankan `Stok::where('produk_id',$id)->where('cabang_id',$targetCabangId)->update(['jumlah' => 0])` lalu `Stok::syncAvailability($id)`, sehingga aturan `is_aktif` dan `ready_marketplace` tidak ditulis dua kali.
- [ ] **Step 4: Test hijau** (`MasterProdukTest` harus tetap 27 lulus).
- [ ] **Step 5: Commit** — `feat(produk): stok awal nol langsung non-aktif`.

---

### Task 6: Marketplace — sembunyikan produk tanpa stok

**Files:**
- Modify: `app/Livewire/Web/Produk.php:93`, `:246`
- Test: `tests/Feature/StokStatusSyncTest.php`

- [ ] **Step 1: Test gagal** — produk `ready_marketplace = true` tapi stok 0 tidak boleh muncul di katalog dan tidak bisa `addToCart`.
- [ ] **Step 2: Jalankan, pastikan gagal.**
- [ ] **Step 3: Implementasi** — tambah `whereHas('stoks', fn ($q) => $q->where('jumlah', '>', 0))` pada query katalog, `addToCart`, dan hitungan katalog.
- [ ] **Step 4: Test hijau.**
- [ ] **Step 5: Commit** — `fix(marketplace): sembunyikan produk yang stoknya habis`.

---

### Task 7: Backfill data lama

**Files:**
- Create: `app/Console/Commands/SyncStatusStok.php`
- Test: `tests/Feature/StokStatusSyncTest.php`

- [ ] **Step 1: Tulis command** — `php artisan stok:sync-status`; query hanya satu arah:
  `Stok` set `is_aktif = false` where `jumlah <= 0`; lalu untuk setiap produk yang punya baris `jumlah <= 0` (atau tanpa baris stok) set `ready_marketplace = false`.
- [ ] **Step 2: Test** — jalankan command dua kali, assert hasil putaran kedua tidak mengubah jumlah baris yang berubah (idempotent).
- [ ] **Step 3: Jalankan di container** — `docker exec soulmate_apps php artisan stok:sync-status`, catat output (target saat ini: 1 baris stok, 1 produk).
- [ ] **Step 4: Verifikasi SQL** — `select count(*) from stoks where jumlah <= 0 and is_aktif = true;` harus `0`.
- [ ] **Step 5: Commit** — `feat(stok): command backfill status stok lama`.

---

## Setelah selesai
- Jalankan `--filter "MasterProdukTest|StokStatusSyncTest|ProductAndStockTest"`, bandingkan `ProductAndStockTest` dengan baseline 10 gagal / 11 lulus.
- Commit dokumentasi terpisah: `docs(stok): tambah design dan plan stok nol non-aktif`.
- Push ke `origin aceng/master-produk` setelah semua test sesuai target.
