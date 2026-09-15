<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\KategoriProduk;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\ServisMasuk;
use App\Models\Stok;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_runs_successfully_and_populates_all_tables()
    {
        $this->seed(DatabaseSeeder::class);

        // 1. Cabang
        $this->assertDatabaseHas('cabangs', ['kode_cabang' => 'CB-PKP', 'status' => 'aktif']);
        $this->assertDatabaseHas('cabangs', ['kode_cabang' => 'CB-SGL', 'status' => 'aktif']);
        $this->assertDatabaseHas('cabangs', ['kode_cabang' => 'CB-KBA', 'status' => 'aktif']);
        $this->assertDatabaseHas('cabangs', ['kode_cabang' => 'CB-TBO', 'status' => 'nonaktif']);

        // 2. Users & Roles
        $this->assertDatabaseHas('users', ['username' => 'owner', 'status' => 'aktif']);
        $this->assertDatabaseHas('users', ['username' => 'superadmin', 'status' => 'aktif']);
        $this->assertDatabaseHas('users', ['username' => 'teknisi_pkp', 'status' => 'aktif']);
        $this->assertDatabaseHas('users', ['username' => 'kasir_pkp', 'status' => 'aktif']);

        $owner = User::where('username', 'owner')->first();
        $this->assertTrue($owner->hasRole('Owner'));

        // 3. Master PPN
        $this->assertDatabaseHas('master_ppns', ['nama' => 'Tanpa PPN', 'persen' => 0.00]);
        $this->assertDatabaseHas('master_ppns', ['nama' => 'PPN 11%', 'persen' => 11.00]);

        // 4. Customers
        $this->assertDatabaseHas('customers', [
            'nama' => 'Hendra Setiawan',
            'tipe' => 'langganan',
        ]);
        $this->assertDatabaseHas('customers', [
            'nama' => 'Budi Walk-In',
            'tipe' => 'umum',
        ]);

        // 5. Kategori & Produk & Stoks
        $this->assertGreaterThan(0, KategoriProduk::count());
        $this->assertGreaterThan(0, Produk::count());
        $this->assertGreaterThan(0, Stok::count());

        $this->assertDatabaseHas('produks', [
            'barcode' => '8991234560011',
            'nama_produk' => 'SSD NVMe M.2 Kingston NV2 512GB PCIe 4.0',
        ]);

        $this->assertDatabaseHas('stoks', [
            'min_stok' => 3,
            'is_aktif' => true,
        ]);

        // 6. Servis Masuk
        $this->assertGreaterThan(0, ServisMasuk::count());
        $firstServis = ServisMasuk::first();
        $this->assertNotNull($firstServis);
        $this->assertStringContainsString('INV-SV-CBPKP-', $firstServis->no_servis);
        $this->assertEquals('pengecekan', $firstServis->status);

        // 7. Penjualan POS
        $this->assertGreaterThan(0, Penjualan::count());
        $this->assertDatabaseHas('penjualans', [
            'status' => 'lunas',
            'is_real' => true,
        ]);
        $this->assertDatabaseHas('penjualans', [
            'status' => 'lunas',
            'is_real' => false,
        ]);
    }
}
