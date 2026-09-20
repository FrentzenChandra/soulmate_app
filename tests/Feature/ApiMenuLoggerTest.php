<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiMenuLoggerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register dummy routes for testing logger
        Route::get('/api/kasir/test', function () {
            return response()->json(['status' => 'ok']);
        });

        Route::post('/api/servis/order', function (\Illuminate\Http\Request $request) {
            return response()->json(['id' => 123, 'nama' => $request->input('nama')], 201);
        });

        Route::post('/api/user/login', function (\Illuminate\Http\Request $request) {
            return response()->json(['token' => 'dummy_token'], 200);
        });

        Route::get('/master/produk', function () {
            return response('Master Produk Page', 200);
        });

        Route::get('/serial-number', function () {
            return response('Serial Number Page', 200);
        });

        Route::post('/livewire/update', function () {
            return response()->json(['components' => []], 200);
        });
    }

    public function test_api_menu_logger_creates_daily_log_file_per_menu(): void
    {
        $date = now()->format('Y-m-d');
        $kasirLogPath = storage_path("logs/menu-kasir-test-{$date}.log");

        if (File::exists($kasirLogPath)) {
            File::delete($kasirLogPath);
        }

        $response = $this->getJson('/api/kasir/test');
        $response->assertStatus(200);

        $this->assertFileExists($kasirLogPath);
        $logContent = File::get($kasirLogPath);
        $this->assertStringContainsString('200 | GET /api/kasir/test', $logContent);
        $this->assertStringContainsString('User: Guest', $logContent);
    }

    public function test_api_menu_logger_masks_sensitive_fields_and_identifies_user(): void
    {
        $date = now()->format('Y-m-d');
        $userLogPath = storage_path("logs/menu-user-{$date}.log");

        if (File::exists($userLogPath)) {
            File::delete($userLogPath);
        }

        $user = User::factory()->create([
            'name' => 'Budi Kasir',
        ]);

        $response = $this->actingAs($user)->postJson('/api/user/login', [
            'username' => 'budi',
            'password' => 'secret12345',
            'pin' => '9988',
        ]);

        $response->assertStatus(200);

        $this->assertFileExists($userLogPath);
        $logContent = File::get($userLogPath);

        $this->assertStringContainsString("User: {$user->id} (Budi Kasir)", $logContent);
        $this->assertStringContainsString('"username":"budi"', $logContent);
        $this->assertStringContainsString('"password":"********"', $logContent);
        $this->assertStringContainsString('"pin":"********"', $logContent);
        $this->assertStringNotContainsString('secret12345', $logContent);
        $this->assertStringNotContainsString('9988', $logContent);
    }

    public function test_web_routes_and_livewire_requests_are_logged_per_menu(): void
    {
        $date = now()->format('Y-m-d');
        $masterProdukLog = storage_path("logs/menu-master-produk-{$date}.log");
        $serialNumberLog = storage_path("logs/menu-serial-number-{$date}.log");

        if (File::exists($masterProdukLog)) {
            File::delete($masterProdukLog);
        }
        if (File::exists($serialNumberLog)) {
            File::delete($serialNumberLog);
        }

        // 1. Visit Web Master Produk
        $this->get('/master/produk')->assertStatus(200);
        $this->assertFileExists($masterProdukLog);
        $this->assertStringContainsString('GET /master/produk', File::get($masterProdukLog));

        // 2. Visit Web Serial Number
        $this->get('/serial-number')->assertStatus(200);
        $this->assertFileExists($serialNumberLog);
        $this->assertStringContainsString('GET /serial-number', File::get($serialNumberLog));

        // 3. Livewire update from Kasir page
        $kasirMenuLog = storage_path("logs/menu-kasir-produk-{$date}.log");
        if (File::exists($kasirMenuLog)) {
            File::delete($kasirMenuLog);
        }

        $this->withHeaders(['Referer' => 'http://localhost/kasir/produk'])
            ->postJson('/livewire/update', [
                'components' => [
                    [
                        'snapshot' => 'App\\Livewire\\Kasir\\Produk\\Index',
                        'updates' => ['search' => 'laptop'],
                        'calls' => [['method' => 'scanBarcode', 'params' => ['12345']]],
                    ]
                ]
            ])->assertStatus(200);

        $this->assertFileExists($kasirMenuLog);
        $kasirContent = File::get($kasirMenuLog);
        $this->assertStringContainsString('POST /livewire/update', $kasirContent);
        $this->assertStringContainsString('scanBarcode', $kasirContent);
        $this->assertStringContainsString('laptop', $kasirContent);
    }
}
