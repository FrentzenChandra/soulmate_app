# Aktifkan Produk: Urut Field & Tanpa Redirect Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Modal "Aktifkan Produk" memakai urutan field Harga Modal &rarr; Harga Jual &rarr; Jumlah Stok, dan setelah aktivasi tidak lagi redirect ke Master Produk.

**Architecture:** Satu perubahan Blade (urutan grid 3 kolom) dan satu perubahan Livewire (`saveAdjustment`) yang membuang blok `redirect()` + `session()->flash()` untuk aktivasi, lalu menyamakan umpan balik via `swal:toast` untuk semua role.

**Tech Stack:** Laravel 11, Livewire 3, Blade, Tailwind, PHPUnit/Livewire Test.

**Spec:** Permintaan user sesi 2026-11-07 (revisi posisi field + batal redirect pasca aktivasi).

## Global Constraints

- Perintah backend/framework wajib lewat container: `docker exec soulmate_apps ...`.
- Test tertarget saja (`--filter`), bukan full suite.
- Zero placeholder pada input; bintang wajib `text-rose-500`.
- Semua mutasi data wajib konfirmasi + toast SweetAlert.
- Commit atomik per perubahan logis (Conventional Commits, Bahasa Indonesia).

## Review Focus

1. Flash `success` aktivasi yang tidak terpakai (tidak ada reload) bisa "nyangkut" dan muncul sebagai toast dobel di navigasi berikutnya &rarr; harus dihapus, bukan dibiarkan.
2. Modal harus tertutup (`modalAdjustOpen = false`) setelah simpan, karena tidak ada lagi navigasi yang memaksa close.
3. Daftar stok harus ter-refresh (`stockUpdated` + tabel re-render) supaya produk yang baru aktif langsung terlihat berubah status.
4. Role tanpa `master.produk.view` tetap mendapat toast (jalur lama hanya untuk mereka) &rarr; jangan sampai notifikasi hilang.
5. Guard permission `abort_if(...)` tetap utuh, jangan ikut terhapus saat membuang blok redirect.

---

### Task 1: Urutan field modal aktivasi

**Files:**
- Modify: `resources/views/livewire/cabang/stok/index.blade.php:395-464`
- Test: `tests/Feature/ProductAndStockTest.php` (`test_inactive_stock_opens_activation_mode_with_existing_prices`)

**Interfaces:**
- Consumes: properti Livewire `$hargaBeli`, `$hargaJual`, `$newStock` (tidak berubah).
- Produces: urutan markup `Harga Modal` &rarr; `Harga Jual` &rarr; `Jumlah Stok`.

- [ ] **Step 1: Tambah assertion urutan pada test mode aktivasi**

```php
->assertSet('modeAdjust', 'aktifkan')
->assertSeeInOrder(['Harga Modal', 'Harga Jual', 'Jumlah Stok'])
```

- [ ] **Step 2: Jalankan test, pastikan gagal sebelum swap**

Run: `docker exec soulmate_apps php artisan test --filter test_inactive_stock_opens_activation_mode_with_existing_prices`
Expected: FAIL (urutan masih Harga Jual lalu Harga Modal).
Catatan: swap Blade sudah diterapkan lebih dulu atas permintaan user, jadi fase merah tidak terobservasi untuk step ini; verifikasi cukup lewat step 4.

- [x] **Step 3: Swap blok input di Blade**

Pindahkan `<div>` berisi label `Harga Modal` + input `wire:model.live.debounce.400ms="hargaBeli"` ke posisi pertama grid, sebelum blok `Harga Jual`. Atribut input tidak diubah. (Sudah diterapkan.)

- [ ] **Step 4: Jalankan ulang test**

Run: `docker exec soulmate_apps php artisan test --filter test_inactive_stock_opens_activation_mode_with_existing_prices`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/cabang/stok/index.blade.php tests/Feature/ProductAndStockTest.php
git commit -m "style(stok-cabang): urutkan field modal aktifkan menjadi modal, jual, stok"
```

### Task 2: Hapus redirect ke Master Produk setelah aktivasi

**Files:**
- Modify: `app/Livewire/Cabang/Stok/Index.php:237-249`
- Test: `tests/Feature/ProductAndStockTest.php:220-260` (ganti `test_activating_inactive_stock_updates_prices_stock_and_redirects_to_master`)

**Interfaces:**
- Consumes: `$this->selectedProdukName`, `$this->newStock`, event `stockUpdated`.
- Produces: `saveAdjustment()` tetap `void`; tidak ada `redirect()`; selalu `dispatch('swal:toast', ['type' => 'success', 'title' => 'Produk Diaktifkan', 'text' => ...])`.

- [ ] **Step 1: Ubah test agar menuntut tanpa redirect**

```php
public function test_activating_inactive_stock_updates_prices_and_stays_on_page()
{
    // ...setup produk + stok nonaktif (sama seperti test lama)...
    Livewire::test(\App\Livewire\Cabang\Stok\Index::class)
        ->call('openAdjustModal', $produk->id)
        ->set('hargaBeli', '210.000')
        ->set('hargaJual', '315.000')
        ->set('newStock', 6)
        ->call('saveAdjustment')
        ->assertHasNoErrors()
        ->assertNoRedirect()
        ->assertSet('modalAdjustOpen', false)
        ->assertDispatched('stockUpdated')
        ->assertDispatched('swal:toast');
}
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `docker exec soulmate_apps php artisan test --filter test_activating_inactive_stock_updates_prices_and_stays_on_page`
Expected: FAIL pada `assertNoRedirect()` (masih redirect ke `master.produk`).

- [ ] **Step 3: Hapus blok redirect + flash pada `saveAdjustment`**

Hapus baris `session()->flash('success', "Produk \"...\" aktif kembali ...")` dan seluruh blok:

```php
if ($user->isOwner() || $user->can('master.produk.view')) {
    $this->redirect(route('master.produk', ['search' => $this->selectedProdukName]));

    return;
}
```

Sisakan `$this->dispatch('swal:toast', [...])` sebagai satu-satunya umpan balik aktivasi (berlaku semua role).

- [ ] **Step 4: Jalankan test targeted modul terkait**

Run: `docker exec soulmate_apps php artisan test --filter ProductAndStockTest`
Expected: PASS semua.

- [ ] **Step 5: Commit**

```bash
git add app/Livewire/Cabang/Stok/Index.php tests/Feature/ProductAndStockTest.php
git commit -m "fix(stok-cabang): hentikan redirect ke master produk setelah aktifkan produk"
```
