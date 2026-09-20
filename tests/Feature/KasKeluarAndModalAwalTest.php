<?php

namespace Tests\Feature;

use App\Livewire\KasKeluar\Index as KasKeluarIndex;
use App\Livewire\Master\KategoriKas\Index as MasterKategoriKasIndex;
use App\Livewire\Master\SumberDana\Index as MasterSumberDanaIndex;
use App\Livewire\ModalAwal\Index as ModalAwalIndex;
use App\Models\Cabang;
use App\Models\KasKeluar;
use App\Models\KategoriKasKeluar;
use App\Models\ModalAwal;
use App\Models\SumberDana;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KasKeluarAndModalAwalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guests_cannot_access_kas_keluar_and_modal_awal()
    {
        $this->get(route('kas-keluar'))->assertRedirect(route('login'));
        $this->get(route('modal-awal'))->assertRedirect(route('login'));
        $this->get(route('master.sumber-dana'))->assertRedirect(route('login'));
        $this->get(route('master.kategori-kas'))->assertRedirect(route('login'));
    }

    public function test_kasir_and_teknisi_cannot_access_kas_keluar()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        // Admin PKP can access Kas Keluar
        $this->actingAs($admin);
        $this->get(route('kas-keluar'))->assertStatus(200);

        $teknisi = User::where('email', 'teknisi.pkp@soulmate.test')->first();
        $this->actingAs($teknisi);
        $this->get(route('kas-keluar'))->assertStatus(403);
    }

    public function test_superadmin_can_access_kas_keluar_and_crud()
    {
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();
        $this->actingAs($superadmin);

        $this->get(route('kas-keluar'))->assertStatus(200);

        $sumberDana = SumberDana::first();
        $kategori = KategoriKasKeluar::first();

        Livewire::test(KasKeluarIndex::class)
            ->call('openCreateModal')
            ->set('keterangan', 'Beli Token Listrik Toko')
            ->set('nominal', '250.000')
            ->set('sumber_dana_id', $sumberDana->id)
            ->set('kategori_kas_keluar_id', $kategori->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kas_keluars', [
            'keterangan' => 'Beli Token Listrik Toko',
            'nominal' => 250000,
        ]);

        $kasKeluar = KasKeluar::where('keterangan', 'Beli Token Listrik Toko')->first();

        // Edit
        Livewire::test(KasKeluarIndex::class)
            ->call('openEditModal', $kasKeluar->id)
            ->set('nominal', '300.000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kas_keluars', [
            'id' => $kasKeluar->id,
            'nominal' => 300000,
        ]);

        // Delete
        Livewire::test(KasKeluarIndex::class)
            ->call('delete', $kasKeluar->id);

        $this->assertDatabaseMissing('kas_keluars', [
            'id' => $kasKeluar->id,
        ]);
    }

    public function test_modal_awal_can_be_set_and_updated()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $superadmin = User::where('email', 'superadmin@soulmate.test')->first();

        $this->actingAs($admin);
        $this->get(route('modal-awal'))->assertStatus(200);

        // 1. Validasi nominal minimal Rp 1 (tidak boleh 0 atau negatif)
        Livewire::test(ModalAwalIndex::class)
            ->call('openCreateModal')
            ->set('nominal', '0')
            ->call('save')
            ->assertHasErrors(['nominal']);

        // 2. Admin PKP input modal awal hari ini (nominal valid)
        Livewire::test(ModalAwalIndex::class)
            ->call('openCreateModal')
            ->set('nominal', '500.000')
            ->set('keterangan', 'Modal awal laci kasir pagi')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('modal_awals', [
            'cabang_id' => $admin->cabang_id,
            'nominal' => 500000,
            'keterangan' => 'Modal awal laci kasir pagi',
        ]);

        $modalAwal = ModalAwal::where('cabang_id', $admin->cabang_id)->first();

        // 3. Admin coba dobel input hari yang sama -> ditolak
        Livewire::test(ModalAwalIndex::class)
            ->call('openCreateModal')
            ->assertDispatched('swal:error');

        // 4. Admin coba input tanggal selain hari ini (backdate/future) -> ditolak
        Livewire::test(ModalAwalIndex::class)
            ->set('tanggal', now()->subDay()->toDateString())
            ->set('nominal', '100.000')
            ->call('save')
            ->assertHasErrors(['tanggal']);

        // 5. Admin cannot edit or delete modal awal (forbidden for non-superadmin/owner)
        Livewire::test(ModalAwalIndex::class)
            ->call('openEditModal', $modalAwal->id)
            ->assertDispatched('swal:error');

        Livewire::test(ModalAwalIndex::class)
            ->call('delete', $modalAwal->id)
            ->assertDispatched('swal:error');

        // Nominal still unchanged
        $this->assertDatabaseHas('modal_awals', [
            'id' => $modalAwal->id,
            'nominal' => 500000,
        ]);

        // 6. Superadmin CAN edit modal awal
        $this->actingAs($superadmin);

        Livewire::test(ModalAwalIndex::class)
            ->call('openEditModal', $modalAwal->id)
            ->set('nominal', '750.000')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('modal_awals', [
            'id' => $modalAwal->id,
            'nominal' => 750000,
        ]);

        // 7. Superadmin can delete modal awal
        Livewire::test(ModalAwalIndex::class)
            ->call('delete', $modalAwal->id)
            ->assertDispatched('swal:success');

        $this->assertDatabaseMissing('modal_awals', [
            'id' => $modalAwal->id,
        ]);
    }

    public function test_master_sumber_dana_crud()
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);

        $this->get(route('master.sumber-dana'))->assertStatus(200);

        Livewire::test(MasterSumberDanaIndex::class)
            ->call('openCreateModal')
            ->set('nama', 'Bank Mandiri Giro')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sumber_danas', [
            'nama' => 'Bank Mandiri Giro',
        ]);

        $sumber = SumberDana::where('nama', 'Bank Mandiri Giro')->first();

        // Edit
        Livewire::test(MasterSumberDanaIndex::class)
            ->call('openEditModal', $sumber->id)
            ->set('nama', 'Bank Mandiri Utama')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sumber_danas', [
            'id' => $sumber->id,
            'nama' => 'Bank Mandiri Utama',
        ]);

        // Delete
        Livewire::test(MasterSumberDanaIndex::class)
            ->call('delete', $sumber->id);

        $this->assertDatabaseMissing('sumber_danas', [
            'id' => $sumber->id,
        ]);
    }

    public function test_master_kategori_kas_crud()
    {
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);

        $this->get(route('master.kategori-kas'))->assertStatus(200);

        Livewire::test(MasterKategoriKasIndex::class)
            ->call('openCreateModal')
            ->set('nama', 'Sewa Tempat')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kategori_kas_keluars', [
            'nama' => 'Sewa Tempat',
        ]);

        $kategori = KategoriKasKeluar::where('nama', 'Sewa Tempat')->first();

        // Toggle status
        Livewire::test(MasterKategoriKasIndex::class)
            ->call('toggleStatus', $kategori->id);

        $this->assertDatabaseHas('kategori_kas_keluars', [
            'id' => $kategori->id,
            'is_aktif' => false,
        ]);

        // Delete
        Livewire::test(MasterKategoriKasIndex::class)
            ->call('delete', $kategori->id);

        $this->assertDatabaseMissing('kategori_kas_keluars', [
            'id' => $kategori->id,
        ]);
    }

    public function test_notification_bell_alerts_when_modal_awal_is_missing_after_10am()
    {
        $admin = User::where('email', 'admin.pkp@soulmate.test')->first();
        $this->actingAs($admin);

        // Hapus modal awal hari ini jika ada
        ModalAwal::where('cabang_id', $admin->cabang_id)
            ->where('tanggal', now()->toDateString())
            ->delete();

        // 1. Sebelum jam 10:00 -> tidak ada alert modal awal
        $this->travelTo(now()->setTime(8, 30, 0));

        Livewire::test(\App\Livewire\Components\NotificationBell::class)
            ->assertSet('modalAwalAlertCount', 0)
            ->assertDontSee('Peringatan Modal Awal');

        // 2. Setelah jam 10:00 -> muncul alert modal awal
        $this->travelTo(now()->setTime(10, 15, 0));

        Livewire::test(\App\Livewire\Components\NotificationBell::class)
            ->assertSet('modalAwalAlertCount', 1)
            ->assertSee('Peringatan Modal Awal')
            ->assertSee('Modal awal kasir hari ini belum dicatat');

        // 3. Setelah modal awal diinput -> alert hilang
        ModalAwal::create([
            'cabang_id' => $admin->cabang_id,
            'user_id' => $admin->id,
            'tanggal' => now()->toDateString(),
            'nominal' => 200000,
        ]);

        Livewire::test(\App\Livewire\Components\NotificationBell::class)
            ->assertSet('modalAwalAlertCount', 0)
            ->assertDontSee('Peringatan Modal Awal');

        // 4. Test untuk Owner di HQ mode
        $owner = User::where('email', 'owner@soulmate.test')->first();
        $this->actingAs($owner);
        session(['active_branch_id' => 'hq']);

        // Hapus modal awal semua cabang hari ini
        ModalAwal::whereDate('tanggal', now()->toDateString())->delete();

        $activeCabangCount = Cabang::where('status', 'aktif')->count();
        Livewire::test(\App\Livewire\Components\NotificationBell::class)
            ->assertSet('modalAwalAlertCount', $activeCabangCount)
            ->assertSee('Peringatan Modal Awal');
    }
}

