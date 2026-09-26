<?php

namespace Tests\Feature;

use App\Livewire\AuditLaci\Index as AuditLaciIndex;
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

class AuditLaciTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guests_cannot_access_audit_laci()
    {
        $this->get(route('audit-laci'))->assertRedirect(route('login'));
    }

    public function test_only_owner_can_access_audit_laci()
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);
        $this->get(route('audit-laci'))->assertStatus(200);

        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);
        $this->get(route('audit-laci'))->assertStatus(403);

        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $this->actingAs($admin);
        $this->get(route('audit-laci'))->assertStatus(403);
    }

    public function test_can_perform_audit_and_save_breakdown_logs()
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);

        // 1. Setup Modal Awal
        ModalAwal::create([
            'cabang_id' => $owner->cabang_id ?: 1,
            'user_id' => $owner->id,
            'tanggal' => now()->toDateString(),
            'nominal' => 200000,
            'keterangan' => 'Modal pagi',
        ]);

        // 2. Setup Penjualan Cash
        $produk = Produk::first();
        $cabangId = $owner->cabang_id ?: 1;
        $pj = Penjualan::create([
            'no_transaksi' => 'INV-PJ-PKP-2609-00099',
            'cabang_id' => $cabangId,
            'kasir_id' => $owner->id,
            'tipe_transaksi' => 'penjualan_barang',
            'tanggal' => now(),
            'subtotal' => 100000,
            'diskon' => 0,
            'total' => 100000,
            'jumlah_bayar' => 100000,
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
            'harga' => 100000,
            'subtotal' => 100000,
        ]);

        $component = Livewire::test(AuditLaciIndex::class);
        $kasSistem = $component->viewData('liveKasSistem');
        $kasFisik = $kasSistem - 20000;

        $component->call('openAuditModal')
            ->set('kasFisik', (string) $kasFisik)
            ->set('catatanAudit', 'Kurang kembalian 20rb')
            ->call('prosesAudit')
            ->assertHasNoErrors()
            ->assertDispatched('swal:success');

        $this->assertDatabaseHas('cek_kas_logs', [
            'cabang_id' => $cabangId,
            'user_id' => $owner->id,
            'modal_awal' => 200000,
            'kas_sistem' => $kasSistem,
            'kas_fisik' => $kasFisik,
            'selisih' => -20000,
            'status_selisih' => 'minus',
            'catatan' => 'Kurang kembalian 20rb',
        ]);
    }

    public function test_period_and_status_filtering_works()
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);
        $cabangId = $owner->cabang_id ?: 1;

        CekKasLog::create([
            'cabang_id' => $cabangId,
            'user_id' => $owner->id,
            'tanggal' => '2026-08-15',
            'waktu_cek' => '2026-08-15 12:00:00',
            'modal_awal' => 100000,
            'penjualan_cash' => 50000,
            'servis_cash' => 0,
            'kas_keluar_cash' => 0,
            'kas_sistem' => 150000,
            'kas_fisik' => 150000,
            'selisih' => 0,
            'status_selisih' => 'klop',
        ]);

        CekKasLog::create([
            'cabang_id' => $cabangId,
            'user_id' => $owner->id,
            'tanggal' => '2026-09-10',
            'waktu_cek' => '2026-09-10 17:00:00',
            'modal_awal' => 100000,
            'penjualan_cash' => 50000,
            'servis_cash' => 0,
            'kas_keluar_cash' => 0,
            'kas_sistem' => 150000,
            'kas_fisik' => 120000,
            'selisih' => -30000,
            'status_selisih' => 'minus',
        ]);

        $component = Livewire::test(AuditLaciIndex::class);

        // Bulanan Agustus 2026
        $component->call('setPeriodeMode', 'bulanan')
            ->set('filterBulan', '08')
            ->set('filterTahun', '2026')
            ->call('applyBulanan')
            ->assertSet('startDate', '2026-08-01')
            ->assertSet('endDate', '2026-08-31');

        // Filter status minus
        $component->set('statusFilter', 'minus')
            ->assertSet('statusFilter', 'minus');
    }
}
