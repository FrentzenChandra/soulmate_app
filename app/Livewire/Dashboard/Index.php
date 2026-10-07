<?php

namespace App\Livewire\Dashboard;

use App\Models\Cabang;
use App\Models\Penjualan;
use App\Models\SaldoRekening;
use App\Models\ServisMasuk;
use App\Models\Stok;
use App\Models\SumberSaldo;
use App\Models\TransaksiFull;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard Overview - Soulmate Komputer')]
class Index extends Component
{
    public string $filterBulanServis = '';

    public string $filterTahunServis = '';

    public function mount(): void
    {
        $this->filterBulanServis = now()->format('m');
        $this->filterTahunServis = now()->format('Y');
    }

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

    public function refreshDashboard(): void
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);
        $cabangId = $isHQ ? null : ($isOwner ? $activeBranchSession : $user->cabang_id);

        Cache::forget('dashboard_branch_' . ($cabangId ?? 'hq') . '_m' . $this->filterBulanServis . '_y' . $this->filterTahunServis);
        Cache::forget('dashboard_stats_' . ($cabangId ?? 'hq') . '_' . ($isHQ ? 'hq' : 'branch'));

        $this->dispatch('swal:toast', type: 'success', message: 'Data dashboard berhasil disinkronkan.');
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $isSuperadmin = $user->isSuperadmin();
        $isManagement = $isOwner || $isSuperadmin;

        // Salam waktu berdasarkan jam lokal
        $hour = (int) Carbon::now()->format('H');
        $salamWaktu = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));

        if (! $isManagement) {
            return view('livewire.dashboard.index', [
                'isManagement' => false,
                'isHQ' => false,
                'isOwner' => false,
                'isSuperadmin' => false,
                'salamWaktu' => $salamWaktu,
                'activeCabang' => $user->cabang,
            ]);
        }

        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $cabangId = null;
        if (! $isHQ) {
            $cabangId = $isOwner ? $activeBranchSession : $user->cabang_id;
        }

        $activeCabang = $cabangId ? Cabang::find($cabangId) : null;

        if ($isHQ) {
            // =========================================================================
            // HQ MULTI-BRANCH AGGREGATE MONITORING
            // =========================================================================
            $dashboardKey = 'dashboard_stats_hq';

            $hqData = Cache::remember($dashboardKey, 30, function () {
                $totalCabangAktif = Cabang::where('status', 'aktif')->count();
                $totalUserAktif = User::where('status', 'aktif')->count();

                $countTotalAktif = ServisMasuk::whereIn('status', ['pengecekan', 'pengerjaan'])->count();
                $countPengecekan = ServisMasuk::where('status', 'pengecekan')->count();
                $countPengerjaan = ServisMasuk::where('status', 'pengerjaan')->count();

                $penjualanQuery = Penjualan::query()->where('is_real', true)->where('status', 'lunas');
                $omzetHariIni = (clone $penjualanQuery)->whereDate('tanggal', Carbon::today())->sum('total');
                $totalTransaksiHariIni = (clone $penjualanQuery)->whereDate('tanggal', Carbon::today())->count();

                $branchSnapshots = [];
                $allCabangs = Cabang::where('status', 'aktif')->with(['users' => function ($q) {
                    $q->role('Superadmin');
                }])->get();

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

                $chartCategories = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $chartCategories[] = $date->translatedFormat('d M');
                }

                $chartSeries = [];
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

                return [
                    'totalCabangAktif' => $totalCabangAktif,
                    'totalUserAktif' => $totalUserAktif,
                    'countTotalAktif' => $countTotalAktif,
                    'countPengecekan' => $countPengecekan,
                    'countPengerjaan' => $countPengerjaan,
                    'omzetHariIni' => $omzetHariIni,
                    'totalTransaksiHariIni' => $totalTransaksiHariIni,
                    'branchSnapshots' => $branchSnapshots,
                    'chartCategories' => $chartCategories,
                    'chartSeries' => $chartSeries,
                ];
            });

            return view('livewire.dashboard.index', array_merge($hqData, [
                'isManagement' => true,
                'isHQ' => true,
                'isOwner' => true,
                'salamWaktu' => $salamWaktu,
                'activeCabang' => null,
            ]));
        }

        // =========================================================================
        // BRANCH DASHBOARD (SUPERADMIN & OWNER IN BRANCH MODE) - 4 ROW ULTRA FAST
        // =========================================================================
        $cacheKey = 'dashboard_branch_' . $cabangId . '_m' . $this->filterBulanServis . '_y' . $this->filterTahunServis;

        $branchData = Cache::remember($cacheKey, 30, function () use ($cabangId) {
            $startBulan = now()->startOfMonth()->startOfDay();
            $nowDate = now()->endOfDay();

            // 1. BARIS 1: Finansial Bulan Berjalan (Tgl 1 s/d Hari Ini) - Single-Pass Query
            $finansial = DB::table('transaksi_fulls')
                ->where('cabang_id', $cabangId)
                ->whereBetween('tanggal', [$startBulan, $nowDate])
                ->where('status', '!=', 'Batal')
                ->selectRaw("
                    COALESCE(SUM(total_jual), 0) as total_pendapatan,
                    COALESCE(SUM(total_modal), 0) as total_hpp,
                    COALESCE(SUM(total_laba), 0) as total_nett,
                    COALESCE(SUM(CASE WHEN status = 'Lunas' THEN total_jual ELSE 0 END), 0) as total_lunas,
                    COALESCE(SUM(CASE WHEN LOWER(status) = 'bon' THEN total_jual ELSE 0 END), 0) as total_bon
                ")
                ->first();

            // 2. BARIS 2: Operasional Servis - Filter Bulan & Tahun - Single-Pass Query
            $bulanServis = (int) ($this->filterBulanServis ?: now()->month);
            $tahunServis = (int) ($this->filterTahunServis ?: now()->year);

            $servisStats = DB::table('servis_masuks')
                ->where('cabang_id', $cabangId)
                ->whereYear('tanggal_masuk', $tahunServis)
                ->whereMonth('tanggal_masuk', $bulanServis)
                ->selectRaw("
                    COUNT(*) as total_masuk,
                    COUNT(CASE WHEN status = 'selesai' AND tanggal_ambil IS NULL THEN 1 END) as selesai_belum_ambil,
                    COUNT(CASE WHEN status = 'batal' AND tanggal_ambil IS NULL THEN 1 END) as batal_belum_ambil
                ")
                ->first();

            // 3. BARIS 3: Saldo Rekening Grouped per Sumber Saldo + Total Keseluruhan
            $sumberSaldos = SumberSaldo::select('id', 'nama')
                ->with(['rekenings' => function ($q) {
                    $q->select('id', 'sumber_saldo_id', 'nama_bank', 'atas_nama', 'nomor_rekening', 'saldo')
                        ->orderBy('id', 'asc');
                }])
                ->get();

            $totalSaldoKeseluruhan = (float) SaldoRekening::sum('saldo');

            // 4. BARIS 4: Pendapatan Real Net Harian (Bulan Berjalan: 1 s/d Hari Ini) - Single-Pass Group By
            $dailyNettRows = DB::table('transaksi_fulls')
                ->where('cabang_id', $cabangId)
                ->whereBetween('tanggal', [$startBulan, $nowDate])
                ->where('status', '!=', 'Batal')
                ->selectRaw("DATE(tanggal) as tgl, COALESCE(SUM(total_laba), 0) as nett")
                ->groupBy('tgl')
                ->pluck('nett', 'tgl')
                ->toArray();

            $chartCategories = [];
            $chartSeriesData = [];
            $todayDay = (int) now()->day;

            for ($d = 1; $d <= $todayDay; $d++) {
                $dateObj = Carbon::create(now()->year, now()->month, $d);
                $dateKey = $dateObj->toDateString();
                $chartCategories[] = $dateObj->translatedFormat('j M');
                $chartSeriesData[] = (int) ($dailyNettRows[$dateKey] ?? 0);
            }

            return [
                'finansial' => $finansial,
                'servisStats' => $servisStats,
                'sumberSaldos' => $sumberSaldos,
                'totalSaldoKeseluruhan' => $totalSaldoKeseluruhan,
                'chartCategories' => $chartCategories,
                'chartSeriesData' => $chartSeriesData,
            ];
        });

        return view('livewire.dashboard.index', array_merge($branchData, [
            'isManagement' => true,
            'isHQ' => false,
            'isOwner' => $isOwner,
            'isSuperadmin' => $isSuperadmin,
            'salamWaktu' => $salamWaktu,
            'activeCabang' => $activeCabang,
        ]));
    }
}
