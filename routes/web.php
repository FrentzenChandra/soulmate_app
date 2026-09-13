<?php

use App\Livewire\Auth\Login;
use App\Livewire\Cabang\Stok\Index as CabangStokIndex;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Kasir\Produk\Index as KasirProdukIndex;
use App\Livewire\Master\Cabang\Index as MasterCabangIndex;
use App\Livewire\Master\Kategori\Index as MasterKategoriIndex;
use App\Livewire\Master\Produk\Index as MasterProdukIndex;
use App\Livewire\Master\User\Index as MasterUserIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

    // Master Routes (Owner & Superadmin)
    Route::prefix('master')->name('master.')->group(function () {
        // Owner Only Routes
        Route::middleware('role:Owner')->group(function () {
            Route::get('/cabang', MasterCabangIndex::class)->name('cabang');
            Route::get('/users', MasterUserIndex::class)->name('users');
        });

        // Master Produk & Kategori (Owner & Superadmin)
        Route::middleware('role:Owner|Superadmin')->group(function () {
            Route::get('/produk', MasterProdukIndex::class)->name('produk');
            Route::get('/kategori', MasterKategoriIndex::class)->name('kategori');
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
