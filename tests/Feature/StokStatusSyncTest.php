<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class StokStatusSyncTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    protected Cabang $cabang1;
    protected Cabang $cabang2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cabang1 = Cabang::create([
            'nama_cabang' => 'Cabang Pangkalpinang',
            'kode_cabang' => 'CB-PKP',
            'status' => 'aktif',
        ]);

        $this->cabang2 = Cabang::create([
            'nama_cabang' => 'Cabang Sungailiat',
            'kode_cabang' => 'CB-SGL',
            'status' => 'aktif',
        ]);
    }

    protected function buatProduk(array $attributes = []): Produk
    {
        return Produk::create(array_merge([
            'nama_produk' => 'SSD Kingston NV2 500GB',
            'barcode' => 'SNV2S-500G',
            'harga_beli' => 550000,
            'harga_jual' => 675000,
            'ready_marketplace' => true,
        ], $attributes));
    }

    public function test_sync_menonaktifkan_stok_dan_marketplace_saat_stok_nol(): void
    {
        $produk = $this->buatProduk();
        $stok = Stok::create([
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang1->id,
            'jumlah' => 0,
            'min_stok' => 2,
            'is_aktif' => true,
        ]);

        Stok::syncAvailability($produk->id);

        $this->assertFalse($stok->fresh()->is_aktif);
        $this->assertFalse($produk->fresh()->ready_marketplace);
    }

    public function test_sync_tidak_mengaktifkan_saat_tidak_diizinkan(): void
    {
        $produk = $this->buatProduk(['ready_marketplace' => false]);
        $stok = Stok::create([
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang1->id,
            'jumlah' => 5,
            'min_stok' => 2,
            'is_aktif' => false,
        ]);

        Stok::syncAvailability($produk->id);

        $this->assertFalse($stok->fresh()->is_aktif);
        $this->assertFalse($produk->fresh()->ready_marketplace);
    }

    public function test_sync_mengaktifkan_stok_dan_marketplace_saat_diizinkan(): void
    {
        $produk = $this->buatProduk(['ready_marketplace' => false]);
        $stok = Stok::create([
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang1->id,
            'jumlah' => 5,
            'min_stok' => 2,
            'is_aktif' => false,
        ]);

        Stok::syncAvailability($produk->id, true);

        $this->assertTrue($stok->fresh()->is_aktif);
        $this->assertTrue($produk->fresh()->ready_marketplace);
    }

    public function test_sync_menahan_marketplace_mati_saat_cabang_lain_masih_nol(): void
    {
        $produk = $this->buatProduk(['ready_marketplace' => false]);

        $stok1 = Stok::create([
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang1->id,
            'jumlah' => 5,
            'min_stok' => 2,
            'is_aktif' => false,
        ]);

        $stok2 = Stok::create([
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang2->id,
            'jumlah' => 0,
            'min_stok' => 2,
            'is_aktif' => true,
        ]);

        Stok::syncAvailability($produk->id, true);

        $this->assertTrue($stok1->fresh()->is_aktif);
        $this->assertFalse($stok2->fresh()->is_aktif);
        $this->assertFalse($produk->fresh()->ready_marketplace);
    }

    public function test_sync_aman_untuk_produk_tanpa_baris_stok(): void
    {
        $produk = $this->buatProduk();

        Stok::syncAvailability($produk->id);

        $this->assertFalse($produk->fresh()->ready_marketplace);
    }

    public function test_command_backfill_idempotent(): void
    {
        $produk = $this->buatProduk();

        Stok::create([
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang1->id,
            'jumlah' => 0,
            'min_stok' => 2,
            'is_aktif' => true,
        ]);

        $this->artisan('stok:sync-status')->assertSuccessful();
        $this->assertFalse($produk->fresh()->ready_marketplace);
        $this->assertFalse(Stok::where('produk_id', $produk->id)->first()->is_aktif);

        $this->artisan('stok:sync-status')
            ->expectsOutputToContain('Stok dinonaktifkan: 0 baris')
            ->assertSuccessful();
    }

    public function test_marketplace_menyembunyikan_produk_tanpa_stok(): void
    {
        Cache::flush();

        $produk = $this->buatProduk(['nama_produk' => 'Produk Stok Kosong']);

        Stok::create([
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang1->id,
            'jumlah' => 0,
            'min_stok' => 2,
            'is_aktif' => true,
        ]);

        Livewire::test(\App\Livewire\Web\Produk::class)
            ->assertDontSee('Produk Stok Kosong');
    }

    public function test_katalog_kasir_menyembunyikan_produk_stok_nol(): void
    {
        $this->actingAsRole('Superadmin', [], $this->cabang1);

        $produk = $this->buatProduk(['nama_produk' => 'Produk Kasir Kosong']);

        Stok::create([
            'produk_id' => $produk->id,
            'cabang_id' => $this->cabang1->id,
            'jumlah' => 0,
            'min_stok' => 2,
            'is_aktif' => true,
        ]);

        Livewire::test(\App\Livewire\Kasir\Produk\Index::class)
            ->assertDontSee('Produk Kasir Kosong');
    }
}
