<?php

namespace App\Livewire\Servis\NonReal;

use App\Events\ServisStatusUpdated;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\MasterServis;
use App\Models\MasterServisStok;
use App\Models\Produk;
use App\Models\ServisDetail;
use App\Models\ServisMasuk;
use App\Models\ServisStatusLog;
use App\Models\Stok;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Servis Non Real - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination;

    // Filters & Search
    public string $search = '';
    public string $statusFilter = 'semua';
    public string $kategoriFilter = 'semua';
    public string $cabangFilter = 'semua';
    public string $sortField = 'tanggal_masuk';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    // State Modal Input Penerimaan Servis Non Real Baru
    public bool $modalCreateOpen = false;
    public string $customTanggalMasuk = '';
    public string $customerType = 'langganan'; // 'langganan' | 'baru'
    public ?int $selectedCustomerId = null;
    public string $customerSearch = '';
    public string $newCustomerNama = '';
    public string $newCustomerNoHp = '';
    public string $kategoriPerangkat = 'laptop';
    public string $tipeUnit = '';
    public array $kelengkapanChips = ['Unit'];
    public string $customKelengkapan = '';
    public string $keluhan = '';
    public string $kondisiBarang = '';
    public string $catatan = '';

    // State Modal Edit Data Unit Servis
    public bool $modalEditOpen = false;
    public ?int $editServisId = null;
    public ?int $editCustomerId = null;
    public string $editCustomerSearch = '';
    public string $editKategoriPerangkat = 'laptop';
    public string $editTipeUnit = '';
    public array $editKelengkapanChips = [];
    public string $editCustomKelengkapan = '';
    public string $editKeluhan = '';
    public string $editKondisiBarang = '';
    public string $editCatatan = '';

    // State Modal Detail Unit & Riwayat Status
    public bool $modalDetailOpen = false;
    public ?ServisMasuk $selectedServis = null;

    // State Modal Kelola Rincian Tindakan (Sparepart & Jasa)
    public bool $modalTindakanOpen = false;
    public ?ServisMasuk $activeServis = null;
    public string $tindakanItemType = 'jasa'; // 'jasa' | 'produk'
    public ?int $selectedMasterServisId = null;
    public string $selectedMasterServisNama = '';
    public string $selectedMasterServisJenis = 'jasa';
    public $selectedMasterServisHarga = 0;
    public ?int $selectedProdukId = null;
    public string $selectedProdukNama = '';
    public $selectedProdukHargaJual = 0;
    public int $itemQty = 1;
    public int $itemGaransiHari = 30;
    public ?int $selectedStokTersedia = null;

    // State Modal Update Status
    public bool $modalStatusOpen = false;
    public ?ServisMasuk $servisToUpdate = null;
    public string $updateStatus = '';
    public string $updateCatatan = '';

    // State Modal Hapus Data Servis
    public bool $modalDeleteOpen = false;
    public ?int $servisToDeleteId = null;
    public string $deletePassword = '';
    public string $deleteAlasan = '';

    // State Modal Konfirmasi Sukses & Cetak Nota
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

    public function updatingStatusFilter(): void
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

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // =========================================================================
    // MODAL CREATE SERVIS NON REAL
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

    public function toggleKelengkapanChip(string $chip): void
    {
        if (in_array($chip, $this->kelengkapanChips)) {
            $this->kelengkapanChips = array_values(array_diff($this->kelengkapanChips, [$chip]));
        } else {
            $this->kelengkapanChips[] = $chip;
        }
    }

    public function toggleEditKelengkapanChip(string $chip): void
    {
        if (in_array($chip, $this->editKelengkapanChips)) {
            $this->editKelengkapanChips = array_values(array_diff($this->editKelengkapanChips, [$chip]));
        } else {
            $this->editKelengkapanChips[] = $chip;
        }
    }

    private function resetCreateForm(): void
    {
        $this->customTanggalMasuk = '';
        $this->customerType = 'langganan';
        $this->selectedCustomerId = null;
        $this->customerSearch = '';
        $this->newCustomerNama = '';
        $this->newCustomerNoHp = '';
        $this->kategoriPerangkat = 'laptop';
        $this->tipeUnit = '';
        $this->kelengkapanChips = ['Unit'];
        $this->customKelengkapan = '';
        $this->keluhan = '';
        $this->kondisiBarang = '';
        $this->catatan = '';
        $this->resetValidation();
    }

    public function selectCustomer(int $id, string $nama): void
    {
        $this->selectedCustomerId = $id;
        $this->customerSearch = $nama;
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
            'kategoriPerangkat' => 'required|in:laptop,printer,komputer,lainnya',
            'tipeUnit' => 'required|string|max:150',
            'keluhan' => 'required|string|min:3',
            'kondisiBarang' => 'nullable|string|max:500',
            'catatan' => 'nullable|string|max:1000',
        ];

        if (! empty($this->customTanggalMasuk)) {
            $rules['customTanggalMasuk'] = 'required|date';
        }

        if ($this->customerType === 'baru') {
            $rules['newCustomerNama'] = 'required|string|min:2|max:100';
            $rules['newCustomerNoHp'] = 'nullable|string|max:25';
        } else {
            $rules['selectedCustomerId'] = 'required|exists:customers,id';
        }

        $this->validate($rules, [
            'selectedCustomerId.required' => 'Wajib memilih pelanggan terdaftar atau buat pelanggan baru.',
            'newCustomerNama.required' => 'Nama pelanggan baru wajib diisi.',
            'tipeUnit.required' => 'Nama / model perangkat servis wajib diisi.',
            'keluhan.required' => 'Keluhan atau kerusakan wajib dijelaskan.',
        ]);

        try {
            DB::beginTransaction();

            $finalCustomerId = null;
            if ($this->customerType === 'baru') {
                $customer = Customer::create([
                    'cabang_id' => $cabang->id,
                    'nama' => trim($this->newCustomerNama),
                    'no_hp' => trim($this->newCustomerNoHp) ?: null,
                    'created_by' => Auth::id(),
                ]);
                $finalCustomerId = $customer->id;
            } elseif ($this->customerType === 'langganan') {
                $finalCustomerId = $this->selectedCustomerId;
            }

            // Tanggal masuk
            $tanggalMasuk = ! empty($this->customTanggalMasuk)
                ? Carbon::parse($this->customTanggalMasuk)
                : Carbon::now();

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

            $allKelengkapan = $this->kelengkapanChips;
            if (! empty(trim($this->customKelengkapan))) {
                $allKelengkapan[] = trim($this->customKelengkapan);
            }
            $kelengkapanString = ! empty($allKelengkapan) ? implode(', ', array_unique($allKelengkapan)) : 'Hanya Unit';

            $servis = ServisMasuk::create([
                'no_servis' => $noServis,
                'cabang_id' => $cabang->id,
                'customer_id' => $finalCustomerId,
                'teknisi_id' => null,
                'created_by' => Auth::id(),
                'kategori_perangkat' => $this->kategoriPerangkat,
                'tipe_unit' => trim($this->tipeUnit),
                'kelengkapan' => $kelengkapanString,
                'keluhan' => trim($this->keluhan),
                'kondisi_barang' => trim((string) $this->kondisiBarang) ?: null,
                'status' => 'pengecekan',
                'is_real' => false,
                'tanggal_masuk' => $tanggalMasuk,
                'estimasi_biaya' => 0,
                'total_biaya' => 0,
                'catatan' => trim((string) $this->catatan) ?: null,
            ]);

            ServisStatusLog::create([
                'servis_id' => $servis->id,
                'user_id' => Auth::id(),
                'status' => 'pengecekan',
                'tanggal' => $tanggalMasuk,
                'catatan' => 'Penerimaan unit servis baru di toko (Tahap Pengecekan/Diagnosa) [Servis Non-Real].',
            ]);

            DB::commit();

            try {
                broadcast(new ServisStatusUpdated($servis->id, $servis->status, $servis->no_servis_frontend, $servis->cabang_id));
            } catch (\Throwable $th) {
                // Ignore broadcast error
            }

            $this->modalCreateOpen = false;
            $this->lastCreatedServis = $servis;
            $this->modalSuccessOpen = true;

            session()->flash('success', "Penerimaan servis non-real {$servis->no_servis_frontend} berhasil dicatat!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // MODAL EDIT DATA UNIT SERVIS
    // =========================================================================
    public function openEditModal(int $id): void
    {
        $servis = ServisMasuk::with('customer')->findOrFail($id);
        $this->editServisId = $servis->id;
        $this->editCustomerId = $servis->customer_id;
        $this->editCustomerSearch = $servis->customer ? ($servis->customer->nama . ' - ' . ($servis->customer->no_hp ?? 'Tanpa No HP')) : '';
        $this->editKategoriPerangkat = $servis->kategori_perangkat;
        $this->editTipeUnit = $servis->tipe_unit;
        $this->editKeluhan = $servis->keluhan;
        $this->editKondisiBarang = $servis->kondisi_barang ?? '';
        $this->editCatatan = $servis->catatan ?? '';

        $chips = [];
        if (! empty($servis->kelengkapan)) {
            $rawChips = array_map('trim', explode(',', $servis->kelengkapan));
            $predefined = ['Unit', 'Charger', 'Tas', 'Kabel Power', 'Dus / Box'];
            $customArr = [];
            foreach ($rawChips as $c) {
                if (in_array($c, $predefined)) {
                    $chips[] = $c;
                } else {
                    $customArr[] = $c;
                }
            }
            $this->editCustomKelengkapan = implode(', ', $customArr);
        } else {
            $this->editCustomKelengkapan = '';
        }
        $this->editKelengkapanChips = $chips;

        $this->modalEditOpen = true;
    }

    public function closeEditModal(): void
    {
        $this->modalEditOpen = false;
        $this->editServisId = null;
        $this->resetValidation();
    }

    public function selectEditCustomer(int $id, string $nama): void
    {
        $this->editCustomerId = $id;
        $this->editCustomerSearch = $nama;
    }

    public function simpanEditServis(): void
    {
        if (! $this->editServisId) return;

        $this->validate([
            'editCustomerId' => 'required|exists:customers,id',
            'editKategoriPerangkat' => 'required|in:laptop,printer,komputer,lainnya',
            'editTipeUnit' => 'required|string|max:150',
            'editKeluhan' => 'required|string|min:3',
            'editKondisiBarang' => 'nullable|string|max:500',
            'editCatatan' => 'nullable|string|max:1000',
        ], [
            'editCustomerId.required' => 'Pelanggan wajib dipilih.',
            'editTipeUnit.required' => 'Nama / model perangkat wajib diisi.',
            'editKeluhan.required' => 'Keluhan / kerusakan wajib diisi.',
        ]);

        try {
            $servis = ServisMasuk::findOrFail($this->editServisId);

            $allKelengkapan = $this->editKelengkapanChips;
            if (! empty(trim($this->editCustomKelengkapan))) {
                $allKelengkapan[] = trim($this->editCustomKelengkapan);
            }
            $kelengkapanString = ! empty($allKelengkapan) ? implode(', ', array_unique($allKelengkapan)) : 'Hanya Unit';

            $servis->update([
                'customer_id' => $this->editCustomerId,
                'kategori_perangkat' => $this->editKategoriPerangkat,
                'tipe_unit' => trim($this->editTipeUnit),
                'kelengkapan' => $kelengkapanString,
                'keluhan' => trim($this->editKeluhan),
                'kondisi_barang' => trim((string) $this->editKondisiBarang) ?: null,
                'catatan' => trim((string) $this->editCatatan) ?: null,
            ]);

            $this->closeEditModal();
            session()->flash('success', "Data unit servis {$servis->no_servis_frontend} berhasil diperbarui!");
        } catch (\Exception $e) {
            $this->addError('editGeneral', 'Gagal memperbarui data servis: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // MODAL DETAIL SERVIS & TIMELINE
    // =========================================================================
    public function openDetailModal(int $id): void
    {
        $this->selectedServis = ServisMasuk::with([
            'customer',
            'cabang',
            'pembuat',
            'teknisi',
            'kasir',
            'details.produk',
            'statusLogs.user',
        ])->findOrFail($id);

        $this->modalDetailOpen = true;
    }

    public function closeDetailModal(): void
    {
        $this->modalDetailOpen = false;
        $this->selectedServis = null;
    }

    // =========================================================================
    // MODAL UPDATE STATUS SERVIS
    // =========================================================================
    public function openStatusModal(int $id): void
    {
        $this->servisToUpdate = ServisMasuk::findOrFail($id);
        $this->updateStatus = $this->servisToUpdate->status;
        $this->updateCatatan = '';
        $this->modalStatusOpen = true;
    }

    public function closeStatusModal(): void
    {
        $this->modalStatusOpen = false;
        $this->servisToUpdate = null;
        $this->updateCatatan = '';
    }

    public function simpanUpdateStatus(): void
    {
        if (! $this->servisToUpdate) return;

        $this->validate([
            'updateStatus' => 'required|in:pengecekan,perbaikan,pengerjaan,selesai,sudah_diambil,sudah_dibayar,batal',
            'updateCatatan' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $now = Carbon::now();
            $oldStatus = $this->servisToUpdate->status;
            $newStatus = $this->updateStatus;
            $this->servisToUpdate->status = $newStatus;

            if ($newStatus === 'selesai' && ! $this->servisToUpdate->tanggal_selesai) {
                $this->servisToUpdate->tanggal_selesai = $now;
            } elseif (($newStatus === 'sudah_diambil' || $newStatus === 'sudah_dibayar') && ! $this->servisToUpdate->tanggal_ambil) {
                $this->servisToUpdate->tanggal_ambil = $now;
            }

            $this->servisToUpdate->save();

            ServisStatusLog::create([
                'servis_id' => $this->servisToUpdate->id,
                'user_id' => Auth::id(),
                'status' => $newStatus,
                'tanggal' => $now,
                'catatan' => trim($this->updateCatatan) ?: "Status diperbarui dari {$oldStatus} ke {$this->servisToUpdate->status_label}.",
            ]);

            DB::commit();

            try {
                broadcast(new ServisStatusUpdated($this->servisToUpdate->id, $newStatus, $this->servisToUpdate->no_servis_frontend, $this->servisToUpdate->cabang_id));
            } catch (\Throwable $th) {
                // Silently continue
            }

            $this->modalStatusOpen = false;
            $this->servisToUpdate = null;
            session()->flash('success', 'Status servis non-real berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('updateStatusError', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // MODAL TINDAKAN & KOMPONEN (RINCIAN SERVIS)
    // =========================================================================
    public function openTindakanModal(int $id): void
    {
        $this->activeServis = ServisMasuk::with(['details.produk', 'customer', 'cabang'])->findOrFail($id);
        $this->tindakanItemType = 'jasa';
        $this->resetTindakanForm();
        $this->modalTindakanOpen = true;
    }

    public function closeTindakanModal(): void
    {
        $this->modalTindakanOpen = false;
        $this->activeServis = null;
        $this->resetTindakanForm();
    }

    private function resetTindakanForm(): void
    {
        $this->selectedMasterServisId = null;
        $this->selectedMasterServisNama = '';
        $this->selectedMasterServisJenis = 'jasa';
        $this->selectedMasterServisHarga = 0;
        $this->selectedProdukId = null;
        $this->selectedProdukNama = '';
        $this->selectedProdukHargaJual = 0;
        $this->itemQty = 1;
        $this->itemGaransiHari = 30;
        $this->selectedStokTersedia = null;
        $this->resetValidation();
    }

    public function selectMasterServis(int $id, string $nama, string $jenis, $biayaStandard): void
    {
        $this->selectedMasterServisId = $id;
        $this->selectedMasterServisNama = $nama;
        $this->selectedMasterServisJenis = $jenis;
        $this->selectedMasterServisHarga = (float) $biayaStandard;
        $this->itemGaransiHari = 30;
    }

    public function selectProdukSparepart(int $id, string $nama, $hargaJual): void
    {
        $this->selectedProdukId = $id;
        $this->selectedProdukNama = $nama;
        $this->selectedProdukHargaJual = (float) $hargaJual;
        $this->itemGaransiHari = 30;

        if ($this->activeServis) {
            $stk = Stok::where('produk_id', $id)
                ->where('cabang_id', $this->activeServis->cabang_id)
                ->first();
            $this->selectedStokTersedia = $stk ? $stk->jumlah : 0;
        }
    }

    public function tambahItemTindakan(): void
    {
        if (! $this->activeServis) return;

        if ($this->tindakanItemType === 'jasa') {
            if (is_string($this->selectedMasterServisHarga)) {
                $this->selectedMasterServisHarga = (float) preg_replace('/[^\d]/', '', $this->selectedMasterServisHarga);
            }

            $this->validate([
                'selectedMasterServisNama' => 'required|string|max:150',
                'selectedMasterServisHarga' => 'required|numeric|min:0',
                'itemQty' => 'required|integer|min:1',
                'itemGaransiHari' => 'required|integer|min:0|max:3650',
            ], [
                'selectedMasterServisNama.required' => 'Nama jasa servis wajib diisi.',
                'selectedMasterServisHarga.required' => 'Biaya tindakan wajib diisi.',
            ]);

            $namaItem = $this->selectedMasterServisNama;
            $harga = (float) $this->selectedMasterServisHarga;
            $jenis = 'jasa';
            $masterServisId = $this->selectedMasterServisId;
            $produkId = null;
        } else {
            if (is_string($this->selectedProdukHargaJual)) {
                $this->selectedProdukHargaJual = (float) preg_replace('/[^\d]/', '', $this->selectedProdukHargaJual);
            }

            $this->validate([
                'selectedProdukNama' => 'required|string|max:150',
                'selectedProdukHargaJual' => 'required|numeric|min:0',
                'itemQty' => 'required|integer|min:1',
                'itemGaransiHari' => 'required|integer|min:0|max:3650',
            ], [
                'selectedProdukNama.required' => 'Sparepart dari Master Produk wajib dipilih.',
                'selectedProdukHargaJual.required' => 'Harga sparepart wajib diisi.',
            ]);

            $namaItem = $this->selectedProdukNama;
            $harga = (float) $this->selectedProdukHargaJual;
            $jenis = 'sparepart';
            $masterServisId = null;
            $produkId = $this->selectedProdukId;
        }

        try {
            DB::beginTransaction();

            $subtotal = $harga * (int) $this->itemQty;

            ServisDetail::create([
                'servis_id' => $this->activeServis->id,
                'master_servis_id' => $masterServisId,
                'produk_id' => $produkId,
                'nama_item' => $namaItem,
                'jenis' => $jenis,
                'qty' => $this->itemQty,
                'harga_satuan' => $harga,
                'subtotal' => $subtotal,
                'garansi_hari' => $this->itemGaransiHari,
            ]);

            // Hitung ulang total biaya pada ServisMasuk
            $totalBiayaBaru = ServisDetail::where('servis_id', $this->activeServis->id)->sum('subtotal');
            $this->activeServis->update(['total_biaya' => $totalBiayaBaru]);

            DB::commit();

            $this->activeServis->load('details.produk');
            $this->resetTindakanForm();
            session()->flash('tindakanSuccess', "Item '{$namaItem}' berhasil ditambahkan ke rincian!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('tindakanError', 'Gagal menambahkan item tindakan: ' . $e->getMessage());
        }
    }

    public function hapusItemTindakan(int $detailId): void
    {
        if (! $this->activeServis) return;

        try {
            DB::beginTransaction();

            $detail = ServisDetail::where('servis_id', $this->activeServis->id)->findOrFail($detailId);
            $namaItem = $detail->nama_item;

            $detail->delete();

            // Hitung ulang total biaya
            $totalBiayaBaru = ServisDetail::where('servis_id', $this->activeServis->id)->sum('subtotal');
            $this->activeServis->update(['total_biaya' => $totalBiayaBaru]);

            DB::commit();

            $this->activeServis->load('details.produk');
            session()->flash('tindakanSuccess', "Item '{$namaItem}' telah dihapus dari rincian.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('tindakanError', 'Gagal menghapus item: ' . $e->getMessage());
        }
    }

    public function selesaikanServisDariTindakan(): void
    {
        if (! $this->activeServis) return;

        if ($this->activeServis->details->isEmpty()) {
            $this->addError('tindakanError', 'Rincian servis masih kosong. Tambahkan minimal 1 tindakan / jasa sebelum menyelesaikan servis.');
            return;
        }

        try {
            DB::beginTransaction();

            $now = Carbon::now();
            $this->activeServis->update([
                'status' => 'selesai',
                'tanggal_selesai' => $now,
            ]);

            ServisStatusLog::create([
                'servis_id' => $this->activeServis->id,
                'user_id' => Auth::id(),
                'status' => 'selesai',
                'tanggal' => $now,
                'catatan' => 'Pengerjaan servis dinyatakan Selesai melalui rincian tindakan. Unit siap diuji & diserahkan ke pelanggan.',
            ]);

            DB::commit();

            try {
                broadcast(new ServisStatusUpdated($this->activeServis->id, 'selesai', $this->activeServis->no_servis_frontend, $this->activeServis->cabang_id));
            } catch (\Throwable $th) {
                // Silently continue
            }

            $kode = $this->activeServis->no_servis_frontend;
            $this->closeTindakanModal();
            session()->flash('success', "Servis {$kode} berhasil diselesaikan!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('tindakanError', 'Gagal menyelesaikan servis: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // MODAL HAPUS DATA SERVIS
    // =========================================================================
    public function openDeleteModal(int $id): void
    {
        $this->servisToDeleteId = $id;
        $this->deletePassword = '';
        $this->deleteAlasan = '';
        $this->modalDeleteOpen = true;
    }

    public function closeDeleteModal(): void
    {
        $this->modalDeleteOpen = false;
        $this->servisToDeleteId = null;
        $this->deletePassword = '';
        $this->deleteAlasan = '';
        $this->resetValidation();
    }

    public function prosesHapusServis(): void
    {
        if (! $this->servisToDeleteId) return;

        $user = Auth::user();
        if (! $user->isOwner() && ! $user->hasRole('Superadmin') && ! $user->hasRole('Admin')) {
            $this->addError('deletePassword', 'Hanya Owner, Superadmin, atau Admin yang berhak menghapus data servis.');
            return;
        }

        $this->validate([
            'deletePassword' => 'required',
            'deleteAlasan' => 'required|string|min:3|max:500',
        ], [
            'deletePassword.required' => 'Password verifikasi wajib diisi.',
            'deleteAlasan.required' => 'Alasan penghapusan wajib diisi.',
        ]);

        if (! Hash::check($this->deletePassword, $user->password)) {
            $this->addError('deletePassword', 'Password konfirmasi salah. Penghapusan dibatalkan.');
            return;
        }

        try {
            DB::beginTransaction();

            $servis = ServisMasuk::with('details')->findOrFail($this->servisToDeleteId);
            $kode = $servis->no_servis_frontend;

            $servis->details()->delete();
            $servis->statusLogs()->delete();
            $servis->delete();

            DB::commit();

            $this->closeDeleteModal();
            session()->flash('success', "Data servis non-real {$kode} telah berhasil dihapus dari sistem!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('deleteGeneral', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $cabangs = $isHQ ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();

        // Autocomplete Pelanggan
        $customerSuggestions = [];
        if (! empty(trim($this->customerSearch)) && strlen(trim($this->customerSearch)) >= 1) {
            $cSearch = trim($this->customerSearch);
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

        // Master Servis Suggestions (Jasa)
        $masterServisSuggestions = [];
        if ($this->modalTindakanOpen && $this->tindakanItemType === 'jasa') {
            $masterServisSuggestions = MasterServis::where('status', 'aktif')
                ->where('jenis', 'jasa')
                ->orderBy('nama_layanan')
                ->get();
        }

        // Master Produk Suggestions (Sparepart)
        $produkSuggestions = [];
        if ($this->modalTindakanOpen && $this->tindakanItemType === 'produk') {
            $produkSuggestions = Produk::where('status', 'aktif')
                ->orderBy('nama_produk')
                ->limit(50)
                ->get();
        }

        // Query Utama Servis Non Real (is_real = false)
        $query = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'teknisi', 'kasir', 'details'])
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

        // Filter Status di Servis Non Real
        if ($this->statusFilter === 'pengecekan') {
            $query->where('status', 'pengecekan');
        } elseif ($this->statusFilter === 'perbaikan') {
            $query->whereIn('status', ['perbaikan', 'pengerjaan']);
        } elseif ($this->statusFilter === 'selesai') {
            $query->where('status', 'selesai');
        } elseif ($this->statusFilter === 'sudah_diambil') {
            $query->whereIn('status', ['sudah_diambil', 'sudah_dibayar']);
        } elseif ($this->statusFilter === 'batal') {
            $query->where('status', 'batal');
        }

        // Filter Kategori Perangkat
        if ($this->kategoriFilter !== 'semua' && ! empty($this->kategoriFilter)) {
            $query->where('kategori_perangkat', $this->kategoriFilter);
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

        // Metrik Ringkasan Servis Non Real
        $metricsBase = clone $query;
        $allCurrent = $metricsBase->get();

        $totalUnit = $allCurrent->count();
        $totalPengecekan = $allCurrent->where('status', 'pengecekan')->count();
        $totalPengerjaan = $allCurrent->whereIn('status', ['perbaikan', 'pengerjaan'])->count();
        $totalSelesai = $allCurrent->where('status', 'selesai')->count();
        $totalSudahDiambil = $allCurrent->whereIn('status', ['sudah_diambil', 'sudah_dibayar'])->count();

        // Sorting
        $sortDir = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['tanggal_masuk', 'no_servis', 'tipe_unit', 'keluhan', 'status', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'tanggal_masuk';
        $query->orderBy($sortColumn, $sortDir);

        $totalItems = (clone $query)->count();
        $servisList = $query->take($this->perPage)->get();
        $hasMore = $totalItems > $this->perPage;

        return view('livewire.servis.non-real.index', [
            'servisList' => $servisList,
            'totalItems' => $totalItems,
            'hasMore' => $hasMore,
            'totalUnit' => $totalUnit,
            'totalPengecekan' => $totalPengecekan,
            'totalPengerjaan' => $totalPengerjaan,
            'totalSelesai' => $totalSelesai,
            'totalSudahDiambil' => $totalSudahDiambil,
            'cabangs' => $cabangs,
            'customerSuggestions' => $customerSuggestions,
            'masterServisSuggestions' => $masterServisSuggestions,
            'produkSuggestions' => $produkSuggestions,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ]);
    }
}
