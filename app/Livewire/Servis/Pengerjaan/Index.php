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
    public string $pengerjaanStatus = 'pengerjaan';
    public string $catatanPengerjaan = '';

    // Billing Item State (Produk & Jasa dari Master Produk)
    public string $sparepartSearch = '';
    public ?int $selectedProdukId = null;
    public string $selectedProdukNama = '';
    public float $selectedProdukHarga = 0;
    public int $selectedProdukStok = 0;
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
    }

    #[On('echo:servis-updates,.ServisStatusUpdated')]
    #[On('echo:servis-updates,ServisStatusUpdated')]
    #[On('echo:servis-updates,App\\Events\\ServisStatusUpdated')]
    public function onServisStatusUpdated($payload = []): void
    {
        // Re-render when status or billing changes in real-time
    }

    public function selectProduk(int $produkId): void
    {
        if (! $this->activeServis && $this->activeServisId) {
            $this->activeServis = ServisMasuk::find($this->activeServisId);
        }
        $produk = Produk::find($produkId);
        if (! $produk || ! $this->activeServis) return;

        $cabangId = $this->activeServis->cabang_id;
        $stokRecord = Stok::where('cabang_id', $cabangId)->where('produk_id', $produkId)->first();
        $stokSisa = (int) ($stokRecord->jumlah ?? 0);

        $this->selectedProdukId = $produk->id;
        $this->selectedProdukNama = $produk->nama_produk;
        $this->selectedProdukHarga = (float) $produk->harga_jual;
        $this->selectedProdukStok = $stokSisa;
        $this->sparepartSearch = $produk->nama_produk;
        $this->sparepartQty = 1;
    }

    public function openPengerjaanModal(int $id): void
    {
        $this->activeServisId = $id;
        $this->activeServis = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'details.produk', 'statusLogs.user'])->findOrFail($id);
        $this->pengerjaanStatus = in_array($this->activeServis->status, ['pengerjaan', 'menunggu_sparepart', 'selesai']) ? $this->activeServis->status : 'pengerjaan';
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
        $this->selectedProdukId = null;
        $this->selectedProdukNama = '';
        $this->selectedProdukHarga = 0;
        $this->selectedProdukStok = 0;
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
            'selectedProdukId' => 'required|exists:produks,id',
            'sparepartQty' => 'required|numeric|min:1',
        ], [
            'selectedProdukId.required' => 'Pilih produk atau jasa servis dari daftar.',
            'sparepartQty.min' => 'Jumlah minimal 1.',
        ]);

        $produk = Produk::findOrFail($this->selectedProdukId);
        $qty = (int) $this->sparepartQty;
        $cabangId = $this->activeServis->cabang_id;

        // Cek stok cabang
        $stokRecord = Stok::where('cabang_id', $cabangId)->where('produk_id', $produk->id)->first();
        $stokAda = $stokRecord ? $stokRecord->jumlah : 0;

        if ($stokRecord && $qty > $stokAda) {
            $this->addError('sparepartQty', "Stok di cabang ini hanya tersedia {$stokAda} unit.");
            return;
        }

        $harga = (float) $produk->harga_jual;
        $subtotal = $harga * $qty;

        ServisDetail::create([
            'servis_id' => $this->activeServis->id,
            'produk_id' => $produk->id,
            'nama_item' => $produk->nama_produk,
            'qty' => $qty,
            'harga' => $harga,
            'subtotal' => $subtotal,
        ]);

        // Update total biaya di servis_masuk
        $totalBiaya = (float) ServisDetail::where('servis_id', $this->activeServis->id)->sum('subtotal');
        $this->activeServis->total_biaya = $totalBiaya;
        $this->activeServis->save();

        $this->activeServis->load('details.produk');
        $this->resetBillingInput();
        session()->flash('billing_success', 'Item rincian biaya berhasil ditambahkan!');
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
            $this->activeServis->load('details.produk');
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
            'pengerjaanStatus' => 'required|in:pengecekan,pengerjaan,menunggu_sparepart,selesai,batal',
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
                if ($oldStatus !== 'selesai' && $oldStatus !== 'sudah_dibayar') {
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
        $this->checkoutServis = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'details.produk'])->findOrFail($id);
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
            if ($this->checkoutServis->status !== 'selesai' && $this->checkoutServis->status !== 'sudah_dibayar') {
                $this->potongStokSparepart($this->checkoutServis);
                $this->checkoutServis->tanggal_selesai = $now;
            }

            $this->checkoutServis->status = 'sudah_dibayar';
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
                'status' => 'sudah_dibayar',
                'tanggal' => $now,
                'catatan' => 'Unit diambil dan tagihan dilunasi. ' . ($effectiveGaransi > 0 ? "Garansi toko {$effectiveGaransi} hari." : 'Tanpa garansi.') . ($this->catatanPelunasan ? " Catatan: {$this->catatanPelunasan}" : ''),
            ]);

            DB::commit();

            // Broadcast real-time Reverb event
            try {
                broadcast(new ServisStatusUpdated($this->checkoutServis->id, 'sudah_dibayar', $this->checkoutServis->no_servis_frontend, $this->checkoutServis->cabang_id));
            } catch (\Throwable $th) {}

            $this->modalCheckoutOpen = false;
            $this->lastPaidServis = $this->checkoutServis->fresh(['customer', 'cabang', 'pembuat', 'kasir', 'details.produk']);
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

        // Autocomplete sparepart search
        $sparepartSuggestions = collect();
        if (! empty(trim($this->sparepartSearch)) && $this->activeServis && ! $this->selectedProdukId) {
            $cabangId = $this->activeServis->cabang_id;
            $queryProd = Produk::query()
                ->whereHas('stoks', function ($sq) use ($cabangId) {
                    $sq->where('cabang_id', $cabangId)->where('is_aktif', true)->where('jumlah', '>', 0);
                })
                ->with(['stoks' => function ($sq) use ($cabangId) {
                    $sq->where('cabang_id', $cabangId);
                }]);

            $words = explode(' ', trim($this->sparepartSearch));
            foreach ($words as $w) {
                if (empty(trim($w))) continue;
                $pattern = '%' . strtolower($w) . '%';
                $queryProd->where(function ($q) use ($pattern) {
                    $q->whereRaw('LOWER(nama_produk) LIKE ?', [$pattern])
                      ->orWhereRaw('LOWER(COALESCE(barcode, \'\')) LIKE ?', [$pattern]);
                });
            }
            $sparepartSuggestions = $queryProd->take(15)->get();
        }

        // Query Servis Pengerjaan
        $query = ServisMasuk::query()->with(['customer', 'cabang', 'kasir', 'details.produk']);

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
            $query->where('status', $this->statusFilter);
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
                            ->orWhereRaw('LOWER(COALESCE(serial_number, \'\')) LIKE ?', [$pattern])
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

        $totalDalamProses = $allCurrent->whereIn('status', ['pengecekan', 'pengerjaan', 'menunggu_sparepart'])->count();
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
