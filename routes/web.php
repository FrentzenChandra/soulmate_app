<?php

use App\Livewire\Auth\Login;
use App\Livewire\Cabang\Stok\Index as CabangStokIndex;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Kasir\Produk\Index as KasirProdukIndex;
use App\Livewire\Laporan\Penjualan\Index as LaporanPenjualanIndex;
use App\Livewire\Pengaturan\HakAkses\Index as HakAksesIndex;
use App\Livewire\Master\Cabang\Index as MasterCabangIndex;
use App\Livewire\Master\Customer\Index as MasterCustomerIndex;
use App\Livewire\Master\Kategori\Index as MasterKategoriIndex;
use App\Livewire\Master\Pembayaran\Index as MasterPembayaranIndex;
use App\Livewire\Master\Ppn\Index as MasterPpnIndex;
use App\Livewire\Master\Produk\Index as MasterProdukIndex;
use App\Livewire\Master\Servis\Index as MasterServisIndex;
use App\Livewire\Master\User\Index as MasterUserIndex;
use App\Livewire\Servis\AntrianDisplay;
use App\Livewire\Servis\Masuk\Index as ServisMasukIndex;
use App\Livewire\Servis\Keluar\Index as ServisKeluarIndex;
use App\Livewire\Servis\Batal\Index as ServisBatalIndex;
use App\Livewire\Servis\NonReal\Index as ServisNonRealIndex;
use App\Livewire\KasKeluar\Index as KasKeluarIndex;
use App\Livewire\ModalAwal\Index as ModalAwalIndex;
use App\Livewire\Saldo\Index as SaldoIndex;
use App\Livewire\Master\SumberDana\Index as MasterSumberDanaIndex;
use App\Livewire\Master\KategoriKas\Index as MasterKategoriKasIndex;
use App\Livewire\Master\Pegawai\Index as MasterPegawaiIndex;
use App\Livewire\SerialNumber\Index as SerialNumberIndex;
use App\Livewire\Transaksi\Full as TransaksiFull;
use App\Livewire\Transaksi\Harian as TransaksiHarian;
use App\Livewire\AuditLaci\Index as AuditLaciIndex;
use App\Livewire\TargetBulanan\Index as TargetBulananIndex;
use App\Livewire\TutupKas\Index as TutupKasIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\Web\Home as WebHome;
use App\Livewire\Web\Produk as WebProduk;
use App\Livewire\Web\Servis as WebServis;
use App\Livewire\Web\TentangKami as WebTentangKami;
use App\Livewire\Web\Kontak as WebKontak;

// Public Website Pages (Soulmate Komputer Landing & Information)
Route::get('/', WebHome::class)->name('home');
Route::get('/beranda', WebHome::class)->name('beranda');
Route::get('/produk', WebProduk::class)->name('web.produk');
Route::get('/servis', WebServis::class)->name('web.servis');
Route::get('/tentang-kami', WebTentangKami::class)->name('web.tentang-kami');
Route::get('/kontak', WebKontak::class)->name('web.kontak');

