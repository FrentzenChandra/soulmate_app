<?php

namespace Tests\Feature;

use App\Livewire\Laporan\Penjualan\Index as LaporanPenjualanIndex;
use App\Models\Cabang;
use App\Models\KategoriProduk;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PenjualanRetur;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReturPenjualanTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected User $adminBerizin;

    protected User $stafTanpaIzinRetur;

    protected Cabang $cabang;

    protected Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $operasionalRole = Role::firstOrCreate(['name' => 'Operasional']);

        $viewPerm = Permission::firstOrCreate(['name' => 'laporan.penjualan.view']);
        $returPerm = Permission::firstOrCreate(['name' => 'laporan.penjualan.retur']);

        $adminRole->syncPermissions([$viewPerm, $returPerm]);
        $operasionalRole->syncPermissions([$viewPerm]);

        $this->cabang = Cabang::create([
            'kode_cabang' => 'CB-PKP',
            'nama_cabang' => 'Cabang Pangkalpinang',
            'status' => 'aktif',
        ]);

        $this->owner = User::create([
            'name' => 'Owner Utama',
            'username' => 'owner',
            'email' => 'owner@soulmate.test',
            'password' => bcrypt('password'),
            'status' => 'aktif',
        ]);
        $this->owner->syncRoles([$ownerRole]);

        $this->adminBerizin = User::create([
            'name' => 'Admin Retur',
            'username' => 'admin_retur',
            'email' => 'admin@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabang->id,
            'status' => 'aktif',
        ]);
        $this->adminBerizin->syncRoles([$adminRole]);

        $this->stafTanpaIzinRetur = User::create([
            'name' => 'Staf Operasional',
            'username' => 'staf_ops',
            'email' => 'ops@soulmate.test',
            'password' => bcrypt('password'),
            'cabang_id' => $this->cabang->id,
            'status' => 'aktif',
        ]);
        $this->stafTanpaIzinRetur->syncRoles([$operasionalRole]);

        $kategori = KategoriProduk::create([
            'nama_kategori' => 'Aksesoris',
            'deskripsi' => 'Aksesoris Komputer',
        ]);

        $this->produk = Produk::create([
            'kategori_id' => $kategori->id,
            'barcode' => 'BRG-001',
            'nama_produk' => 'CCTV Ezviz C6N',
            'harga_beli' => 300000,
            'harga_jual' => 410000,
        ]);

        // Stok awal setelah terpotong 2 unit dari penjualan real (misal tadinya 10 -> sekarang 8)
        Stok::create([
            'produk_id' => $this->produk->id,
            'cabang_id' => $this->cabang->id,
            'jumlah' => 8,
            'min_stok' => 2,
            'is_aktif' => true,
        ]);
    }

    public function test_retur_stok_pada_transaksi_real_mengembalikan_stok_dan_mencatat_histori_tanpa_mengurangi_omzet(): void
    {
        $penjualan = Penjualan::create([
            'no_transaksi' => 'INV-PJ-CBPKP-261013-00001',
            'cabang_id' => $this->cabang->id,
            'kasir_id' => $this->adminBerizin->id,
            'tipe_transaksi' => 'penjualan_barang',
            'tanggal' => now(),
            'subtotal' => 820000,
            'diskon' => 0,
            'ppn_persen' => 0,
            'ppn_nominal' => 0,
            'total' => 820000,
            'jumlah_bayar' => 820000,
            'kembalian' => 0,
            'metode_bayar' => 'Tunai',
            'status' => 'lunas',
            'is_real' => true,
        ]);

        $detail = PenjualanDetail::create([
            'penjualan_id' => $penjualan->id,
            'tipe_item' => 'barang',
            'produk_id' => $this->produk->id,
            'nama_item' => $this->produk->nama_produk,
            'qty' => 2,
            'qty_retur' => 0,
            'harga' => 410000,
            'subtotal' => 820000,
        ]);

        Livewire::actingAs($this->adminBerizin)
            ->test(LaporanPenjualanIndex::class)
            ->call('openReturModal', $penjualan->id)
            ->assertSet('modalReturOpen', true)
            ->assertSet("returItems.{$detail->id}.sisa_qty", 2)
            ->set('alasanRetur', 'Tukar unit / Barang batal dibeli')
            ->call('prosesReturStok')
            ->assertSet('modalReturOpen', false);

        // Pastikan stok bertambah +2 (dari 8 menjadi 10)
        $stok = Stok::where('produk_id', $this->produk->id)
            ->where('cabang_id', $this->cabang->id)
            ->first();
        $this->assertEquals(10, $stok->jumlah);

        // Pastikan qty_retur pada detail tercatat 2
        $detail->refresh();
        $this->assertEquals(2, $detail->qty_retur);

        // Pastikan status transaksi diubah menjadi batal
        $penjualan->refresh();
        $this->assertEquals('batal', $penjualan->status);
        $this->assertEquals(820000, (float) $penjualan->total);

        // Pastikan histori retur tercatat lengkap
        $returLog = PenjualanRetur::where('penjualan_id', $penjualan->id)->first();
        $this->assertNotNull($returLog);
        $this->assertEquals(2, $returLog->qty_retur);
        $this->assertEquals(8, $returLog->stok_sebelum);
        $this->assertEquals(10, $returLog->stok_sesudah);
        $this->assertEquals('Tukar unit / Barang batal dibeli', $returLog->alasan);
        $this->assertEquals($this->adminBerizin->id, $returLog->user_id);
    }

    public function test_transaksi_non_real_ditolak_di_backend_dan_tombol_retur_tidak_muncul(): void
    {
        $penjualanNonReal = Penjualan::create([
            'no_transaksi' => 'INV-PJ-CBPKP-261013-00002',
            'cabang_id' => $this->cabang->id,
            'kasir_id' => $this->adminBerizin->id,
            'tipe_transaksi' => 'penjualan_barang',
            'tanggal' => now(),
            'subtotal' => 410000,
            'diskon' => 0,
            'ppn_persen' => 0,
            'ppn_nominal' => 0,
            'total' => 410000,
            'jumlah_bayar' => 410000,
            'kembalian' => 0,
            'metode_bayar' => 'Tunai',
            'status' => 'lunas',
            'is_real' => false,
        ]);

        PenjualanDetail::create([
            'penjualan_id' => $penjualanNonReal->id,
            'tipe_item' => 'barang',
            'produk_id' => $this->produk->id,
            'nama_item' => $this->produk->nama_produk,
            'qty' => 1,
            'qty_retur' => 0,
            'harga' => 410000,
            'subtotal' => 410000,
        ]);

        $component = Livewire::actingAs($this->adminBerizin)
            ->test(LaporanPenjualanIndex::class)
            ->assertDontSee("openReturModal({$penjualanNonReal->id})")
            ->call('openReturModal', $penjualanNonReal->id)
            ->assertSet('modalReturOpen', false);

        // Pastikan stok tetap 8 (tidak berubah)
        $stok = Stok::where('produk_id', $this->produk->id)
            ->where('cabang_id', $this->cabang->id)
            ->first();
        $this->assertEquals(8, $stok->jumlah);
        $this->assertEquals(0, PenjualanRetur::count());
    }

    public function test_hak_akses_retur_menolak_user_tanpa_permission_dan_card_omzet_batal_akurat(): void
    {
        $penjualanBatal = Penjualan::create([
            'no_transaksi' => 'INV-PJ-CBPKP-261013-00003',
            'cabang_id' => $this->cabang->id,
            'kasir_id' => $this->adminBerizin->id,
            'tipe_transaksi' => 'penjualan_barang',
            'tanggal' => now(),
            'subtotal' => 500000,
            'diskon' => 0,
            'ppn_persen' => 0,
            'ppn_nominal' => 0,
            'total' => 500000,
            'jumlah_bayar' => 500000,
            'kembalian' => 0,
            'metode_bayar' => 'Tunai',
            'status' => 'batal',
            'is_real' => true,
        ]);

        Livewire::actingAs($this->stafTanpaIzinRetur)
            ->test(LaporanPenjualanIndex::class)
            ->assertViewHas('totalOmzetBatal', 500000.0)
            ->assertViewHas('totalTransaksiBatal', 1)
            ->call('openReturModal', $penjualanBatal->id)
            ->assertForbidden();
    }
}

