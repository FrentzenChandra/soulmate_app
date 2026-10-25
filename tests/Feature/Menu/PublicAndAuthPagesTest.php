<?php

namespace Tests\Feature\Menu;

use App\Livewire\Auth\Login;
use App\Livewire\Servis\AntrianDisplay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\InteractsWithRolesAndPermissions;

class PublicAndAuthPagesTest extends TestCase
{
    use RefreshDatabase, InteractsWithRolesAndPermissions;

    public function test_halaman_publik_website_dapat_diakses_bebas_oleh_siapa_saja(): void
    {
        $this->get(route('home'))->assertStatus(200);
        $this->get(route('beranda'))->assertStatus(200);
        $this->get(route('web.produk'))->assertStatus(200);
        $this->get(route('web.servis'))->assertStatus(200);
        $this->get(route('web.tentang-kami'))->assertStatus(200);
        $this->get(route('web.kontak'))->assertStatus(200);
        $this->get(route('antrian.display'))->assertStatus(200);
    }

    public function test_redirect_antrian_servis_ke_antrian_display(): void
    {
        $response = $this->get('/antrian-servis');

        $response->assertRedirect(route('antrian.display'));
    }

    public function test_halaman_login_dapat_diakses_guest(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    public function test_user_yang_sudah_login_di_redirect_dari_halaman_login(): void
    {
        $this->actingAsRole('Admin');

        $response = $this->get(route('login'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_login_berhasil_dengan_username_dan_password_valid(): void
    {
        $this->setupRolesAndPermissions();

        $user = User::create([
            'name' => 'Budi Santoso',
            'username' => 'budisantoso',
            'email' => 'budi@soulmate.test',
            'password' => bcrypt('password123'),
            'cabang_id' => $this->defaultTestCabang->id,
            'status' => 'aktif',
        ]);
        $user->assignRole('Admin');

        Livewire::test(Login::class)
            ->set('identity', 'budisantoso')
            ->set('password', 'password123')
            ->call('authenticate')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_berhasil_dengan_email_dan_password_valid(): void
    {
        $this->setupRolesAndPermissions();

        $user = User::create([
            'name' => 'Siti Rahma',
            'username' => 'sitirahma',
            'email' => 'siti@soulmate.test',
            'password' => bcrypt('secret123'),
            'cabang_id' => $this->defaultTestCabang->id,
            'status' => 'aktif',
        ]);
        $user->assignRole('Teknisi');

        Livewire::test(Login::class)
            ->set('identity', 'siti@soulmate.test')
            ->set('password', 'secret123')
            ->call('authenticate')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_gagal_dengan_password_salah(): void
    {
        $this->setupRolesAndPermissions();

        User::create([
            'name' => 'Doni Test',
            'username' => 'donitest',
            'email' => 'doni@soulmate.test',
            'password' => bcrypt('benar123'),
            'cabang_id' => $this->defaultTestCabang->id,
            'status' => 'aktif',
        ]);

        Livewire::test(Login::class)
            ->set('identity', 'donitest')
            ->set('password', 'salahpassword')
            ->call('authenticate')
            ->assertHasErrors(['identity']);

        $this->assertGuest();
    }

    public function test_login_ditolak_jika_akun_nonaktif(): void
    {
        $this->setupRolesAndPermissions();

        User::create([
            'name' => 'Akun Dinonaktifkan',
            'username' => 'user_nonaktif',
            'email' => 'nonaktif@soulmate.test',
            'password' => bcrypt('password123'),
            'cabang_id' => $this->defaultTestCabang->id,
            'status' => 'nonaktif',
        ]);

        Livewire::test(Login::class)
            ->set('identity', 'user_nonaktif')
            ->set('password', 'password123')
            ->call('authenticate')
            ->assertHasErrors(['identity']);

        $this->assertGuest();
    }

    public function test_rate_limiter_mencegah_brute_force_login(): void
    {
        RateLimiter::clear(RateLimiter::availableIn('test_key'));

        $component = Livewire::test(Login::class);

        // Lakukan 5 kali percobaan gagal
        for ($i = 0; $i < 5; $i++) {
            $component->set('identity', 'target_brute_force')
                ->set('password', 'wrong')
                ->call('authenticate');
        }

        // Percobaan ke-6 wajib diblokir oleh Rate Limiter
        $component->set('identity', 'target_brute_force')
            ->set('password', 'wrong')
            ->call('authenticate')
            ->assertHasErrors(['identity']);
    }

    public function test_logout_berhasil_dan_redirect_ke_login(): void
    {
        $this->actingAsRole('Admin');
        $this->assertAuthenticated();

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_render_tv_monitor_antrian_display(): void
    {
        $this->setupRolesAndPermissions();

        Livewire::test(AntrianDisplay::class)
            ->assertStatus(200)
            ->call('showList', 'laptop', 'antrian')
            ->assertSet('modalListOpen', true)
            ->assertSet('selectedCategory', 'laptop')
            ->call('closeModal')
            ->assertSet('modalListOpen', false);
    }
}

