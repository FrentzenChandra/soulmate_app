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
    public string $startDate = '';

    public string $endDate = '';

    public string $periodeMode = 'range'; // 'range' | 'bulanan' | 'tahunan'

    public string $filterBulan = '';

    public string $filterTahun = '';

    public string $tipeFilter = 'semua'; // 'semua' | 'penjualan' | 'servis'

    public string $metodeFilter = 'semua';

    public string $statusFilter = 'semua'; // 'semua' | 'lunas' | 'bon'

    public string $search = '';

    // State Modal Cek Kas Cepat / Audit Laci
    public bool $modalCekKasOpen = false;

    public bool $modalTutupKasOpen = false; // alias

    public $kasFisik = 0;

    public string $catatanAudit = '';

    public string $catatanClosing = ''; // alias

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! ($user->isOwner() || $user->isAdmin() || $user->isTeknisi() || $user->hasRole('Kasir'))) {
            abort(403, 'Akses Transaksi Harian dibatasi.');
        }

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

    public function getTodayModalAwalProperty(): ?ModalAwal
    {
        $cabang = $this->activeCabang;
        if (! $cabang) {
            return null;
        }

        return ModalAwal::where('cabang_id', $cabang->id)
            ->whereDate('tanggal', $this->startDate)
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
            ->whereDate('tanggal', $this->startDate)
            ->latest()
            ->first();
    }

    public function getTransactionsData(): array
    {
        $cabang = $this->activeCabang;
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $rows = [];

        // 1. Penjualan Produk
        if ($this->tipeFilter === 'semua' || $this->tipeFilter === 'penjualan') {
            $penjualanQuery = Penjualan::with(['details.produk', 'kasir', 'ppn', 'customer'])
                ->where('is_real', true)
                ->whereBetween('tanggal', [$start, $end]);

            if ($cabang) {
                $penjualanQuery->where('cabang_id', $cabang->id);
            }

            if ($this->metodeFilter !== 'semua') {
                $penjualanQuery->where('metode_bayar', $this->metodeFilter);
            }

            if ($this->statusFilter === 'lunas') {
                $penjualanQuery->where('status', 'lunas');
            } elseif ($this->statusFilter === 'bon') {
                $penjualanQuery->whereIn('status', ['bon', 'belum_lunas']);
            }

            $penjualans = $penjualanQuery->orderByDesc('tanggal')->get();

            foreach ($penjualans as $pj) {
                $itemNames = [];

                foreach ($pj->details as $d) {
                    $itemNames[] = "{$d->qty}x {$d->nama_item}";
                }

                $tglCarbon = $pj->tanggal ? Carbon::parse($pj->tanggal) : null;
                $hargaJual = (float) $pj->total;
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
                    'tanggal' => $tglCarbon ? $tglCarbon->format('Y-m-d H:i') : '-',
                    'tanggal_group' => $this->formatIndonesianDate($tglCarbon),
                    'tanggal_timestamp' => $tglCarbon ? $tglCarbon->timestamp : 0,
                    'no_transaksi' => $pj->no_transaksi_frontend,
                    'customer' => $pj->customer?->nama ?? 'Umum',
                    'item_detail' => implode(', ', $itemNames) ?: 'Produk Kasir',
                    'items_list' => ! empty($itemNames) ? $itemNames : ['Produk Kasir'],
                    'harga_jual' => $hargaJual,
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

        // 2. Servis Masuk
        if ($this->tipeFilter === 'semua' || $this->tipeFilter === 'servis') {
            $servisQuery = ServisMasuk::with(['details.masterServis', 'details.produk', 'teknisi', 'kasir', 'customer'])
                ->whereBetween('tanggal_masuk', [$start, $end]);

            if ($cabang) {
                $servisQuery->where('cabang_id', $cabang->id);
            }

            if ($this->metodeFilter !== 'semua') {
                $servisQuery->where('metode_pembayaran', $this->metodeFilter);
            }

            if ($this->statusFilter === 'lunas') {
                $servisQuery->where(function ($q) {
                    $q->whereIn('status', ['sudah_diambil', 'sudah_dibayar'])
                        ->orWhere(function ($sq) {
                            $sq->where('total_biaya', '>', 0)->whereColumn('bayar', '>=', 'total_biaya');
                        });
                });
            } elseif ($this->statusFilter === 'bon') {
                $servisQuery->whereNotIn('status', ['sudah_diambil', 'sudah_dibayar', 'batal'])
                    ->where(function ($sq) {
                        $sq->where('total_biaya', 0)->orWhereColumn('bayar', '<', 'total_biaya');
                    });
            }

            $servisList = $servisQuery->orderByDesc('tanggal_masuk')->get();

            foreach ($servisList as $sv) {
                $itemNames = [];

                foreach ($sv->details as $d) {
                    $itemNames[] = "{$d->qty}x {$d->nama_item}";
                }

                $tgl = $sv->tanggal_masuk ?? $sv->created_at;
                $tglCarbon = $tgl ? Carbon::parse($tgl) : null;
                $hargaJual = (float) ($sv->total_biaya > 0 ? $sv->total_biaya : ($sv->estimasi_biaya ?: 0));
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
                    'tanggal' => $tglCarbon ? $tglCarbon->format('Y-m-d H:i') : '-',
                    'tanggal_group' => $this->formatIndonesianDate($tglCarbon),
                    'tanggal_timestamp' => $tglCarbon ? $tglCarbon->timestamp : 0,
                    'no_transaksi' => $sv->no_servis_frontend,
                    'customer' => $sv->customer?->nama ?? 'Umum',
                    'item_detail' => $detailText,
                    'items_list' => ! empty($servisItemList) ? $servisItemList : [$detailText],
                    'harga_jual' => $hargaJual,
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
    }

    public function closeCekKasModal(): void
    {
        $this->modalCekKasOpen = false;
        $this->modalTutupKasOpen = false;
    }

    public function prosesCekKas(): void
    {
        $this->validate([
            'kasFisik' => 'required|numeric|min:0',
            'catatanAudit' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $cabang = $this->activeCabang;

        if (! $cabang) {
            $this->addError('kasFisik', 'Cabang tidak terdeteksi.');

            return;
        }

        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // 1. Kas Keluar Cash
        $totalKasKeluarCash = (float) KasKeluar::where('cabang_id', $cabang->id)
            ->whereBetween('tanggal', [$start, $end])
            ->whereHas('sumberDana', function ($sq) {
                $sq->whereRaw('LOWER(nama) LIKE ?', ['%cash%'])
                    ->orWhereRaw('LOWER(nama) LIKE ?', ['%tunai%'])
                    ->orWhereRaw('LOWER(nama) LIKE ?', ['%kasir%']);
            })
            ->sum('nominal');

        // 2. Penjualan Cash (Lunas)
        $totalPenjualanCash = (float) Penjualan::where('cabang_id', $cabang->id)
            ->where('is_real', true)
            ->where('status', 'lunas')
            ->whereBetween('tanggal', [$start, $end])
            ->where(function ($q) {
                $q->whereRaw('LOWER(metode_bayar) LIKE ?', ['%cash%'])
                    ->orWhereRaw('LOWER(metode_bayar) LIKE ?', ['%tunai%']);
            })
            ->sum('total');

        // 3. Servis Cash (Lunas)
        $totalServisCash = (float) ServisMasuk::where('cabang_id', $cabang->id)
            ->whereBetween('tanggal_masuk', [$start, $end])
            ->where(function ($q) {
                $q->whereIn('status', ['sudah_diambil', 'sudah_dibayar'])
                    ->orWhere(function ($sq) {
                        $sq->where('total_biaya', '>', 0)->whereColumn('bayar', '>=', 'total_biaya');
                    });
            })
            ->where(function ($q) {
                $q->whereRaw('LOWER(metode_pembayaran) LIKE ?', ['%cash%'])
                    ->orWhereRaw('LOWER(metode_pembayaran) LIKE ?', ['%tunai%']);
            })
            ->sum('total_biaya');

        $modalAwalNominal = (float) ($this->todayModalAwal?->nominal ?? 0);
        $kasSistem = $modalAwalNominal + $totalPenjualanCash + $totalServisCash - $totalKasKeluarCash;

        $kasFisik = (float) $this->kasFisik;
        $selisih = $kasFisik - $kasSistem;

        $statusSelisih = 'klop';
        if ($selisih < 0) {
            $statusSelisih = 'minus';
        } elseif ($selisih > 0) {
            $statusSelisih = 'lebih';
        }

        $catatan = trim(($this->catatanAudit ?: '') . ' ' . ($this->catatanClosing ?: ''));

        CekKasLog::create([
            'cabang_id' => $cabang->id,
            'user_id' => $user->id,
            'tanggal' => $this->startDate,
            'waktu_cek' => now(),
            'modal_awal' => $modalAwalNominal,
            'penjualan_cash' => $totalPenjualanCash,
            'servis_cash' => $totalServisCash,
            'kas_keluar_cash' => $totalKasKeluarCash,
            'kas_sistem' => $kasSistem,
            'kas_fisik' => $kasFisik,
            'selisih' => $selisih,
            'status_selisih' => $statusSelisih,
            'catatan' => $catatan ?: null,
        ]);

        $this->closeCekKasModal();
        $this->dispatch('swal:success', [
            'title' => 'Audit Kas Tercatat!',
            'text' => 'Catatan audit kas berhasil disimpan. Status: ' . strtoupper($statusSelisih),
        ]);
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

        $allRows = $this->getTransactionsData();
        $this->dispatch('harian-table-updated', data: $allRows);

        $totalPenjualanCash = 0;
        $totalServisCash = 0;
        $totalPenjualanLunasAll = 0;
        $totalServisLunasAll = 0;
        $totalPenjualanBon = 0;
        $totalServisBelumLunas = 0;
        $totalPenjualanBruto = 0;
        $totalServisBruto = 0;
        $totalOmzetAll = 0;

        foreach ($allRows as $r) {
            if ($r['status'] !== 'Batal') {
                $totalOmzetAll += $r['harga_jual'];
                if ($r['tipe'] === 'penjualan') {
                    $totalPenjualanBruto += $r['harga_jual'];
                } else {
                    $totalServisBruto += $r['harga_jual'];
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

        // Arus Kas Masuk Bersih (Kas Lunas - Kas Keluar)
        $saldoKasBersih = $totalPemasukanLunasAll - $totalKasKeluarAll;

        $modalAwalNominal = (float) ($this->todayModalAwal?->nominal ?? 0);
        $kasSistem = $modalAwalNominal + $totalPenjualanCash + $totalServisCash - $totalKasKeluarCash;

        $cekKasLogs = CekKasLog::with('user')
            ->where('cabang_id', $cabang?->id)
            ->whereBetween('tanggal', [$this->startDate, $this->endDate])
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
            'totalPenjualanBruto' => $totalPenjualanBruto,
            'totalServisBruto' => $totalServisBruto,
            'totalKasKeluarCash' => $totalKasKeluarCash,
            'totalKasKeluarAll' => $totalKasKeluarAll,
            'saldoKasBersih' => $saldoKasBersih,
            'kasSistem' => $kasSistem,
            'totalOmzetAll' => $totalOmzetAll,
            'totalPemasukanLunasAll' => $totalPemasukanLunasAll,
            'cekKasLogs' => $cekKasLogs,
            'metodeList' => MetodePembayaran::getAktifCached(),
        ]);
    }

    private function formatIndonesianDate(?Carbon $date): string
    {
        if (! $date) {
            return '-';
        }

        $days = [
            'Sun' => 'Minggu',
            'Mon' => 'Senin',
            'Tue' => 'Selasa',
            'Wed' => 'Rabu',
            'Thu' => 'Kamis',
            'Fri' => 'Jumat',
            'Sat' => 'Sabtu',
        ];

        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        $dayName = $days[$date->format('D')] ?? $date->format('l');
        $dayNum = $date->format('d');
        $monthName = $months[(int) $date->format('n')] ?? $date->format('M');
        $year = $date->format('Y');

        return "{$dayName}, {$dayNum} {$monthName} {$year}";
    }
}
