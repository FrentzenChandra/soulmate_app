# Nonaktifkan Produk (Stok 0 + Marketplace Off) & Banner Hero Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Tombol nonaktif produk menolkan stok cabang aktif dan mematikan `ready_marketplace`, plus modal konfirmasi memakai banner foto bergaya hero.

**Architecture:** Satu perubahan backend di jalur non-HQ `deleteProduk()` (stok cabang sendiri jadi 0 + `ready_marketplace = false`), satu perubahan Blade di modal konfirmasi (banner `h-36` + preview `5 Unit -> 0 Unit`). Query marketplace tidak disentuh karena sudah menyaring `ready_marketplace`.

**Tech Stack:** Laravel 12, Livewire 3, Tailwind CSS 3.4, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-11-03-nonaktif-produk-hero-design.md`

## Global Constraints

- Tailwind v3.4: hanya class yang sudah ada di `public/build/assets/app-*.css` yang boleh dipakai. `w-13`/`h-13` TIDAK valid. `h-36`, `object-contain`, `line-through`, `bg-gray-50` sudah terverifikasi ada.
- Dilarang gradient / mesh multi-warna. Solid: brand purple `#5E17EB`, tech blue `#0052FF`, danger rose `#e11d48` (`rose-500`/`rose-600`).
- Anti-mojibake: pakai entity `&rarr;`, `&bull;`, jangan karakter mentah.
- Dilarang atribut `placeholder` pada input apa pun.
- Semua perintah artisan wajib lewat Docker: `docker exec soulmate_apps php artisan ...`.
- Commit atomik per task, Conventional Commits bahasa Indonesia, `git add` per path spesifik (dilarang `git add -A`).
- Jalur HQ Owner (`Hapus Produk Master`) tidak diubah. `app/Livewire/Web/Produk.php` tidak diubah.
- Tanpa migrasi, tanpa kolom baru, tanpa dependency baru.

## Review Focus

- Stok cabang lain ikut menjadi 0 karena aksi 1 cabang — test `test_superadmin_deactivates_product_zeroes_stock_and_hides_marketplace` (Task 1).
- Produk dengan `ready_marketplace = true` tapi stok 0 masih tampil di marketplace — test `test_deactivated_product_is_hidden_from_public_marketplace` (Task 1).
- Produk tanpa baris `stoks` untuk cabang itu (data lama) bikin error `update()` — test `test_deactivating_product_without_stock_row_does_not_error` (Task 1).
- Modal produk tanpa foto menampilkan kotak banner kosong raksasa — test `test_delete_modal_has_no_hero_banner_when_product_has_no_photo` (Task 2).
- Stok berubah 0 tapi `is_aktif` masih true (update setengah jalan) — assertion `jumlah` + `is_aktif` di satu `assertDatabaseHas` (Task 1).

---

### Task 1: Backend — nonaktifkan produk menolkan stok cabang dan mematikan marketplace

**Files:**
- Modify: `app/Livewire/Master/Produk/Index.php:509-520`
- Test: `tests/Feature/Menu/MasterProdukTest.php`

**Interfaces:**
- Consumes: `App\Models\Stok` (`update(['jumlah' => int, 'is_aktif' => bool])`), `App\Models\Produk::update(array)`, `App\Livewire\Web\Produk` (komponen marketplace, tidak diubah).
- Produces: perilaku `deleteProduk(int $id)` jalur non-HQ; tidak ada signature baru.

- [ ] **Step 1: Catat baseline test**

Run: `docker exec soulmate_apps php artisan test --filter MasterProdukTest`
Expected: catat jumlah PASS/FAIL sebagai pembanding.

- [ ] **Step 2: Tambah import di `tests/Feature/Menu/MasterProdukTest.php`**

Tambahkan setelah `use App\Livewire\Master\Produk\Index as MasterProdukIndex;`:

```php
use App\Livewire\Web\Produk as WebProduk;
use App\Models\Stok;
```

- [ ] **Step 3: Tulis 3 test yang gagal**

Tambahkan method berikut di dalam class, setelah `test_superadmin_deactivates_product_stock_in_branch`:

