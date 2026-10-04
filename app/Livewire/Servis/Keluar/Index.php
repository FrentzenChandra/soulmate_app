<?php

namespace App\Livewire\Servis\Keluar;

use App\Events\ServisStatusUpdated;
use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\MasterServis;
use App\Models\MasterServisStok;
use App\Models\ServisDetail;
use App\Models\ServisMasuk;
use App\Models\ServisStatusLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Servis Keluar - Pengambilan & Pelunasan Unit - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'semua')]
    public string $statusFilter = 'semua'; // 'semua' | 'selesai' (belum diambil) | 'sudah_diambil'

    #[Url(except: 'semua')]
    public string $tipeFilter = 'semua'; // 'semua' | 'real' | 'non_real'

    #[Url(except: 'semua')]
    public string $cabangFilter = 'semua';

    public int $perPage = 15;

    // State Modal Detail (Lihat)
    public bool $modalDetailOpen = false;
    public ?ServisMasuk $selectedServis = null;

    // State Modal Edit Nota / Rincian Servis
    public bool $modalEditNotaOpen = false;
    public ?int $editNotaServisId = null;
    public ?ServisMasuk $editNotaServis = null;
    public string $itemSearch = '';
    public ?int $selectedMasterServisId = null;
    public string $selectedMasterServisNama = '';
    public string $selectedMasterServisJenis = 'jasa';
    public $selectedMasterServisHarga = 0;
    public int $itemQty = 1;
    public int $itemGaransiHari = 0;
    public ?int $selectedStokTersedia = null;

    // State Modal Centang Diambil / Pelunasan Kasir
    public bool $modalAmbilOpen = false;
    public ?ServisMasuk $servisToAmbil = null;
    public string $metodePembayaran = 'cash'; // 'cash' | 'transfer' | 'qris'
    public $nominalBayar = 0;
    public string $catatanAmbil = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole('Superadmin') && ! $user->hasRole('Admin') && ! $user->hasRole('Teknisi') && ! $user->hasRole('Kasir'))) {
            abort(403, 'Akses modul Servis Keluar dibatasi.');
        }

        $this->sortField = 'default';
        $this->sortDirection = 'asc';
    }

    #[On('echo:servis-updates,.ServisStatusUpdated')]
    #[On('echo:servis-updates,ServisStatusUpdated')]
    public function onServisStatusUpdated($payload = []): void
    {
        // Re-render automatically when status changes in real-time
    }

    public function updatingSearch(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingTipeFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingCabangFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function loadMore(): void
    {
        $this->perPage += 15;
    }

    public function openDetailModal(int $id): void
    {
        $this->selectedServis = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'teknisi', 'kasir', 'statusLogs.user', 'details.masterServis'])->findOrFail($id);
        $this->modalDetailOpen = true;
    }

    public function closeDetailModal(): void
    {
        $this->modalDetailOpen = false;
        $this->selectedServis = null;
    }

    // =========================================================================
    // MODAL EDIT NOTA
    // =========================================================================

    public function openEditNotaModal(int $id): void
    {
        $servis = ServisMasuk::with(['details.masterServis', 'customer', 'cabang'])->findOrFail($id);

        if ($servis->status !== 'selesai') {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Aksi Ditolak',
                'text' => 'Nota tidak dapat diedit karena unit servis sudah diserahkan/diambil.',
            ]);
            return;
        }

        $this->editNotaServisId = $id;
        $this->editNotaServis = $servis;
        $this->itemSearch = '';
        $this->selectedMasterServisId = null;
        $this->selectedMasterServisNama = '';
        $this->selectedMasterServisJenis = 'jasa';
        $this->selectedMasterServisHarga = 0;
        $this->itemQty = 1;
        $this->itemGaransiHari = 0;
        $this->selectedStokTersedia = null;
        $this->modalEditNotaOpen = true;
    }

    public function closeEditNotaModal(): void
    {
        $this->modalEditNotaOpen = false;
        $this->editNotaServisId = null;
        $this->editNotaServis = null;
        $this->selectedStokTersedia = null;
    }

    public function selectMasterItem(int $id, string $nama, string $jenis, float $harga, int $garansiHari = 0): void
    {
        $this->selectedMasterServisId = $id;
        $this->selectedMasterServisNama = $nama;
        $this->selectedMasterServisJenis = $jenis;
        $this->selectedMasterServisHarga = $harga;
        $this->itemGaransiHari = $garansiHari;
        $this->itemSearch = $nama;
        $this->selectedStokTersedia = null;

        if ($jenis === 'sparepart' && $this->editNotaServis) {
            $cabangId = $this->editNotaServis->cabang_id;
            $stk = MasterServisStok::where('master_servis_id', $id)
                ->where('cabang_id', $cabangId)
                ->first();
            $this->selectedStokTersedia = $stk ? $stk->jumlah : 0;

            if ($this->editNotaServis->is_real && $this->selectedStokTersedia <= 0) {
                $this->dispatch('swal:warning', [
                    'title' => 'Stok Habis!',
                    'text' => "Stok sparepart '{$nama}' kosong.",
                ]);
            }
        }
    }

    public function updatedItemQty($value): void
    {
        $qty = (int) $value;
        if ($this->selectedMasterServisJenis === 'sparepart' && $this->selectedStokTersedia !== null) {
            if ($this->editNotaServis && $this->editNotaServis->is_real) {
                if ($qty > $this->selectedStokTersedia) {
                    $this->dispatch('swal:warning', [
                        'title' => 'Stok Melebihi Batas!',
                        'text' => "Jumlah yang diinput ({$qty} unit) melebihi stok yang tersedia ({$this->selectedStokTersedia} unit).",
                    ]);
                }
            }
        }
    }

    public function tambahItemNota(): void
    {
        if (! $this->editNotaServisId) return;

        $servis = ServisMasuk::find($this->editNotaServisId);
        if (! $servis || $servis->status !== 'selesai') {
            $this->closeEditNotaModal();
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Aksi Ditolak',
                'text' => 'Nota tidak dapat diubah karena unit servis sudah diserahkan/diambil.',
            ]);
            return;
        }

        if (is_string($this->selectedMasterServisHarga)) {
            $this->selectedMasterServisHarga = (float) preg_replace('/[^\d]/', '', $this->selectedMasterServisHarga);
        }

        $this->validate([
            'selectedMasterServisNama' => 'required|string|max:150',
            'selectedMasterServisHarga' => 'required|numeric|min:0',
            'itemQty' => 'required|integer|min:1',
            'itemGaransiHari' => 'required|integer|min:0|max:3650',
        ]);

        // Cek stok fisik jika sparepart dan servis REAL
        if ($this->selectedMasterServisJenis === 'sparepart' && $this->selectedMasterServisId && $servis->is_real) {
            $cabangId = $servis->cabang_id;
            $stk = MasterServisStok::where('master_servis_id', $this->selectedMasterServisId)
                ->where('cabang_id', $cabangId)
                ->first();
            $sisaStok = $stk ? $stk->jumlah : 0;
            if ($sisaStok < (int) $this->itemQty) {
                $this->dispatch('swal:warning', [
                    'title' => 'Stok Tidak Cukup!',
                    'text' => "Stok sparepart '{$this->selectedMasterServisNama}' tidak mencukupi (Tersedia: {$sisaStok} unit, Diminta: {$this->itemQty} unit).",
                ]);
                $this->addError('itemQty', "Stok sparepart tidak mencukupi (Tersedia: {$sisaStok} unit).");
                return;
            }
        }

        $subtotal = (float) $this->selectedMasterServisHarga * (int) $this->itemQty;

        ServisDetail::create([
            'servis_id' => $this->editNotaServisId,
            'master_servis_id' => $this->selectedMasterServisId,
            'nama_item' => $this->selectedMasterServisNama,
            'jenis' => $this->selectedMasterServisJenis,
            'qty' => $this->itemQty,
            'harga' => (float) $this->selectedMasterServisHarga,
            'subtotal' => $subtotal,
            'garansi_hari' => $this->itemGaransiHari,
        ]);

        $this->recalculateServisTotal($this->editNotaServisId);

        $this->itemSearch = '';
        $this->selectedMasterServisId = null;
        $this->selectedMasterServisNama = '';
        $this->selectedMasterServisJenis = 'jasa';
        $this->selectedMasterServisHarga = 0;
        $this->itemQty = 1;
        $this->itemGaransiHari = 0;
        $this->selectedStokTersedia = null;

        $this->editNotaServis = ServisMasuk::with(['details.masterServis', 'customer', 'cabang'])->find($this->editNotaServisId);
    }

    public function hapusItemNota(int $detailId): void
    {
        if (! $this->editNotaServisId) return;

        $servis = ServisMasuk::find($this->editNotaServisId);
        if (! $servis || $servis->status !== 'selesai') {
            $this->closeEditNotaModal();
            return;
        }

        $detail = ServisDetail::where('servis_id', $this->editNotaServisId)->find($detailId);
        if ($detail) {
            $detail->delete();
            $this->recalculateServisTotal($this->editNotaServisId);
            $this->editNotaServis = ServisMasuk::with(['details.masterServis', 'customer', 'cabang'])->find($this->editNotaServisId);
        }
    }

    private function recalculateServisTotal(int $servisId): void
    {
        $servis = ServisMasuk::find($servisId);
        if (! $servis) return;

        $total = ServisDetail::where('servis_id', $servisId)->sum('subtotal');
        $servis->total_biaya = $total;
        $servis->estimasi_biaya = $total;
        $servis->save();
    }

    // =========================================================================
    // MODAL CENTANG DIAMBIL / KASIR SERAH TERIMA
    // =========================================================================

    public function openAmbilModal(int $id): void
    {
        $this->servisToAmbil = ServisMasuk::with(['details', 'customer', 'cabang'])->findOrFail($id);
        $this->nominalBayar = (float) $this->servisToAmbil->total_biaya;
        $this->metodePembayaran = 'cash';
        $this->catatanAmbil = '';
        $this->modalAmbilOpen = true;
    }

    public function closeAmbilModal(): void
    {
        $this->modalAmbilOpen = false;
        $this->servisToAmbil = null;
    }

    public function prosesPenyerahanUnit(): void
    {
        if (! $this->servisToAmbil) return;

        if (is_string($this->nominalBayar)) {
            $this->nominalBayar = (float) preg_replace('/[^\d]/', '', $this->nominalBayar);
        }

        $totalBiaya = (float) $this->servisToAmbil->total_biaya;

        if ($totalBiaya > 0) {
            $this->validate([
                'nominalBayar' => "required|numeric|min:{$totalBiaya}",
                'metodePembayaran' => 'required|in:cash,transfer,qris',
            ], [
                'nominalBayar.min' => 'Nominal bayar tidak boleh kurang dari total biaya servis.',
            ]);
        }

        try {
            DB::beginTransaction();

            $now = Carbon::now();
            $kembalian = max(0, (float) $this->nominalBayar - $totalBiaya);

            $this->servisToAmbil->update([
                'status' => 'sudah_diambil',
                'tanggal_ambil' => $now,
                'kasir_id' => Auth::id(),
                'metode_pembayaran' => $this->metodePembayaran,
                'bayar' => (float) $this->nominalBayar,
                'kembalian' => $kembalian,
            ]);

            ServisStatusLog::create([
                'servis_id' => $this->servisToAmbil->id,
                'user_id' => Auth::id(),
                'status' => 'sudah_diambil',
                'tanggal' => $now,
                'catatan' => 'Unit telah diserahterimakan dan dilunasi oleh pelanggan. ' . ($this->catatanAmbil ?: ''),
            ]);

            DB::commit();

            try {
                broadcast(new ServisStatusUpdated($this->servisToAmbil->id, 'sudah_diambil', $this->servisToAmbil->no_servis_frontend, $this->servisToAmbil->cabang_id));
            } catch (\Throwable $th) {
                // Ignore broadcast error
            }

            $servisKode = $this->servisToAmbil->no_servis_frontend;
            $this->closeAmbilModal();
            session()->flash('success', "Unit servis {$servisKode} berhasil diserahterimakan ke pelanggan!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('ambilError', 'Gagal memproses penyerahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $cabangs = $isHQ ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();

        // Suggestions for Master Servis items in Edit Nota modal
        $masterServisSuggestions = [];
        if ($this->modalEditNotaOpen && ! empty($this->itemSearch) && ! $this->selectedMasterServisId) {
            $msQuery = MasterServis::where('is_aktif', true);
            $pattern = '%' . strtolower($this->itemSearch) . '%';
            $masterServisSuggestions = $msQuery->where(function ($q) use ($pattern) {
                $q->whereRaw('LOWER(nama_item) LIKE ?', [$pattern])
                  ->orWhereRaw('LOWER(kode_item) LIKE ?', [$pattern]);
            })->take(6)->get();
        }

        // Query Utama Servis Keluar (status: 'selesai' yang sudah dicetak notanya ATAU 'sudah_diambil' / 'sudah_dibayar')
        $query = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'teknisi', 'kasir', 'details.masterServis']);

        // Scoping Cabang
        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            if ($targetCabangId) {
                $query->where('cabang_id', $targetCabangId);
            }
        } elseif ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)) {
            $query->where('cabang_id', (int) $this->cabangFilter);
        }

        // Tipe Transaksi Real Only
        $query->where('is_real', true);

        // Filter Status di Servis Keluar
        if ($this->statusFilter === 'selesai') {
            $query->where('status', 'selesai')->where('is_cetak_nota', true);
        } elseif ($this->statusFilter === 'sudah_diambil') {
            $query->whereIn('status', ['sudah_diambil', 'sudah_dibayar']);
        } else {
            $query->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->where('status', 'selesai')->where('is_cetak_nota', true);
                })->orWhereIn('status', ['sudah_diambil', 'sudah_dibayar']);
            });
        }

        // Pencarian Multi-Kata Longgar
        if (! empty($this->search)) {
            $cleanSearch = trim($this->search);
            $words = array_filter(explode(' ', $cleanSearch));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(no_servis) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(tipe_unit) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(keluhan) LIKE ?', [$pattern])
                            ->orWhereHas('customer', function ($cq) use ($pattern) {
                                $cq->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(COALESCE(no_hp, \'\')) LIKE ?', [$pattern]);
                            });
                    });
                }
            });
        }

        // Metrik Ringkasan Servis Keluar
        $metricsBase = clone $query;
        $allCurrent = $metricsBase->get();

        $totalSiapDiambil = $allCurrent->where('status', 'selesai')->where('is_cetak_nota', true)->count();
        $totalSudahDiambil = $allCurrent->whereIn('status', ['sudah_diambil', 'sudah_dibayar'])->count();
        $totalPendapatanServis = $allCurrent->whereIn('status', ['sudah_diambil', 'sudah_dibayar'])->sum('total_biaya');

        // Sorting
        $sortDir = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';
        if ($this->sortField === 'default') {
            $query->orderByRaw("CASE WHEN status = 'selesai' THEN 0 ELSE 1 END")
                  ->orderByRaw("CASE WHEN status = 'selesai' THEN COALESCE(tanggal_selesai, tanggal_masuk) ELSE COALESCE(tanggal_ambil, tanggal_selesai) END DESC")
                  ->orderByDesc('id');
        } elseif ($this->sortField === 'status') {
            if ($sortDir === 'asc') {
                $query->orderByRaw("CASE WHEN status = 'selesai' THEN 0 ELSE 1 END")->orderByDesc('id');
            } else {
                $query->orderByRaw("CASE WHEN status = 'selesai' THEN 1 ELSE 0 END")->orderByDesc('id');
            }
        } else {
            $allowedSorts = ['tanggal_masuk', 'tanggal_selesai', 'tanggal_ambil', 'no_servis', 'tipe_unit', 'total_biaya', 'id'];
            $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'tanggal_ambil';
            $query->orderBy($sortColumn, $sortDir)->orderByDesc('id');
        }

        $totalItems = (clone $query)->count();
        $servisList = $query->take($this->perPage)->get();
        $hasMore = $totalItems > $this->perPage;

        return view('livewire.servis.keluar.index', [
            'servisList' => $servisList,
            'totalItems' => $totalItems,
            'hasMore' => $hasMore,
            'totalSiapDiambil' => $totalSiapDiambil,
            'totalSudahDiambil' => $totalSudahDiambil,
            'totalPendapatanServis' => $totalPendapatanServis,
            'cabangs' => $cabangs,
            'masterServisSuggestions' => $masterServisSuggestions,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ]);
    }
}
