<?php

namespace App\Livewire\Dashboard;

use App\Models\Cabang;
use App\Models\Penjualan;
use App\Models\ServisMasuk;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard Overview')]
class Index extends Component
{
    public function switchToBranch($cabangId)
    {
        if (Auth::user()->isOwner()) {
            session(['active_branch_id' => $cabangId]);

            return redirect()->route('dashboard');
        }
    }

    public function switchToHq()
    {
        if (Auth::user()->isOwner()) {
            session(['active_branch_id' => 'hq']);

            return redirect()->route('dashboard');
        }
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $cabangId = null;
        if (! $isHQ) {
            $cabangId = $isOwner ? $activeBranchSession : $user->cabang_id;
        }

        $activeCabang = $cabangId ? Cabang::find($cabangId) : null;

        // --- GLOBAL / HQ METRICS ---
        $totalCabangAktif = Cabang::where('status', 'aktif')->count();
        $totalUserAktif = User::where('status', 'aktif')->count();

        // Servis Query
        $servisQuery = ServisMasuk::query()
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId));

        $countPengecekan = (clone $servisQuery)->where('status', 'pengecekan')->count();
        $countPengerjaan = (clone $servisQuery)->where('status', 'pengerjaan')->count();
        $countSelesai = (clone $servisQuery)->where('status', 'selesai')->count();
        $countTotalAktif = $countPengecekan + $countPengerjaan + $countSelesai;

        // Breakdown Perangkat untuk TV Display Monitor Antrian
        $antrianLaptop = [
            'cek' => (clone $servisQuery)->where('kategori_perangkat', 'laptop')->where('status', 'pengecekan')->count(),
            'prg' => (clone $servisQuery)->where('kategori_perangkat', 'laptop')->where('status', 'pengerjaan')->count(),
            'ok' => (clone $servisQuery)->where('kategori_perangkat', 'laptop')->where('status', 'selesai')->count(),
        ];
        $antrianPrinter = [
            'cek' => (clone $servisQuery)->where('kategori_perangkat', 'printer')->where('status', 'pengecekan')->count(),
            'prg' => (clone $servisQuery)->where('kategori_perangkat', 'printer')->where('status', 'pengerjaan')->count(),
            'ok' => (clone $servisQuery)->where('kategori_perangkat', 'printer')->where('status', 'selesai')->count(),
        ];
        $antrianKomputer = [
            'cek' => (clone $servisQuery)->where('kategori_perangkat', 'komputer')->where('status', 'pengecekan')->count(),
            'prg' => (clone $servisQuery)->where('kategori_perangkat', 'komputer')->where('status', 'pengerjaan')->count(),
            'ok' => (clone $servisQuery)->where('kategori_perangkat', 'komputer')->where('status', 'selesai')->count(),
        ];

        // Penjualan & Omzet (Hanya Transaksi Riil/Nyata)
        $penjualanQuery = Penjualan::query()
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->where('is_real', true)
            ->where('status', 'lunas');

        $omzetHariIni = (clone $penjualanQuery)
            ->whereDate('tanggal', Carbon::today())
            ->sum('total');

        $omzetBulanIni = (clone $penjualanQuery)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('total');

        $totalTransaksiHariIni = (clone $penjualanQuery)
            ->whereDate('tanggal', Carbon::today())
            ->count();

        $totalTransaksiBulanIni = (clone $penjualanQuery)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        // Servis Masuk Terbaru
        $recentServis = ServisMasuk::with(['customer', 'teknisi', 'cabang'])
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->latest('tanggal_masuk')
            ->take(5)
            ->get();

        // Penjualan Terbaru
        $recentPenjualan = Penjualan::with(['customer', 'kasir', 'cabang'])
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->latest('tanggal')
            ->take(5)
            ->get();

        // Stok Menipis (< 5) Produk Aktif
        $lowStocks = Stok::with(['produk', 'cabang'])
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->where('is_aktif', true)
            ->whereHas('produk')
            ->whereHas('cabang', fn ($q) => $q->where('status', 'aktif'))
            ->where('jumlah', '<=', 5)
            ->orderBy('jumlah', 'asc')
            ->take(5)
            ->get();

        // Data Snapshot Cabang Khusus HQ
        $branchSnapshots = [];
        $allCabangs = Cabang::where('status', 'aktif')->with(['users' => function ($q) {
            $q->role('Superadmin');
        }])->get();

        if ($isHQ) {
            foreach ($allCabangs as $c) {
                $cOmzet = Penjualan::where('cabang_id', $c->id)->where('is_real', true)->where('status', 'lunas')->whereDate('tanggal', Carbon::today())->sum('total');
                $cServisAktif = ServisMasuk::where('cabang_id', $c->id)->whereIn('status', ['pengecekan', 'pengerjaan'])->count();
                $cSuperadmin = $c->users->first()?->name ?? 'Belum ada Admin';
                $cTotalStaff = User::where('cabang_id', $c->id)->where('status', 'aktif')->count();

                $branchSnapshots[] = [
                    'id' => $c->id,
                    'kode' => $c->kode_cabang,
                    'nama' => $c->nama_cabang,
                    'alamat' => $c->alamat,
                    'no_wa' => $c->no_wa,
                    'superadmin' => $cSuperadmin,
                    'omzet_hari_ini' => $cOmzet,
                    'servis_aktif' => $cServisAktif,
                    'total_staff' => $cTotalStaff,
                ];
            }
        }

        // Data Chart 7 Hari Terakhir
        $chartCategories = [];
        $chartSeries = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartCategories[] = $date->translatedFormat('d M');
        }

        if ($isHQ) {
            foreach ($allCabangs as $c) {
                $seriesData = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $dailySum = Penjualan::where('cabang_id', $c->id)
                        ->where('is_real', true)
                        ->where('status', 'lunas')
                        ->whereDate('tanggal', $date)
                        ->sum('total');
                    $seriesData[] = (int) $dailySum;
                }
                $chartSeries[] = [
                    'name' => $c->nama_cabang,
                    'data' => $seriesData,
                ];
            }
        } else {
            $seriesData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $dailySum = Penjualan::where('cabang_id', $cabangId)
                    ->where('is_real', true)
                    ->where('status', 'lunas')
                    ->whereDate('tanggal', $date)
                    ->sum('total');
                $seriesData[] = (int) $dailySum;
            }
            $chartSeries[] = [
                'name' => $activeCabang?->nama_cabang ?? 'Penjualan',
                'data' => $seriesData,
            ];
        }

        return view('livewire.dashboard.index', [
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
            'activeCabang' => $activeCabang,
            'totalCabangAktif' => $totalCabangAktif,
            'totalUserAktif' => $totalUserAktif,
            'countTotalAktif' => $countTotalAktif,
            'countPengecekan' => $countPengecekan,
            'countPengerjaan' => $countPengerjaan,
            'countSelesai' => $countSelesai,
            'antrianLaptop' => $antrianLaptop,
            'antrianPrinter' => $antrianPrinter,
            'antrianKomputer' => $antrianKomputer,
            'omzetHariIni' => $omzetHariIni,
            'omzetBulanIni' => $omzetBulanIni,
            'totalTransaksiHariIni' => $totalTransaksiHariIni,
            'totalTransaksiBulanIni' => $totalTransaksiBulanIni,
            'recentServis' => $recentServis,
            'recentPenjualan' => $recentPenjualan,
            'lowStocks' => $lowStocks,
            'branchSnapshots' => $branchSnapshots,
            'chartCategories' => $chartCategories,
            'chartSeries' => $chartSeries,
        ]);
    }
}