```php
public function test_superadmin_deactivates_product_zeroes_stock_and_hides_marketplace(): void
{
    $this->actingAsRole('Superadmin', [], $this->cabang1);

    $cabang2 = Cabang::create([
        'nama_cabang' => 'Cabang Sungailiat',
        'kode_cabang' => 'CB-SGL',
        'status' => 'aktif',
    ]);

    $produk = Produk::create([
        'nama_produk' => 'Mouse Pad XL Polos',
        'barcode' => 'MPAD-XL',
        'harga_beli' => 35000,
        'harga_jual' => 55000,
        'ready_marketplace' => true,
    ]);

    Stok::create(['produk_id' => $produk->id, 'cabang_id' => $this->cabang1->id, 'jumlah' => 5, 'min_stok' => 2, 'is_aktif' => true]);
    Stok::create(['produk_id' => $produk->id, 'cabang_id' => $cabang2->id, 'jumlah' => 7, 'min_stok' => 2, 'is_aktif' => true]);

    Livewire::test(MasterProdukIndex::class)
        ->call('openDeleteConfirmation', $produk->id)
        ->call('confirmDelete')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('stoks', [
        'produk_id' => $produk->id,
        'cabang_id' => $this->cabang1->id,
        'jumlah' => 0,
        'is_aktif' => 0,
    ]);

    $this->assertDatabaseHas('stoks', [
        'produk_id' => $produk->id,
        'cabang_id' => $cabang2->id,
        'jumlah' => 7,
        'is_aktif' => 1,
    ]);

    $this->assertFalse($produk->fresh()->ready_marketplace);
}

public function test_deactivated_product_is_hidden_from_public_marketplace(): void
{
    $this->actingAsRole('Superadmin', [], $this->cabang1);

    $produk = Produk::create([
        'nama_produk' => 'Mouse Pad XL Polos',
        'barcode' => 'MPAD-XL',
        'harga_beli' => 35000,
        'harga_jual' => 55000,
        'ready_marketplace' => true,
    ]);

    Stok::create(['produk_id' => $produk->id, 'cabang_id' => $this->cabang1->id, 'jumlah' => 5, 'min_stok' => 2, 'is_aktif' => true]);

    Livewire::test(WebProduk::class)->assertSee('Mouse Pad XL Polos');

    Livewire::test(MasterProdukIndex::class)
        ->call('openDeleteConfirmation', $produk->id)
        ->call('confirmDelete');

    Livewire::test(WebProduk::class)->assertDontSee('Mouse Pad XL Polos');
}

public function test_deactivating_product_without_stock_row_does_not_error(): void
{
    $this->actingAsRole('Superadmin', [], $this->cabang1);

    $produk = Produk::create([
        'nama_produk' => 'Kabel HDMI 3M',
        'barcode' => 'HDMI-3M',
        'harga_beli' => 25000,
        'harga_jual' => 45000,
        'ready_marketplace' => true,
    ]);

    Livewire::test(MasterProdukIndex::class)
        ->call('openDeleteConfirmation', $produk->id)
        ->call('confirmDelete')
        ->assertHasNoErrors();

    $this->assertFalse($produk->fresh()->ready_marketplace);
}
```

- [ ] **Step 4: Jalankan, pastikan gagal karena perilaku lama**

Run: `docker exec soulmate_apps php artisan test --filter MasterProdukTest`
Expected: 3 test baru FAIL. Yang gagal pada `assertFalse($produk->fresh()->ready_marketplace)` atau `assertDatabaseHas` `jumlah => 0`.

- [ ] **Step 5: Implementasi di `deleteProduk()` jalur non-HQ**

Ganti blok `if (! $isHQ) { ... return; }` (`app/Livewire/Master/Produk/Index.php:509-520`) menjadi:

```php
        if (! $isHQ) {
            $targetCabangId = $user->isOwner() ? (int) $activeBranchId : (int) $user->cabang_id;
            $targetCabangId = $targetCabangId ?: 1;

            // ponytail: stok dinolkan tanpa audit trail (tabel stoks tidak menyimpan riwayat).
            // Tambah tabel riwayat mutasi stok kalau angka lama harus bisa dipulihkan.
            Stok::where('produk_id', $id)
                ->where('cabang_id', $targetCabangId)
                ->update(['jumlah' => 0, 'is_aktif' => false]);

            $produk->update(['ready_marketplace' => false]);

            session()->flash('success', "Produk \"{$produk->nama_produk}\" dinonaktifkan: stok cabang ini menjadi 0 dan produk disembunyikan dari Website Marketplace.");

            return;
        }
```

- [ ] **Step 6: Jalankan test, pastikan lulus**

Run: `docker exec soulmate_apps php artisan test --filter MasterProdukTest`
Expected: PASS semua (termasuk 3 test baru) dan tidak ada test lama yang turun status.

- [ ] **Step 7: Commit**

```bash
git add app/Livewire/Master/Produk/Index.php tests/Feature/Menu/MasterProdukTest.php
git commit -m "feat(produk): nonaktifkan produk menolkan stok cabang dan mematikan marketplace"
```

---

### Task 2: View — banner hero foto produk di modal nonaktifkan

**Files:**
- Modify: `resources/views/livewire/master/produk/index.blade.php:861-887` (blok `<!-- Product Snapshot Card -->`)
- Test: `tests/Feature/Menu/MasterProdukTest.php`

**Interfaces:**
- Consumes: properti publik komponen `MasterProdukIndex`: `$deleteProdukFoto`, `$deleteProdukNama`, `$deleteProdukBarcode`, `$deleteProdukKategori`, `$deleteProdukStok`.
- Produces: markup modal, tanpa API baru.

