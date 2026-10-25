<?php

namespace Tests\Feature\Menu;

use App\Livewire\Master\Kategori\Index as MasterKategoriIndex;
use App\Livewire\Master\KategoriKas\Index as MasterKategoriKasIndex;
use App\Livewire\Master\Pembayaran\Index as MasterPembayaranIndex;
use App\Livewire\Master\SumberDana\Index as MasterSumberDanaIndex;
use App\Models\KategoriKasKeluar;
use App\Models\KategoriProduk;
use App\Models\MetodePembayaran;
use App\Models\SumberDana;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class MasterSettingsTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    public static function roleAccessProvider(): array
    {
        return [
            'Owner dapat akses penuh' => ['Owner', 200],
            'Superadmin berhak akses' => ['Superadmin', 200],
            'Admin ditolak 403' => ['Admin', 403],
            'Teknisi ditolak 403' => ['Teknisi', 403],
            'Bendahara ditolak 403' => ['Bendahara', 403],
            'Operasional ditolak 403' => ['Operasional', 403],
            'Magang ditolak 403' => ['Magang', 403],
        ];
    }

    #[DataProvider('roleAccessProvider')]
    public function test_akses_halaman_master_settings_berdasarkan_role(string $role, int $expectedStatus): void
    {
        $this->actingAsRole($role);

        $this->get(route('master.kategori'))->assertStatus($expectedStatus);
        $this->get(route('master.metode-pembayaran'))->assertStatus($expectedStatus);
        $this->get(route('master.sumber-dana'))->assertStatus($expectedStatus);
        $this->get(route('master.kategori-kas'))->assertStatus($expectedStatus);
    }

    public function test_guest_di_redirect_ke_halaman_login(): void
    {
        $this->get(route('master.kategori'))->assertRedirect(route('login'));
        $this->get(route('master.metode-pembayaran'))->assertRedirect(route('login'));
        $this->get(route('master.sumber-dana'))->assertRedirect(route('login'));
        $this->get(route('master.kategori-kas'))->assertRedirect(route('login'));
    }

    public function test_crud_kategori_produk(): void
    {
        $this->actingAsRole('Superadmin');

        // Create
        Livewire::test(MasterKategoriIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->set('nama_kategori', 'Aksesoris Komputer')
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('kategori_produks', [
            'nama_kategori' => 'Aksesoris Komputer',
        ]);

        $kategori = KategoriProduk::where('nama_kategori', 'Aksesoris Komputer')->first();

        // Edit
        Livewire::test(MasterKategoriIndex::class)
            ->call('openEditModal', $kategori->id)
            ->assertSet('nama_kategori', 'Aksesoris Komputer')
            ->set('nama_kategori', 'Aksesoris Laptop & PC')
            ->call('save');

        $this->assertDatabaseHas('kategori_produks', [
            'id' => $kategori->id,
            'nama_kategori' => 'Aksesoris Laptop & PC',
        ]);

        // Delete
        Livewire::test(MasterKategoriIndex::class)
            ->call('deleteKategori', $kategori->id);

        $this->assertDatabaseMissing('kategori_produks', [
            'id' => $kategori->id,
        ]);
    }

    public function test_crud_metode_pembayaran(): void
    {
        $this->actingAsRole('Superadmin');

        // Create
        Livewire::test(MasterPembayaranIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->set('nama', 'QRIS Mandiri')
            ->set('kategori', 'qris')
            ->set('urutan', 1)
            ->set('is_aktif', true)
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('metode_pembayarans', [
            'nama' => 'QRIS Mandiri',
            'kategori' => 'qris',
        ]);

        $metode = MetodePembayaran::where('nama', 'QRIS Mandiri')->first();

        // Edit
        Livewire::test(MasterPembayaranIndex::class)
            ->call('openEditModal', $metode->id)
            ->set('nama', 'QRIS Mandiri Dinamis')
            ->call('save');

        $this->assertDatabaseHas('metode_pembayarans', [
            'id' => $metode->id,
            'nama' => 'QRIS Mandiri Dinamis',
        ]);

        // Delete
        Livewire::test(MasterPembayaranIndex::class)
            ->call('delete', $metode->id);

        $this->assertDatabaseMissing('metode_pembayarans', [
            'id' => $metode->id,
        ]);
    }

    public function test_crud_sumber_dana(): void
    {
        $this->actingAsRole('Superadmin');

        // Create
        Livewire::test(MasterSumberDanaIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->set('nama', 'Brankas Toko')
            ->set('is_aktif', true)
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('sumber_danas', [
            'nama' => 'Brankas Toko',
        ]);

        $sumberDana = SumberDana::where('nama', 'Brankas Toko')->first();

        // Edit
        Livewire::test(MasterSumberDanaIndex::class)
            ->call('openEditModal', $sumberDana->id)
            ->set('nama', 'Brankas Toko Utama')
            ->call('save');

        $this->assertDatabaseHas('sumber_danas', [
            'id' => $sumberDana->id,
            'nama' => 'Brankas Toko Utama',
        ]);

        // Delete
        Livewire::test(MasterSumberDanaIndex::class)
            ->call('delete', $sumberDana->id);

        $this->assertDatabaseMissing('sumber_danas', [
            'id' => $sumberDana->id,
        ]);
    }

    public function test_crud_kategori_kas_keluar(): void
    {
        $this->actingAsRole('Superadmin');

        // Create
        Livewire::test(MasterKategoriKasIndex::class)
            ->call('openCreateModal')
            ->assertSet('modalOpen', true)
            ->set('nama', 'Biaya Kebersihan')
            ->set('is_aktif', true)
            ->call('save')
            ->assertSet('modalOpen', false);

        $this->assertDatabaseHas('kategori_kas_keluars', [
            'nama' => 'Biaya Kebersihan',
        ]);

        $katKas = KategoriKasKeluar::where('nama', 'Biaya Kebersihan')->first();

        // Edit
        Livewire::test(MasterKategoriKasIndex::class)
            ->call('openEditModal', $katKas->id)
            ->set('nama', 'Biaya Kebersihan & Keamanan')
            ->call('save');

        $this->assertDatabaseHas('kategori_kas_keluars', [
            'id' => $katKas->id,
            'nama' => 'Biaya Kebersihan & Keamanan',
        ]);

        // Delete
        Livewire::test(MasterKategoriKasIndex::class)
            ->call('delete', $katKas->id);

        $this->assertDatabaseMissing('kategori_kas_keluars', [
            'id' => $katKas->id,
        ]);
    }
}

