<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\KategoriKasKeluar;
use App\Models\KategoriProduk;
use App\Models\MasterPpn;
use App\Models\MetodePembayaran;
use App\Models\SumberDana;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class CachingStrategyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_master_data_caching_and_auto_invalidation()
    {
        // 1. KategoriProduk
        Cache::flush();
        $kategoris1 = KategoriProduk::getAllCached();
        $this->assertTrue(Cache::has('master_kategoris_all'));

        $newKat = KategoriProduk::create(['nama_kategori' => 'Kategori Baru Cache Test', 'slug' => 'kategori-baru-cache-test']);
        $this->assertFalse(Cache::has('master_kategoris_all')); // auto flushed on save

        $kategoris2 = KategoriProduk::getAllCached();
        $this->assertTrue($kategoris2->contains('id', $newKat->id));

        // 2. MetodePembayaran
        $metode1 = MetodePembayaran::getAktifCached();
        $this->assertTrue(Cache::has('master_metode_pembayarans_active'));

        $newMetode = MetodePembayaran::create(['nama' => 'QRIS Test', 'kategori' => 'qris', 'urutan' => 99, 'is_aktif' => true]);
        $this->assertFalse(Cache::has('master_metode_pembayarans_active')); // auto flushed

        $metode2 = MetodePembayaran::getAktifCached();
        $this->assertTrue($metode2->contains('id', $newMetode->id));

        // 3. SumberDana
        $sumber1 = SumberDana::getAktifCached();
        $this->assertTrue(Cache::has('master_sumber_danas_active'));

        $newSumber = SumberDana::create(['nama' => 'Rekening BCA Baru', 'is_aktif' => true]);
        $this->assertFalse(Cache::has('master_sumber_danas_active'));

        $sumber2 = SumberDana::getAktifCached();
        $this->assertTrue($sumber2->contains('id', $newSumber->id));

        // 4. Cabang
        $cabangs1 = Cabang::getAllCached();
        $this->assertTrue(Cache::has('master_cabangs_active'));

        $newCabang = Cabang::create(['kode_cabang' => 'CB-TEST', 'nama_cabang' => 'Cabang Test', 'status' => 'aktif']);
        $this->assertFalse(Cache::has('master_cabangs_active'));

        $cabangs2 = Cabang::getAllCached();
        $this->assertTrue($cabangs2->contains('id', $newCabang->id));

        // 5. MasterPpn
        $ppn1 = MasterPpn::getAktifCached();
        $this->assertTrue(Cache::has('master_ppn_active'));
    }

    public function test_dashboard_and_navbar_aggregations_are_cached()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        Cache::flush();

        // Test Dashboard caching
        Livewire::test(\App\Livewire\Dashboard\Index::class)
            ->assertStatus(200);

        $dashboardKey = 'dashboard_stats_' . $superadmin->cabang_id . '_branch';
        $this->assertTrue(Cache::has($dashboardKey));

        // Test Navbar Ringkasan Caching
        Livewire::test(\App\Livewire\Components\NavbarRingkasanTransaksi::class)
            ->assertStatus(200);

        $startOfMonth = now()->startOfMonth()->toDateString();
        $today = now()->toDateString();
        $navbarKey = 'navbar_ringkasan_' . $superadmin->cabang_id . '_' . $startOfMonth . '_' . $today;
        $this->assertTrue(Cache::has($navbarKey));
    }
}
