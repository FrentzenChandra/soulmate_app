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
}
