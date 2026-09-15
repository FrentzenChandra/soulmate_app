<?php

use App\Livewire\Auth\Login;
use App\Livewire\Cabang\Stok\Index as CabangStokIndex;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Kasir\Produk\Index as KasirProdukIndex;
use App\Livewire\Laporan\Penjualan\Index as LaporanPenjualanIndex;
use App\Livewire\Master\Cabang\Index as MasterCabangIndex;
use App\Livewire\Master\Customer\Index as MasterCustomerIndex;
use App\Livewire\Master\Kategori\Index as MasterKategoriIndex;
use App\Livewire\Master\Ppn\Index as MasterPpnIndex;
use App\Livewire\Master\Produk\Index as MasterProdukIndex;
use App\Livewire\Master\User\Index as MasterUserIndex;
use App\Livewire\Servis\Masuk\Index as ServisMasukIndex;
use App\Livewire\Servis\Pengerjaan\Index as ServisPengerjaanIndex;
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

        // Customer / Pelanggan (Owner, Superadmin, Admin, Kasir, Teknisi)
        Route::middleware('role:Owner|Superadmin|Admin|Kasir|Teknisi')->group(function () {
            Route::get('/customer', MasterCustomerIndex::class)->name('customer');
        });
    });

    // Layanan Servis & Reparasi (Owner, Superadmin, Admin, Teknisi, Kasir)
    Route::middleware('role:Owner|Superadmin|Admin|Teknisi|Kasir')->group(function () {
        Route::get('/servis/masuk', ServisMasukIndex::class)->name('servis.masuk');
        Route::get('/servis/pengerjaan', ServisPengerjaanIndex::class)->name('servis.pengerjaan');
        Route::get('/servis/{servis}/tanda-terima', function (\App\Models\ServisMasuk $servis) {
            $user = Auth::user();
            if (! $user->isOwner() && $user->cabang_id !== $servis->cabang_id) {
                abort(403, 'Akses tanda terima servis cabang lain dibatasi.');
            }
            $servis->load(['customer', 'cabang', 'pembuat', 'teknisi']);

            return view('servis.tanda-terima', ['servis' => $servis]);
        })->name('servis.tanda-terima');
        Route::get('/servis/{servis}/invoice', function (\App\Models\ServisMasuk $servis) {
            $user = Auth::user();
            if (! $user->isOwner() && $user->cabang_id !== $servis->cabang_id) {
                abort(403, 'Akses faktur servis cabang lain dibatasi.');
            }
            $servis->load(['customer', 'cabang', 'pembuat', 'teknisi', 'kasir', 'details.produk']);

            return view('servis.invoice', ['servis' => $servis]);
        })->name('servis.invoice');
    });

    // Kasir POS & Penjualan (Owner, Superadmin, Admin, Kasir)
    Route::middleware('role:Owner|Superadmin|Admin|Kasir')->group(function () {
        Route::get('/kasir/produk', KasirProdukIndex::class)->name('kasir.produk');
        Route::get('/kasir/produk/invoice/{penjualan}', function (\App\Models\Penjualan $penjualan) {
            $user = Auth::user();
            if (! $user->isOwner() && $user->cabang_id !== $penjualan->cabang_id) {
                abort(403, 'Akses invoice cabang lain dibatasi.');
            }
            $penjualan->load(['details.produk', 'cabang', 'kasir', 'customer', 'ppn']);

            return view('kasir.invoice', ['penjualan' => $penjualan]);
        })->name('kasir.produk.invoice');
        Route::get('/laporan/penjualan', LaporanPenjualanIndex::class)->name('laporan.penjualan');
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
