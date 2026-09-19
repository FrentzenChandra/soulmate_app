<?php

namespace Tests\Feature;

use App\Livewire\Kasir\Produk\Index as KasirProdukIndex;
use App\Livewire\Master\Pembayaran\Index as MasterPembayaranIndex;
use App\Models\Cabang;
use App\Models\MetodePembayaran;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MasterMetodePembayaranTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guests_cannot_access_master_metode_pembayaran()
    {
        $response = $this->get(route('master.metode-pembayaran'));
        $response->assertRedirect(route('login'));
    }

    public function test_owner_and_superadmin_can_access_master_metode_pembayaran()
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);

        $response = $this->get(route('master.metode-pembayaran'));
        $response->assertStatus(200);
        $response->assertSee('Master Metode Pembayaran');

        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $response = $this->get(route('master.metode-pembayaran'));
        $response->assertStatus(200);
    }

    public function test_can_create_new_payment_method()
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);

        Livewire::test(MasterPembayaranIndex::class)
            ->call('openCreateModal')
            ->set('nama', 'Transfer Mandiri')
            ->set('kategori', 'transfer')
            ->set('nomor_rekening', '1330099887766')
            ->set('atas_nama', 'Soulmate Komputer Cabang PKP')
            ->set('urutan', 5)
            ->set('is_aktif', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('swal:success');

        $this->assertDatabaseHas('metode_pembayarans', [
            'nama' => 'Transfer Mandiri',
            'kategori' => 'transfer',
            'nomor_rekening' => '1330099887766',
        ]);
    }

    public function test_can_edit_existing_payment_method()
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);

        $metode = MetodePembayaran::where('nama', 'QRIS')->first();
        $this->assertNotNull($metode);

        Livewire::test(MasterPembayaranIndex::class)
            ->call('openEditModal', $metode->id)
            ->assertSet('nama', 'QRIS')
            ->set('atas_nama', 'PT Soulmate Sukses')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('metode_pembayarans', [
            'id' => $metode->id,
            'atas_nama' => 'PT Soulmate Sukses',
        ]);
    }

    public function test_can_toggle_status_and_delete_payment_method()
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);

        $metode = MetodePembayaran::where('nama', 'Debit EDC')->first();

        // Toggle status
        Livewire::test(MasterPembayaranIndex::class)
            ->call('toggleStatus', $metode->id)
            ->assertDispatched('swal:success');

        $this->assertFalse((bool) $metode->fresh()->is_aktif);

        // Delete
        Livewire::test(MasterPembayaranIndex::class)
            ->call('delete', $metode->id)
            ->assertDispatched('swal:success');

        $this->assertDatabaseMissing('metode_pembayarans', [
            'id' => $metode->id,
        ]);
    }

    public function test_loose_multi_word_search_and_sorting()
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);

        Livewire::test(MasterPembayaranIndex::class)
            ->set('search', 'transfer bca')
            ->assertSee('Transfer BCA')
            ->assertDontSee('Debit EDC')
            ->set('search', '')
            ->call('sortBy', 'nama')
            ->assertSet('sortField', 'nama')
            ->assertSet('sortDirection', 'asc');
    }

    public function test_kasir_pos_checkout_uses_selected_master_payment_method()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first() ?? User::where('email', 'superadmin@soulmate.test')->first();
        $cabang = Cabang::where('kode_cabang', 'CB-PKP')->first();
        $this->actingAs($admin);

        $produk = Produk::first();
        $stok = Stok::where('produk_id', $produk->id)->where('cabang_id', $cabang->id)->first();
        $stok->update(['jumlah' => 10, 'is_aktif' => true]);

        Livewire::test(KasirProdukIndex::class)
            ->call('addToCart', $produk->id)
            ->call('openPaymentModal')
            ->assertSee('Transfer BCA')
            ->call('selectMetodeBayar', 'Transfer BCA')
            ->assertSet('metodeBayar', 'Transfer BCA')
            ->call('prosesPembayaran')
            ->assertHasNoErrors()
            ->assertSet('modalSuccessOpen', true);

        $this->assertDatabaseHas('penjualans', [
            'metode_bayar' => 'Transfer BCA',
            'cabang_id' => $cabang->id,
        ]);
    }
}
