<?php

namespace App\Livewire\Servis\Pengerjaan;

use App\Events\ServisStatusUpdated;
use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\Produk;
use App\Models\ServisDetail;
use App\Models\ServisMasuk;
use App\Models\ServisStatusLog;
use App\Models\Stok;
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
#[Title('Servis Keluar & Kasir Pelunasan - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'semua')]
    public string $statusFilter = 'semua';

    #[Url(except: 'semua')]
    public string $ambilFilter = 'semua';

    #[Url(except: 'semua')]
    public string $kategoriFilter = 'semua';

    #[Url(except: 'semua')]
    public string $cabangFilter = 'semua';

    public int $perPage = 15;

    // State Modal Workspace Pengerjaan & Billing
    public bool $modalPengerjaanOpen = false;
    public ?int $activeServisId = null;
    public ?ServisMasuk $activeServis = null;
    public string $pengerjaanStatus = 'perbaikan';
    public string $catatanPengerjaan = '';

    // Billing Item State (Layanan Jasa & Sparepart dari Master Servis)
    public string $sparepartSearch = '';
    public ?int $selectedMasterServisId = null;
    public string $selectedMasterServisNama = '';
    public string $selectedMasterServisJenis = 'jasa';
    public float $selectedMasterServisHarga = 0;
    public int $sparepartQty = 1;

    // State Modal Checkout Kasir Pelunasan
    public bool $modalCheckoutOpen = false;
    public ?int $checkoutServisId = null;
    public ?ServisMasuk $checkoutServis = null;
    public int $garansiHari = 30; // Default 30 hari
    public string $customGaransiHari = '';
    public string $catatanPelunasan = '';

    // State Modal Konfirmasi Sukses Bayar
    public bool $modalSuccessPayOpen = false;
    public ?ServisMasuk $lastPaidServis = null;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole('Superadmin') && ! $user->hasRole('Admin') && ! $user->hasRole('Teknisi') && ! $user->hasRole('Kasir'))) {
            abort(403, 'Akses modul servis dibatasi.');
        }

        $this->sortField = 'tanggal_masuk';
        $this->sortDirection = 'desc';

        // Auto-default filter kategori berdasarkan bagian user jika tidak diset via URL
        if (! request()->has('kategoriFilter') && $user) {
            $defaultKategori = $user->defaultKategoriPerangkat();
            if ($defaultKategori) {
                $this->kategoriFilter = $defaultKategori;
            }
        }
    }

    #[On('echo:servis-updates,.ServisStatusUpdated')]
    #[On('echo:servis-updates,ServisStatusUpdated')]
    #[On('echo:servis-updates,App\\Events\\ServisStatusUpdated')]
    public function onServisStatusUpdated($payload = []): void
    {
        // Re-render when status or billing changes in real-time
    }

    public function selectMasterServis(int $masterServisId): void
    {
        if (! $this->activeServis && $this->activeServisId) {
            $this->activeServis = ServisMasuk::find($this->activeServisId);
        }
        $item = \App\Models\MasterServis::find($masterServisId);
        if (! $item || ! $this->activeServis) return;

        $this->selectedMasterServisId = $item->id;
        $this->selectedMasterServisNama = $item->nama_item;
        $this->selectedMasterServisJenis = $item->jenis;
        $this->selectedMasterServisHarga = (float) $item->harga_jual;
        $this->sparepartSearch = $item->nama_item;
        $this->sparepartQty = 1;
    }

    public function openPengerjaanModal(int $id): void
    {
        $this->activeServisId = $id;
        $this->activeServis = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'details.masterServis', 'statusLogs.user'])->findOrFail($id);
        $this->pengerjaanStatus = in_array($this->activeServis->status, ['pengecekan', 'menunggu_konfirmasi', 'perbaikan', 'pengerjaan', 'menunggu_sparepart', 'selesai', 'batal']) ? $this->activeServis->status : 'perbaikan';
        $this->catatanPengerjaan = '';
        $this->resetBillingInput();
        $this->modalPengerjaanOpen = true;
    }

    public function closePengerjaanModal(): void
    {
        $this->modalPengerjaanOpen = false;
        $this->activeServis = null;
        $this->activeServisId = null;
        $this->resetBillingInput();
    }

    public function resetBillingInput(): void
    {
        $this->sparepartSearch = '';
        $this->selectedMasterServisId = null;
        $this->selectedMasterServisNama = '';
        $this->selectedMasterServisJenis = 'jasa';
        $this->selectedMasterServisHarga = 0;
        $this->sparepartQty = 1;
        $this->resetValidation();
    }

    public function tambahItemBilling(): void
    {
        if (! $this->activeServis && $this->activeServisId) {
            $this->activeServis = ServisMasuk::find($this->activeServisId);
        }
        if (! $this->activeServis) return;

        $this->validate([
            'selectedMasterServisId' => 'required|exists:master_servis,id',
            'sparepartQty' => 'required|numeric|min:1',
        ], [
            'selectedMasterServisId.required' => 'Pilih item layanan jasa atau sparepart dari Master Servis.',
            'sparepartQty.min' => 'Jumlah minimal 1.',
        ]);

        $item = \App\Models\MasterServis::findOrFail($this->selectedMasterServisId);
        $qty = (int) $this->sparepartQty;
        $harga = (float) $item->harga_jual;
        $subtotal = $harga * $qty;

        ServisDetail::create([
            'servis_id' => $this->activeServis->id,
            'master_servis_id' => $item->id,
            'nama_item' => $item->nama_item,
            'jenis' => $item->jenis,
            'qty' => $qty,
            'harga' => $harga,
            'subtotal' => $subtotal,
        ]);

        // Update total biaya di servis_masuk
        $totalBiaya = (float) ServisDetail::where('servis_id', $this->activeServis->id)->sum('subtotal');
        $this->activeServis->total_biaya = $totalBiaya;
        $this->activeServis->save();

        $this->activeServis->load('details.masterServis');
        $this->resetBillingInput();
        session()->flash('billing_success', 'Item layanan servis berhasil ditambahkan ke rincian!');
    }

    public function hapusItemBilling(int $detailId): void
    {
        if (! $this->activeServis && $this->activeServisId) {
            $this->activeServis = ServisMasuk::find($this->activeServisId);
        }
        if (! $this->activeServis) return;

        $detail = ServisDetail::where('servis_id', $this->activeServis->id)->where('id', $detailId)->first();
        if ($detail) {
            $detail->delete();
            $totalBiaya = (float) ServisDetail::where('servis_id', $this->activeServis->id)->sum('subtotal');
            $this->activeServis->total_biaya = $totalBiaya;
            $this->activeServis->save();
            $this->activeServis->load('details.masterServis');
            session()->flash('billing_success', 'Item rincian biaya berhasil dihapus!');
        }
    }

    public function simpanPengerjaan(): void
    {
        if (! $this->activeServis && $this->activeServisId) {
            $this->activeServis = ServisMasuk::find($this->activeServisId);
        }
        if (! $this->activeServis) return;

        $this->validate([
            'pengerjaanStatus' => 'required|in:pengecekan,menunggu_konfirmasi,perbaikan,pengerjaan,menunggu_sparepart,selesai,batal',
            'catatanPengerjaan' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $now = Carbon::now();
            $oldStatus = $this->activeServis->status;
            $newStatus = $this->pengerjaanStatus;

            $this->activeServis->status = $newStatus;

            // Jika status diubah jadi Selesai
            if ($newStatus === 'selesai' && ! $this->activeServis->tanggal_selesai) {
                $this->activeServis->tanggal_selesai = $now;

                // Potong stok sparepart jika sebelumnya belum dipotong
                if ($oldStatus !== 'selesai' && $oldStatus !== 'sudah_dibayar' && $oldStatus !== 'sudah_diambil') {
                    $this->potongStokSparepart($this->activeServis);
                }
            }

            $this->activeServis->save();

            // Catat log
            $logCatatan = trim($this->catatanPengerjaan) ?: "Pengerjaan diperbarui ke status {$this->activeServis->status_label}.";
            ServisStatusLog::create([
                'servis_id' => $this->activeServis->id,
                'user_id' => Auth::id(),
                'status' => $newStatus,
                'tanggal' => $now,
                'catatan' => $logCatatan,
            ]);

            DB::commit();

            // Broadcast real-time Reverb event
            try {
                broadcast(new ServisStatusUpdated($this->activeServis->id, $newStatus, $this->activeServis->no_servis_frontend, $this->activeServis->cabang_id));
            } catch (\Throwable $th) {}

            $this->modalPengerjaanOpen = false;
            $this->activeServis = null;
            session()->flash('success', 'Data pengerjaan servis berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('pengerjaanError', 'Gagal menyimpan data pengerjaan: ' . $e->getMessage());
        }
    }

    protected function potongStokSparepart(ServisMasuk $servis): void
    {
        if (! $servis->is_real) {
            return;
        }

        $spareparts = ServisDetail::where('servis_id', $servis->id)->whereNotNull('produk_id')->get();
        foreach ($spareparts as $item) {
            $stok = Stok::where('cabang_id', $servis->cabang_id)
                ->where('produk_id', $item->produk_id)
                ->lockForUpdate()
                ->first();

            if ($stok) {
                $stok->jumlah = max(0, $stok->jumlah - $item->qty);
                $stok->save();
            }
        }
    }

    public function openCheckoutModal(int $id): void
    {
        $this->checkoutServis = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'details.masterServis'])->findOrFail($id);
        $this->garansiHari = 30;
        $this->customGaransiHari = '';
        $this->catatanPelunasan = '';
        $this->modalCheckoutOpen = true;
    }

    public function closeCheckoutModal(): void
    {
        $this->modalCheckoutOpen = false;
        $this->checkoutServis = null;
    }

    public function setGaransi(int $days): void
    {
        $this->garansiHari = $days;
        $this->customGaransiHari = '';
    }

    public function simpanPelunasan(): void
    {
        if (! $this->checkoutServis) return;

        $effectiveGaransi = $this->garansiHari;
        if (! empty($this->customGaransiHari) && is_numeric($this->customGaransiHari)) {
            $effectiveGaransi = (int) $this->customGaransiHari;
        }

        $totalBiaya = (float) $this->checkoutServis->total_biaya;

        try {
            DB::beginTransaction();

            $now = Carbon::now();
            $garansiSampai = $effectiveGaransi > 0 ? $now->copy()->addDays($effectiveGaransi)->toDateString() : null;

            // Jika sebelumnya belum dipotong stok (misal langsung dari pengecekan ke pembayaran)
            if ($this->checkoutServis->status !== 'selesai' && $this->checkoutServis->status !== 'sudah_dibayar' && $this->checkoutServis->status !== 'sudah_diambil') {
                $this->potongStokSparepart($this->checkoutServis);
                $this->checkoutServis->tanggal_selesai = $now;
            }

            $this->checkoutServis->status = 'sudah_diambil';
            $this->checkoutServis->tanggal_ambil = $now;
            $this->checkoutServis->metode_pembayaran = 'tunai';
            $this->checkoutServis->bayar = $totalBiaya;
            $this->checkoutServis->kembalian = 0;
            $this->checkoutServis->garansi_hari = $effectiveGaransi;
            $this->checkoutServis->garansi_sampai = $garansiSampai;
            $this->checkoutServis->kasir_id = Auth::id();
            $this->checkoutServis->save();

            ServisStatusLog::create([
                'servis_id' => $this->checkoutServis->id,
                'user_id' => Auth::id(),
                'status' => 'sudah_diambil',
                'tanggal' => $now,
                'catatan' => 'Unit diambil dan tagihan dilunasi. ' . ($effectiveGaransi > 0 ? "Garansi toko {$effectiveGaransi} hari." : 'Tanpa garansi.') . ($this->catatanPelunasan ? " Catatan: {$this->catatanPelunasan}" : ''),
            ]);

            DB::commit();

            // Broadcast real-time Reverb event
            try {
                broadcast(new ServisStatusUpdated($this->checkoutServis->id, 'sudah_diambil', $this->checkoutServis->no_servis_frontend, $this->checkoutServis->cabang_id));
            } catch (\Throwable $th) {}

            $this->modalCheckoutOpen = false;
            $this->lastPaidServis = $this->checkoutServis->fresh(['customer', 'cabang', 'pembuat', 'kasir', 'details.masterServis']);
            $this->modalSuccessPayOpen = true;

            // Trigger auto open invoice
            $this->dispatch('open-print-invoice', url: route('servis.invoice', $this->checkoutServis->id) . '?autoprint=1');
            session()->flash('success', "Pelunasan servis {$this->checkoutServis->no_servis_frontend} berhasil diproses!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('checkoutError', 'Gagal memproses pelunasan: ' . $e->getMessage());
        }
    }

    public function loadMore(): void
    {
        $this->perPage += 15;
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $cabangs = $isHQ ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();

        // Autocomplete suggestions dari Master Servis (Jasa & Sparepart)
        $sparepartSuggestions = collect();
        if (! empty(trim($this->sparepartSearch)) && $this->activeServis && ! $this->selectedMasterServisId) {
            $queryServisItem = \App\Models\MasterServis::query()->where('is_aktif', true);

            $words = explode(' ', trim($this->sparepartSearch));
            foreach ($words as $w) {
                if (empty(trim($w))) continue;
                $pattern = '%' . strtolower($w) . '%';
                $queryServisItem->where(function ($q) use ($pattern) {
                    $q->whereRaw('LOWER(nama_item) LIKE ?', [$pattern])
                      ->orWhereRaw('LOWER(COALESCE(kode_item, \'\')) LIKE ?', [$pattern])
                      ->orWhereRaw('LOWER(COALESCE(keterangan, \'\')) LIKE ?', [$pattern]);
                });
            }
            $sparepartSuggestions = $queryServisItem->take(15)->get();
        }

        // Query Servis Pengerjaan
        $query = ServisMasuk::query()->with(['customer', 'cabang', 'kasir', 'details.masterServis']);

        // Scoping Cabang
        if (! $isOwner) {
            $query->where('cabang_id', $user->cabang_id);
        } else {
            if (! $isHQ && $activeBranchSession) {
                $query->where('cabang_id', (int) $activeBranchSession);
            } elseif ($this->cabangFilter !== 'semua' && is_numeric($this->cabangFilter)) {
                $query->where('cabang_id', (int) $this->cabangFilter);
            }
        }

        // Filter Status
        if ($this->statusFilter !== 'semua') {
            if ($this->statusFilter === 'perbaikan' || $this->statusFilter === 'pengerjaan') {
                $query->whereIn('status', ['perbaikan', 'pengerjaan']);
            } elseif ($this->statusFilter === 'sudah_diambil' || $this->statusFilter === 'sudah_dibayar') {
                $query->whereIn('status', ['sudah_diambil', 'sudah_dibayar']);
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        // Filter Pengambilan
        if ($this->ambilFilter === 'sudah_diambil') {
            $query->whereNotNull('tanggal_ambil');
        } elseif ($this->ambilFilter === 'belum_diambil') {
            $query->whereNull('tanggal_ambil');
        } elseif ($this->ambilFilter === 'hari_ini') {
            $query->whereDate('tanggal_ambil', Carbon::today());
        }

        // Filter Kategori Perangkat
        if ($this->kategoriFilter !== 'semua') {
            $query->where('kategori_perangkat', $this->kategoriFilter);
        }

        // Loose Multi-Word Search
        if (! empty(trim($this->search))) {
            $words = array_filter(explode(' ', trim($this->search)));
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

        // Metrics Summary
        $metricsBase = clone $query;
        $allCurrent = $metricsBase->get();

        $totalDalamProses = $allCurrent->whereIn('status', ['pengecekan', 'menunggu_konfirmasi', 'perbaikan', 'pengerjaan', 'menunggu_sparepart'])->count();
        $totalSiapAmbil = $allCurrent->where('status', 'selesai')->count();
        $totalSudahDiambil = $allCurrent->where('status', 'sudah_dibayar')->count();
        $totalOmzetServis = $allCurrent->where('status', 'sudah_dibayar')->sum('total_biaya');

        // Sorting
        $sortDir = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['tanggal_masuk', 'tanggal_ambil', 'no_servis', 'tipe_unit', 'status', 'total_biaya', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'tanggal_masuk';
        $query->orderBy($sortColumn, $sortDir);

        $totalItems = (clone $query)->count();
        $servisList = $query->take($this->perPage)->get();
        $hasMore = $totalItems > $this->perPage;

        return view('livewire.servis.pengerjaan.index', [
            'servisList' => $servisList,
            'totalItems' => $totalItems,
            'hasMore' => $hasMore,
            'totalDalamProses' => $totalDalamProses,
            'totalSiapAmbil' => $totalSiapAmbil,
            'totalSudahDiambil' => $totalSudahDiambil,
            'totalOmzetServis' => $totalOmzetServis,
            'cabangs' => $cabangs,
            'sparepartSuggestions' => $sparepartSuggestions,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ]);
    }
}
