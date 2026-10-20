<?php

namespace App\Livewire\Components;

use App\Models\Cabang;
use App\Models\KasKeluar;
use App\Models\TransaksiFull;
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
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function setPeriodeMode(string $mode): void
    {
        $this->periodeMode = $mode;
        if ($mode === 'range') {
            $this->startDate = now()->startOfMonth()->toDateString();
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

            return null; // HQ = Semua cabang
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
        $cacheKey = 'navbar_ringkasan_' . ($cabang?->id ?? 'hq') . '_' . $this->startDate . '_' . $this->endDate;

        $summary = \Illuminate\Support\Facades\Cache::remember($cacheKey, 30, function () use ($cabang, $start, $end) {
            // 1. Kas Keluar
            $kasKeluarQuery = KasKeluar::whereBetween('tanggal', [$start, $end]);
            if ($cabang) {
                $kasKeluarQuery->where('cabang_id', $cabang->id);
            }
            $totalKasKeluarAll = (float) $kasKeluarQuery->sum('nominal');

            // 2. Transaksi FULL Data
            $trxQuery = TransaksiFull::whereBetween('tanggal', [$start, $end]);
            if ($cabang) {
                $trxQuery->where('cabang_id', $cabang->id);
            }
            $allTrx = $trxQuery->get();

            $totalPenjualanBruto = 0;
            $totalServisBruto = 0;
            $modalPenjualanTotal = 0;
            $modalServisTotal = 0;
            $marginPenjualanTotal = 0;
            $marginServisTotal = 0;
            $totalPemasukanLunasAll = 0;
            $totalBelumLunasAll = 0;
            $totalMarginLunas = 0;

            foreach ($allTrx as $trx) {
                $statusLower = strtolower(trim($trx->status ?? ''));
                if ($statusLower === 'batal') {
                    continue;
                }

                $nominalJual = (float) ($trx->total_jual ?: $trx->grand_total);
                $modal = (float) $trx->total_modal;
                $laba = (float) $trx->total_laba;

                if ($trx->tipe_transaksi === 'servis') {
                    $totalServisBruto += $nominalJual;
                    $modalServisTotal += $modal;
                    $marginServisTotal += $laba;
                } else {
                    $totalPenjualanBruto += $nominalJual;
                    $modalPenjualanTotal += $modal;
                    $marginPenjualanTotal += $laba;
                }

                if ($statusLower === 'lunas') {
                    $totalPemasukanLunasAll += $nominalJual;
                    $totalMarginLunas += $laba;
                } elseif (in_array($statusLower, ['bon', 'belum lunas'])) {
                    $totalBelumLunasAll += $nominalJual;
                }
            }

            $totalOmzetAll = $totalPenjualanBruto + $totalServisBruto;
            $totalModalAll = $modalPenjualanTotal + $modalServisTotal;
            $totalMarginAll = $marginPenjualanTotal + $marginServisTotal;
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
