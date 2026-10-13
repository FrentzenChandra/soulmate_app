<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\ServisDetail;
use App\Models\ServisMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Kasir']);

        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'kasir.produk.print']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'servis.masuk.print']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'servis.keluar.print']);
        $adminRole->givePermissionTo(['kasir.produk.print', 'servis.masuk.print', 'servis.keluar.print']);

        $this->cabang = Cabang::create([
            'kode_cabang' => 'CB01',
            'nama_cabang' => 'Cabang Utama',
            'alamat' => 'Jl. Merdeka No 1',
            'no_wa' => '081234567890',
            'status' => 'aktif',
        ]);

        $this->admin = User::factory()->create([
            'cabang_id' => $this->cabang->id,
        ]);
        $this->admin->assignRole('Admin');
    }

    public function test_can_generate_dompdf_for_kasir_invoice(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Budi Santoso',
            'no_hp' => '08123456789',
        ]);

        $produk = Produk::create([
            'nama_produk' => 'Mouse Wireless Logitech',
            'barcode' => '89912345678',
            'harga_jual' => 150000,
            'harga_beli' => 100000,
            'status' => 'aktif',
        ]);

        $penjualan = Penjualan::create([
            'no_transaksi' => 'INV-PJ-CB01-260918-00001',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $customer->id,
            'kasir_id' => $this->admin->id,
            'tipe_transaksi' => 'produk',
            'tanggal' => now(),
            'subtotal' => 150000,
            'diskon' => 0,
            'ppn_persen' => 0,
            'ppn_nominal' => 0,
            'total' => 150000,
            'bayar' => 150000,
            'kembalian' => 0,
            'metode_bayar' => 'cash',
        ]);

        PenjualanDetail::create([
            'penjualan_id' => $penjualan->id,
            'produk_id' => $produk->id,
            'nama_item' => $produk->nama_produk,
            'qty' => 1,
            'harga' => 150000,
            'subtotal' => 150000,
            'harga_beli' => 100000,
            'tipe_item' => 'produk',
        ]);

        // HTML view test
        $responseHtml = $this->get(route('kasir.produk.invoice', $penjualan->id));
        $responseHtml->assertOk();
        $responseHtml->assertSee('Cetak Print');

        // PDF generation test (DomPDF)
        $responsePdf = $this->get(route('kasir.produk.invoice', ['penjualan' => $penjualan->id, 'format' => 'pdf']));
        $responsePdf->assertOk();
        $this->assertStringContainsString('application/pdf', $responsePdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $responsePdf->getContent());

        // Test DomPDF directly for canvas sizing & page count (24.13 x 13.97 cm)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.kasir-invoice', ['penjualan' => $penjualan])
            ->setPaper([0, 0, 684.0, 396.0]);
        $pdf->render();
        $canvas = $pdf->getCanvas();
        $this->assertEquals(1, $canvas->get_page_count(), 'Invoice kasir must fit in exactly 1 page');
        $this->assertEqualsWithDelta(684.0, $canvas->get_width(), 0.01);
        $this->assertEqualsWithDelta(396.0, $canvas->get_height(), 0.01);
    }

    public function test_can_generate_dompdf_for_servis_tanda_terima(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Dewi Lestari',
            'no_hp' => '08987654321',
        ]);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CB01-260918-00001',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Lenovo ThinkPad T480',
            'keluhan' => 'Mati total setelah terkena petir',
            'kelengkapan' => 'Unit + Charger',
            'kondisi_barang' => 'Mulus',
            'status' => 'pengecekan',
            'tanggal_masuk' => now(),
        ]);

        // HTML view test
        $responseHtml = $this->get(route('servis.tanda-terima', $servis->id));
        $responseHtml->assertOk();
        $responseHtml->assertSee('TANDA TERIMA BARANG SERVIS');

        // PDF generation test (DomPDF)
        $responsePdf = $this->get(route('servis.tanda-terima', ['servis' => $servis->id, 'format' => 'pdf']));
        $responsePdf->assertOk();
        $this->assertStringContainsString('application/pdf', $responsePdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $responsePdf->getContent());

        // Test DomPDF directly for canvas sizing & page count (24 x 15 cm)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.servis-tanda-terima', ['servis' => $servis])
            ->setPaper([0, 0, 680.315, 425.197]);
        $pdf->render();
        $canvas = $pdf->getCanvas();
        $this->assertEquals(1, $canvas->get_page_count(), 'Tanda terima servis must fit in exactly 1 page');
        $this->assertEqualsWithDelta(680.315, $canvas->get_width(), 0.01);
        $this->assertEqualsWithDelta(425.197, $canvas->get_height(), 0.01);
    }

    public function test_can_generate_dompdf_for_servis_invoice(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Dewi Lestari',
            'no_hp' => '08987654321',
        ]);

        $servis = ServisMasuk::create([
            'no_servis' => 'INV-SV-CB01-260918-00001',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $customer->id,
            'created_by' => $this->admin->id,
            'kasir_id' => $this->admin->id,
            'teknisi_id' => $this->admin->id,
            'kategori_perangkat' => 'laptop',
            'tipe_unit' => 'Lenovo ThinkPad T480',
            'keluhan' => 'Mati total',
            'status' => 'selesai',
            'tanggal_masuk' => now()->subDays(2),
            'tanggal_ambil' => now(),
            'total_biaya' => 350000,
            'metode_pembayaran' => 'cash',
            'bayar' => 350000,
            'kembalian' => 0,
            'garansi_hari' => 30,
            'garansi_sampai' => now()->addDays(30)->toDateString(),
        ]);

        ServisDetail::create([
            'servis_id' => $servis->id,
            'nama_item' => 'Jasa Reball IC Power',
            'qty' => 1,
            'harga' => 350000,
            'subtotal' => 350000,
            'tipe' => 'jasa',
        ]);

        // HTML view test
        $responseHtml = $this->get(route('servis.invoice', $servis->id));
        $responseHtml->assertOk();
        $responseHtml->assertSee('FAKTUR SERVIS');

        // PDF generation test (DomPDF)
        $responsePdf = $this->get(route('servis.invoice', ['servis' => $servis->id, 'format' => 'pdf']));
        $responsePdf->assertOk();
        $this->assertStringContainsString('application/pdf', $responsePdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $responsePdf->getContent());

        // Test DomPDF directly for canvas sizing & page count (24 x 15 cm)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.servis-invoice', ['servis' => $servis])
            ->setPaper([0, 0, 680.315, 425.197]);
        $pdf->render();
        $canvas = $pdf->getCanvas();
        $this->assertEquals(1, $canvas->get_page_count(), 'Faktur servis must fit in exactly 1 page');
        $this->assertEqualsWithDelta(680.315, $canvas->get_width(), 0.01);
        $this->assertEqualsWithDelta(425.197, $canvas->get_height(), 0.01);
    }

    public function test_petugas_kasir_fallback_hierarchy_on_servis_invoice(): void
    {
        $this->actingAs($this->admin);

        $customer = Customer::create([
            'cabang_id' => $this->cabang->id,
            'nama' => 'Joko Anwar',
            'no_hp' => '0899999999',
        ]);

        $kasirUser = User::factory()->create(['name' => 'Staf Kasir Utama', 'cabang_id' => $this->cabang->id]);
        $teknisiUser = User::factory()->create(['name' => 'Teknisi Laptop Handal', 'cabang_id' => $this->cabang->id]);
        $creatorUser = User::factory()->create(['name' => 'Admin Pembuat Tiket', 'cabang_id' => $this->cabang->id]);

        // Kasus 1: Ada kasir_id -> wajib tampil nama kasir
        $sv1 = ServisMasuk::create([
            'no_servis' => 'INV-SV-CB01-261013-00010',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $customer->id,
            'kategori_perangkat' => 'laptop',
            'keluhan' => 'Mati total',
            'tanggal_masuk' => now(),
            'kasir_id' => $kasirUser->id,
            'teknisi_id' => $teknisiUser->id,
            'created_by' => $creatorUser->id,
            'status' => 'selesai',
            'is_real' => true,
        ]);
        $this->get(route('servis.invoice', $sv1->id))->assertSee('Staf Kasir Utama');

        // Kasus 2: kasir_id null, ada teknisi_id -> wajib tampil nama teknisi
        $sv2 = ServisMasuk::create([
            'no_servis' => 'INV-SV-CB01-261013-00011',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $customer->id,
            'kategori_perangkat' => 'laptop',
            'keluhan' => 'Mati total',
            'tanggal_masuk' => now(),
            'kasir_id' => null,
            'teknisi_id' => $teknisiUser->id,
            'created_by' => $creatorUser->id,
            'status' => 'selesai',
            'is_real' => true,
        ]);
        $this->get(route('servis.invoice', $sv2->id))->assertSee('Teknisi Laptop Handal');

        // Kasus 3: kasir_id null & teknisi_id null, ada created_by -> wajib tampil nama creator/pembuat
        $sv3 = ServisMasuk::create([
            'no_servis' => 'INV-SV-CB01-261013-00012',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $customer->id,
            'kategori_perangkat' => 'laptop',
            'keluhan' => 'Mati total',
            'tanggal_masuk' => now(),
            'kasir_id' => null,
            'teknisi_id' => null,
            'created_by' => $creatorUser->id,
            'status' => 'selesai',
            'is_real' => true,
        ]);
        $this->get(route('servis.invoice', $sv3->id))->assertSee('Admin Pembuat Tiket');

        // Kasus 4: kasir null, teknisi null, creator ber-nama kosong -> wajib tampil Soulmate Komputer
        $emptyUser = User::factory()->create(['name' => '', 'cabang_id' => $this->cabang->id]);
        $sv4 = ServisMasuk::create([
            'no_servis' => 'INV-SV-CB01-261013-00013',
            'cabang_id' => $this->cabang->id,
            'customer_id' => $customer->id,
            'kategori_perangkat' => 'laptop',
            'keluhan' => 'Mati total',
            'tanggal_masuk' => now(),
            'kasir_id' => null,
            'teknisi_id' => null,
            'created_by' => $emptyUser->id,
            'status' => 'selesai',
            'is_real' => true,
        ]);
        $this->get(route('servis.invoice', $sv4->id))->assertSee('Soulmate Komputer');
    }
}
