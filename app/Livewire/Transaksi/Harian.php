<?php

namespace App\Livewire\Transaksi;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\MasterPpn;
use App\Models\MasterServis;
use App\Models\MetodePembayaran;
use App\Models\Produk;
use App\Models\TransaksiFull;
use App\Models\TransaksiFullDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Transaksi Harian - Soulmate Komputer')]
class Harian extends Component
{
    use WithPagination, WithSorting;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'semua')]
    public string $tipeFilter = 'semua'; // 'semua' | 'penjualan' | 'servis'

    #[Url(except: 'semua')]
    public string $statusFilter = 'semua'; // 'semua' | 'Lunas' | 'BON' | 'Belum Lunas' | 'Batal'

    #[Url(except: 'semua')]
    public string $metodeFilter = 'semua';

    public string $filterDate = '';
    public int $perPage = 15;

    // State Modal Form (Create / Edit)
    public bool $modalFormOpen = false;
    public ?int $transaksiId = null;
    public string $formNoTransaksi = '';
    public string $formTipeTransaksi = 'penjualan';
    public string $formTanggal = '';
    public string $formCustomerType = 'umum';
    public ?int $formCustomerId = null;
    public string $formCustomerSearch = '';
    public string $formNamaPelanggan = 'Pelanggan Umum';
    public string $formNoHp = '';
    public ?int $formMetodePembayaranId = null;
    public string $formMetodePembayaran = 'Cash';
    public ?int $formPpnId = null;
    public float $formPpnPersen = 0;
    public string $formStatus = 'Lunas';
    public string $formCatatan = '';
    public array $items = [];
    public array $productSearchQueries = [];

    // State Modal Detail
    public bool $modalDetailOpen = false;
    public ?TransaksiFull $selectedTransaksi = null;

    // State Modal Delete
    public bool $modalDeleteOpen = false;
    public ?int $deleteId = null;
    public ?string $deleteCode = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole('Superadmin') && ! $user->hasRole('Admin') && ! $user->hasRole('Teknisi') && ! $user->hasRole('Kasir'))) {
            abort(403, 'Akses modul Transaksi Harian dibatasi.');
        }

        $this->filterDate = now()->toDateString();
        $this->sortField = 'tanggal';
        $this->sortDirection = 'desc';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTipeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingMetodeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDate(): void
    {
        $this->resetPage();
    }

    // =========================================================================
    // MODAL FORM (CREATE / EDIT)
    // =========================================================================

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->transaksiId = null;
        $this->formTanggal = now()->format('Y-m-d\TH:i');
        $this->formTipeTransaksi = 'penjualan';
        $this->generateDefaultNoTransaksi();

        $activePpn = MasterPpn::getAktifCached();
        if ($activePpn) {
            $this->formPpnId = $activePpn->id;
            $this->formPpnPersen = (float) $activePpn->persen;
        }

        $defaultMetode = MetodePembayaran::where('is_aktif', true)->first();
        if ($defaultMetode) {
            $this->formMetodePembayaranId = $defaultMetode->id;
            $this->formMetodePembayaran = $defaultMetode->nama;
        }

        $this->addItemRow();
        $this->modalFormOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $transaksi = TransaksiFull::with(['details', 'customer'])->findOrFail($id);
        $this->resetForm();

        $this->transaksiId = $transaksi->id;
        $this->formNoTransaksi = $transaksi->no_transaksi_frontend;
        $this->formTipeTransaksi = $transaksi->tipe_transaksi;
        $this->formTanggal = $transaksi->tanggal ? $transaksi->tanggal->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
        $this->formCustomerType = $transaksi->customer_id ? 'langganan' : 'umum';
        $this->formCustomerId = $transaksi->customer_id;
        $this->formNamaPelanggan = $transaksi->nama_pelanggan;
        $this->formNoHp = $transaksi->no_hp ?? '';
        $this->formMetodePembayaranId = $transaksi->metode_pembayaran_id;
        $this->formMetodePembayaran = $transaksi->metode_pembayaran;
        $this->formPpnId = $transaksi->ppn_id;
        $this->formPpnPersen = (float) $transaksi->ppn_persen;
        $this->formStatus = $transaksi->status;
        $this->formCatatan = $transaksi->catatan ?? '';

        $this->items = [];
        foreach ($transaksi->details as $idx => $d) {
            $this->items[] = [
                'produk_id' => $d->produk_id,
                'nama_item' => $d->nama_item,
                'qty' => $d->qty,
                'harga_modal' => (float) $d->harga_modal,
                'harga_jual' => (float) $d->harga_jual,
                'subtotal_modal' => (float) $d->subtotal_modal,
                'subtotal_jual' => (float) $d->subtotal_jual,
                'laba' => (float) $d->laba,
            ];
            $this->productSearchQueries[$idx] = $d->nama_item;
        }

        if (empty($this->items)) {
            $this->addItemRow();
        }

        $this->modalFormOpen = true;
    }

    public function closeFormModal(): void
    {
        $this->modalFormOpen = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->transaksiId = null;
        $this->formNoTransaksi = '';
        $this->formTipeTransaksi = 'penjualan';
        $this->formTanggal = '';
        $this->formCustomerType = 'umum';
        $this->formCustomerId = null;
        $this->formCustomerSearch = '';
        $this->formNamaPelanggan = 'Pelanggan Umum';
        $this->formNoHp = '';
        $this->formMetodePembayaranId = null;
        $this->formMetodePembayaran = 'Cash';
        $this->formPpnId = null;
        $this->formPpnPersen = 0;
        $this->formStatus = 'Lunas';
        $this->formCatatan = '';
        $this->items = [];
        $this->productSearchQueries = [];
        $this->resetValidation();
    }

    public function updatedFormTipeTransaksi(): void
    {
        if (! $this->transaksiId) {
            $this->generateDefaultNoTransaksi();
        }
    }

    public function updatedFormTanggal(): void
    {
        if (! $this->transaksiId) {
            $this->generateDefaultNoTransaksi();
        }
    }

    public function generateDefaultNoTransaksi(): void
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');

        $cabangId = $isOwner && $activeBranchSession !== 'hq' ? (int) $activeBranchSession : ($user->cabang_id ?: 1);

        $prefix = $this->formTipeTransaksi === 'servis' ? 'SV' : 'PJ';
        $date = ! empty($this->formTanggal) ? Carbon::parse($this->formTanggal) : now();
        $yymmdd = $date->format('ymd');

        $countToday = TransaksiFull::where('cabang_id', $cabangId)
            ->whereDate('tanggal', $date->toDateString())
            ->where('tipe_transaksi', $this->formTipeTransaksi)
            ->count() + 1;

        $sequence = str_pad($countToday, 5, '0', STR_PAD_LEFT);
        $this->formNoTransaksi = "{$prefix}-{$yymmdd}-{$sequence}";
    }

    public function selectCustomer(int $id, string $nama, ?string $noHp): void
    {
        $this->formCustomerId = $id;
        $this->formNamaPelanggan = $nama;
        $this->formNoHp = $noHp ?? '';
        $this->formCustomerSearch = $nama;
    }

    public function updatedFormPpnId($value): void
    {
        if ($value) {
            $ppn = MasterPpn::find($value);
            $this->formPpnPersen = $ppn ? (float) $ppn->persen : 0;
        } else {
            $this->formPpnPersen = 0;
        }
    }

    public function updatedFormMetodePembayaranId($value): void
    {
        if ($value) {
            $metode = MetodePembayaran::find($value);
            $this->formMetodePembayaran = $metode ? $metode->nama : 'Cash';
        }
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'items.')) {
            $parts = explode('.', $propertyName);
            if (count($parts) >= 3 && $parts[2] === 'nama_item') {
                $idx = (int) $parts[1];
                $this->items[$idx]['produk_id'] = null;
                $this->productSearchQueries[$idx] = $this->items[$idx]['nama_item'] ?? '';
            }
            if (count($parts) >= 2 && is_numeric($parts[1])) {
                $idx = (int) $parts[1];
                $this->recalculateRow($idx);
            }
        }
    }

    public function addItemRow(): void
    {
        $idx = count($this->items);
        $this->items[] = [
            'produk_id' => null,
            'nama_item' => '',
            'qty' => 1,
            'harga_modal' => 0,
            'harga_jual' => 0,
            'subtotal_modal' => 0,
            'subtotal_jual' => 0,
            'laba' => 0,
        ];
        $this->productSearchQueries[$idx] = '';
    }

    public function removeItemRow(int $index): void
    {
        unset($this->items[$index]);
        unset($this->productSearchQueries[$index]);
        $this->items = array_values($this->items);
        $this->productSearchQueries = array_values($this->productSearchQueries);

        if (empty($this->items)) {
            $this->addItemRow();
        }
    }

    public function selectItemSuggestion(int $index, string $nama, float $hargaModal, float $hargaJual, ?int $produkId = null): void
    {
        if (! isset($this->items[$index])) return;

        $qty = (int) ($this->items[$index]['qty'] ?? 1);
        if ($qty < 1) $qty = 1;

        $this->items[$index]['produk_id'] = $produkId;
        $this->items[$index]['nama_item'] = $nama;
        $this->items[$index]['harga_modal'] = $hargaModal;
        $this->items[$index]['harga_jual'] = $hargaJual;
        $this->items[$index]['qty'] = $qty;
        $this->items[$index]['subtotal_modal'] = $hargaModal * $qty;
        $this->items[$index]['subtotal_jual'] = $hargaJual * $qty;
        $this->items[$index]['laba'] = ($hargaJual * $qty) - ($hargaModal * $qty);

        $this->productSearchQueries[$index] = $nama;
    }

    public function selectProductForItem(int $index, int $produkId, string $nama, float $hargaBeli, float $hargaJual): void
    {
        $this->selectItemSuggestion($index, $nama, $hargaBeli, $hargaJual, $produkId);
    }

    public function recalculateRow(int $index): void
    {
        if (! isset($this->items[$index])) return;

        $qty = (int) ($this->items[$index]['qty'] ?? 1);
        if ($qty < 1) $qty = 1;
        $this->items[$index]['qty'] = $qty;

        $rawModal = $this->items[$index]['harga_modal'] ?? 0;
        $rawJual = $this->items[$index]['harga_jual'] ?? 0;

        $hargaModal = is_numeric($rawModal) ? (float) $rawModal : (float) preg_replace('/[^0-9]/', '', (string) $rawModal);
        $hargaJual = is_numeric($rawJual) ? (float) $rawJual : (float) preg_replace('/[^0-9]/', '', (string) $rawJual);

        $this->items[$index]['harga_modal'] = $hargaModal;
        $this->items[$index]['harga_jual'] = $hargaJual;
        $this->items[$index]['subtotal_modal'] = $hargaModal * $qty;
        $this->items[$index]['subtotal_jual'] = $hargaJual * $qty;
        $this->items[$index]['laba'] = ($hargaJual * $qty) - ($hargaModal * $qty);
    }

    public function simpanTransaksi(): void
    {
        $this->validate([
            'formNoTransaksi' => 'required|string|max:100',
            'formTipeTransaksi' => 'required|in:penjualan,servis',
            'formTanggal' => 'required|date',
            'formNamaPelanggan' => 'required|string|max:150',
            'formMetodePembayaran' => 'required|string|max:50',
            'formStatus' => 'required|in:Lunas,BON,Belum Lunas,Batal',
            'items' => 'required|array|min:1',
            'items.*.nama_item' => 'required|string|max:200',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga_modal' => 'required|numeric|min:0',
            'items.*.harga_jual' => 'required|numeric|min:0',
        ], [
            'formNoTransaksi.required' => 'Nomor transaksi wajib diisi.',
            'formNamaPelanggan.required' => 'Nama pelanggan wajib diisi.',
            'items.required' => 'Minimal 1 item transaksi wajib diinput.',
            'items.min' => 'Minimal 1 item transaksi wajib diinput.',
            'items.*.nama_item.required' => 'Nama barang/layanan wajib diisi.',
            'items.*.qty.min' => 'Kuantiti minimal 1 unit.',
            'items.*.harga_modal.required' => 'Biaya modal wajib diisi (minimal 0).',
            'items.*.harga_jual.required' => 'Harga jual wajib diisi (minimal 0).',
        ]);

        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $cabangId = $isOwner && $activeBranchSession !== 'hq' ? (int) $activeBranchSession : ($user->cabang_id ?: 1);

        try {
            DB::beginTransaction();

            $totalModal = 0;
            $totalJual = 0;

            foreach ($this->items as $row) {
                $qty = (int) $row['qty'];
                $hModal = (float) $row['harga_modal'];
                $hJual = (float) $row['harga_jual'];

                $totalModal += ($hModal * $qty);
                $totalJual += ($hJual * $qty);
            }

            $totalLaba = $totalJual - $totalModal;
            $ppnNominal = ($totalJual * (float) $this->formPpnPersen) / 100;
            $grandTotal = $totalJual + $ppnNominal;

            $data = [
                'cabang_id' => $cabangId,
                'user_id' => Auth::id(),
                'customer_id' => $this->formCustomerType === 'langganan' ? $this->formCustomerId : null,
                'nama_pelanggan' => trim($this->formNamaPelanggan) ?: 'Pelanggan Umum',
                'no_hp' => trim($this->formNoHp) ?: null,
                'no_transaksi' => trim($this->formNoTransaksi),
                'tipe_transaksi' => $this->formTipeTransaksi,
                'tanggal' => Carbon::parse($this->formTanggal),
                'metode_pembayaran_id' => $this->formMetodePembayaranId,
                'metode_pembayaran' => $this->formMetodePembayaran,
                'ppn_id' => $this->formPpnId,
                'ppn_persen' => $this->formPpnPersen,
                'ppn_nominal' => $ppnNominal,
                'total_modal' => $totalModal,
                'total_jual' => $totalJual,
                'total_laba' => $totalLaba,
                'grand_total' => $grandTotal,
                'status' => $this->formStatus,
                'catatan' => trim($this->formCatatan) ?: null,
            ];

            if ($this->transaksiId) {
                $transaksi = TransaksiFull::findOrFail($this->transaksiId);
                $transaksi->update($data);
                $transaksi->details()->delete();
            } else {
                $transaksi = TransaksiFull::create($data);
            }

            foreach ($this->items as $row) {
                $qty = (int) $row['qty'];
                $hModal = (float) $row['harga_modal'];
                $hJual = (float) $row['harga_jual'];

                TransaksiFullDetail::create([
                    'transaksi_full_id' => $transaksi->id,
                    'produk_id' => $row['produk_id'] ?? null,
                    'nama_item' => trim($row['nama_item']),
                    'qty' => $qty,
                    'harga_modal' => $hModal,
                    'harga_jual' => $hJual,
                    'subtotal_modal' => $hModal * $qty,
                    'subtotal_jual' => $hJual * $qty,
                    'laba' => ($hJual * $qty) - ($hModal * $qty),
                ]);
            }

            DB::commit();

            $this->closeFormModal();
            session()->flash('success', "Transaksi {$transaksi->no_transaksi_frontend} berhasil disimpan!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('formError', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function updateStatusDirect(int $id, string $newStatus): void
    {
        $allowed = ['Lunas', 'BON', 'Belum Lunas', 'Batal'];
        if (! in_array($newStatus, $allowed)) return;

        $transaksi = TransaksiFull::find($id);
        if ($transaksi) {
            $transaksi->status = $newStatus;
            $transaksi->save();
            session()->flash('success', "Status transaksi {$transaksi->no_transaksi_frontend} diubah ke {$newStatus}.");
        }
    }

    public function openDetailModal(int $id): void
    {
        $this->selectedTransaksi = TransaksiFull::with(['details.produk', 'customer', 'cabang', 'user', 'ppn'])->findOrFail($id);
        $this->modalDetailOpen = true;
    }

    public function closeDetailModal(): void
    {
        $this->modalDetailOpen = false;
        $this->selectedTransaksi = null;
    }

    public function confirmDelete(int $id, string $code): void
    {
        $this->deleteId = $id;
        $this->deleteCode = $code;
        $this->modalDeleteOpen = true;
    }

    public function deleteTransaksi(): void
    {
        if (! $this->deleteId) return;

        $transaksi = TransaksiFull::find($this->deleteId);
        if ($transaksi) {
            $code = $transaksi->no_transaksi_frontend;
            $transaksi->delete();
            session()->flash('success', "Transaksi {$code} berhasil dihapus.");
        }

        $this->modalDeleteOpen = false;
        $this->deleteId = null;
        $this->deleteCode = '';
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $ppnList = MasterPpn::where('is_aktif', true)->orderBy('persen')->get();
        $metodeList = MetodePembayaran::where('is_aktif', true)->orderBy('urutan')->get();

        // Autocomplete Customer
        $customerSuggestions = [];
        if ($this->modalFormOpen && ! empty($this->formCustomerSearch) && $this->formCustomerType === 'langganan' && ! $this->formCustomerId) {
            $custQ = Customer::query();
            if (! $isHQ) {
                $targetCabang = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
                if ($targetCabang) $custQ->where('cabang_id', $targetCabang);
            }
            $pattern = '%' . strtolower($this->formCustomerSearch) . '%';
            $customerSuggestions = $custQ->where(function ($q) use ($pattern) {
                $q->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                  ->orWhereRaw('LOWER(COALESCE(no_hp, \'\')) LIKE ?', [$pattern]);
            })->take(5)->get();
        }

        // Suggestions Master Produk & Master Servis untuk Item Baris Form
        $productSuggestionsMap = [];
        if ($this->modalFormOpen) {
            foreach ($this->items as $idx => $itemRow) {
                $queryStr = trim($itemRow['nama_item'] ?? '');
                if (mb_strlen($queryStr) >= 1 && empty($itemRow['produk_id'])) {
                    $keywords = array_values(array_filter(explode(' ', strtolower($queryStr))));

                    $prodQ = Produk::query();
                    foreach ($keywords as $kw) {
                        $prodQ->where(function ($q) use ($kw) {
                            $q->whereRaw('LOWER(nama_produk) LIKE ?', ["%{$kw}%"])
                              ->orWhereRaw('LOWER(COALESCE(barcode, \'\')) LIKE ?', ["%{$kw}%"]);
                        });
                    }
                    $prodMatches = $prodQ->take(5)->get()->map(fn($p) => [
                        'type' => 'produk',
                        'id' => $p->id,
                        'nama' => $p->nama_produk,
                        'kode' => $p->barcode ?: 'PRODUK',
                        'harga_modal' => (float) $p->harga_beli,
                        'harga_jual' => (float) $p->harga_jual,
                        'badge' => 'Master Produk',
                    ]);

                    $servisQ = MasterServis::aktif();
                    foreach ($keywords as $kw) {
                        $servisQ->where(function ($q) use ($kw) {
                            $q->whereRaw('LOWER(nama_item) LIKE ?', ["%{$kw}%"])
                              ->orWhereRaw('LOWER(COALESCE(kode_item, \'\')) LIKE ?', ["%{$kw}%"]);
                        });
                    }
                    $servisMatches = $servisQ->take(5)->get()->map(fn($s) => [
                        'type' => 'servis',
                        'id' => null,
                        'nama' => $s->nama_item,
                        'kode' => $s->kode_item ?: ($s->jenis === 'sparepart' ? 'SPAREPART' : 'JASA'),
                        'harga_modal' => (float) ($s->harga_modal ?? 0),
                        'harga_jual' => (float) ($s->harga_jual ?? 0),
                        'badge' => $s->jenis === 'sparepart' ? 'Sparepart' : 'Jasa Servis',
                    ]);

                    $productSuggestionsMap[$idx] = $prodMatches->concat($servisMatches)->take(8)->values()->all();
                }
            }
        }

        // Query Utama Transaksi Harian (Hanya Tabel Utama, Fokus Hari Terpilih)
        $query = TransaksiFull::with(['customer', 'cabang', 'user', 'details']);

        // Scoping Cabang
        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            if ($targetCabangId) {
                $query->where('cabang_id', $targetCabangId);
            }
        }

        // Filter Tanggal Harian
        if (! empty($this->filterDate)) {
            $query->whereDate('tanggal', Carbon::parse($this->filterDate)->toDateString());
        }

        // Filter Tipe
        if ($this->tipeFilter !== 'semua') {
            $query->where('tipe_transaksi', $this->tipeFilter);
        }

        // Filter Status
        if ($this->statusFilter !== 'semua') {
            $query->where('status', $this->statusFilter);
        }

        // Filter Metode
        if ($this->metodeFilter !== 'semua') {
            $query->where('metode_pembayaran', $this->metodeFilter);
        }

        // Multi-Word Loose Search
        if (! empty($this->search)) {
            $cleanSearch = trim($this->search);
            $words = array_filter(explode(' ', $cleanSearch));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(no_transaksi) LIKE ?', [$pattern])
                           ->orWhereRaw('LOWER(nama_pelanggan) LIKE ?', [$pattern])
                           ->orWhereRaw('LOWER(COALESCE(no_hp, \'\')) LIKE ?', [$pattern])
                           ->orWhereHas('user', fn ($uq) => $uq->whereRaw('LOWER(name) LIKE ?', [$pattern]))
                           ->orWhereHas('details', fn ($dq) => $dq->whereRaw('LOWER(nama_item) LIKE ?', [$pattern]));
                    });
                }
            });
        }

        // Sorting
        $sortDir = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['tanggal', 'no_transaksi', 'grand_total', 'total_laba', 'status', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'tanggal';
        $query->orderBy($sortColumn, $sortDir)->orderByDesc('id');

        $totalItems = (clone $query)->count();
        $transaksiList = $query->take($this->perPage)->get();
        $hasMore = $totalItems > $this->perPage;

        $allCurrent = (clone $query)->get();
        $tableData = $allCurrent->map(function ($item) {
            return [
                'id' => $item->id,
                'no_transaksi' => $item->no_transaksi_frontend,
                'tipe_transaksi' => $item->tipe_transaksi,
                'waktu' => $item->tanggal ? $item->tanggal->format('d/m/Y H:i') : '-',
                'tanggal_group' => $item->tanggal ? $item->tanggal->translatedFormat('l, d F Y') : '-',
                'petugas' => $item->user?->name ?? 'Kasir',
                'pelanggan' => $item->nama_pelanggan,
                'no_hp' => $item->no_hp ?: '-',
                'rincian_item' => $item->details->map(fn($d) => $d->qty . 'x ' . $d->nama_item)->implode(', ') ?: 'Tanpa item',
                'metode_pembayaran' => $item->metode_pembayaran,
                'total_modal' => (float) $item->total_modal,
                'grand_total' => (float) $item->grand_total,
                'total_laba' => (float) $item->total_laba,
                'status' => $item->status,
                'keterangan' => $item->catatan ?: '-',
            ];
        })->values()->all();

        $this->dispatch('transaksi-harian-table-updated', data: $tableData);

        return view('livewire.transaksi.harian', [
            'transaksiList' => $transaksiList,
            'tableData' => $tableData,
            'totalItems' => $totalItems,
            'hasMore' => $hasMore,
            'ppnList' => $ppnList,
            'metodeList' => $metodeList,
            'customerSuggestions' => $customerSuggestions,
            'productSuggestionsMap' => $productSuggestionsMap,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ]);
    }
}
