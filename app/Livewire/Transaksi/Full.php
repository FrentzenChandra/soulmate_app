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

        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
        $this->tableData = $this->getTransactionsData();
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
                    'modal' => $totalModal,
                    'harga_jual' => $hargaJual,
                    'margin' => $margin,
                    'status' => $statusBayar,
                    'pajak' => (float) $pj->ppn_nominal,
                    'metode_bayar' => $pj->metode_bayar ?: 'Cash',
                    'is_cash' => $isCash,
                    'petugas' => $pj->kasir?->name ?? 'Kasir',
                ];
            }
        }

        // 2. Ambil Servis Keluar (Sudah Diambil / Dilunasi)
        if ($this->tipeFilter === 'semua' || $this->tipeFilter === 'servis') {
            $servisQuery = ServisMasuk::with(['details.masterServis', 'details.produk', 'teknisi', 'kasir', 'customer'])
                ->whereIn('status', ['sudah_diambil', 'sudah_dibayar', 'selesai'])
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('tanggal_ambil', [$start, $end])
                      ->orWhere(function ($sub) use ($start, $end) {
                          $sub->whereNull('tanggal_ambil')
                              ->whereBetween('tanggal_selesai', [$start, $end]);
                      });
                });

            if ($cabang) {
                $servisQuery->where('cabang_id', $cabang->id);
            }

            $servisList = $servisQuery->orderByDesc('tanggal_ambil')->get();

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

                $tgl = $sv->tanggal_ambil ?? $sv->tanggal_selesai ?? $sv->updated_at;
                $hargaJual = (float) $sv->total_biaya;
                $margin = $hargaJual - $totalModal;
                $isCash = in_array(strtolower(trim($sv->metode_pembayaran ?: '')), ['cash', 'tunai']);

                $detailText = $sv->kategori_perangkat_label . ($sv->tipe_unit ? " ({$sv->tipe_unit})" : '');
                if (! empty($itemNames)) {
                    $detailText .= ' - ' . implode(', ', $itemNames);
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
                    'modal' => $totalModal,
                    'harga_jual' => $hargaJual,
                    'margin' => $margin,
                    'status' => 'Lunas',
                    'pajak' => 0,
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

        // Hitung Summary Metrics
        $totalPenjualanCash = 0;
        $totalServisCash = 0;
        $totalOmzetAll = 0;
        $totalModalAll = 0;
        $totalMarginAll = 0;

        foreach ($allRows as $r) {
            $totalOmzetAll += $r['harga_jual'];
            $totalModalAll += $r['modal'];
            $totalMarginAll += $r['margin'];

            if ($r['is_cash']) {
                if ($r['tipe'] === 'penjualan') {
                    $totalPenjualanCash += $r['harga_jual'];
                } else {
                    $totalServisCash += $r['harga_jual'];
                }
            }
        }

        $saldoKasTunaiBersih = ($totalPenjualanCash + $totalServisCash) - $totalKasKeluarCash;

        return view('livewire.transaksi.full', [
            'tableData' => $allRows,
            'totalPenjualanCash' => $totalPenjualanCash,
            'totalServisCash' => $totalServisCash,
            'totalKasKeluarCash' => $totalKasKeluarCash,
            'totalKasKeluarAll' => $totalKasKeluarAll,
            'saldoKasTunaiBersih' => $saldoKasTunaiBersih,
            'totalOmzetAll' => $totalOmzetAll,
            'totalModalAll' => $totalModalAll,
            'totalMarginAll' => $totalMarginAll,
            'metodeList' => $metodeList,
        ]);
    }
}
