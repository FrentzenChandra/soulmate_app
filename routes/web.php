<?php

use App\Livewire\Auth\Login;
use App\Livewire\Cabang\Stok\Index as CabangStokIndex;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Kasir\Produk\Index as KasirProdukIndex;
use App\Livewire\Master\Cabang\Index as MasterCabangIndex;
use App\Livewire\Master\Customer\Index as MasterCustomerIndex;
use App\Livewire\Master\Kategori\Index as MasterKategoriIndex;
use App\Livewire\Master\Ppn\Index as MasterPpnIndex;
use App\Livewire\Master\Produk\Index as MasterProdukIndex;
use App\Livewire\Master\User\Index as MasterUserIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Redirect root to dashboard or login
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

// Guest Routes (Authentication)
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    // Master Routes
    Route::prefix('master')->name('master.')->group(function () {
        // Owner Only Routes
        Route::middleware('role:Owner')->group(function () {
            Route::get('/cabang', MasterCabangIndex::class)->name('cabang');
            Route::get('/ppn', MasterPpnIndex::class)->name('ppn');
        });

        // Owner & Superadmin
        Route::middleware('role:Owner|Superadmin')->group(function () {
            Route::get('/users', MasterUserIndex::class)->name('users');
            Route::get('/produk', MasterProdukIndex::class)->name('produk');
            Route::get('/kategori', MasterKategoriIndex::class)->name('kategori');
        });

        // Customer / Pelanggan (Owner, Superadmin, Kasir, Teknisi)
        Route::middleware('role:Owner|Superadmin|Kasir|Teknisi')->group(function () {
            Route::get('/customer', MasterCustomerIndex::class)->name('customer');
        });
    });

    // Kasir POS & Penjualan (Owner, Superadmin, Kasir)
    Route::middleware('role:Owner|Superadmin|Kasir')->group(function () {
        Route::get('/kasir/produk', KasirProdukIndex::class)->name('kasir.produk');
    });

    // Operasional Stok Cabang
    Route::get('/cabang/stok', CabangStokIndex::class)->name('cabang.stok');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});

// Storage fallback route (serves public files seamlessly across Windows junction, PHP built-in server, and Apache/Nginx)
Route::get('/storage/{path}', function (string $path) {
    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    $filePath = Storage::disk('public')->path($path);
    $mimeType = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';

    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*')->name('storage.fallback');