- [ ] **Step 1: Tulis 2 test yang gagal**

Tambahkan di `tests/Feature/Menu/MasterProdukTest.php`:

```php
public function test_delete_modal_shows_hero_banner_when_product_has_photo(): void
{
    $this->actingAsRole('Superadmin', [], $this->cabang1);

    $produk = Produk::create([
        'nama_produk' => 'SSD Kingston NV2 500GB',
        'barcode' => 'SNV2S-500G',
        'harga_beli' => 550000,
        'harga_jual' => 675000,
        'ready_marketplace' => true,
        'foto_url' => 'images/logo_bulat.png',
    ]);

    Livewire::test(MasterProdukIndex::class)
        ->call('openDeleteConfirmation', $produk->id)
        ->assertSeeHtml('h-36')
        ->assertSeeHtml('object-contain')
        ->assertSee('Website Marketplace');
}

public function test_delete_modal_has_no_hero_banner_when_product_has_no_photo(): void
{
    $this->actingAsRole('Superadmin', [], $this->cabang1);

    $produk = Produk::create([
        'nama_produk' => 'Kabel HDMI 3M',
        'barcode' => 'HDMI-3M',
        'harga_beli' => 25000,
        'harga_jual' => 45000,
    ]);

    Livewire::test(MasterProdukIndex::class)
        ->call('openDeleteConfirmation', $produk->id)
        ->assertDontSeeHtml('object-contain');
}
```

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `docker exec soulmate_apps php artisan test --filter MasterProdukTest`
Expected: 2 test baru FAIL (`h-36` belum ada di markup).

- [ ] **Step 3: Ganti blok Product Snapshot Card**

Ganti seluruh `<div class="px-6 py-3"> ... </div>` (baris 862-887) dengan:

```blade
                    <div class="px-6 py-3">
                        @if($deleteProdukFoto)
                            <a href="{{ asset($deleteProdukFoto) }}" target="_blank" rel="noopener"
                               title="Buka foto ukuran penuh"
                               class="block mb-3 rounded-2xl overflow-hidden border border-gray-200 bg-gray-50">
                                <img src="{{ asset($deleteProdukFoto) }}" alt="{{ $deleteProdukNama }}"
                                     class="w-full h-36 object-contain">
                            </a>
                        @endif

                        <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-200/90 flex items-center space-x-3.5">
                            @if(! $deleteProdukFoto)
                                <div class="w-10 h-10 rounded-lg bg-purple-50 text-[#5E17EB] border border-purple-100 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-extrabold text-gray-900 truncate leading-snug">{{ $deleteProdukNama }}</h4>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="font-mono text-[11px] text-gray-500 bg-white px-1.5 py-0.5 rounded border border-gray-200">{{ $deleteProdukBarcode ?: '-' }}</span>
                                    <span class="text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">{{ $deleteProdukKategori }}</span>
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <span class="text-[10px] font-bold text-gray-400 block uppercase">Sisa Stok</span>
                                <span class="text-sm font-black block mt-0.5">
                                    <span class="text-gray-400 line-through">{{ $deleteProdukStok }} Unit</span>
                                    <span class="text-gray-400">&rarr;</span>
                                    <span class="text-rose-600">0 Unit</span>
                                </span>
                            </div>
                        </div>

                        <p class="text-[11px] text-gray-500 mt-2">
                            Stok cabang ini akan menjadi 0 dan produk disembunyikan dari Website Marketplace.
                        </p>
                    </div>
```

- [ ] **Step 4: Jalankan test, pastikan lulus**

Run: `docker exec soulmate_apps php artisan test --filter MasterProdukTest`
Expected: PASS semua.

- [ ] **Step 5: Verifikasi class CSS tersedia tanpa rebuild**

Run: `docker exec soulmate_apps php artisan test --filter MasterProdukTest` (sudah di Step 4)
Cek: `h-36` ada di `public/build/assets/app-BKyeELVP.css` (sudah diverifikasi sebelum plan ditulis). Tidak perlu `npm run build`.

- [ ] **Step 6: Jalankan test modul berdekatan**

Run: `docker exec soulmate_apps php artisan test --filter "MasterProdukTest|ProductAndStockTest"`
Expected: `MasterProdukTest` hijau. `ProductAndStockTest`: bandingkan dengan baseline Task 1 Step 1; tidak boleh bertambah jumlah FAIL.

- [ ] **Step 7: Commit**

```bash
git add resources/views/livewire/master/produk/index.blade.php tests/Feature/Menu/MasterProdukTest.php
git commit -m "style(produk): banner hero foto produk di modal nonaktifkan"
```

---

## Setelah selesai

- Commit design doc + plan terpisah: `docs(produk): tambah design dan plan nonaktif produk`.
- Push ke `origin main` dan `origin aceng/master-produk` hanya setelah semua test di atas sesuai target dan ada izin user.