// Guest Routes (Authentication - Dilindungi Throttle)
Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// TV Monitor / Public Antrian Servis Display
Route::get('/antrian', AntrianDisplay::class)->name('antrian.display');
Route::get('/antrian-servis', fn () => redirect()->route('antrian.display'));

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    // Master Routes
    Route::prefix('master')->name('master.')->group(function () {
        // Owner Only Routes (Konteks Kantor Pusat HQ)
        Route::middleware('role:Owner')->group(function () {
            Route::get('/cabang', MasterCabangIndex::class)->name('cabang');
            Route::get('/ppn', MasterPpnIndex::class)->name('ppn');
            Route::get('/hak-akses', HakAksesIndex::class)->name('hak-akses');
            Route::get('/via-pajak', \App\Livewire\Master\ViaPajak\Index::class)->name('via-pajak');
            Route::get('/kode-pajak', \App\Livewire\Master\KodePajak\Index::class)->name('kode-pajak');
            Route::get('/pph', \App\Livewire\Master\Pph\Index::class)->name('pph');
            Route::get('/jam-kerja', \App\Livewire\Master\JamKerja\Index::class)->name('jam-kerja');
        });

        // Owner & Superadmin / Permission: master.users.view & master.settings.view
        Route::get('/users', MasterUserIndex::class)->name('users')->middleware('can:master.users.view');
        Route::get('/orang-pajak', \App\Livewire\Master\OrangPajak\Index::class)->name('orang-pajak')->middleware('can:master.orang_pajak.view');
        Route::get('/kategori', MasterKategoriIndex::class)->name('kategori')->middleware('can:master.settings.view');
        Route::get('/metode-pembayaran', MasterPembayaranIndex::class)->name('metode-pembayaran')->middleware('can:master.settings.view');
        Route::get('/sumber-dana', MasterSumberDanaIndex::class)->name('sumber-dana')->middleware('can:master.settings.view');
        Route::get('/kategori-kas', MasterKategoriKasIndex::class)->name('kategori-kas')->middleware('can:master.settings.view');

        // Master Pegawai, Produk, Servis, & Customer (Dynamic Permissions)
        Route::get('/pegawai', MasterPegawaiIndex::class)->name('pegawai')->middleware('can:master.pegawai.view');
        Route::get('/produk', MasterProdukIndex::class)->name('produk')->middleware('can:master.produk.view');
        Route::get('/servis', MasterServisIndex::class)->name('servis')->middleware('can:master.servis.view');
        Route::get('/customer', MasterCustomerIndex::class)->name('customer')->middleware('can:master.customer.view');
        Route::get('/distributor', \App\Livewire\Master\Distributor\Index::class)->name('distributor')->middleware('can:master.distributor.view');
        Route::get('/wifi-absensi', \App\Livewire\Master\WifiAbsen\Index::class)->name('wifi-absen')->middleware('can:master.wifi_absensi.view');
    });

    // Layanan Servis & Reparasi (Dynamic Permissions)
    Route::get('/servis/masuk', ServisMasukIndex::class)->name('servis.masuk')->middleware('can:servis.masuk.view');
    Route::get('/servis/keluar', ServisKeluarIndex::class)->name('servis.keluar')->middleware('can:servis.keluar.view');
    Route::get('/servis/batal', ServisBatalIndex::class)->name('servis.batal')->middleware('can:servis.batal.view');
    Route::get('/servis/non-real', ServisNonRealIndex::class)->name('servis.non-real')->middleware('can:servis.non_real.view');
    Route::get('/klaim-garansi', \App\Livewire\KlaimGaransi\Index::class)->name('klaim-garansi.index')->middleware('can:klaim_garansi.view');
    Route::get('/servis/antrian', AntrianDisplay::class)->name('servis.antrian');
    Route::get('/servis/pengerjaan', fn () => redirect()->route('servis.keluar'))->name('servis.pengerjaan');
    Route::get('/servis/{servis}/tanda-terima', function (\App\Models\ServisMasuk $servis) {
        $user = Auth::user();
        if (! $user->isOwner() && $user->cabang_id !== $servis->cabang_id) {
            abort(403, 'Akses tanda terima servis cabang lain dibatasi.');
        }
        $servis->load(['customer', 'cabang', 'pembuat', 'teknisi']);

        if (request()->query('format') === 'pdf' || request()->has('pdf') || request()->query('autosave') === '1') {
            $pdf = Pdf::loadView('pdf.servis-tanda-terima', ['servis' => $servis])
                ->setPaper([0, 0, 680.315, 425.197]);
            $filename = 'Tanda_Terima_Servis_' . Str::slug($servis->no_servis_frontend ?? $servis->no_servis) . '.pdf';

            return request()->query('download') === '1'
                ? $pdf->download($filename)
                : $pdf->stream($filename);
        }

        return view('servis.tanda-terima', ['servis' => $servis]);
    })->name('servis.tanda-terima')->middleware('can:servis.masuk.print');

    Route::get('/servis/{servis}/invoice', function (\App\Models\ServisMasuk $servis) {
        $user = Auth::user();
        if (! $user->isOwner() && $user->cabang_id !== $servis->cabang_id) {
            abort(403, 'Akses faktur servis cabang lain dibatasi.');
        }
        $servis->load(['customer', 'cabang', 'pembuat', 'teknisi', 'kasir', 'details.produk']);

        if (request()->query('format') === 'pdf' || request()->has('pdf') || request()->query('autosave') === '1') {
            $pdf = Pdf::loadView('pdf.servis-invoice', ['servis' => $servis])
                ->setPaper([0, 0, 680.315, 425.197]);
            $filename = 'Faktur_Servis_' . Str::slug($servis->no_servis_frontend ?? $servis->no_servis) . '.pdf';

            return request()->query('download') === '1'
                ? $pdf->download($filename)
                : $pdf->stream($filename);
        }

        return view('servis.invoice', ['servis' => $servis]);
    })->name('servis.invoice')->middleware('can:servis.keluar.print');

    // Kasir POS & Penjualan (Dynamic Permissions)
    Route::get('/kasir/produk', KasirProdukIndex::class)->name('kasir.produk')->middleware('can:kasir.produk.view');
    Route::get('/kasir/produk/invoice/{penjualan}', function (\App\Models\Penjualan $penjualan) {
        $user = Auth::user();
        if (! $user->isOwner() && $user->cabang_id !== $penjualan->cabang_id) {
            abort(403, 'Akses invoice cabang lain dibatasi.');
        }
        $penjualan->load(['details.produk', 'cabang', 'kasir', 'customer', 'ppn']);

        if (request()->query('format') === 'pdf' || request()->has('pdf') || request()->query('autosave') === '1') {
            $pdf = Pdf::loadView('pdf.kasir-invoice', ['penjualan' => $penjualan])
                ->setPaper([0, 0, 680.315, 396.850]);
            $filename = 'Sales_Invoice_' . Str::slug($penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi) . '.pdf';

            return request()->query('download') === '1'
                ? $pdf->download($filename)
                : $pdf->stream($filename);
        }

        return view('kasir.invoice', ['penjualan' => $penjualan]);
    })->name('kasir.produk.invoice')->middleware('can:kasir.produk.print');

    Route::get('/laporan/penjualan', LaporanPenjualanIndex::class)->name('laporan.penjualan')->middleware('can:laporan.penjualan.view');

    // Keuangan & Kas (Kas Keluar & Saldo: Dynamic Permissions)
    Route::get('/kas-keluar', KasKeluarIndex::class)->name('kas-keluar')->middleware('can:kas.keluar.view');
    Route::get('/saldo', SaldoIndex::class)->name('saldo.index')->middleware('can:saldo.view');

    // Transaksi FULL & Target Bulanan (Dynamic Permissions)
    Route::get('/transaksi/full', TransaksiFull::class)->name('transaksi.full')->middleware('can:transaksi.full.view');
    Route::get('/transaksi/full/export-pdf', [\App\Http\Controllers\TransaksiFullExportController::class, 'exportPdf'])->name('transaksi.full.export-pdf')->middleware('can:transaksi.full.view');
    Route::get('/faktur-pajak', \App\Livewire\FakturPajak\Index::class)->name('faktur-pajak.index')->middleware('can:pajak.view');
    Route::get('/target-bulanan', TargetBulananIndex::class)->name('target.bulanan')->middleware('can:target.bulanan.view');

    // Audit Laci (Owner Only)
    Route::middleware('role:Owner')->group(function () {
        Route::get('/audit-laci', AuditLaciIndex::class)->name('audit-laci');
    });

    Route::middleware('role:Owner|Superadmin|Admin|Bendahara|Kasir')->group(function () {
        Route::get('/modal-awal', ModalAwalIndex::class)->name('modal-awal');
        Route::get('/tutup-kas', TutupKasIndex::class)->name('tutup-kas');
    });

    Route::get('/transaksi/harian', TransaksiHarian::class)->name('transaksi.harian')->middleware('can:transaksi.harian.view');

    // Serial Number & Manajemen Stok (Dynamic Permissions)
    Route::get('/serial-number', SerialNumberIndex::class)->name('serial-number')->middleware('can:serial_number.view');
    Route::get('/cabang/stok', CabangStokIndex::class)->name('cabang.stok')->middleware('can:cabang.stok.view');

    // Absensi Staf & Riwayat (Dynamic Permissions)
    Route::get('/absensi', \App\Livewire\Absensi\Index::class)->name('absensi.index')->middleware('can:absensi.view');
    Route::get('/absensi/riwayat', \App\Livewire\Absensi\Riwayat::class)->name('absensi.riwayat')->middleware('can:absensi.riwayat');

    // Slip Gaji Karyawan (Dynamic Permissions)
    Route::get('/slip-gaji', \App\Livewire\SlipGaji\Index::class)->name('slip-gaji.index')->middleware('can:gaji.view');
    Route::get('/slip-gaji/buat', \App\Livewire\SlipGaji\Form::class)->name('slip-gaji.create')->middleware('can:gaji.create');
    Route::get('/slip-gaji/{slip}', \App\Livewire\SlipGaji\Form::class)->name('slip-gaji.edit')->middleware('can:gaji.view');
    Route::get('/slip-gaji/{slip}/pdf', [\App\Http\Controllers\SlipGajiPdfController::class, 'exportPdf'])->name('slip-gaji.pdf')->middleware('can:gaji.export_pdf');

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
