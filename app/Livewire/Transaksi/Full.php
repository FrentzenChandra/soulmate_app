<?php

namespace App\Livewire\Transaksi;

use App\Models\Cabang;
use App\Models\KasKeluar;
use App\Models\MetodePembayaran;
use App\Models\Penjualan;
use App\Models\ServisMasuk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Transaksi FULL - Soulmate Komputer')]
class Full extends Component
{
    public string $startDate = '';

    public string $endDate = '';

    public string $periodeMode = 'range'; // 'range' | 'bulanan' | 'tahunan'

    public string $filterBulan = '';

    public string $filterTahun = '';

    public string $tipeFilter = 'semua'; // 'semua' | 'penjualan' | 'servis'

    public string $metodeFilter = 'semua';

    public string $statusFilter = 'semua'; // 'semua' | 'lunas' | 'bon'

    public string $search = '';

    public array $tableData = [];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! ($user->isOwner() || $user->isSuperadmin() || $user->isAdmin())) {
            abort(403, 'Akses Transaksi FULL dibatasi.');
        }

        $this->filterBulan = now()->format('m');
        $this->filterTahun = now()->format('Y');
        $this->startDate = now()->toDateString();
        $this->endDate = now()->toDateString();
        $this->tableData = $this->getTransactionsData();
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

    public function getTransactionsData(): array
    {
        $cabang = $this->activeCabang;
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $rows = [];

        // 1. Ambil Penjualan Produk (Real)
        if ($this->tipeFilter === 'semua' || $this->tipeFilter === 'penjualan') {
            $penjualanQuery = Penjualan::with(['details.produk', 'kasir', 'ppn', 'customer'])
                ->where('is_real', true)
                ->whereBetween('tanggal', [$start, $end]);

            if ($cabang) {
                $penjualanQuery->where('cabang_id', $cabang->id);
            }

            $penjualans = $penjualanQuery->orderByDesc('tanggal')->get();

            foreach ($penjualans as $pj) {
                $itemNames = [];
                $totalModal = 0;

                foreach ($pj->details as $d) {
                    $itemNames[] = "{$d->qty}x {$d->nama_item}";
                    $hargaBeli = $d->produk ? (float) $d->produk->harga_beli : 0;
                    $totalModal += ($hargaBeli * $d->qty);
                }

                $hargaJual = (float) $pj->total;
                $margin = $hargaJual - $totalModal;
                $isCash = in_array(strtolower(trim($pj->metode_bayar ?: '')), ['cash', 'tunai']);

                $statusBayar = ($pj->status === 'bon' || $pj->status === 'belum_lunas') ? 'Bon' : 'Lunas';

                $pajakNominal = (float) $pj->ppn_nominal;
                $pajakPersen = (float) ($pj->ppn_persen ?? ($pj->ppn?->persentase ?? 0));
                $pajakTipe = $pj->ppn ? ($pj->ppn->nama . ($pajakPersen > 0 ? " ({$pajakPersen}%)" : '')) : ($pajakPersen > 0 ? "PPN {$pajakPersen}%" : ($pajakNominal > 0 ? 'PPN' : 'Non-PPN'));

                $rows[] = [
                    'id' => 'PJ-' . $pj->id,
                    'raw_id' => $pj->id,
                    'tipe' => 'penjualan',
                    'tipe_label' => 'Penjualan',
                    'tanggal' => $pj->tanggal ? $pj->tanggal->format('Y-m-d H:i') : '-',
                    'tanggal_timestamp' => $pj->tanggal ? $pj->tanggal->timestamp : 0,
                    'no_transaksi' => $pj->no_transaksi_frontend,
                    'customer' => $pj->customer?->nama ?? 'Umum',
                    'item_detail' => implode(', ', $itemNames) ?: 'Produk Kasir',
                    'items_list' => ! empty($itemNames) ? $itemNames : ['Produk Kasir'],
                    'modal' => $totalModal,
                    'harga_jual' => $hargaJual,
                    'margin' => $margin,
                    'status' => $statusBayar,
                    'pajak' => $pajakNominal,
                    'pajak_tipe' => $pajakTipe,
                    'pajak_persen' => $pajakPersen,
                    'metode_bayar' => $pj->metode_bayar ?: 'Cash',
                    'is_cash' => $isCash,
                    'petugas' => $pj->kasir?->name ?? 'Kasir',
                ];
            }
        }

        // 2. Servis Masuk / Dibuat pada Periode Ini
        if ($this->tipeFilter === 'semua' || $this->tipeFilter === 'servis') {
            $servisQuery = ServisMasuk::with(['details.masterServis', 'details.produk', 'teknisi', 'kasir', 'customer'])
                ->whereBetween('tanggal_masuk', [$start, $end]);

            if ($cabang) {
                $servisQuery->where('cabang_id', $cabang->id);
            }

            $servisList = $servisQuery->orderByDesc('tanggal_masuk')->get();

            foreach ($servisList as $sv) {
                $itemNames = [];
                $totalModal = 0;

                foreach ($sv->details as $d) {
                    $itemNames[] = "{$d->qty}x {$d->nama_item}";
                    $hargaModal = 0;
                    if ($d->masterServis) {
                        $hargaModal = (float) $d->masterServis->harga_modal;
                    } elseif ($d->produk) {
                        $hargaModal = (float) $d->produk->harga_beli;
                    }
                    $totalModal += ($hargaModal * $d->qty);
                }

                $tgl = $sv->tanggal_masuk ?? $sv->created_at;
                $hargaJual = (float) ($sv->total_biaya > 0 ? $sv->total_biaya : ($sv->estimasi_biaya ?: 0));
                $margin = $hargaJual - $totalModal;
                $isCash = in_array(strtolower(trim($sv->metode_pembayaran ?: '')), ['cash', 'tunai']);

                $isLunas = in_array($sv->status, ['sudah_diambil', 'sudah_dibayar']) || ($sv->total_biaya > 0 && (float) $sv->bayar >= (float) $sv->total_biaya);
                if ($sv->status === 'batal') {
                    $statusBayar = 'Batal';
                } elseif ($isLunas) {
                    $statusBayar = 'Lunas';
                } else {
                    $statusBayar = 'Belum Lunas';
                }

                $servisUnitInfo = $sv->kategori_perangkat_label . ($sv->tipe_unit ? " ({$sv->tipe_unit})" : '');
                $detailText = $servisUnitInfo;
                if (! empty($itemNames)) {
                    $detailText .= ' - ' . implode(', ', $itemNames);
                }

                $servisItemList = [];
                if (! empty($servisUnitInfo)) {
                    $servisItemList[] = $servisUnitInfo;
                }
                foreach ($itemNames as $in) {
                    $servisItemList[] = $in;
                }

                $rows[] = [
                    'id' => 'SV-' . $sv->id,
                    'raw_id' => $sv->id,
                    'tipe' => 'servis',
                    'tipe_label' => 'Servis',
                    'tanggal' => $tgl ? Carbon::parse($tgl)->format('Y-m-d H:i') : '-',
                    'tanggal_timestamp' => $tgl ? Carbon::parse($tgl)->timestamp : 0,
                    'no_transaksi' => $sv->no_servis_frontend,
                    'customer' => $sv->customer?->nama ?? 'Umum',
                    'item_detail' => $detailText,
                    'items_list' => ! empty($servisItemList) ? $servisItemList : [$detailText],
                    'modal' => $totalModal,
                    'harga_jual' => $hargaJual,
                    'margin' => $margin,
                    'status' => $statusBayar,
                    'pajak' => 0,
                    'pajak_tipe' => 'Bebas Pajak',
                    'pajak_persen' => 0,
                    'metode_bayar' => ucfirst($sv->metode_pembayaran ?: 'Cash'),
                    'is_cash' => $isCash,
                    'petugas' => $sv->teknisi?->name ?? ($sv->kasir?->name ?? 'Teknisi'),
                ];
            }
        }

        // Sorting default desc by timestamp
        usort($rows, fn ($a, $b) => $b['tanggal_timestamp'] <=> $a['tanggal_timestamp']);

        return $rows;
    }

    public function render()
    {
        $cabang = $this->activeCabang;
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // Kas Keluar Cash
        $kasKeluarQuery = KasKeluar::with('sumberDana')
            ->whereBetween('tanggal', [$start, $end]);

        if ($cabang) {
            $kasKeluarQuery->where('cabang_id', $cabang->id);
        }

        $allKasKeluar = $kasKeluarQuery->get();
        $totalKasKeluarCash = (float) $allKasKeluar->filter(function ($k) {
            $sdNama = strtolower($k->sumberDana?->nama ?? '');

            return str_contains($sdNama, 'cash') || str_contains($sdNama, 'tunai') || str_contains($sdNama, 'kasir');
        })->sum('nominal');

        $totalKasKeluarAll = (float) $allKasKeluar->sum('nominal');

        // Master Metode Pembayaran for filters
        $metodeList = MetodePembayaran::aktif()->orderBy('urutan')->get();

        $this->tableData = $this->getTransactionsData();
        $allRows = $this->tableData;

        $totalPenjualanCash = 0;
        $totalServisCash = 0;
        $totalPenjualanLunasAll = 0;
        $totalServisLunasAll = 0;
        $totalPenjualanBon = 0;
        $totalServisBelumLunas = 0;
        $totalMarginLunas = 0;
        $totalMarginBon = 0;
        $totalOmzetAll = 0;
        $totalModalAll = 0;
        $totalMarginAll = 0;

        $marginPenjualanLunas = 0;
        $marginPenjualanTotal = 0;
        $marginServisLunas = 0;
        $marginServisTotal = 0;

        foreach ($allRows as $r) {
            if ($r['status'] !== 'Batal') {
                $totalOmzetAll += $r['harga_jual'];
                $totalModalAll += $r['modal'];
                $totalMarginAll += $r['margin'];

                if ($r['status'] === 'Lunas') {
                    $totalMarginLunas += $r['margin'];
                } else {
                    $totalMarginBon += $r['margin'];
                }

                if ($r['tipe'] === 'penjualan') {
                    $marginPenjualanTotal += $r['margin'];
                    if ($r['status'] === 'Lunas') {
                        $marginPenjualanLunas += $r['margin'];
                    }
                } else {
                    $marginServisTotal += $r['margin'];
                    if ($r['status'] === 'Lunas') {
                        $marginServisLunas += $r['margin'];
                    }
                }
            }

            if ($r['tipe'] === 'penjualan') {
                if ($r['status'] === 'Lunas') {
                    $totalPenjualanLunasAll += $r['harga_jual'];
                    if ($r['is_cash']) {
                        $totalPenjualanCash += $r['harga_jual'];
                    }
                } elseif ($r['status'] === 'Bon' || $r['status'] === 'Belum Lunas') {
                    $totalPenjualanBon += $r['harga_jual'];
                }
            } else {
                if ($r['status'] === 'Lunas') {
                    $totalServisLunasAll += $r['harga_jual'];
                    if ($r['is_cash']) {
                        $totalServisCash += $r['harga_jual'];
                    }
                } elseif ($r['status'] === 'Belum Lunas' || $r['status'] === 'Bon') {
                    $totalServisBelumLunas += $r['harga_jual'];
                }
            }
        }

        $totalBelumLunasAll = $totalPenjualanBon + $totalServisBelumLunas;
        $totalPemasukanLunasAll = $totalPenjualanLunasAll + $totalServisLunasAll;

        // 2 Tipe Laba Bersih (Nett Profit): Margin (Harga Jual - Modal) - Kas Keluar
        $labaBersihReal = $totalMarginLunas - $totalKasKeluarAll;
        $labaBersihPlusBon = $totalMarginAll - $totalKasKeluarAll;

        // Arus Kas Masuk Bersih
        $saldoBersihReal = $totalPemasukanLunasAll - $totalKasKeluarAll;
        $saldoBersihPlusBon = $totalOmzetAll - $totalKasKeluarAll;

        $saldoBersihSemuaMetode = $saldoBersihReal;
        $saldoKasTunaiBersih = ($totalPenjualanCash + $totalServisCash) - $totalKasKeluarCash;

        return view('livewire.transaksi.full', [
            'tableData' => $allRows,
            'totalPenjualanCash' => $totalPenjualanCash,
            'totalServisCash' => $totalServisCash,
            'totalPenjualanLunasAll' => $totalPenjualanLunasAll,
            'totalServisLunasAll' => $totalServisLunasAll,
            'totalPenjualanBon' => $totalPenjualanBon,
            'totalServisBelumLunas' => $totalServisBelumLunas,
            'totalBelumLunasAll' => $totalBelumLunasAll,
            'totalPemasukanLunasAll' => $totalPemasukanLunasAll,
            'totalKasKeluarCash' => $totalKasKeluarCash,
            'totalKasKeluarAll' => $totalKasKeluarAll,
            'labaBersihReal' => $labaBersihReal,
            'labaBersihPlusBon' => $labaBersihPlusBon,
            'saldoBersihReal' => $saldoBersihReal,
            'saldoBersihPlusBon' => $saldoBersihPlusBon,
            'saldoBersihSemuaMetode' => $saldoBersihSemuaMetode,
            'saldoKasTunaiBersih' => $saldoKasTunaiBersih,
            'totalOmzetAll' => $totalOmzetAll,
            'totalModalAll' => $totalModalAll,
            'totalMarginAll' => $totalMarginAll,
            'totalMarginLunas' => $totalMarginLunas,
            'totalMarginBon' => $totalMarginBon,
            'marginPenjualanLunas' => $marginPenjualanLunas,
            'marginPenjualanTotal' => $marginPenjualanTotal,
            'marginServisLunas' => $marginServisLunas,
            'marginServisTotal' => $marginServisTotal,
            'metodeList' => $metodeList,
        ]);
    }
}
