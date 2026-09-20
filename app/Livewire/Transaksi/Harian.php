<?php

namespace App\Livewire\Transaksi;

use App\Models\Cabang;
use App\Models\CekKasLog;
use App\Models\KasKeluar;
use App\Models\MetodePembayaran;
use App\Models\ModalAwal;
use App\Models\Penjualan;
use App\Models\ServisMasuk;
use App\Models\TutupKas;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Transaksi Harian & Cek Kas - Soulmate Komputer')]
class Harian extends Component
{
    public string $selectedDate = '';

    public string $tipeFilter = 'semua';

    public array $tableData = [];

    // State Modal Cek Kas Cepat / Audit Laci
    public bool $modalCekKasOpen = false;

    public bool $modalTutupKasOpen = false; // alias

    public $kasFisik = 0;

    public string $catatanAudit = '';

    public string $catatanClosing = ''; // alias

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! ($user->isOwner() || $user->isSuperadmin() || $user->isAdmin() || $user->isTeknisi() || $user->hasRole('Kasir'))) {
            abort(403, 'Akses Transaksi Harian dibatasi.');
        }

        $this->selectedDate = now()->toDateString();
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

    public function getTodayModalAwalProperty(): ?ModalAwal
    {
        $cabang = $this->activeCabang;
        if (! $cabang) {
            return null;
        }

        return ModalAwal::where('cabang_id', $cabang->id)
            ->whereDate('tanggal', $this->selectedDate)
            ->first();
    }

    public function getTodayTutupKasProperty(): ?TutupKas
    {
        $cabang = $this->activeCabang;
        if (! $cabang) {
            return null;
        }

        return TutupKas::with('user')
            ->where('cabang_id', $cabang->id)
            ->whereDate('tanggal', $this->selectedDate)
            ->latest()
            ->first();
    }

    public function getTransactionsData(): array
    {
        $cabang = $this->activeCabang;
        $start = Carbon::parse($this->selectedDate)->startOfDay();
        $end = Carbon::parse($this->selectedDate)->endOfDay();

        $rows = [];

        // 1. Penjualan Produk Hari Ini
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
                    'tanggal' => $pj->tanggal ? $pj->tanggal->format('H:i') : '-',
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

        // 2. Servis Masuk / Dibuat Hari Ini
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
                    'tanggal' => $tgl ? Carbon::parse($tgl)->format('H:i') : '-',
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

        usort($rows, fn ($a, $b) => $b['tanggal_timestamp'] <=> $a['tanggal_timestamp']);

        return $rows;
    }

    public function openCekKasModal(): void
    {
        $this->resetValidation();
        $this->kasFisik = 0;
        $this->catatanAudit = '';
        $this->catatanClosing = '';
        $this->modalCekKasOpen = true;
        $this->modalTutupKasOpen = true;
    }

    public function openTutupKasModal(): void
    {
        $this->openCekKasModal();
    }

    public function closeCekKasModal(): void
    {
        $this->modalCekKasOpen = false;
        $this->modalTutupKasOpen = false;
        $this->resetValidation();
    }

    public function closeTutupKasModal(): void
    {
        $this->closeCekKasModal();
    }

    public function prosesCekKas(): void
    {
        $cabang = $this->activeCabang;
        if (! $cabang) {
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => 'Cabang tidak valid.']);

            return;
        }

        if (is_string($this->kasFisik)) {
            $this->kasFisik = (float) preg_replace('/[^\d]/', '', $this->kasFisik);
        }

        $this->validate([
            'kasFisik' => 'required|numeric|min:0',
            'catatanAudit' => 'nullable|string|max:500',
        ]);

        $modalAwalNominal = (float) ($this->todayModalAwal?->nominal ?? 0);

        // Hitung Data Finansial Hari Ini
        $start = Carbon::parse($this->selectedDate)->startOfDay();
        $end = Carbon::parse($this->selectedDate)->endOfDay();

        // Penjualan Cash Lunas
        $penjualanCash = (float) Penjualan::where('cabang_id', $cabang->id)
            ->where('is_real', true)
            ->where('status', '!=', 'bon')
            ->where('status', '!=', 'batal')
            ->whereBetween('tanggal', [$start, $end])
            ->whereIn('metode_bayar', ['cash', 'Cash', 'Tunai', 'tunai'])
            ->sum('total');

        // Servis Cash Lunas Hari Ini
        $servisCash = (float) ServisMasuk::where('cabang_id', $cabang->id)
            ->whereBetween('tanggal_masuk', [$start, $end])
            ->where(function ($q) {
                $q->whereIn('status', ['sudah_diambil', 'sudah_dibayar'])
                  ->orWhereRaw('total_biaya > 0 AND bayar >= total_biaya');
            })
            ->whereIn('metode_pembayaran', ['cash', 'Cash', 'Tunai', 'tunai'])
            ->sum('total_biaya');

        // Kas Keluar Cash
        $kasKeluarCash = (float) KasKeluar::where('cabang_id', $cabang->id)
            ->whereBetween('tanggal', [$start, $end])
            ->whereHas('sumberDana', function ($q) {
                $q->whereRaw('LOWER(nama) LIKE ?', ['%cash%'])
                  ->orWhereRaw('LOWER(nama) LIKE ?', ['%tunai%'])
                  ->orWhereRaw('LOWER(nama) LIKE ?', ['%kasir%']);
            })
            ->sum('nominal');

        $kasSistem = $modalAwalNominal + $penjualanCash + $servisCash - $kasKeluarCash;
        $kasFisik = (float) $this->kasFisik;
        $selisih = $kasFisik - $kasSistem;

        $statusSelisih = 'klop';
        if ($selisih < 0) {
            $statusSelisih = 'minus';
        } elseif ($selisih > 0) {
            $statusSelisih = 'plus';
        }

        $note = trim($this->catatanAudit) ?: (trim($this->catatanClosing) ?: null);

        CekKasLog::create([
            'cabang_id' => $cabang->id,
            'user_id' => Auth::id(),
            'tanggal' => $this->selectedDate,
            'waktu_cek' => now(),
            'kas_sistem' => $kasSistem,
            'kas_fisik' => $kasFisik,
            'selisih' => $selisih,
            'status_selisih' => $statusSelisih,
            'catatan' => $note,
        ]);

        $this->closeCekKasModal();
        $this->dispatch('swal:success', [
            'title' => 'Cek Kas Berhasil!',
            'text' => 'Snapshot audit kasir hari ini telah berhasil dicatat tanpa mengunci transaksi.',
        ]);
    }

    public function prosesTutupKas(): void
    {
        $this->prosesCekKas();
    }

    public function render()
    {
        $cabang = $this->activeCabang;
        $start = Carbon::parse($this->selectedDate)->startOfDay();
        $end = Carbon::parse($this->selectedDate)->endOfDay();

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
        $totalKasKeluarAll = (float) $allKasKeluar->sum('nominal');

        // 2 Tipe Laba Bersih (Nett Profit): Margin (Harga Jual - Modal) - Kas Keluar
        $labaBersihReal = $totalMarginLunas - $totalKasKeluarAll;
        $labaBersihPlusBon = $totalMarginAll - $totalKasKeluarAll;

        // Arus Kas Masuk Bersih
        $saldoBersihReal = $totalPemasukanLunasAll - $totalKasKeluarAll;
        $saldoBersihPlusBon = $totalOmzetAll - $totalKasKeluarAll;

        $modalAwalNominal = (float) ($this->todayModalAwal?->nominal ?? 0);
        $kasSistem = $modalAwalNominal + $totalPenjualanCash + $totalServisCash - $totalKasKeluarCash;

        $cekKasLogs = CekKasLog::with('user')
            ->where('cabang_id', $cabang?->id)
            ->whereDate('tanggal', $this->selectedDate)
            ->orderByDesc('waktu_cek')
            ->get();

        return view('livewire.transaksi.harian', [
            'tableData' => $allRows,
            'modalAwalNominal' => $modalAwalNominal,
            'totalPenjualanCash' => $totalPenjualanCash,
            'totalServisCash' => $totalServisCash,
            'totalPenjualanLunasAll' => $totalPenjualanLunasAll,
            'totalServisLunasAll' => $totalServisLunasAll,
            'totalPenjualanBon' => $totalPenjualanBon,
            'totalServisBelumLunas' => $totalServisBelumLunas,
            'totalBelumLunasAll' => $totalBelumLunasAll,
            'totalKasKeluarCash' => $totalKasKeluarCash,
            'totalKasKeluarAll' => $totalKasKeluarAll,
            'saldoBersihReal' => $saldoBersihReal,
            'saldoBersihPlusBon' => $saldoBersihPlusBon,
            'labaBersihReal' => $labaBersihReal,
            'labaBersihPlusBon' => $labaBersihPlusBon,
            'kasSistem' => $kasSistem,
            'totalOmzetAll' => $totalOmzetAll,
            'totalModalAll' => $totalModalAll,
            'totalMarginAll' => $totalMarginAll,
            'totalMarginLunas' => $totalMarginLunas,
            'totalMarginBon' => $totalMarginBon,
            'cekKasLogs' => $cekKasLogs,
        ]);
    }
}
