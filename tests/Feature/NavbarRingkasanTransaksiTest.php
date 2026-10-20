<?php

namespace Tests\Feature;

use App\Livewire\Components\NavbarRingkasanTransaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NavbarRingkasanTransaksiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_superadmin_and_owner_can_see_navbar_ringkasan_transaksi(): void
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        Livewire::test(NavbarRingkasanTransaksi::class)
            ->assertSee('Ringkasan Akumulasi Toko')
            ->assertSee('Pendapatan (Bruto)')
            ->assertSee('HPP (Modal)')
            ->assertSee('Nett (Margin)')
            ->assertSee('Penjualan (Nett)')
            ->assertSee('Servis (Nett)');
    }

    public function test_period_filter_modes_on_navbar(): void
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        Livewire::test(NavbarRingkasanTransaksi::class)
            ->call('setPeriodeMode', 'bulanan')
            ->assertSet('periodeMode', 'bulanan')
            ->call('setPeriodeMode', 'tahunan')
            ->assertSet('periodeMode', 'tahunan')
            ->call('setPeriodeMode', 'range')
            ->assertSet('periodeMode', 'range');
    }

    public function test_bon_transactions_are_correctly_summed_in_navbar_ringkasan(): void
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        \App\Models\TransaksiFull::create([
            'cabang_id' => $superadmin->cabang_id ?: 1,
            'no_transaksi' => 'INV-PJ-TEST-BON-01',
            'tipe_transaksi' => 'penjualan',
            'nama_pelanggan' => 'Pelanggan Bon Test',
            'metode_pembayaran' => 'Cash',
            'tanggal' => now(),
            'total_modal' => 50000,
            'total_jual' => 150000,
            'total_laba' => 100000,
            'grand_total' => 150000,
            'status' => 'Bon', // Status with Capital B
        ]);

        \Illuminate\Support\Facades\Cache::flush();

        Livewire::test(NavbarRingkasanTransaksi::class)
            ->assertViewHas('totalBelumLunasAll', function ($totalBon) {
                return $totalBon >= 150000;
            })
            ->assertSee('Jumlah Bon');
    }
}
