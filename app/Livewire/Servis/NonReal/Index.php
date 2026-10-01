<?php

namespace App\Livewire\Servis\NonReal;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\MasterServis;
use App\Models\Produk;
use App\Models\ServisDetail;
use App\Models\ServisMasuk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
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
    public string $kategoriFilter = 'semua';

    #[Url(except: 'semua')]
    public string $cabangFilter = 'semua';

    public int $perPage = 15;

    // State Modal Form Tambah Servis Non Real Baru
    public bool $modalCreateOpen = false;
    public string $customTanggalMasuk = '';
    public string $customerType = 'langganan'; // 'langganan' | 'baru'
    public ?int $selectedCustomerId = null;
    public string $customerSearch = '';
    public string $newCustomerNama = '';
    public string $newCustomerNoHp = '';
    public string $tipeUnit = '';
    public array $createItems = [];

    // State Modal Form Edit Servis Non Real
    public bool $modalEditOpen = false;
    public ?int $editServisId = null;
    public string $editTanggalMasuk = '';
    public string $editCustomerType = 'langganan';
    public ?int $editCustomerId = null;
    public string $editCustomerSearch = '';
    public string $editNewCustomerNama = '';
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

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole('Superadmin') && ! $user->hasRole('Admin') && ! $user->hasRole('Teknisi') && ! $user->hasRole('Kasir'))) {
            abort(403, 'Akses modul Servis Non Real dibatasi.');
        }

        $this->sortField = 'tanggal_masuk';
        $this->sortDirection = 'desc';
    }

    public function updatingSearch(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingKategoriFilter(): void
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
        $this->selectedMasterServisHarga = (int) $harga;
        $this->itemGaransiHari = $garansiHari;
        $this->itemSearch = $nama;
    }

    public function selectProdukItem(int $id, string $nama, float $harga, int $garansiHari = 30): void
    {
        $this->selectedProdukId = $id;
        $this->selectedMasterServisId = null;
        $this->selectedMasterServisNama = $nama;
        $this->selectedMasterServisHarga = (int) $harga;
        $this->itemGaransiHari = $garansiHari;
        $this->itemSearch = $nama;
    }

    public function addItemToCreate(): void
    {
        $this->validateItemInput();

        $subtotal = (float) $this->selectedMasterServisHarga * (int) $this->itemQty;

        $this->createItems[] = [
            'tipe' => $this->itemType,
            'master_servis_id' => $this->selectedMasterServisId,
            'produk_id' => $this->selectedProdukId,
            'nama_item' => $this->selectedMasterServisNama,
            'harga_satuan' => (float) $this->selectedMasterServisHarga,
            'qty' => (int) $this->itemQty,
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
            'tipe' => $this->itemType,
            'master_servis_id' => $this->selectedMasterServisId,
            'produk_id' => $this->selectedProdukId,
            'nama_item' => $this->selectedMasterServisNama,
            'harga_satuan' => (float) $this->selectedMasterServisHarga,
            'qty' => (int) $this->itemQty,
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
        if (! $this->selectedMasterServisId && ! $this->selectedProdukId) {
            $this->addError('itemSearch', 'Pilih item jasa atau sparepart dari daftar pencarian.');
            return;
        }

        $this->validate([
            'selectedMasterServisNama' => 'required|string|max:200',
            'selectedMasterServisHarga' => 'required|numeric|min:0',
            'itemQty' => 'required|integer|min:1|max:999',
            'itemGaransiHari' => 'required|integer|min:0|max:3650',
        ], [
            'selectedMasterServisNama.required' => 'Nama item wajib terisi.',
            'selectedMasterServisHarga.required' => 'Harga satuan wajib diisi.',
            'itemQty.min' => 'Jumlah minimal 1.',
            'itemGaransiHari.min' => 'Garansi minimal 0 hari.',
        ]);
    }

    // =========================================================================
    // MODAL TAMBAH SERVIS NON REAL
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
        $this->customerType = 'langganan';
        $this->selectedCustomerId = null;
        $this->customerSearch = '';
        $this->newCustomerNama = '';
        $this->newCustomerNoHp = '';
        $this->tipeUnit = '';
        $this->createItems = [];
        $this->resetItemForm();
        $this->resetValidation();
    }

    public function setCustomerType(string $type): void
    {
        $this->customerType = $type;
        $this->selectedCustomerId = null;
        $this->customerSearch = '';
        $this->newCustomerNama = '';
        $this->newCustomerNoHp = '';
    }

    public function selectCustomer(int $id, string $nama, string $noHp = ''): void
    {
        $this->selectedCustomerId = $id;
        $this->customerSearch = $nama;
    }

    public function resetCustomer(): void
    {
        $this->selectedCustomerId = null;
        $this->customerSearch = '';
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
            'tipeUnit' => 'required|string|max:150',
            'customTanggalMasuk' => 'required|date',
        ];

        if ($this->customerType === 'langganan') {
            $rules['selectedCustomerId'] = 'required|exists:customers,id';
        } else {
            $rules['newCustomerNama'] = 'required|string|min:2|max:100';
            $rules['newCustomerNoHp'] = 'required|string|min:8|max:20';
        }

        $this->validate($rules, [
            'tipeUnit.required' => 'Tipe unit perangkat wajib diisi.',
            'customTanggalMasuk.required' => 'Waktu faktur servis wajib diisi.',
            'selectedCustomerId.required' => 'Pilih pelanggan dari daftar langganan.',
            'newCustomerNama.required' => 'Nama lengkap pelanggan baru wajib diisi.',
            'newCustomerNoHp.required' => 'Nomor WhatsApp pelanggan baru wajib diisi.',
        ]);

        try {
            DB::beginTransaction();

            $tanggalMasukParsed = Carbon::parse($this->customTanggalMasuk);
            $waktuSelesaiParsed = $tanggalMasukParsed->copy();

            // 1. Resolve Customer ID
            if ($this->customerType === 'langganan') {
                $customerId = $this->selectedCustomerId;
            } else {
                $cleanHp = preg_replace('/[^0-9]/', '', $this->newCustomerNoHp);
                $customer = Customer::create([
                    'cabang_id' => $cabang->id,
                    'nama' => trim($this->newCustomerNama),
                    'no_hp' => $cleanHp,
                    'tipe' => 'langganan',
                ]);
                $customerId = $customer->id;
            }

            // 2. Generate Nomor Faktur Servis
            $dateCode = $tanggalMasukParsed->format('ymd');
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

            // 3. Hitung Finansial & Garansi
            $totalBiaya = (float) collect($this->createItems)->sum('subtotal');
            $maxGaransiHari = ! empty($this->createItems) ? (int) collect($this->createItems)->max('garansi_hari') : 30;
            $garansiSampai = $waktuSelesaiParsed->copy()->addDays($maxGaransiHari)->toDateString();

            // 4. Create ServisMasuk Non-Real
            $servis = ServisMasuk::create([
                'no_servis' => $noServis,
                'cabang_id' => $cabang->id,
                'customer_id' => $customerId,
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
                'tanggal_masuk' => $tanggalMasukParsed,
                'tanggal_selesai' => $waktuSelesaiParsed,
                'tanggal_ambil' => $waktuSelesaiParsed,
                'estimasi_biaya' => $totalBiaya,
                'total_biaya' => $totalBiaya,
                'metode_pembayaran' => 'Cash',
                'bayar' => $totalBiaya,
                'kembalian' => 0,
                'garansi_hari' => $maxGaransiHari,
                'garansi_sampai' => $garansiSampai,
                'catatan' => null,
            ]);

            // 5. Simpan Rincian Details (Tanpa potong stok)
            foreach ($this->createItems as $item) {
                ServisDetail::create([
                    'servis_id' => $servis->id,
                    'master_servis_id' => $item['master_servis_id'],
                    'produk_id' => $item['produk_id'],
                    'nama_item' => $item['nama_item'],
                    'jenis' => $item['tipe'],
                    'harga' => $item['harga_satuan'],
                    'qty' => $item['qty'],
                    'subtotal' => $item['subtotal'],
                    'garansi_hari' => $item['garansi_hari'],
                    'garansi_sampai' => $waktuSelesaiParsed->copy()->addDays($item['garansi_hari'])->toDateString(),
                ]);
            }

            DB::commit();

            $this->closeCreateModal();
            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => "Faktur Servis {$noServis} berhasil disimpan."]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Gagal menyimpan transaksi servis non real: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // MODAL EDIT / UPDATE SERVIS NON REAL
    // =========================================================================

    public function openEditModal(int $id): void
    {
        $servis = ServisMasuk::with(['customer', 'details'])->findOrFail($id);

        $this->editServisId = $servis->id;
        $this->editTanggalMasuk = $servis->tanggal_masuk ? $servis->tanggal_masuk->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
        $this->editCustomerType = 'langganan';
        $this->editCustomerId = $servis->customer_id;
        $this->editCustomerSearch = $servis->customer?->nama ?? '';
        $this->editNewCustomerNama = '';
        $this->editNewCustomerNoHp = '';
        $this->editTipeUnit = $servis->tipe_unit;

        $this->editItems = [];
        foreach ($servis->details as $d) {
            $this->editItems[] = [
                'id' => $d->id,
                'tipe' => $d->jenis ?? ($d->master_servis_id ? 'jasa' : 'sparepart'),
                'master_servis_id' => $d->master_servis_id,
                'produk_id' => $d->produk_id,
                'nama_item' => $d->nama_item,
                'harga_satuan' => (float) ($d->harga ?? ($d->qty > 0 ? $d->subtotal / $d->qty : $d->subtotal)),
                'qty' => (int) $d->qty,
                'subtotal' => (float) $d->subtotal,
                'garansi_hari' => (int) ($d->garansi_hari ?? 0),
            ];
        }

        $this->resetItemForm();
        $this->resetValidation();
        $this->modalEditOpen = true;
    }

    public function closeEditModal(): void
    {
        $this->modalEditOpen = false;
        $this->editServisId = null;
        $this->editItems = [];
        $this->resetItemForm();
        $this->resetValidation();
    }

    public function setEditCustomerType(string $type): void
    {
        $this->editCustomerType = $type;
        $this->editCustomerId = null;
        $this->editCustomerSearch = '';
        $this->editNewCustomerNama = '';
        $this->editNewCustomerNoHp = '';
    }

    public function selectEditCustomer(int $id, string $nama, string $noHp = ''): void
    {
        $this->editCustomerId = $id;
        $this->editCustomerSearch = $nama;
    }

    public function resetEditCustomer(): void
    {
        $this->editCustomerId = null;
        $this->editCustomerSearch = '';
    }

    public function simpanEditServis(): void
    {
        $servis = ServisMasuk::findOrFail($this->editServisId);

        $rules = [
            'editTipeUnit' => 'required|string|max:150',
            'editTanggalMasuk' => 'required|date',
        ];

        if ($this->editCustomerType === 'langganan') {
            $rules['editCustomerId'] = 'required|exists:customers,id';
        } else {
            $rules['editNewCustomerNama'] = 'required|string|min:2|max:100';
            $rules['editNewCustomerNoHp'] = 'required|string|min:8|max:20';
        }

        $this->validate($rules, [
            'editTipeUnit.required' => 'Tipe unit perangkat wajib diisi.',
            'editTanggalMasuk.required' => 'Waktu faktur servis wajib diisi.',
            'editCustomerId.required' => 'Pilih pelanggan dari daftar langganan.',
            'editNewCustomerNama.required' => 'Nama lengkap pelanggan baru wajib diisi.',
            'editNewCustomerNoHp.required' => 'Nomor WhatsApp pelanggan baru wajib diisi.',
        ]);

        try {
            DB::beginTransaction();

            $tanggalMasukParsed = Carbon::parse($this->editTanggalMasuk);
            $waktuSelesaiParsed = $tanggalMasukParsed->copy();

            // 1. Resolve Customer ID
            if ($this->editCustomerType === 'langganan') {
                $customerId = $this->editCustomerId;
            } else {
                $cleanHp = preg_replace('/[^0-9]/', '', $this->editNewCustomerNoHp);
                $customer = Customer::create([
                    'cabang_id' => $servis->cabang_id,
                    'nama' => trim($this->editNewCustomerNama),
                    'no_hp' => $cleanHp,
                    'tipe' => 'langganan',
                ]);
                $customerId = $customer->id;
            }

            // 2. Hitung Finansial & Garansi
            $totalBiaya = (float) collect($this->editItems)->sum('subtotal');
            $maxGaransiHari = ! empty($this->editItems) ? (int) collect($this->editItems)->max('garansi_hari') : 30;
            $garansiSampai = $waktuSelesaiParsed->copy()->addDays($maxGaransiHari)->toDateString();

            // 3. Update ServisMasuk
            $servis->update([
                'customer_id' => $customerId,
                'tipe_unit' => trim($this->editTipeUnit),
                'tanggal_masuk' => $tanggalMasukParsed,
                'tanggal_selesai' => $waktuSelesaiParsed,
                'tanggal_ambil' => $waktuSelesaiParsed,
                'estimasi_biaya' => $totalBiaya,
                'total_biaya' => $totalBiaya,
                'bayar' => $totalBiaya,
                'garansi_hari' => $maxGaransiHari,
                'garansi_sampai' => $garansiSampai,
            ]);

            // 4. Sync ServisDetail (Hapus & Insert Ulang)
            ServisDetail::where('servis_id', $servis->id)->delete();

            foreach ($this->editItems as $item) {
                ServisDetail::create([
                    'servis_id' => $servis->id,
                    'master_servis_id' => $item['master_servis_id'],
                    'produk_id' => $item['produk_id'],
                    'nama_item' => $item['nama_item'],
                    'jenis' => $item['tipe'],
                    'harga' => $item['harga_satuan'],
                    'qty' => $item['qty'],
                    'subtotal' => $item['subtotal'],
                    'garansi_hari' => $item['garansi_hari'],
                    'garansi_sampai' => $waktuSelesaiParsed->copy()->addDays($item['garansi_hari'])->toDateString(),
                ]);
            }

            DB::commit();

            $this->closeEditModal();
            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => "Data Servis {$servis->no_servis_frontend} berhasil diperbarui."]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Gagal memperbarui transaksi servis non real: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // DETAIL & HAPUS
    // =========================================================================

    public function openDetailModal(int $id): void
    {
        $this->selectedServis = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'details.masterServis', 'details.produk'])->findOrFail($id);
        $this->modalDetailOpen = true;
    }

    public function closeDetailModal(): void
    {
        $this->modalDetailOpen = false;
        $this->selectedServis = null;
    }

    public function confirmDelete(int $id): void
    {
        $servis = ServisMasuk::findOrFail($id);
        $this->deleteServisId = $servis->id;
        $this->deleteServisKode = $servis->no_servis_frontend;
        $this->modalDeleteOpen = true;
    }

    public function deleteServis(): void
    {
        if (! $this->deleteServisId) {
            return;
        }

        try {
            DB::beginTransaction();

            $servis = ServisMasuk::findOrFail($this->deleteServisId);
            ServisDetail::where('servis_id', $servis->id)->delete();
            $servis->delete();

            DB::commit();

            $this->modalDeleteOpen = false;
            $this->deleteServisId = null;
            $this->deleteServisKode = '';
            $this->dispatch('swal:success', ['title' => 'Dihapus!', 'text' => 'Data Servis Non Real berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Gagal menghapus servis: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $cabangs = $isHQ ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();

        // Autocomplete Customer untuk modal create / edit
        $customerSuggestions = [];
        $activeCustomerSearch = $this->modalEditOpen ? $this->editCustomerSearch : $this->customerSearch;
        $activeCustomerId = $this->modalEditOpen ? $this->editCustomerId : $this->selectedCustomerId;

        if (! empty(trim($activeCustomerSearch)) && ! $activeCustomerId) {
            $custQuery = Customer::query();
            if (! $isHQ) {
                $targetCabang = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
                if ($targetCabang) $custQuery->where('cabang_id', $targetCabang);
            }
            $cSearch = trim($activeCustomerSearch);
            $cWords = array_filter(explode(' ', $cSearch));
            $customerSuggestions = $custQuery->where(function ($q) use ($cWords) {
                foreach ($cWords as $w) {
                    $p = '%' . strtolower($w) . '%';
                    $q->where(function ($sq) use ($p) {
                        $sq->whereRaw('LOWER(nama) LIKE ?', [$p])
                           ->orWhereRaw('LOWER(COALESCE(no_hp, \'\')) LIKE ?', [$p]);
                    });
                }
            })->take(5)->get();
        }

        // Suggestions for items in Create & Edit modal
        $masterServisSuggestions = [];
        $produkSuggestions = [];
        if (($this->modalCreateOpen || $this->modalEditOpen) && ! empty($this->itemSearch) && ! $this->selectedMasterServisId && ! $this->selectedProdukId) {
            $pattern = '%' . strtolower(trim($this->itemSearch)) . '%';
            $targetCabangId = $user->isOwner() ? (int) $activeBranchSession : (int) $user->cabang_id;

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
                    ->with(['stoks' => function ($sq) use ($targetCabangId) {
                        if ($targetCabangId) {
                            $sq->where('cabang_id', $targetCabangId);
                        }
                    }])
                    ->take(6)
                    ->get();
            }
        }

        // Query Utama Servis Non Real (is_real = false)
        $query = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'details'])->where('is_real', false);

        // Scoping Cabang
        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            if ($targetCabangId) {
                $query->where('cabang_id', $targetCabangId);
            }
        } elseif ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)) {
            $query->where('cabang_id', (int) $this->cabangFilter);
        }

        // Filter Kategori Perangkat
        if ($this->kategoriFilter !== 'semua') {
            $query->where('kategori_perangkat', $this->kategoriFilter);
        }

        // Multi-word Loose Search
        if (! empty(trim($this->search))) {
            $words = array_filter(explode(' ', trim($this->search)));
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

        // Clone query untuk ringkasan metrik statistik
        $summaryQuery = clone $query;
        $totalServisNonReal = $summaryQuery->count();
        $totalPendapatanNonReal = (float) $summaryQuery->sum('total_biaya');

        // Total Item Jasa & Sparepart
        $servisIds = (clone $query)->pluck('id');
        $totalItemJasa = ServisDetail::whereIn('servis_id', $servisIds)->where('jenis', 'jasa')->count();
        $totalItemSparepart = ServisDetail::whereIn('servis_id', $servisIds)->where('jenis', 'sparepart')->count();

        // Sorting & Pagination
        $servisList = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.servis.non-real.index', [
            'servisList' => $servisList,
            'cabangs' => $cabangs,
            'customerSuggestions' => $customerSuggestions,
            'masterServisSuggestions' => $masterServisSuggestions,
            'produkSuggestions' => $produkSuggestions,
            'totalServisNonReal' => $totalServisNonReal,
            'totalPendapatanNonReal' => $totalPendapatanNonReal,
            'totalItemJasa' => $totalItemJasa,
            'totalItemSparepart' => $totalItemSparepart,
        ]);
    }
}
