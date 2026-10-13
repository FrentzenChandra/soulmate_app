<?php

namespace App\Livewire\Laporan\Penjualan;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PenjualanRetur;
use App\Models\Stok;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    #[Url(except: 'transaksi')]
    public string $activeTab = 'transaksi';

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

    // Modal Retur Stok Barang (Khusus Transaksi Real)
    public bool $modalReturOpen = false;

    public ?int $returPenjualanId = null;

    public string $returNoTransaksi = '';

    public string $returCustomerName = '';

    public float $returTotal = 0;

    public array $returItems = [];

    public string $alasanRetur = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->can('laporan.penjualan.view'))) {
            abort(403, 'Akses laporan penjualan dibatasi.');
        }

        if (request()->has('tipeTransaksiFilter') && in_array(request('tipeTransaksiFilter'), ['real', 'non_real', 'semua'])) {
            $this->tipeTransaksiFilter = (string) request('tipeTransaksiFilter');
        }

        if ($this->tipeTransaksiFilter === 'non_real') {
            $this->activeTab = 'transaksi';
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

    public function switchTab(string $tab): void
    {
        if ($this->tipeTransaksiFilter === 'non_real' && $tab === 'histori_retur') {
            $this->activeTab = 'transaksi';

            return;
        }

        if (in_array($tab, ['transaksi', 'histori_retur'])) {
            $this->activeTab = $tab;
            $this->perPage = 15;
            $this->resetPage();
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

    public function updatingTipeTransaksiFilter($value): void
    {
        if ($value === 'non_real') {
            $this->activeTab = 'transaksi';
        }
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

        $penjualan = Penjualan::with(['customer', 'kasir', 'cabang', 'ppn', 'details.produk', 'returs.petugas', 'returs.produk'])->findOrFail($id);

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

    public function openReturModal(int $id): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('laporan.penjualan.retur')), 403, 'Akses fitur retur stok dibatasi.');

        $penjualan = Penjualan::with(['customer', 'details.produk', 'cabang'])->find($id);
        if (! $penjualan) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'text' => 'Data transaksi tidak ditemukan.',
            ]);

            return;
        }

        // Guard Backend: Hanya Transaksi Real yang boleh diretur stoknya
        if (! $penjualan->is_real) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Retur Stok Ditolak',
                'text' => 'Transaksi Non-Real tidak memotong stok fisik sehingga tidak dapat diretur.',
            ]);

            return;
        }

        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            if ($targetCabangId && (int) $penjualan->cabang_id !== (int) $targetCabangId) {
                $this->dispatch('swal:modal', [
                    'type' => 'error',
                    'title' => 'Akses Dibatasi',
                    'text' => 'Anda tidak memiliki akses untuk meretur transaksi cabang lain.',
                ]);

                return;
            }
        }

        $items = [];
        $totalSisaUnit = 0;
        foreach ($penjualan->details as $d) {
            $qtyBeli = (int) $d->qty;
            $qtySudah = (int) ($d->qty_retur ?? 0);
            $sisa = max(0, $qtyBeli - $qtySudah);
            $totalSisaUnit += $sisa;

            $items[$d->id] = [
                'detail_id' => $d->id,
                'produk_id' => $d->produk_id,
                'nama_item' => $d->produk?->nama_produk ?? $d->nama_item,
                'barcode' => $d->produk?->barcode ?? '',
                'qty_beli' => $qtyBeli,
                'qty_sudah_retur' => $qtySudah,
                'sisa_qty' => $sisa,
            ];
        }

        if ($totalSisaUnit <= 0) {
            $this->dispatch('swal:modal', [
                'type' => 'info',
                'title' => 'Sudah Diretur Penuh',
                'text' => "Seluruh barang pada transaksi {$penjualan->no_transaksi_frontend} sudah pernah diretur sebelumnya.",
            ]);

            return;
        }

        $this->modalDetailOpen = false;
        $this->returPenjualanId = $penjualan->id;
        $this->returNoTransaksi = $penjualan->no_transaksi_frontend;
        $this->returCustomerName = $penjualan->customer?->nama ?? 'Pelanggan Umum';
        $this->returTotal = (float) $penjualan->total;
        $this->returItems = $items;
        $this->alasanRetur = '';
        $this->resetValidation();
        $this->modalReturOpen = true;
    }

    public function closeReturModal(): void
    {
        $this->modalReturOpen = false;
        $this->returPenjualanId = null;
        $this->returNoTransaksi = '';
        $this->returCustomerName = '';
        $this->returTotal = 0;
        $this->returItems = [];
        $this->alasanRetur = '';
        $this->resetValidation();
    }

    public function prosesReturStok(): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('laporan.penjualan.retur')), 403, 'Akses fitur retur stok dibatasi.');

        if (! $this->returPenjualanId) {
            return;
        }

        $this->validate([
            'alasanRetur' => ['required', 'string', 'max:255'],
        ], [
            'alasanRetur.required' => 'Alasan retur transaksi wajib diisi.',
            'alasanRetur.max' => 'Alasan retur maksimal 255 karakter.',
        ]);

        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        try {
            $totalReturned = 0;
            $noTransaksiFrontend = '';

            DB::transaction(function () use ($user, $isHQ, $isOwner, $activeBranchSession, &$totalReturned, &$noTransaksiFrontend) {
                $penjualan = Penjualan::where('id', $this->returPenjualanId)->lockForUpdate()->first();

                if (! $penjualan) {
                    throw new \RuntimeException('Data transaksi tidak ditemukan.');
                }

                // Pengecekan Backend Ketat: Wajib Transaksi Real (is_real = true)
                if (! $penjualan->is_real) {
                    throw new \RuntimeException('Transaksi Non-Real tidak memotong stok fisik sehingga tidak dapat diretur.');
                }

                if (! $isHQ) {
                    $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
                    if ($targetCabangId && (int) $penjualan->cabang_id !== (int) $targetCabangId) {
                        throw new \RuntimeException('Akses retur transaksi cabang lain dibatasi.');
                    }
                }

                $noTransaksiFrontend = $penjualan->no_transaksi_frontend;

                $details = PenjualanDetail::where('penjualan_id', $penjualan->id)
                    ->lockForUpdate()
                    ->get();

                foreach ($details as $detail) {
                    $sisaServer = max(0, (int) $detail->qty - (int) $detail->qty_retur);
                    if ($sisaServer <= 0) {
                        continue;
                    }

                    $stokSebelum = 0;
                    $stokSesudah = 0;

                    if ($detail->produk_id) {
                        $stok = Stok::where('produk_id', $detail->produk_id)
                            ->where('cabang_id', $penjualan->cabang_id)
                            ->lockForUpdate()
                            ->first();

                        if (! $stok) {
                            $stok = Stok::create([
                                'produk_id' => $detail->produk_id,
                                'cabang_id' => $penjualan->cabang_id,
                                'jumlah' => 0,
                                'min_stok' => 3,
                                'is_aktif' => true,
                            ]);
                        }

                        $stokSebelum = (int) $stok->jumlah;
                        $stok->increment('jumlah', $sisaServer);
                        $stokSesudah = $stokSebelum + $sisaServer;
                    }

                    $detail->increment('qty_retur', $sisaServer);

                    PenjualanRetur::create([
                        'penjualan_id' => $penjualan->id,
                        'penjualan_detail_id' => $detail->id,
                        'cabang_id' => $penjualan->cabang_id,
                        'produk_id' => $detail->produk_id,
                        'user_id' => $user->id,
                        'qty_retur' => $sisaServer,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'alasan' => trim($this->alasanRetur),
                    ]);

                    $totalReturned += $sisaServer;
                }

                if ($totalReturned <= 0) {
                    throw new \RuntimeException('Tidak ada unit barang yang dapat diretur pada transaksi ini.');
                }

                // Ubah status penjualan menjadi batal setelah seluruh item diretur
                $penjualan->update(['status' => 'batal']);
            });

            $this->dispatch('stockUpdated');
            $this->closeReturModal();

            $msg = "Retur transaksi berhasil! Seluruh barang ({$totalReturned} unit) nota {$noTransaksiFrontend} telah dikembalikan ke stok dan status transaksi diubah menjadi Batal.";
            $this->dispatch('swal:toast', [
                'type' => 'success',
                'title' => 'Transaksi Diretur',
                'text' => $msg,
            ]);
            session()->flash('success', $msg);

        } catch (\Throwable $e) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Gagal Memproses Retur',
                'text' => $e->getMessage(),
            ]);
        }
    }

    public function deletePenjualan(int $id): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole('Superadmin'))) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'text' => 'Hanya Owner dan Superadmin yang memiliki hak akses untuk menghapus riwayat transaksi.',
            ]);

            return;
        }

        $penjualan = Penjualan::with(['details'])->find($id);
        if (! $penjualan) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'text' => 'Data transaksi tidak ditemukan.',
            ]);

            return;
        }

        // Isolasi Cabang untuk Superadmin (non-owner)
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            if ($targetCabangId && (int) $penjualan->cabang_id !== (int) $targetCabangId) {
                $this->dispatch('swal:modal', [
                    'type' => 'error',
                    'title' => 'Akses Dibatasi',
                    'text' => 'Anda hanya berhak menghapus transaksi di cabang Anda sendiri.',
                ]);

                return;
            }
        }

        $noTransaksi = $penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi;

        DB::transaction(function () use ($penjualan) {
            // Hapus rincian item & nota penjualan (stok fisik tidak diubah)
            $penjualan->details()->delete();
            $penjualan->delete();
        });

        if ($this->modalDetailOpen && $this->selectedPenjualanId === $id) {
            $this->closeDetailModal();
        }

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Transaksi Dihapus',
            'text' => "Riwayat transaksi {$noTransaksi} berhasil dihapus.",
        ]);
        session()->flash('success', "Transaksi {$noTransaksi} berhasil dihapus.");
    }

    protected function applyPeriodFilter($query, string $column = 'tanggal'): void
    {
        if ($this->periodeFilter === 'hari_ini') {
            $query->whereDate($column, Carbon::today());
        } elseif ($this->periodeFilter === 'kemarin') {
            $query->whereDate($column, Carbon::yesterday());
        } elseif ($this->periodeFilter === '7_hari') {
            $query->whereBetween($column, [
                Carbon::today()->subDays(6)->startOfDay(),
                Carbon::today()->endOfDay(),
            ]);
        } elseif ($this->periodeFilter === 'bulan_ini') {
            $query->whereBetween($column, [
                Carbon::now()->startOfMonth()->startOfDay(),
                Carbon::now()->endOfMonth()->endOfDay(),
            ]);
        } elseif ($this->periodeFilter === 'bulan_lalu') {
            $query->whereBetween($column, [
                Carbon::now()->subMonth()->startOfMonth()->startOfDay(),
                Carbon::now()->subMonth()->endOfMonth()->endOfDay(),
            ]);
        } elseif ($this->periodeFilter === 'custom') {
            $start = ! empty($this->startDate) ? Carbon::parse($this->startDate)->startOfDay() : Carbon::now()->startOfMonth();
            $end = ! empty($this->endDate) ? Carbon::parse($this->endDate)->endOfDay() : Carbon::now()->endOfDay();
            $query->whereBetween($column, [$start, $end]);
        }
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
        $query = Penjualan::with(['customer', 'kasir', 'cabang', 'ppn', 'details.produk'])
            ->withSum('returs', 'qty_retur');

        // 1. Filter Cabang
        if (! $isHQ && $targetCabangId) {
            $query->where('cabang_id', $targetCabangId);
        } elseif ($isHQ && $this->cabangFilter !== 'semua') {
            $query->where('cabang_id', $this->cabangFilter);
        }

        // 2. Filter Periode Tanggal
        $this->applyPeriodFilter($query, 'tanggal');

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
        $batalTransactions = $allFiltered->where('is_real', true)->filter(fn ($pj) => strtolower($pj->status ?? '') === 'batal');
        $nonRealTransactions = $allFiltered->where('is_real', false);

        // Metrik Transaksi Real
        $totalOmzetReal = (float) $realTransactions->sum('total');
        $totalTransaksiReal = (int) $realTransactions->count();
        $totalQtyReal = (int) $realTransactions->sum(fn ($pj) => $pj->details->sum('qty'));
        $totalDiskonReal = (float) $realTransactions->sum('diskon');
        $totalPpnReal = (float) $realTransactions->sum('ppn_nominal');

        // Metrik Transaksi Batal (Real)
        $totalOmzetBatal = (float) $batalTransactions->sum('total');
        $totalTransaksiBatal = (int) $batalTransactions->count();

        // Metrik Transaksi Non-Real
        $totalNilaiNonReal = (float) $nonRealTransactions->sum('total');
        $totalNotaNonReal = (int) $nonRealTransactions->count();
        $totalQtyNonReal = (int) $nonRealTransactions->sum(fn ($pj) => $pj->details->sum('qty'));
        $totalDiskonNonReal = (float) $nonRealTransactions->sum('diskon');
        $totalPpnNonReal = (float) $nonRealTransactions->sum('ppn_nominal');

        // Query Histori Retur Stok
        $returQuery = PenjualanRetur::with(['penjualan.customer', 'produk', 'petugas', 'cabang']);
        if (! $isHQ && $targetCabangId) {
            $returQuery->where('cabang_id', $targetCabangId);
        } elseif ($isHQ && $this->cabangFilter !== 'semua') {
            $returQuery->where('cabang_id', $this->cabangFilter);
        }
        $this->applyPeriodFilter($returQuery, 'created_at');

        if (! empty($this->search)) {
            $cleanSearch = trim($this->search);
            $words = array_filter(explode(' ', $cleanSearch));
            $returQuery->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%'.strtolower($word).'%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(alasan) LIKE ?', [$pattern])
                            ->orWhereHas('penjualan', function ($pq) use ($pattern) {
                                $pq->whereRaw('LOWER(no_transaksi) LIKE ?', [$pattern]);
                            })
                            ->orWhereHas('produk', function ($prq) use ($pattern) {
                                $prq->whereRaw('LOWER(nama_produk) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(COALESCE(barcode, \'\')) LIKE ?', [$pattern]);
                            })
                            ->orWhereHas('petugas', function ($uq) use ($pattern) {
                                $uq->whereRaw('LOWER(name) LIKE ?', [$pattern]);
                            });
                    });
                }
            });
        }

        $totalReturLogs = (clone $returQuery)->count();
        $totalUnitRetur = (int) (clone $returQuery)->sum('qty_retur');

        // Sorting & Fetching Berdasarkan Tab Aktif
        $sortDir = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['tanggal', 'no_transaksi', 'total', 'subtotal', 'diskon', 'created_at', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'tanggal';
        $query->orderBy($sortColumn, $sortDir);

        $totalItems = (clone $query)->count();
        $penjualans = $this->activeTab === 'transaksi' ? $query->take($this->perPage)->get() : collect();
        $returLogs = $this->activeTab === 'histori_retur'
            ? $returQuery->orderByDesc('created_at')->orderByDesc('id')->take($this->perPage)->get()
            : collect();

        $hasMore = $this->activeTab === 'transaksi'
            ? ($totalItems > $this->perPage)
            : ($totalReturLogs > $this->perPage);

        return view('livewire.laporan.penjualan.index', [
            'penjualans' => $penjualans,
            'returLogs' => $returLogs,
            'totalReturLogs' => $totalReturLogs,
            'totalUnitRetur' => $totalUnitRetur,
            'totalItems' => $totalItems,
            'hasMore' => $hasMore,
            'totalOmzetReal' => $totalOmzetReal,
            'totalTransaksiReal' => $totalTransaksiReal,
            'totalQtyReal' => $totalQtyReal,
            'totalDiskonReal' => $totalDiskonReal,
            'totalPpnReal' => $totalPpnReal,
            'totalOmzetBatal' => $totalOmzetBatal,
            'totalTransaksiBatal' => $totalTransaksiBatal,
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