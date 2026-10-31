<?php

namespace Tests\Feature;

use App\Livewire\Birthday\TodayPage;
use App\Livewire\Notification\BirthdayNotification;
use App\Models\Cabang;
use App\Models\User;
use App\Services\BirthdayService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BirthdayNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_celebrant_does_not_see_notification_about_themselves(): void
    {
        $cabang = Cabang::first();
        $today = now('Asia/Jakarta');

        $celebrant = User::create([
            'name' => 'Budi Ultah',
            'email' => 'budi@test.com',
            'username' => 'budiultah',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'tanggal_lahir' => $today->copy()->subYears(25)->toDateString(),
            'bagian' => 'Teknisi Laptop',
            'status' => 'aktif',
        ]);

        $service = app(BirthdayService::class);
        $notif = $service->getNotification($celebrant);

        // Celebrant tidak boleh melihat notifikasi apapun tentang dirinya
        $this->assertNull($notif['phase']);
        $this->assertEmpty($notif['users']);
    }

    public function test_viewer_sees_today_birthday_of_other_user_and_redirects(): void
    {
        $cabang = Cabang::first();
        $today = now('Asia/Jakarta');

        $celebrant = User::create([
            'name' => 'Siti Ultah',
            'email' => 'siti@test.com',
            'username' => 'sitiultah',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'tanggal_lahir' => $today->copy()->subYears(24)->toDateString(),
            'bagian' => 'Admin Stok',
            'status' => 'aktif',
        ]);

        $viewer = User::create([
            'name' => 'Viewer Staff',
            'email' => 'viewer@test.com',
            'username' => 'viewerstaff',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($viewer);

        // Komponen BirthdayNotification di layout harus redirect ke route birthday.today
        Livewire::test(BirthdayNotification::class)
            ->assertRedirect(route('birthday.today'));

        // Cek bahwa last_birthday_notif_at viewer terisi hari ini
        $viewer->refresh();
        $this->assertNotNull($viewer->last_birthday_notif_at);
        $this->assertTrue($viewer->last_birthday_notif_at->isSameDay($today));

        // Halaman /selamat-ulang-tahun menampilkan nama selebran Siti Ultah
        $response = $this->get(route('birthday.today'));
        $response->assertOk();
        $response->assertSee('Siti Ultah');
        $response->assertSee('Admin Stok');
        $response->assertSee('✦ HARI YANG ISTIMEWA ✦');
    }

    public function test_celebrant_visiting_today_page_is_redirected_to_dashboard(): void
    {
        $cabang = Cabang::first();
        $today = now('Asia/Jakarta');

        $celebrant = User::create([
            'name' => 'Dedi Ultah',
            'email' => 'dedi@test.com',
            'username' => 'dediultah',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'tanggal_lahir' => $today->copy()->subYears(28)->toDateString(),
            'status' => 'aktif',
        ]);

        $this->actingAs($celebrant);

        // Selebran yang membuka halaman /selamat-ulang-tahun langsung diarahkan ke dashboard
        $this->get(route('birthday.today'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_today_page_redirects_to_dashboard_if_no_one_has_birthday_today(): void
    {
        $cabang = Cabang::first();

        $viewer = User::create([
            'name' => 'Rina Staff',
            'email' => 'rina@test.com',
            'username' => 'rinastaff',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($viewer);

        // Tidak ada yang ultah hari ini -> redirect dashboard
        $this->get(route('birthday.today'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_viewer_sees_upcoming_modal_for_h7_to_h1(): void
    {
        $cabang = Cabang::first();
        $targetDate = now('Asia/Jakarta')->addDays(5);

        $celebrant = User::create([
            'name' => 'Randi H5',
            'email' => 'randi@test.com',
            'username' => 'randih5',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'tanggal_lahir' => $targetDate->copy()->subYears(22)->toDateString(),
            'bagian' => 'Teknisi Printer',
            'status' => 'aktif',
        ]);

        $viewer = User::create([
            'name' => 'Staf Pengamat',
            'email' => 'pengamat@test.com',
            'username' => 'pengamat',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($viewer);

        Livewire::test(BirthdayNotification::class)
            ->assertSet('showModal', true)
            ->assertSet('daysLeft', 5)
            ->assertSee('Randi H5')
            ->assertSee('tinggal')
            ->assertSee('5')
            ->assertSee('hari lagi!')
            ->assertSee('Tinggal sebentar lagi. Jangan sampai terlewat!')
            ->assertSee('Siap, nanti saya ingatkan! 🎉');
    }

    public function test_h1_shows_besok_title(): void
    {
        $cabang = Cabang::first();
        $targetDate = now('Asia/Jakarta')->addDays(1);

        User::create([
            'name' => 'Maya Besok',
            'email' => 'maya@test.com',
            'username' => 'mayabesok',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'tanggal_lahir' => $targetDate->copy()->subYears(23)->toDateString(),
            'bagian' => 'Bendahara',
            'status' => 'aktif',
        ]);

        $viewer = User::create([
            'name' => 'Viewer Maya',
            'email' => 'viewermaya@test.com',
            'username' => 'viewermaya',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($viewer);

        Livewire::test(BirthdayNotification::class)
            ->assertSet('showModal', true)
            ->assertSet('daysLeft', 1)
            ->assertSee('Maya Besok')
            ->assertSee('BESOK!')
            ->assertSee('BESOK! Siapkan ucapan terbaikmu malam ini. 🎂');
    }

    public function test_notification_only_shown_once_per_day_per_viewer(): void
    {
        $cabang = Cabang::first();
        $targetDate = now('Asia/Jakarta')->addDays(3);

        User::create([
            'name' => 'Kiki H3',
            'email' => 'kiki@test.com',
            'username' => 'kikih3',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'tanggal_lahir' => $targetDate->copy()->subYears(26)->toDateString(),
            'status' => 'aktif',
        ]);

        $viewer = User::create([
            'name' => 'Viewer Limit',
            'email' => 'viewerlimit@test.com',
            'username' => 'viewerlimit',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($viewer);

        // Putaran pertama: muncul
        Livewire::test(BirthdayNotification::class)
            ->assertSet('showModal', true);

        // Putaran kedua di hari yang sama: tidak muncul karena last_birthday_notif_at sudah hari ini
        Livewire::test(BirthdayNotification::class)
            ->assertSet('showModal', false);
    }

    public function test_leap_year_february_29_celebrated_on_february_28_in_non_leap_year(): void
    {
        $cabang = Cabang::first();

        // Buat user lahir 29 Februari 2000
        $leapUser = User::create([
            'name' => 'Lahir Kabisat',
            'email' => 'kabisat@test.com',
            'username' => 'lahirkabisat',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'tanggal_lahir' => '2000-02-29',
            'status' => 'aktif',
        ]);

        $service = app(BirthdayService::class);

        // Simulasikan tanggal 28 Februari 2025 (tahun non-kabisat)
        Carbon::setTestNow(Carbon::parse('2025-02-28 09:00:00', 'Asia/Jakarta'));

        $this->assertTrue($service->isUserBirthdayToday($leapUser));

        Carbon::setTestNow(); // Reset clock
    }

    public function test_soft_deleted_user_is_ignored(): void
    {
        $cabang = Cabang::first();
        $today = now('Asia/Jakarta');

        $deletedCelebrant = User::create([
            'name' => 'Mantan Staf',
            'email' => 'mantan@test.com',
            'username' => 'mantanstaf',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'tanggal_lahir' => $today->copy()->subYears(30)->toDateString(),
            'status' => 'aktif',
        ]);
        $deletedCelebrant->delete(); // Soft delete

        $viewer = User::create([
            'name' => 'Staf Tetap',
            'email' => 'tetap@test.com',
            'username' => 'staftetap',
            'password' => bcrypt('password'),
            'cabang_id' => $cabang->id,
            'status' => 'aktif',
        ]);

        $service = app(BirthdayService::class);
        $notif = $service->getNotification($viewer);

        // Tidak boleh memicu notifikasi untuk user yang telah dihapus
        $this->assertNull($notif['phase']);
        $this->assertEmpty($notif['users']);
    }
}

