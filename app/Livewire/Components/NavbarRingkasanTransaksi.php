<?php

namespace App\Livewire\Components;

use App\Models\Cabang;
use App\Models\KasKeluar;
use App\Models\Penjualan;
use App\Models\ServisMasuk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NavbarRingkasanTransaksi extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public string $periodeMode = 'range'; // 'range' | 'bulanan' | 'tahunan'

    public string $filterBulan = '';

    public string $filterTahun = '';

    public function mount(): void
    {
        $this->filterBulan = now()->format('m');
        $this->filterTahun = now()->format('Y');
        $this->startDate = now()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function setPeriodeMode(string $mode): void
    {
        $this->periodeMode = $mode;
        if ($mode === 'range') {
            $this->startDate = now()->toDateString();
            $this->endDate = now()->toDateString();
        } elseif ($mode === 'bulanan') {
            $this->applyBulanan();
        } elseif ($mode === 'tahunan') {
            $this->applyTahunan();
        }
    }

    public function updatedFilterBulan(): void
    {
        if ($this->periodeMode === 'bulanan') {
            $this->applyBulanan();
        }
    }

    public function updatedFilterTahun(): void
    {
        if ($this->periodeMode === 'bulanan') {
            $this->applyBulanan();
        } elseif ($this->periodeMode === 'tahunan') {
            $this->applyTahunan();
        }
    }

    public function applyBulanan(): void
    {
        $year = (int) ($this->filterTahun ?: now()->year);
        $month = (int) ($this->filterBulan ?: now()->month);
        $date = Carbon::createFromDate($year, $month, 1);
        $this->startDate = $date->copy()->startOfMonth()->toDateString();
        $this->endDate = $date->copy()->endOfMonth()->toDateString();
    }

    public function applyTahunan(): void
    {
        $year = (int) ($this->filterTahun ?: now()->year);
        $date = Carbon::createFromDate($year, 1, 1);
        $this->startDate = $date->copy()->startOfYear()->toDateString();
        $this->endDate = $date->copy()->endOfYear()->toDateString();
    }

    public function getActiveCabangProperty(): ?Cabang
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        if ($user->isOwner()) {
            $sess = session('active_branch_id', 'hq');
            if ($sess !== 'hq') {
                return Cabang::find((int) $sess);
            }

            return Cabang::where('status', 'aktif')->first();
        }

        return $user->cabang;
    }

    public function render()
    {
        $user = Auth::user();
        if (! $user || (! $user->isSuperadmin() && ! $user->isOwner())) {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        $cabang = $this->activeCabang;
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();
        $cacheKey = 'navbar_ringkasan_' . ($cabang?->id ?? 'all') . '_' . $this->startDate . '_' . $this->endDate;

        $summary = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($cabang, $start, $end) {
            // 1. Kas Keluar
            $kasKeluarQuery = KasKeluar::whereBetween('tanggal', [$start, $end]);
            if ($cabang) {
                $kasKeluarQuery->where('cabang_id', $cabang->id);
            }
            $totalKasKeluarAll = (float) $kasKeluarQuery->sum('nominal');

            // 2. Penjualan
            $penjualanQuery = Penjualan::with('details.produk')
                ->where('is_real', true)
                ->whereBetween('tanggal', [$start, $end]);
            if ($cabang) {
                $penjualanQuery->where('cabang_id', $cabang->id);
            }
            $penjualans = $penjualanQuery->get();

            $totalPenjualanLunas = 0;
            $totalPenjualanBon = 0;
            $marginPenjualanTotal = 0;
            $modalPenjualanTotal = 0;
            $totalPenjualanBruto = 0;

            foreach ($penjualans as $pj) {
                $modal = 0;
                foreach ($pj->details as $d) {
                    $beli = $d->produk ? (float) $d->produk->harga_beli : 0;
                    $modal += ($beli * $d->qty);
                }
                $jual = (float) $pj->total;
                $margin = $jual - $modal;

                $totalPenjualanBruto += $jual;
                $modalPenjualanTotal += $modal;
                $marginPenjualanTotal += $margin;

                if ($pj->status === 'bon' || $pj->status === 'belum_lunas') {
                    $totalPenjualanBon += $jual;
                } else {
                    $totalPenjualanLunas += $jual;
                }
            }

            // 3. Servis
            $servisQuery = ServisMasuk::with(['details.masterServis', 'details.produk'])
                ->whereBetween('tanggal_masuk', [$start, $end]);
            if ($cabang) {
                $servisQuery->where('cabang_id', $cabang->id);
            }
            $servisList = $servisQuery->get();

            $totalServisLunas = 0;
            $totalServisBelumLunas = 0;
            $marginServisTotal = 0;
            $modalServisTotal = 0;
            $totalServisBruto = 0;
            $totalMarginLunas = 0;

            // Add lunas margin from Penjualan
            foreach ($penjualans as $pj) {
                if ($pj->status !== 'bon' && $pj->status !== 'belum_lunas') {
                    $m = 0;
                    foreach ($pj->details as $d) {
                        $m += (($d->produk ? (float) $d->produk->harga_beli : 0) * $d->qty);
                    }
                    $totalMarginLunas += ((float) $pj->total - $m);
                }
            }

            foreach ($servisList as $sv) {
                if ($sv->status === 'batal') {
                    continue;
                }

                $modal = 0;
                foreach ($sv->details as $d) {
                    $hargaModal = 0;
                    if ($d->masterServis) {
                        $hargaModal = (float) $d->masterServis->harga_modal;
                    } elseif ($d->produk) {
                        $hargaModal = (float) $d->produk->harga_beli;
                    }
                    $modal += ($hargaModal * $d->qty);
                }
                $jual = (float) ($sv->total_biaya > 0 ? $sv->total_biaya : ($sv->estimasi_biaya ?: 0));
                $margin = $jual - $modal;

                $totalServisBruto += $jual;
                $modalServisTotal += $modal;
                $marginServisTotal += $margin;

                $isLunas = in_array($sv->status, ['sudah_diambil', 'sudah_dibayar']) || ($sv->total_biaya > 0 && (float) $sv->bayar >= (float) $sv->total_biaya);
                if ($isLunas) {
                    $totalServisLunas += $jual;
                    $totalMarginLunas += $margin;
                } else {
                    $totalServisBelumLunas += $jual;
                }
            }

            $totalOmzetAll = $totalPenjualanBruto + $totalServisBruto;
            $totalModalAll = $modalPenjualanTotal + $modalServisTotal;
            $totalMarginAll = $marginPenjualanTotal + $marginServisTotal;
            $totalPemasukanLunasAll = $totalPenjualanLunas + $totalServisLunas;
            $totalBelumLunasAll = $totalPenjualanBon + $totalServisBelumLunas;
            $labaBersihReal = $totalMarginLunas - $totalKasKeluarAll;

            return [
                'totalOmzetAll' => $totalOmzetAll,
                'totalModalAll' => $totalModalAll,
                'totalMarginAll' => $totalMarginAll,
                'totalPemasukanLunasAll' => $totalPemasukanLunasAll,
                'totalBelumLunasAll' => $totalBelumLunasAll,
                'marginPenjualanTotal' => $marginPenjualanTotal,
                'marginServisTotal' => $marginServisTotal,
                'totalPenjualanBruto' => $totalPenjualanBruto,
                'totalServisBruto' => $totalServisBruto,
                'totalKasKeluarAll' => $totalKasKeluarAll,
                'labaBersihReal' => $labaBersihReal,
            ];
        });

        return view('livewire.components.navbar-ringkasan-transaksi', $summary);
    }
}
