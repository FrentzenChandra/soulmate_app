<?php

namespace App\Livewire\Servis\NonReal;

use App\Events\ServisStatusUpdated;
use App\Livewire\Traits\WithSorting;
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
    public string $statusFilter = 'semua';

    #[Url(except: 'semua')]
    public string $kategoriFilter = 'semua';

    #[Url(except: 'semua')]
    public string $cabangFilter = 'semua';

    public int $perPage = 15;

    // State Modal Form Penerimaan Servis Non Real Baru
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

    // State Modal Form Edit Servis Non Real
    public bool $modalEditOpen = false;
    public ?int $editServisId = null;
    public string $editTipeUnit = '';
    public string $editKategoriPerangkat = 'laptop';
    public string $editKeluhan = '';
    public array $editKelengkapanChips = [];
    public string $editCustomKelengkapan = '';
    public string $editKondisiBarang = '';
    public string $editCatatan = '';

    // State Modal Detail (Lihat)
    public bool $modalDetailOpen = false;
    public ?ServisMasuk $selectedServis = null;

    // State Modal Catatan Teknisi
    public bool $modalCatatanTeknisiOpen = false;
    public ?int $catatanServisId = null;
    public string $catatanTeknisiText = '';

    // State Modal Hapus Servis
    public bool $modalDeleteOpen = false;
    public ?int $deleteServisId = null;
    public ?string $deleteServisKode = '';

    // State Modal Update Status Cepat (Logo Kunci Inggris)
    public bool $modalStatusOpen = false;
    public ?ServisMasuk $servisToUpdate = null;
    public string $updateStatus = 'perbaikan';
    public string $updateCatatan = '';

    // State Modal Kelola Tindakan & Komponen
    public bool $modalTindakanOpen = false;
    public ?int $activeServisId = null;
    public ?ServisMasuk $activeServis = null;
    public string $itemType = 'jasa'; // 'jasa' | 'sparepart'
    public string $itemSearch = '';
    public ?int $selectedMasterServisId = null;
    public ?int $selectedProdukId = null;
    public string $selectedMasterServisNama = '';
    public string $selectedMasterServisJenis = 'jasa';
    public $selectedMasterServisHarga = 0;
    public int $itemQty = 1;
    public int $itemGaransiHari = 30;
    public ?int $selectedStokTersedia = null;

    // State Modal Konfirmasi Sukses & Cetak Tanda Terima
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

    // =========================================================================
    // MODAL FORM PENERIMAAN SERVIS NON REAL BARU
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
        $this->customTanggalMasuk = now()->format('Y-m-d\TH:i');
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
            $rules['newCustomerNoHp'] = 'required|string|max:25';
        } else {
            $rules['selectedCustomerId'] = 'required|exists:customers,id';
        }

        $this->validate($rules, [
            'selectedCustomerId.required' => 'Wajib memilih pelanggan terdaftar atau buat pelanggan baru.',
            'newCustomerNama.required' => 'Nama pelanggan baru wajib diisi.',
            'newCustomerNoHp.required' => 'Nomor WhatsApp / HP pelanggan wajib diisi.',
            'tipeUnit.required' => 'Tipe / model perangkat servis wajib diisi.',
            'keluhan.required' => 'Keluhan atau indikasi kerusakan wajib dijelaskan.',
        ]);

        try {
            DB::beginTransaction();

            $finalCustomerId = null;
            if ($this->customerType === 'baru') {
                $customer = Customer::create([
                    'cabang_id' => $cabang->id,
                    'nama' => trim($this->newCustomerNama),
                    'no_hp' => trim($this->newCustomerNoHp),
                    'created_by' => Auth::id(),
                ]);
                $finalCustomerId = $customer->id;
            } elseif ($this->customerType === 'langganan') {
                $finalCustomerId = $this->selectedCustomerId;
            }

            // Tanggal masuk (custom tanggal, hari, waktu & jam)
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

            $this->resetCreateForm();
            session()->flash('success', "Unit servis non real {$servis->no_servis_frontend} berhasil diterima!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Gagal menyimpan data servis: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // SUB-ACTIONS DARI MODAL DETAIL (LIHAT)
    // =========================================================================

    public function openDetailModal(int $id): void
    {
        $this->selectedServis = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'statusLogs.user', 'details.produk', 'details.masterServis'])->findOrFail($id);
        $this->modalDetailOpen = true;
    }

    public function closeDetailModal(): void
    {
        $this->modalDetailOpen = false;
        $this->selectedServis = null;
    }

    public function openEditModal(int $id): void
    {
        $servis = ServisMasuk::findOrFail($id);
        $this->editServisId = $servis->id;
        $this->editTipeUnit = $servis->tipe_unit;
        $this->editKategoriPerangkat = $servis->kategori_perangkat ?: 'laptop';
        $this->editKeluhan = $servis->keluhan;
        $this->editKondisiBarang = $servis->kondisi_barang ?? '';
        $this->editCatatan = $servis->catatan ?? '';

        $chips = array_map('trim', explode(',', $servis->kelengkapan ?? ''));
        $this->editKelengkapanChips = array_values(array_filter($chips));
        $this->editCustomKelengkapan = '';

        $this->modalDetailOpen = false;
        $this->modalEditOpen = true;
    }

    public function closeEditModal(): void
    {
        $this->modalEditOpen = false;
        $this->editServisId = null;
    }

    public function simpanEditServis(): void
    {
        if (! $this->editServisId) return;

        $this->validate([
            'editTipeUnit' => 'required|string|max:150',
            'editKategoriPerangkat' => 'required|in:laptop,printer,komputer,lainnya',
            'editKeluhan' => 'required|string|min:3',
        ]);

        $servis = ServisMasuk::findOrFail($this->editServisId);

        $allKelengkapan = $this->editKelengkapanChips;
        if (! empty(trim($this->editCustomKelengkapan))) {
            $allKelengkapan[] = trim($this->editCustomKelengkapan);
        }
        $kelengkapanString = ! empty($allKelengkapan) ? implode(', ', array_unique($allKelengkapan)) : 'Hanya Unit';

        $servis->update([
            'tipe_unit' => trim($this->editTipeUnit),
            'kategori_perangkat' => $this->editKategoriPerangkat,
            'keluhan' => trim($this->editKeluhan),
            'kelengkapan' => $kelengkapanString,
            'kondisi_barang' => trim($this->editKondisiBarang) ?: null,
            'catatan' => trim($this->editCatatan) ?: null,
        ]);

        $this->modalEditOpen = false;
        $this->editServisId = null;
        session()->flash('success', "Data unit servis {$servis->no_servis_frontend} berhasil diperbarui.");
    }

    public function openCatatanTeknisiModal(int $id): void
    {
        $servis = ServisMasuk::findOrFail($id);
        $this->catatanServisId = $servis->id;
        $this->catatanTeknisiText = $servis->catatan ?? '';
        $this->modalDetailOpen = false;
        $this->modalCatatanTeknisiOpen = true;
    }

    public function closeCatatanTeknisiModal(): void
    {
        $this->modalCatatanTeknisiOpen = false;
        $this->catatanServisId = null;
        $this->catatanTeknisiText = '';
    }

    public function simpanCatatanTeknisi(): void
    {
        if (! $this->catatanServisId) return;

        $servis = ServisMasuk::findOrFail($this->catatanServisId);
        $servis->update([
            'catatan' => trim($this->catatanTeknisiText) ?: null,
        ]);

        $this->modalCatatanTeknisiOpen = false;
        $this->catatanServisId = null;
        $this->catatanTeknisiText = '';
        session()->flash('success', "Catatan teknisi untuk tiket {$servis->no_servis_frontend} berhasil disimpan.");
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
            session()->flash('success', "Tiket servis {$kode} berhasil dihapus dari sistem.");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menghapus servis: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // MODAL UPDATE STATUS CEPAT (LOGO KUNCI INGGRIS)
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
    // MODAL TINDAKAN & KOMPONEN (DENGAN TOMBOL SELESAI)
    // =========================================================================

    public function openTindakanModal(int $id): void
    {
        $this->activeServisId = $id;
        $this->activeServis = ServisMasuk::with(['details.masterServis', 'details.produk', 'customer', 'cabang'])->findOrFail($id);
        $this->itemType = 'jasa';
        $this->resetItemForm();
        $this->modalTindakanOpen = true;
    }

    public function closeTindakanModal(): void
    {
        $this->modalTindakanOpen = false;
        $this->activeServisId = null;
        $this->activeServis = null;
        $this->resetItemForm();
    }

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
        $this->selectedMasterServisJenis = $this->itemType;
        $this->selectedMasterServisHarga = 0;
        $this->itemQty = 1;
        $this->itemGaransiHari = $this->itemType === 'jasa' ? 30 : 0;
        $this->selectedStokTersedia = null;
    }

    public function selectMasterItem(int $id, string $nama, string $jenis, float $harga, int $garansiHari = 0): void
    {
        $this->selectedMasterServisId = $id;
        $this->selectedProdukId = null;
        $this->selectedMasterServisNama = $nama;
        $this->selectedMasterServisJenis = 'jasa';
        $this->selectedMasterServisHarga = $harga;
        $this->itemGaransiHari = $garansiHari;
        $this->itemSearch = $nama;
        $this->selectedStokTersedia = null;
    }

    public function selectProdukItem(int $id, string $nama, float $harga, int $stok = 0, int $garansiHari = 0): void
    {
        $this->selectedProdukId = $id;
        $this->selectedMasterServisId = null;
        $this->selectedMasterServisNama = $nama;
        $this->selectedMasterServisJenis = 'sparepart';
        $this->selectedMasterServisHarga = $harga;
        $this->selectedStokTersedia = $stok;
        $this->itemGaransiHari = $garansiHari;
        $this->itemSearch = $nama;
    }

    public function tambahItemTindakan(): void
    {
        if (! $this->activeServisId) return;

        if (is_string($this->selectedMasterServisHarga)) {
            $this->selectedMasterServisHarga = (float) preg_replace('/[^\d]/', '', $this->selectedMasterServisHarga);
        }

        $this->validate([
            'selectedMasterServisNama' => 'required|string|max:150',
            'selectedMasterServisHarga' => 'required|numeric|min:0',
            'itemQty' => 'required|integer|min:1',
            'itemGaransiHari' => 'required|integer|min:0|max:3650',
        ]);

        $subtotal = (float) $this->selectedMasterServisHarga * (int) $this->itemQty;

        ServisDetail::create([
            'servis_id' => $this->activeServisId,
            'master_servis_id' => $this->selectedMasterServisId,
            'produk_id' => $this->selectedProdukId,
            'nama_item' => $this->selectedMasterServisNama,
            'jenis' => $this->selectedMasterServisJenis ?: ($this->selectedProdukId ? 'sparepart' : 'jasa'),
            'qty' => $this->itemQty,
            'harga' => (float) $this->selectedMasterServisHarga,
            'subtotal' => $subtotal,
            'garansi_hari' => $this->itemGaransiHari,
        ]);

        $this->recalculateServisTotal($this->activeServisId);

        $this->resetItemForm();

        $this->activeServis = ServisMasuk::with(['details.masterServis', 'details.produk', 'customer', 'cabang'])->find($this->activeServisId);
    }

    public function hapusItemTindakan(int $detailId): void
    {
        $detail = ServisDetail::where('servis_id', $this->activeServisId)->find($detailId);
        if ($detail) {
            $detail->delete();
            $this->recalculateServisTotal($this->activeServisId);
            $this->activeServis = ServisMasuk::with(['details.masterServis', 'details.produk', 'customer', 'cabang'])->find($this->activeServisId);
        }
    }

    private function recalculateServisTotal(int $servisId): void
    {
        $total = ServisDetail::where('servis_id', $servisId)->sum('subtotal');
        ServisMasuk::where('id', $servisId)->update([
            'total_biaya' => $total,
        ]);
    }

    public function selesaikanServis(): void
    {
        if (! $this->activeServis) return;

        try {
            DB::beginTransaction();

            $now = Carbon::now();
            $this->activeServis->status = 'selesai';
            $this->activeServis->tanggal_selesai = $now;
            $this->activeServis->save();

            ServisStatusLog::create([
                'servis_id' => $this->activeServis->id,
                'user_id' => Auth::id(),
                'status' => 'selesai',
                'tanggal' => $now,
                'catatan' => 'Unit servis telah selesai dikerjakan teknisi dan siap diambil oleh pelanggan.',
            ]);

            DB::commit();

            try {
                broadcast(new ServisStatusUpdated($this->activeServis->id, 'selesai', $this->activeServis->no_servis_frontend, $this->activeServis->cabang_id));
            } catch (\Throwable $th) {
                // Ignore broadcast error
            }

            $kodeFrontend = $this->activeServis->no_servis_frontend;
            $this->closeTindakanModal();
            session()->flash('success', "Pengerjaan unit {$kodeFrontend} berhasil diselesaikan!");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyelesaikan servis: ' . $e->getMessage());
        }
    }

    public function cetakNota(int $id): void
    {
        $servis = ServisMasuk::findOrFail($id);
        $servis->is_cetak_nota = true;
        $servis->save();

        session()->flash('success', "Status cetak faktur {$servis->no_servis_frontend} berhasil diperbarui.");
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

        // Suggestions for items in Tindakan modal
        $masterServisSuggestions = [];
        $produkSuggestions = [];
        if ($this->modalTindakanOpen && ! empty($this->itemSearch) && ! $this->selectedMasterServisId && ! $this->selectedProdukId) {
            $pattern = '%' . strtolower(trim($this->itemSearch)) . '%';
            $targetCabangId = $this->activeServis?->cabang_id ?? ($user->isOwner() ? (int) $activeBranchSession : (int) $user->cabang_id);

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
        $query = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'statusLogs.user', 'details.produk', 'details.masterServis'])
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

        // Metrik Ringkasan Antrean Non Real
        $metricsBase = clone $query;
        $allCurrent = $metricsBase->get();

        $totalUnitMasuk = $allCurrent->count();
        $totalPengecekan = $allCurrent->where('status', 'pengecekan')->count();
        $totalPengerjaan = $allCurrent->whereIn('status', ['perbaikan', 'pengerjaan'])->count();
        $totalSiapDiambil = $allCurrent->where('status', 'selesai')->count();

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
            'totalUnitMasuk' => $totalUnitMasuk,
            'totalPengecekan' => $totalPengecekan,
            'totalPengerjaan' => $totalPengerjaan,
            'totalSiapDiambil' => $totalSiapDiambil,
            'cabangs' => $cabangs,
            'customerSuggestions' => $customerSuggestions,
            'masterServisSuggestions' => $masterServisSuggestions,
            'produkSuggestions' => $produkSuggestions,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ]);
    }
}
