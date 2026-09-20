<?php

namespace Tests\Feature;

use App\Livewire\Transaksi\Harian as TransaksiHarian;
use App\Models\CekKasLog;
use App\Models\ModalAwal;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransaksiHarianAndTutupKasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guests_cannot_access_transaksi_harian()
    {
        $this->get(route('transaksi.harian'))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_transaksi_harian_and_perform_cek_kas()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $this->actingAs($admin);

        $this->get(route('transaksi.harian'))->assertStatus(200);

        // 1. Set modal awal hari ini
        ModalAwal::create([
            'cabang_id' => $admin->cabang_id,
            'user_id' => $admin->id,
            'tanggal' => now()->toDateString(),
            'nominal' => 300000,
            'keterangan' => 'Uang modal pagi',
        ]);

        // 2. Buat penjualan cash hari ini
        $produk = Produk::first();
        $pj = Penjualan::create([
            'no_transaksi' => 'INV-PJ-PKP-2609-00002',
            'cabang_id' => $admin->cabang_id,
            'kasir_id' => $admin->id,
            'tipe_transaksi' => 'penjualan_barang',
            'tanggal' => now(),
            'subtotal' => 150000,
            'diskon' => 0,
            'total' => 150000,
            'jumlah_bayar' => 150000,
            'kembalian' => 0,
            'metode_bayar' => 'Cash',
            'status' => 'lunas',
            'is_real' => true,
        ]);

        PenjualanDetail::create([
            'penjualan_id' => $pj->id,
            'tipe_item' => 'barang',
            'produk_id' => $produk->id,
            'nama_item' => $produk->nama_produk,
            'qty' => 1,
            'harga' => 150000,
            'subtotal' => 150000,
        ]);

        // Kas sistem = Modal Awal (300.000) + Penjualan Cash (150.000) = 450.000

        // 3. Proses Cek Kas Cepat dengan kas fisik sesuai kasSistem (status: klop)
        $component = Livewire::test(TransaksiHarian::class);
        $kasSistem = $component->viewData('kasSistem');

        $component->call('openCekKasModal')
            ->set('kasFisik', (string) $kasSistem)
            ->set('catatanAudit', 'Rutin audit pagi')
            ->call('prosesCekKas')
            ->assertHasNoErrors()
            ->assertDispatched('swal:success');

        $this->assertDatabaseHas('cek_kas_logs', [
            'cabang_id' => $admin->cabang_id,
            'kas_sistem' => $kasSistem,
            'kas_fisik' => $kasSistem,
            'selisih' => 0,
            'status_selisih' => 'klop',
            'catatan' => 'Rutin audit pagi',
        ]);
    }
}
