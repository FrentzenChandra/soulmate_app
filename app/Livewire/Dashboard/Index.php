<?php

namespace App\Livewire\Dashboard;

use App\Models\Cabang;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\ServisMasuk;
use App\Models\Stok;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard Overview')]
class Index extends Component
{
    public ?int $selectedCabangId = null;

    public function mount()
    {
        $user = Auth::user();
        if (!$user->isOwner()) {
            $this->selectedCabangId = $user->cabang_id;
        }
    }

    public function filterCabang(?int $cabangId)
    {
        if (Auth::user()->isOwner()) {
            $this->selectedCabangId = $cabangId;
        }
    }

    public function render()
    {
        $user = Auth::user();
        $cabangId = $user->isOwner() ? $this->selectedCabangId : $user->cabang_id;

        // Query Servis Aktif
        $servisQuery = ServisMasuk::query()
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId));

        $countPengecekan = (clone $servisQuery)->where('status', 'pengecekan')->count();
        $countPengerjaan = (clone $servisQuery)->where('status', 'pengerjaan')->count();
        $countSelesai = (clone $servisQuery)->where('status', 'selesai')->count();
        $countTotalAktif = $countPengecekan + $countPengerjaan + $countSelesai;

        // Breakdown Perangkat untuk TV Display Monitor Antrian
        $antrianLaptop = [
            'cek' => (clone $servisQuery)->where('kategori_perangkat', 'laptop')->where('status', 'pengecekan')->count(),
            'prg' => (clone $servisQuery)->where('kategori_perangkat', 'laptop')->where('status', 'pengerjaan')->count(),
            'ok'  => (clone $servisQuery)->where('kategori_perangkat', 'laptop')->where('status', 'selesai')->count(),
        ];
        $antrianPrinter = [
            'cek' => (clone $servisQuery)->where('kategori_perangkat', 'printer')->where('status', 'pengecekan')->count(),
            'prg' => (clone $servisQuery)->where('kategori_perangkat', 'printer')->where('status', 'pengerjaan')->count(),
            'ok'  => (clone $servisQuery)->where('kategori_perangkat', 'printer')->where('status', 'selesai')->count(),
        ];
        $antrianKomputer = [
            'cek' => (clone $servisQuery)->where('kategori_perangkat', 'komputer')->where('status', 'pengecekan')->count(),
            'prg' => (clone $servisQuery)->where('kategori_perangkat', 'komputer')->where('status', 'pengerjaan')->count(),
            'ok'  => (clone $servisQuery)->where('kategori_perangkat', 'komputer')->where('status', 'selesai')->count(),
        ];

        // Query Penjualan & Omzet
        $penjualanQuery = Penjualan::query()
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->where('status', 'lunas');

        $omzetHariIni = (clone $penjualanQuery)
            ->whereDate('tanggal', Carbon::today())
            ->sum('total');

        $omzetBulanIni = (clone $penjualanQuery)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('total');

        $totalTransaksiBulanIni = (clone $penjualanQuery)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();

        // Servis Masuk Terbaru
        $recentServis = ServisMasuk::with(['customer', 'teknisi', 'cabang'])
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->latest('tanggal_masuk')
            ->take(5)
            ->get();

        // Penjualan Terbaru
        $recentPenjualan = Penjualan::with(['customer', 'kasir', 'cabang'])
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->latest('tanggal')
            ->take(5)
            ->get();

        // Stok Menipis (< 5)
        $lowStocks = Stok::with(['produk', 'cabang'])
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->where('jumlah', '<=', 5)
            ->orderBy('jumlah', 'asc')
            ->take(5)
            ->get();

        $cabangs = Cabang::where('status', 'aktif')->get();

        return view('livewire.dashboard.index', [
            'cabangs' => $cabangs,
            'selectedCabang' => $cabangId ? Cabang::find($cabangId) : null,
            'countTotalAktif' => $countTotalAktif,
            'countPengecekan' => $countPengecekan,
            'countPengerjaan' => $countPengerjaan,
            'countSelesai' => $countSelesai,
            'antrianLaptop' => $antrianLaptop,
            'antrianPrinter' => $antrianPrinter,
            'antrianKomputer' => $antrianKomputer,
            'omzetHariIni' => $omzetHariIni,
            'omzetBulanIni' => $omzetBulanIni,
            'totalTransaksiBulanIni' => $totalTransaksiBulanIni,
            'recentServis' => $recentServis,
            'recentPenjualan' => $recentPenjualan,
            'lowStocks' => $lowStocks,
        ]);
    }
}
