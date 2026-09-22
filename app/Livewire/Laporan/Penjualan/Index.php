<?php

namespace App\Livewire\Laporan\Penjualan;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\Penjualan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Laporan & Riwayat Penjualan - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'bulan_ini')]
    public string $periodeFilter = 'bulan_ini';

    #[Url(except: '')]
    public string $startDate = '';

    #[Url(except: '')]
    public string $endDate = '';

    #[Url(except: 'semua')]
    public string $tipeTransaksiFilter = 'semua';

    #[Url(except: 'semua')]
    public string $kasirFilter = 'semua';

    #[Url(except: 'semua')]
    public string $cabangFilter = 'semua';

    #[Url(except: 'semua')]
    public string $statusFilter = 'semua';

    public int $perPage = 15;

    // Modal Detail Transaksi
    public bool $modalDetailOpen = false;

    public ?int $selectedPenjualanId = null;

    public ?Penjualan $selectedPenjualan = null;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole('Superadmin') && ! $user->hasRole('Admin') && ! $user->hasRole('Kasir'))) {
            abort(403, 'Akses dibatasi.');
        }

        if (request()->has('tipeTransaksiFilter') && in_array(request('tipeTransaksiFilter'), ['real', 'non_real', 'semua'])) {
            $this->tipeTransaksiFilter = (string) request('tipeTransaksiFilter');
        }

        $this->sortField = 'tanggal';
        $this->sortDirection = 'desc';

        if (empty($this->startDate)) {
            $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (empty($this->endDate)) {
            $this->endDate = Carbon::now()->format('Y-m-d');
        }
    }

    public function loadMore(): void
    {
        $this->perPage += 15;
    }

    public function updatingSearch(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingPeriodeFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingTipeTransaksiFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingKasirFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingCabangFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updateStatusDirect(int $id, string $newStatus): void
    {
        $user = Auth::user();
        $allowed = ['lunas', 'bon', 'batal'];
        if (! in_array($newStatus, $allowed)) {
            return;
        }

        $penjualan = Penjualan::find($id);
        if (! $penjualan) {
            return;
        }

        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            if ($targetCabangId && (int) $penjualan->cabang_id !== (int) $targetCabangId) {
                session()->flash('error', 'Akses transaksi cabang lain dibatasi.');

                return;
            }
        }

        $penjualan->status = $newStatus;
        if ($newStatus === 'lunas') {
            $penjualan->jumlah_bayar = $penjualan->total;
            $penjualan->kembalian = 0;
            if ($penjualan->metode_bayar === 'bon' || empty($penjualan->metode_bayar)) {
                $penjualan->metode_bayar = 'tunai';
            }
        }

        $penjualan->save();

        $labelStatus = match ($newStatus) {
            'lunas' => 'LUNAS',
            'bon' => 'BON',
            'batal' => 'BATAL',
            default => strtoupper($newStatus),
        };

        session()->flash('success', "Status transaksi {$penjualan->no_transaksi_frontend} berhasil diubah ke {$labelStatus}.");
    }

    public function openDetailModal(int $id): void
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $penjualan = Penjualan::with(['customer', 'kasir', 'cabang', 'ppn', 'details.produk'])->findOrFail($id);

        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : $user->cabang_id;
            if ($targetCabangId && (int) $penjualan->cabang_id !== (int) $targetCabangId) {
                session()->flash('error', 'Akses transaksi cabang lain dibatasi.');

                return;
            }
        }

        $this->selectedPenjualanId = $penjualan->id;
        $this->selectedPenjualan = $penjualan;
        $this->modalDetailOpen = true;
    }

    public function closeDetailModal(): void
    {
        $this->modalDetailOpen = false;
        $this->selectedPenjualanId = null;
        $this->selectedPenjualan = null;
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $targetCabangId = null;
        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            $targetCabangId = $targetCabangId ?: 1;
        }

        $cabangs = Cabang::where('status', 'aktif')->orderBy('nama_cabang', 'asc')->get();

        // Daftar Kasir untuk Filter
        $kasirQuery = User::where('status', 'aktif');
        if (! $isHQ && $targetCabangId) {
            $kasirQuery->where('cabang_id', $targetCabangId);
        }
        $kasirs = $kasirQuery->orderBy('name', 'asc')->get();

        // Base Query Penjualan
        $query = Penjualan::with(['customer', 'kasir', 'cabang', 'ppn', 'details.produk']);

        // 1. Filter Cabang
        if (! $isHQ && $targetCabangId) {
            $query->where('cabang_id', $targetCabangId);
        } elseif ($isHQ && $this->cabangFilter !== 'semua') {
            $query->where('cabang_id', $this->cabangFilter);
        }

        // 2. Filter Periode Tanggal
        if ($this->periodeFilter === 'hari_ini') {
            $query->whereDate('tanggal', Carbon::today());
        } elseif ($this->periodeFilter === 'kemarin') {
            $query->whereDate('tanggal', Carbon::yesterday());
        } elseif ($this->periodeFilter === '7_hari') {
            $query->whereBetween('tanggal', [
                Carbon::today()->subDays(6)->startOfDay(),
                Carbon::today()->endOfDay(),
            ]);
        } elseif ($this->periodeFilter === 'bulan_ini') {
            $query->whereBetween('tanggal', [
                Carbon::now()->startOfMonth()->startOfDay(),
                Carbon::now()->endOfMonth()->endOfDay(),
            ]);
        } elseif ($this->periodeFilter === 'bulan_lalu') {
            $query->whereBetween('tanggal', [
                Carbon::now()->subMonth()->startOfMonth()->startOfDay(),
                Carbon::now()->subMonth()->endOfMonth()->endOfDay(),
            ]);
        } elseif ($this->periodeFilter === 'custom') {
            $start = ! empty($this->startDate) ? Carbon::parse($this->startDate)->startOfDay() : Carbon::now()->startOfMonth();
            $end = ! empty($this->endDate) ? Carbon::parse($this->endDate)->endOfDay() : Carbon::now()->endOfDay();
            $query->whereBetween('tanggal', [$start, $end]);
        }

        // 3. Filter Jenis Transaksi (Real vs Formalitas)
        if ($this->tipeTransaksiFilter === 'real') {
            $query->where('is_real', true);
        } elseif ($this->tipeTransaksiFilter === 'non_real') {
            $query->where('is_real', false);
        }

        // 4. Filter Kasir
        if ($this->kasirFilter !== 'semua') {
            $query->where('kasir_id', $this->kasirFilter);
        }

        // 5. Filter Status Pembayaran
        if ($this->statusFilter === 'lunas') {
            $query->whereIn('status', ['lunas', 'selesai']);
        } elseif ($this->statusFilter === 'bon') {
            $query->whereIn('status', ['bon', 'belum_lunas']);
        } elseif ($this->statusFilter === 'batal') {
            $query->where('status', 'batal');
        }

        // 6. Pencarian Multi-Kata Longgar (Universal Non-Strict Search)
        if (! empty($this->search)) {
            $cleanSearch = trim($this->search);
            $words = array_filter(explode(' ', $cleanSearch));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%'.strtolower($word).'%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(no_transaksi) LIKE ?', [$pattern])
                            ->orWhereHas('customer', function ($cq) use ($pattern) {
                                $cq->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(COALESCE(no_hp, \'\')) LIKE ?', [$pattern]);
                            })
                            ->orWhereHas('kasir', function ($kq) use ($pattern) {
                                $kq->whereRaw('LOWER(name) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(COALESCE(username, \'\')) LIKE ?', [$pattern]);
                            })
                            ->orWhereHas('details.produk', function ($pq) use ($pattern) {
                                $pq->whereRaw('LOWER(nama_produk) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(COALESCE(barcode, \'\')) LIKE ?', [$pattern]);
                            });
                    });
                }
            });
        }

        // Hitung Ringkasan Metrik Finansial pada Filter Terpilih
        $metricsQuery = clone $query;
        $allFiltered = $metricsQuery->get();

        $realTransactions = $allFiltered->where('is_real', true)->filter(fn ($pj) => in_array(strtolower($pj->status ?? ''), ['lunas', 'selesai']));
        $nonRealTransactions = $allFiltered->where('is_real', false);

        // Metrik Transaksi Real
        $totalOmzetReal = (float) $realTransactions->sum('total');
        $totalTransaksiReal = (int) $realTransactions->count();
        $totalQtyReal = (int) $realTransactions->sum(fn ($pj) => $pj->details->sum('qty'));
        $totalDiskonReal = (float) $realTransactions->sum('diskon');
        $totalPpnReal = (float) $realTransactions->sum('ppn_nominal');

        // Metrik Transaksi Non-Real
        $totalNilaiNonReal = (float) $nonRealTransactions->sum('total');
        $totalNotaNonReal = (int) $nonRealTransactions->count();
        $totalQtyNonReal = (int) $nonRealTransactions->sum(fn ($pj) => $pj->details->sum('qty'));
        $totalDiskonNonReal = (float) $nonRealTransactions->sum('diskon');
        $totalPpnNonReal = (float) $nonRealTransactions->sum('ppn_nominal');

        // Sorting
        $sortDir = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['tanggal', 'no_transaksi', 'total', 'subtotal', 'diskon', 'created_at', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'tanggal';
        $query->orderBy($sortColumn, $sortDir);

        $totalItems = (clone $query)->count();
        $penjualans = $query->take($this->perPage)->get();
        $hasMore = $totalItems > $this->perPage;

        return view('livewire.laporan.penjualan.index', [
            'penjualans' => $penjualans,
            'totalItems' => $totalItems,
            'hasMore' => $hasMore,
            'totalOmzetReal' => $totalOmzetReal,
            'totalTransaksiReal' => $totalTransaksiReal,
            'totalQtyReal' => $totalQtyReal,
            'totalDiskonReal' => $totalDiskonReal,
            'totalPpnReal' => $totalPpnReal,
            'totalNilaiNonReal' => $totalNilaiNonReal,
            'totalNotaNonReal' => $totalNotaNonReal,
            'totalQtyNonReal' => $totalQtyNonReal,
            'totalDiskonNonReal' => $totalDiskonNonReal,
            'totalPpnNonReal' => $totalPpnNonReal,
            'totalDiskon' => $totalDiskonReal,
            'totalPpn' => $totalPpnReal,
            'cabangs' => $cabangs,
            'kasirs' => $kasirs,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ]);
    }
}