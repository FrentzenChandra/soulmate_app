<?php

namespace App\Livewire\Servis\NonReal;

use App\Events\ServisStatusUpdated;
use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\MasterServis;
use App\Models\Produk;
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
#[Title('Servis Non Real - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'semua')]
    public string $cabangFilter = 'semua';

    public int $perPage = 15;

    // State Modal Form Tambah Servis Non Real Baru
    public bool $modalCreateOpen = false;
    public string $customTanggalMasuk = '';
    public ?int $selectedCustomerId = null;
    public string $customerSearch = 'Konsumen';
    public string $newCustomerNoHp = '';
    public string $tipeUnit = '';
    public array $createItems = [];

    // State Modal Form Edit Servis Non Real
    public bool $modalEditOpen = false;
    public ?int $editServisId = null;
    public string $editTanggalMasuk = '';
    public ?int $editCustomerId = null;
    public string $editCustomerSearch = '';
    public string $editNewCustomerNoHp = '';
    public string $editTipeUnit = '';
    public array $editItems = [];

    // State Sub-Form Tambah Item Rincian (Jasa & Sparepart)
    public string $itemType = 'jasa'; // 'jasa' | 'sparepart'
    public string $itemSearch = '';
    public ?int $selectedMasterServisId = null;
    public ?int $selectedProdukId = null;
    public string $selectedMasterServisNama = '';
    public $selectedMasterServisHarga = 0;
    public int $itemQty = 1;
    public int $itemGaransiHari = 30;

    // State Modal Detail (Lihat)
    public bool $modalDetailOpen = false;
    public ?ServisMasuk $selectedServis = null;

    // State Modal Hapus Servis
    public bool $modalDeleteOpen = false;
    public ?int $deleteServisId = null;
    public ?string $deleteServisKode = '';

    // State Modal Konfirmasi Sukses
    public bool $modalSuccessOpen = false;
    public ?ServisMasuk $lastCreatedServis = null;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole('Superadmin') && ! $user->hasRole('Admin') && ! $user->hasRole('Teknisi') && ! $user->hasRole('Kasir') && ! $user->hasRole('Bendahara'))) {
            abort(403, 'Akses modul Servis Non Real dibatasi.');
        }

        $this->sortField = 'tanggal_masuk';
        $this->sortDirection = 'desc';
    }

    #[On('echo:servis-updates,.ServisStatusUpdated')]
    #[On('echo:servis-updates,ServisStatusUpdated')]
    public function onServisStatusUpdated($payload = []): void
    {
        // Re-render automatically
    }

    public function updatingSearch(): void
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

    // =========================================================================
    // SUB-FORM RINCIAN ITEM (JASA & SPAREPART)
    // =========================================================================

    public function setItemType(string $type): void
    {
        $this->itemType = in_array($type, ['jasa', 'sparepart']) ? $type : 'jasa';
        $this->resetItemForm();
    }

    public function resetItemForm(): void
    {
        $this->itemSearch = '';
        $this->selectedMasterServisId = null;
        $this->selectedProdukId = null;
        $this->selectedMasterServisNama = '';
        $this->selectedMasterServisHarga = 0;
        $this->itemQty = 1;
        $this->itemGaransiHari = $this->itemType === 'jasa' ? 30 : 0;
    }

    public function selectMasterItem(int $id, string $nama, float $harga, int $garansiHari = 0): void
    {
        $this->selectedMasterServisId = $id;
        $this->selectedProdukId = null;
        $this->selectedMasterServisNama = $nama;
        $this->selectedMasterServisHarga = $harga;
        $this->itemGaransiHari = $garansiHari;
        $this->itemSearch = $nama;
    }

    public function selectProdukItem(int $id, string $nama, float $harga, int $garansiHari = 30): void
    {
        $this->selectedProdukId = $id;
        $this->selectedMasterServisId = null;
        $this->selectedMasterServisNama = $nama;
        $this->selectedMasterServisHarga = $harga;
        $this->itemGaransiHari = $garansiHari;
        $this->itemSearch = $nama;
    }

    public function addItemToCreate(): void
    {
        $this->validateItemInput();

        $subtotal = (float) $this->selectedMasterServisHarga * (int) $this->itemQty;

        $this->createItems[] = [
            'master_servis_id' => $this->selectedMasterServisId,
            'produk_id' => $this->selectedProdukId,
            'nama_item' => trim($this->selectedMasterServisNama),
            'jenis' => $this->selectedProdukId ? 'sparepart' : 'jasa',
            'qty' => (int) $this->itemQty,
            'harga' => (float) $this->selectedMasterServisHarga,
            'subtotal' => $subtotal,
            'garansi_hari' => (int) $this->itemGaransiHari,
        ];

        $this->resetItemForm();
    }

    public function removeItemFromCreate(int $index): void
    {
        if (isset($this->createItems[$index])) {
            unset($this->createItems[$index]);
            $this->createItems = array_values($this->createItems);
        }
    }

    public function addItemToEdit(): void
    {
        $this->validateItemInput();

        $subtotal = (float) $this->selectedMasterServisHarga * (int) $this->itemQty;

        $this->editItems[] = [
            'id' => null,
            'master_servis_id' => $this->selectedMasterServisId,
            'produk_id' => $this->selectedProdukId,
            'nama_item' => trim($this->selectedMasterServisNama),
            'jenis' => $this->selectedProdukId ? 'sparepart' : 'jasa',
            'qty' => (int) $this->itemQty,
            'harga' => (float) $this->selectedMasterServisHarga,
            'subtotal' => $subtotal,
            'garansi_hari' => (int) $this->itemGaransiHari,
        ];

        $this->resetItemForm();
    }

    public function removeItemFromEdit(int $index): void
    {
        if (isset($this->editItems[$index])) {
            unset($this->editItems[$index]);
            $this->editItems = array_values($this->editItems);
        }
    }

    private function validateItemInput(): void
    {
        if (is_string($this->selectedMasterServisHarga)) {
            $this->selectedMasterServisHarga = (float) preg_replace('/[^\d]/', '', $this->selectedMasterServisHarga);
        }

        $this->validate([
            'selectedMasterServisNama' => 'required|string|max:150',
            'selectedMasterServisHarga' => 'required|numeric|min:0',
            'itemQty' => 'required|integer|min:1',
            'itemGaransiHari' => 'required|integer|min:0|max:3650',
        ], [
            'selectedMasterServisNama.required' => 'Pilih item dari pencarian master layanan jasa atau produk.',
            'selectedMasterServisHarga.required' => 'Harga satuan wajib diisi.',
            'itemQty.min' => 'Jumlah minimal 1.',
        ]);
    }

    // =========================================================================
    // MODAL FORM TAMBAH SERVIS NON REAL BARU
    // =========================================================================

    public function openCreateModal(): void
    {
        $this->resetCreateForm();
        $this->modalCreateOpen = true;
    }

    public function closeCreateModal(): void
    {
        $this->modalCreateOpen = false;
        $this->resetCreateForm();
    }

    private function resetCreateForm(): void
    {
        $this->customTanggalMasuk = now()->format('Y-m-d\TH:i');
        $this->selectedCustomerId = null;
        $this->customerSearch = 'Konsumen';
        $this->newCustomerNoHp = '';
        $this->tipeUnit = '';
        $this->createItems = [];
        $this->resetItemForm();
        $this->resetValidation();
    }

    public function selectCustomer(int $id, string $nama, string $noHp = ''): void
    {
        $this->selectedCustomerId = $id;
        $this->customerSearch = $nama;
        $this->newCustomerNoHp = $noHp;
    }

    public function resetCustomer(): void
    {
        $this->selectedCustomerId = null;
        $this->customerSearch = 'Konsumen';
        $this->newCustomerNoHp = '';
    }

    public function selectEditCustomer(int $id, string $nama, string $noHp = ''): void
    {
        $this->editCustomerId = $id;
        $this->editCustomerSearch = $nama;
        $this->editNewCustomerNoHp = $noHp;
    }

    public function resetEditCustomer(): void
    {
        $this->editCustomerId = null;
        $this->editCustomerSearch = '';
        $this->editNewCustomerNoHp = '';
    }

    public function simpanServisMasuk(): void
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $targetCabangId = $isHQ
            ? ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter) ? (int) $this->cabangFilter : Cabang::where('status', 'aktif')->first()?->id)
            : ($isOwner ? (int) $activeBranchSession : (int) $user->cabang_id);

        $cabang = Cabang::find($targetCabangId);
        if (! $cabang) {
            $this->addError('general', 'Cabang operasional tidak valid atau belum dipilih.');
            return;
        }

        $rules = [
            'customTanggalMasuk' => 'required|date',
            'customerSearch' => 'required|string|min:2|max:100',
            'tipeUnit' => 'required|string|max:150',
            'createItems' => 'required|array|min:1',
        ];

        $this->validate($rules, [
            'customTanggalMasuk.required' => 'Waktu faktur servis wajib ditentukan.',
            'customerSearch.required' => 'Nama pelanggan wajib diisi.',
            'customerSearch.min' => 'Nama pelanggan minimal 2 karakter.',
            'tipeUnit.required' => 'Tipe / model perangkat servis wajib diisi.',
            'createItems.min' => 'Wajib menambahkan minimal 1 rincian layanan jasa atau sparepart.',
        ]);

        try {
            DB::beginTransaction();

            $finalCustomerId = null;
            if ($this->selectedCustomerId) {
                $finalCustomerId = $this->selectedCustomerId;
            } else {
                $customerName = trim($this->customerSearch);
                $cleanHp = preg_replace('/[^0-9]/', '', $this->newCustomerNoHp);
                $existing = Customer::where('cabang_id', $cabang->id)
                    ->whereRaw('LOWER(nama) = ?', [strtolower($customerName)])
                    ->first();

                if ($existing) {
                    $finalCustomerId = $existing->id;
                    if ($cleanHp && empty($existing->no_hp)) {
                        $existing->update(['no_hp' => $cleanHp]);
                    }
                } else {
                    $customer = Customer::create([
                        'cabang_id' => $cabang->id,
                        'nama' => $customerName,
                        'no_hp' => $cleanHp ?: '-',
                        'created_by' => Auth::id(),
                    ]);
                    $finalCustomerId = $customer->id;
                }
            }

            $tanggalMasuk = Carbon::parse($this->customTanggalMasuk);
            $tanggalSelesai = clone $tanggalMasuk; // Waktu selesai servis disamakan dengan waktu faktur

            // Generate Nomor Servis Atomik Harian: INV-SV-[CABANG]-[YYMMDD]-[00001]
            $dateCode = $tanggalMasuk->format('ymd');
            $kodeCabangClean = str_replace('-', '', $cabang->kode_cabang);
            $prefix = "INV-SV-{$kodeCabangClean}-{$dateCode}-";

            $lastServis = ServisMasuk::where('cabang_id', $cabang->id)
                ->where('no_servis', 'LIKE', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $nextSeq = 1;
            if ($lastServis && preg_match('/-(\d+)$/', $lastServis->no_servis, $m)) {
                $nextSeq = (int) $m[1] + 1;
            }

            $noUrut = str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT);
            $noServis = "{$prefix}{$noUrut}";

            $totalBiaya = (float) array_sum(array_column($this->createItems, 'subtotal'));
            $maxGaransi = (int) max(array_column($this->createItems, 'garansi_hari') ?: [0]);
            $garansiSampai = $maxGaransi > 0 ? (clone $tanggalSelesai)->addDays($maxGaransi) : null;

            $servis = ServisMasuk::create([
                'no_servis' => $noServis,
                'cabang_id' => $cabang->id,
                'customer_id' => $finalCustomerId,
                'teknisi_id' => Auth::id(),
                'kasir_id' => Auth::id(),
                'created_by' => Auth::id(),
                'kategori_perangkat' => 'laptop',
                'tipe_unit' => trim($this->tipeUnit),
                'serial_number' => null,
                'kelengkapan' => 'Hanya Unit',
                'keluhan' => 'Servis Non Real',
                'kondisi_barang' => null,
                'status' => 'selesai',
                'is_real' => false,
                'is_cetak_nota' => false,
                'tanggal_masuk' => $tanggalMasuk,
                'tanggal_selesai' => $tanggalSelesai,
                'tanggal_ambil' => $tanggalSelesai,
                'estimasi_biaya' => $totalBiaya,
                'total_biaya' => $totalBiaya,
                'metode_pembayaran' => 'Cash',
                'bayar' => $totalBiaya,
                'kembalian' => 0,
                'garansi_hari' => $maxGaransi,
                'garansi_sampai' => $garansiSampai,
                'catatan' => null,
            ]);

            foreach ($this->createItems as $item) {
                ServisDetail::create([
                    'servis_id' => $servis->id,
                    'master_servis_id' => $item['master_servis_id'] ?? null,
                    'produk_id' => $item['produk_id'] ?? null,
                    'nama_item' => $item['nama_item'],
                    'jenis' => $item['jenis'],
                    'qty' => (int) $item['qty'],
                    'harga' => (float) $item['harga'],
                    'subtotal' => (float) $item['subtotal'],
                    'garansi_hari' => (int) $item['garansi_hari'],
                ]);
            }

            ServisStatusLog::create([
                'servis_id' => $servis->id,
                'user_id' => Auth::id(),
                'status' => 'selesai',
                'tanggal' => $tanggalSelesai,
                'catatan' => 'Penerimaan dan penyelesaian transaksi [Servis Non-Real].',
            ]);

            DB::commit();

            try {
                broadcast(new ServisStatusUpdated($servis->id, 'selesai', $servis->no_servis_frontend, $servis->cabang_id));
            } catch (\Throwable $th) {
                // Ignore broadcast error
            }

            $this->modalCreateOpen = false;
            $this->lastCreatedServis = $servis;
            $this->modalSuccessOpen = true;

            $this->resetCreateForm();
            session()->flash('success', "Servis non real {$servis->no_servis_frontend} berhasil disimpan!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Gagal menyimpan data servis: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // MODAL FORM EDIT / UPDATE SERVIS NON REAL
    // =========================================================================

    public function openEditModal(int $id): void
    {
        $servis = ServisMasuk::with(['customer', 'details'])->findOrFail($id);
        $this->editServisId = $servis->id;
        $this->editTanggalMasuk = $servis->tanggal_masuk ? $servis->tanggal_masuk->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
        $this->editCustomerId = $servis->customer_id;
        $this->editCustomerSearch = $servis->customer?->nama ?? '';
        $this->editNewCustomerNoHp = $servis->customer?->no_hp ?? '';
        $this->editTipeUnit = $servis->tipe_unit;

        $this->editItems = [];
        foreach ($servis->details as $det) {
            $this->editItems[] = [
                'id' => $det->id,
                'master_servis_id' => $det->master_servis_id,
                'produk_id' => $det->produk_id,
                'nama_item' => $det->nama_item,
                'jenis' => $det->jenis,
                'qty' => (int) $det->qty,
                'harga' => (float) $det->harga,
                'subtotal' => (float) $det->subtotal,
                'garansi_hari' => (int) $det->garansi_hari,
            ];
        }

        $this->resetItemForm();
        $this->modalDetailOpen = false;
        $this->modalEditOpen = true;
    }

    public function closeEditModal(): void
    {
        $this->modalEditOpen = false;
        $this->editServisId = null;
        $this->editItems = [];
        $this->resetItemForm();
    }

    public function simpanEditServis(): void
    {
        if (! $this->editServisId) return;

        $servis = ServisMasuk::findOrFail($this->editServisId);

        $rules = [
            'editTanggalMasuk' => 'required|date',
            'editCustomerSearch' => 'required|string|min:2|max:100',
            'editTipeUnit' => 'required|string|max:150',
            'editItems' => 'required|array|min:1',
        ];

        $this->validate($rules, [
            'editTanggalMasuk.required' => 'Waktu faktur servis wajib ditentukan.',
            'editCustomerSearch.required' => 'Nama pelanggan wajib diisi.',
            'editCustomerSearch.min' => 'Nama pelanggan minimal 2 karakter.',
            'editTipeUnit.required' => 'Tipe perangkat wajib diisi.',
            'editItems.min' => 'Wajib memiliki minimal 1 rincian servis.',
        ]);

        try {
            DB::beginTransaction();

            $finalCustomerId = null;
            if ($this->editCustomerId) {
                $finalCustomerId = $this->editCustomerId;
            } else {
                $customerName = trim($this->editCustomerSearch);
                $cleanHp = preg_replace('/[^0-9]/', '', $this->editNewCustomerNoHp);
                $existing = Customer::where('cabang_id', $servis->cabang_id)
                    ->whereRaw('LOWER(nama) = ?', [strtolower($customerName)])
                    ->first();

                if ($existing) {
                    $finalCustomerId = $existing->id;
                    if ($cleanHp && empty($existing->no_hp)) {
                        $existing->update(['no_hp' => $cleanHp]);
                    }
                } else {
                    $customer = Customer::create([
                        'cabang_id' => $servis->cabang_id,
                        'nama' => $customerName,
                        'no_hp' => $cleanHp ?: '-',
                        'created_by' => Auth::id(),
                    ]);
                    $finalCustomerId = $customer->id;
                }
            }

            $tanggalMasuk = Carbon::parse($this->editTanggalMasuk);
            $tanggalSelesai = clone $tanggalMasuk;

            $totalBiaya = (float) array_sum(array_column($this->editItems, 'subtotal'));
            $maxGaransi = (int) max(array_column($this->editItems, 'garansi_hari') ?: [0]);
            $garansiSampai = $maxGaransi > 0 ? (clone $tanggalSelesai)->addDays($maxGaransi) : null;

            $servis->update([
                'customer_id' => $finalCustomerId,
                'tipe_unit' => trim($this->editTipeUnit),
                'tanggal_masuk' => $tanggalMasuk,
                'tanggal_selesai' => $tanggalSelesai,
                'tanggal_ambil' => $tanggalSelesai,
                'estimasi_biaya' => $totalBiaya,
                'total_biaya' => $totalBiaya,
                'bayar' => $totalBiaya,
                'garansi_hari' => $maxGaransi,
                'garansi_sampai' => $garansiSampai,
            ]);

            // Sinkronkan rincian servis
            $servis->details()->delete();
            foreach ($this->editItems as $item) {
                ServisDetail::create([
                    'servis_id' => $servis->id,
                    'master_servis_id' => $item['master_servis_id'] ?? null,
                    'produk_id' => $item['produk_id'] ?? null,
                    'nama_item' => $item['nama_item'],
                    'jenis' => $item['jenis'],
                    'qty' => (int) $item['qty'],
                    'harga' => (float) $item['harga'],
                    'subtotal' => (float) $item['subtotal'],
                    'garansi_hari' => (int) $item['garansi_hari'],
                ]);
            }

            DB::commit();

            $this->modalEditOpen = false;
            $this->editServisId = null;
            $this->editItems = [];
            session()->flash('success', "Data servis non real {$servis->no_servis_frontend} berhasil diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Gagal memperbarui servis: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // MODAL DETAIL & HAPUS
    // =========================================================================

    public function openDetailModal(int $id): void
    {
        $this->selectedServis = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'details.produk', 'details.masterServis'])->findOrFail($id);
        $this->modalDetailOpen = true;
    }

    public function closeDetailModal(): void
    {
        $this->modalDetailOpen = false;
        $this->selectedServis = null;
    }

    public function openDeleteModal(int $id, string $kode = ''): void
    {
        $this->deleteServisId = $id;
        $this->deleteServisKode = $kode;
        $this->modalDetailOpen = false;
        $this->modalDeleteOpen = true;
    }

    public function closeDeleteModal(): void
    {
        $this->modalDeleteOpen = false;
        $this->deleteServisId = null;
        $this->deleteServisKode = '';
    }

    public function konfirmasiHapusServis(): void
    {
        if (! $this->deleteServisId) return;

        $user = Auth::user();
        if (! $user->isOwner() && ! $user->hasRole('Superadmin') && ! $user->hasRole('Admin')) {
            abort(403, 'Akses hapus tiket servis dibatasi.');
        }

        try {
            DB::beginTransaction();

            $servis = ServisMasuk::with('details')->findOrFail($this->deleteServisId);
            $kode = $servis->no_servis_frontend;

            $servis->details()->delete();
            $servis->statusLogs()->delete();
            $servis->delete();

            DB::commit();

            $this->modalDeleteOpen = false;
            $this->deleteServisId = null;
            session()->flash('success', "Servis {$kode} berhasil dihapus dari sistem.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menghapus servis: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $cabangs = $isHQ ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();

        // Autocomplete Pelanggan Create / Edit
        $customerSuggestions = [];
        $activeCustomerSearch = $this->modalEditOpen ? $this->editCustomerSearch : $this->customerSearch;
        $activeCustomerId = $this->modalEditOpen ? $this->editCustomerId : $this->selectedCustomerId;

        if (! empty(trim($activeCustomerSearch)) && strlen(trim($activeCustomerSearch)) >= 1 && ! $activeCustomerId) {
            $cSearch = trim($activeCustomerSearch);
            $cWords = array_filter(explode(' ', $cSearch));
            $customerSuggestions = Customer::query()
                ->where(function ($q) use ($cWords) {
                    foreach ($cWords as $w) {
                        $p = '%' . strtolower($w) . '%';
                        $q->where(function ($sq) use ($p) {
                            $sq->whereRaw('LOWER(nama) LIKE ?', [$p])
                               ->orWhereRaw('LOWER(COALESCE(no_hp, \'\')) LIKE ?', [$p]);
                        });
                    }
                })
                ->limit(8)
                ->get();
        }

        // Autocomplete Item MasterServis & Produk
        $masterServisSuggestions = [];
        $produkSuggestions = [];
        if (($this->modalCreateOpen || $this->modalEditOpen) && ! empty($this->itemSearch) && ! $this->selectedMasterServisId && ! $this->selectedProdukId) {
            $pattern = '%' . strtolower(trim($this->itemSearch)) . '%';

            if ($this->itemType === 'jasa') {
                $masterServisSuggestions = MasterServis::where('jenis', 'jasa')
                    ->where('is_aktif', true)
                    ->where(function ($q) use ($pattern) {
                        $q->whereRaw('LOWER(nama_item) LIKE ?', [$pattern])
                          ->orWhereRaw('LOWER(COALESCE(kode_item, \'\')) LIKE ?', [$pattern]);
                    })
                    ->take(6)
                    ->get();
            } else {
                $produkSuggestions = Produk::where(function ($q) use ($pattern) {
                        $q->whereRaw('LOWER(nama_produk) LIKE ?', [$pattern])
                          ->orWhereRaw('LOWER(COALESCE(barcode, \'\')) LIKE ?', [$pattern]);
                    })
                    ->take(6)
                    ->get();
            }
        }

        // Query Utama Servis Non Real (is_real = false)
        $query = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'details.produk', 'details.masterServis'])
            ->where('is_real', false);

        // Scoping Cabang
        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            if ($targetCabangId) {
                $query->where('cabang_id', $targetCabangId);
            }
        } elseif ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)) {
            $query->where('cabang_id', (int) $this->cabangFilter);
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
                            ->orWhereHas('customer', function ($cq) use ($pattern) {
                                $cq->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(COALESCE(no_hp, \'\')) LIKE ?', [$pattern]);
                            });
                    });
                }
            });
        }

        // Metrik Ringkasan Non Real
        $metricsBase = clone $query;
        $allCurrent = $metricsBase->get();

        $totalUnitMasuk = $allCurrent->count();
        $totalPendapatan = (float) $allCurrent->sum('total_biaya');
        $allServisIds = $allCurrent->pluck('id');
        $totalJasa = $allServisIds->isNotEmpty() ? ServisDetail::whereIn('servis_id', $allServisIds)->where('jenis', 'jasa')->count() : 0;
        $totalSparepart = $allServisIds->isNotEmpty() ? ServisDetail::whereIn('servis_id', $allServisIds)->where('jenis', 'sparepart')->count() : 0;

        // Sorting
        $sortDir = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['tanggal_masuk', 'no_servis', 'tipe_unit', 'total_biaya', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'tanggal_masuk';
        $query->orderBy($sortColumn, $sortDir);

        $totalItems = (clone $query)->count();
        $servisList = $query->take($this->perPage)->get();
        $hasMore = $totalItems > $this->perPage;

        return view('livewire.servis.non-real.index', [
            'servisList' => $servisList,
            'totalItems' => $totalItems,
            'hasMore' => $hasMore,
            'totalUnitMasuk' => $totalUnitMasuk,
            'totalPendapatan' => $totalPendapatan,
            'totalJasa' => $totalJasa,
            'totalSparepart' => $totalSparepart,
            'cabangs' => $cabangs,
            'customerSuggestions' => $customerSuggestions,
            'masterServisSuggestions' => $masterServisSuggestions,
            'produkSuggestions' => $produkSuggestions,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ]);
    }
}
