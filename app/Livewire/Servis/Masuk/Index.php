<?php

namespace App\Livewire\Servis\Masuk;

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
#[Title('Servis Masuk - Antrean & Pengerjaan Unit - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'semua')]
    public string $statusFilter = 'semua'; // 'semua' | 'pengecekan' | 'perbaikan'

    #[Url(except: 'semua')]
    public string $tipeFilter = 'semua'; // 'semua' | 'real' | 'non_real'

    #[Url(except: 'semua')]
    public string $kategoriFilter = 'semua';

    #[Url(except: 'semua')]
    public string $cabangFilter = 'semua';

    public int $perPage = 15;

    // State Modal Form Penerimaan Servis Baru
    public bool $modalCreateOpen = false;
    public bool $isReal = true;
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

    // State Modal Form Edit Servis Masuk
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

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->can('servis.masuk.view'))) {
            abort(403, 'Akses modul Servis Masuk dibatasi.');
        }

        $this->sortField = 'tanggal_masuk';
        $this->sortDirection = 'desc';
    }

    #[On('echo:servis-updates,.ServisStatusUpdated')]
    #[On('echo:servis-updates,ServisStatusUpdated')]
    public function onServisStatusUpdated($payload = []): void
    {
        // Re-render automatically when status changes across any client
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

    public function openCreateModal(): void
    {
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('servis.masuk.create'), 403, 'Akses tambah servis dibatasi.');

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
            $this->kelengkapanChips = array_values(array_filter($this->kelengkapanChips, fn ($c) => $c !== $chip));
        } else {
            $this->kelengkapanChips[] = $chip;
        }
    }

    public function toggleEditKelengkapanChip(string $chip): void
    {
        if (in_array($chip, $this->editKelengkapanChips)) {
            $this->editKelengkapanChips = array_values(array_filter($this->editKelengkapanChips, fn ($c) => $c !== $chip));
        } else {
            $this->editKelengkapanChips[] = $chip;
        }
    }

    private function resetCreateForm(): void
    {
        $this->isReal = true;
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
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('servis.masuk.create'), 403, 'Akses tambah servis dibatasi.');

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
            'kategoriPerangkat' => 'required|in:laptop,printer,komputer',
            'tipeUnit' => 'required|string|max:150',
            'keluhan' => 'required|string|min:3',
            'kondisiBarang' => 'nullable|string|max:500',
            'catatan' => 'nullable|string|max:1000',
        ];

        if (! $this->isReal && ! empty($this->customTanggalMasuk)) {
            $rules['customTanggalMasuk'] = 'required|date';
        }

        if ($this->customerType === 'baru') {
            $rules['newCustomerNama'] = 'required|string|min:2|max:100';
            $rules['newCustomerNoHp'] = 'required|string|min:8|max:20';
        } elseif ($this->customerType === 'langganan') {
            $rules['selectedCustomerId'] = 'required|exists:customers,id';
        }

        $this->validate($rules, [
            'tipeUnit.required' => 'Nama / tipe barang unit wajib diisi.',
            'keluhan.required' => 'Permasalahan atau keluhan unit wajib diisi.',
            'newCustomerNama.required' => 'Nama pelanggan baru wajib diisi.',
            'newCustomerNoHp.required' => 'Nomor WhatsApp / HP pelanggan wajib diisi.',
            'selectedCustomerId.required' => 'Silakan pilih data pelanggan terdaftar.',
            'customTanggalMasuk.required' => 'Tanggal masuk servis wajib diisi.',
        ]);

        try {
            DB::beginTransaction();

            $finalCustomerId = null;
            if ($this->customerType === 'baru') {
                $cleanHp = preg_replace('/[^0-9]/', '', $this->newCustomerNoHp);
                $customer = Customer::create([
                    'cabang_id' => $cabang->id,
                    'nama' => trim($this->newCustomerNama),
                    'no_hp' => $cleanHp,
                ]);
                $finalCustomerId = $customer->id;
            } elseif ($this->customerType === 'langganan') {
                $finalCustomerId = $this->selectedCustomerId;
            }

            // Tanggal masuk (custom jika non-real, now jika real)
            $tanggalMasuk = (! $this->isReal && ! empty($this->customTanggalMasuk))
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
                'status' => 'antrean_masuk',
                'is_real' => true,
                'tanggal_masuk' => $tanggalMasuk,
                'estimasi_biaya' => 0,
                'total_biaya' => 0,
                'catatan' => trim((string) $this->catatan) ?: null,
            ]);

            ServisStatusLog::create([
                'servis_id' => $servis->id,
                'user_id' => Auth::id(),
                'status' => 'antrean_masuk',
                'tanggal' => $tanggalMasuk,
                'catatan' => 'Penerimaan unit servis baru di toko (Antrean Masuk).' . (! $this->isReal ? ' [Servis Non-Real]' : ''),
            ]);

            DB::commit();

            try {
                broadcast(new ServisStatusUpdated($servis->id, $servis->status, $servis->no_servis_frontend, $servis->cabang_id));
            } catch (\Throwable $th) {
                // Ignore broadcast error
            }

            $this->modalCreateOpen = false;
            $this->dispatch('open-print-receipt', url: route('servis.tanda-terima', $servis->id) . '?autoprint=1');
            $this->resetCreateForm();
            session()->flash('success', "Unit servis {$servis->no_servis_frontend} berhasil diterima!");
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
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('servis.masuk.detail'), 403, 'Akses detail servis dibatasi.');

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
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('servis.masuk.edit'), 403, 'Akses edit servis dibatasi.');

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
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('servis.masuk.edit'), 403, 'Akses edit servis dibatasi.');

        if (! $this->editServisId) return;

        $this->validate([
            'editTipeUnit' => 'required|string|max:150',
            'editKategoriPerangkat' => 'required|in:laptop,printer,komputer',
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
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('servis.masuk.tindakan'), 403, 'Akses catatan teknisi dibatasi.');

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
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('servis.masuk.tindakan'), 403, 'Akses catatan teknisi dibatasi.');

        if (! $this->catatanServisId) return;

        $servis = ServisMasuk::findOrFail($this->catatanServisId);
        $servis->catatan = trim($this->catatanTeknisiText) ?: null;
        $servis->save();

        ServisStatusLog::create([
            'servis_id' => $servis->id,
            'user_id' => Auth::id(),
            'status' => $servis->status,
            'tanggal' => Carbon::now(),
            'catatan' => 'Update Catatan Teknisi: ' . ($this->catatanTeknisiText ?: '(dikosongkan)'),
        ]);

        $this->modalCatatanTeknisiOpen = false;
        $this->catatanServisId = null;
        session()->flash('success', "Catatan teknisi untuk {$servis->no_servis_frontend} berhasil disimpan.");
    }

    public function openDeleteModal(int $id, string $kode): void
    {
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('servis.masuk.delete'), 403, 'Akses hapus servis dibatasi.');

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
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('servis.masuk.delete'), 403, 'Akses hapus servis dibatasi.');

        if (! $this->deleteServisId) return;

        $servis = ServisMasuk::with('details')->findOrFail($this->deleteServisId);
        $kodeFrontend = $servis->no_servis_frontend;

        // Kembalikan seluruh stok sparepart produk ke cabang terkait jika servis REAL
        if ($servis->is_real) {
            foreach ($servis->details as $det) {
                if ($det->produk_id) {
                    $stok = Stok::where('produk_id', $det->produk_id)
                        ->where('cabang_id', $servis->cabang_id)
                        ->first();
                    if ($stok) {
                        $stok->increment('jumlah', $det->qty);
                    }
                }
            }
        }

        // Delete details and logs
        $servis->details()->delete();
        $servis->statusLogs()->delete();
        $servis->delete();

        $this->modalDeleteOpen = false;
        $this->deleteServisId = null;
        session()->flash('success', "Data servis {$kodeFrontend} berhasil dihapus.");
    }

    // =========================================================================
    // MODAL STATUS CEPAT (LOGO KUNCI INGGRIS)
    // =========================================================================

    public function openStatusModal(int $id): void
    {
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('servis.masuk.status'), 403, 'Akses ubah status servis dibatasi.');

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
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('servis.masuk.status'), 403, 'Akses ubah status servis dibatasi.');

        if (! $this->servisToUpdate) return;

        $this->validate([
            'updateStatus' => 'required|in:antrean_masuk,pengecekan,menunggu_konfirmasi,perbaikan,pengerjaan,menunggu_sparepart,selesai,sudah_diambil,sudah_dibayar,batal',
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

            // Kembalikan stok sparepart jika servis dibatalkan (Hanya jika servis REAL)
            if ($newStatus === 'batal' && $oldStatus !== 'batal' && $this->servisToUpdate->is_real) {
                $this->servisToUpdate->load('details');
                foreach ($this->servisToUpdate->details as $det) {
                    if ($det->produk_id) {
                        $stok = Stok::where('produk_id', $det->produk_id)
                            ->where('cabang_id', $this->servisToUpdate->cabang_id)
                            ->first();
                        if ($stok) {
                            $stok->increment('jumlah', $det->qty);
                        }
                    }
                }
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
            session()->flash('success', 'Status servis berhasil diperbarui!');
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
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('servis.masuk.tindakan'), 403, 'Akses tindakan servis dibatasi.');

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

        if ($this->activeServis?->is_real && $stok <= 0) {
            $this->dispatch('swal:warning', [
                'title' => 'Stok Habis!',
                'text' => "Stok sparepart '{$nama}' kosong.",
            ]);
        }
    }

    public function updatedItemQty($value): void
    {
        $qty = (int) $value;
        if ($this->itemType === 'sparepart' && $this->selectedProdukId && $this->selectedStokTersedia !== null) {
            if ($this->activeServis && $this->activeServis->is_real) {
                if ($qty > $this->selectedStokTersedia) {
                    $this->dispatch('swal:warning', [
                        'title' => 'Stok Melebihi Batas!',
                        'text' => "Jumlah yang diinput ({$qty} unit) melebihi stok yang tersedia ({$this->selectedStokTersedia} unit).",
                    ]);
                }
            }
        }
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

        // Cek dan kurangi stok fisik cabang jika sparepart dan servis REAL
        if ($this->selectedProdukId && $this->activeServis->is_real) {
            $cabangId = $this->activeServis->cabang_id;
            $stok = Stok::where('produk_id', $this->selectedProdukId)
                ->where('cabang_id', $cabangId)
                ->first();

            $sisaStok = $stok ? $stok->jumlah : 0;
            if ($sisaStok < (int) $this->itemQty) {
                $this->dispatch('swal:warning', [
                    'title' => 'Stok Tidak Cukup!',
                    'text' => "Stok sparepart '{$this->selectedMasterServisNama}' tidak mencukupi (Tersedia: {$sisaStok} unit, Diminta: {$this->itemQty} unit).",
                ]);
                $this->addError('itemQty', "Stok sparepart tidak mencukupi (Tersedia: {$sisaStok} unit).");
                return;
            }

            $stok->decrement('jumlah', (int) $this->itemQty);
        }

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
            // Kembalikan stok jika item adalah sparepart dari produk dan servis REAL
            if ($detail->produk_id && $this->activeServis->is_real) {
                $cabangId = $this->activeServis->cabang_id;
                $stok = Stok::where('produk_id', $detail->produk_id)
                    ->where('cabang_id', $cabangId)
                    ->first();
                if ($stok) {
                    $stok->increment('jumlah', $detail->qty);
                }
            }

            $detail->delete();
            $this->recalculateServisTotal($this->activeServisId);
            $this->activeServis = ServisMasuk::with(['details.masterServis', 'details.produk', 'customer', 'cabang'])->find($this->activeServisId);
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

    public function selesaikanServis(): void
    {
        if (! $this->activeServisId) return;

        $servis = ServisMasuk::findOrFail($this->activeServisId);
        $now = Carbon::now();

        $servis->status = 'selesai';
        $servis->tanggal_selesai = $now;
        $this->recalculateServisTotal($servis->id);
        $servis->save();

        ServisStatusLog::create([
            'servis_id' => $servis->id,
            'user_id' => Auth::id(),
            'status' => 'selesai',
            'tanggal' => $now,
            'catatan' => 'Pengerjaan unit selesai, siap diambil oleh pelanggan.',
        ]);

        try {
            broadcast(new ServisStatusUpdated($servis->id, 'selesai', $servis->no_servis_frontend, $servis->cabang_id));
        } catch (\Throwable $th) {
            // Silently continue
        }

        $this->closeTindakanModal();

        session()->flash('success', "Pengerjaan unit {$servis->no_servis_frontend} telah Selesai! Silakan cetak nota saat pelanggan mengambil unit untuk memindahkannya ke Servis Keluar.");
    }

    // =========================================================================
    // CETAK NOTA FAKTUR SERVIS (MEMINDAHKAN RECORD KE SERVIS KELUAR)
    // =========================================================================

    public function cetakNota(int $id): void
    {
        $servis = ServisMasuk::findOrFail($id);
        $servis->is_cetak_nota = true;
        if (! $servis->tanggal_selesai) {
            $servis->tanggal_selesai = Carbon::now();
        }
        $servis->save();

        ServisStatusLog::create([
            'servis_id' => $servis->id,
            'user_id' => Auth::id(),
            'status' => $servis->status,
            'tanggal' => Carbon::now(),
            'catatan' => 'Nota faktur servis telah dicetak. Data unit berpindah ke Servis Keluar.',
        ]);

        try {
            broadcast(new ServisStatusUpdated($servis->id, $servis->status, $servis->no_servis_frontend, $servis->cabang_id));
        } catch (\Throwable $th) {
            // Silently continue
        }

        $this->dispatch('open-print-invoice', url: route('servis.invoice', $servis->id) . '?autoprint=1');

        session()->flash('success', "Nota servis {$servis->no_servis_frontend} berhasil dicetak! Data unit kini berpindah ke Servis Keluar.");
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $cabangs = $isHQ ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();

        // Autocomplete Customer untuk modal create
        $customerSuggestions = [];
        if (! empty($this->customerSearch) && $this->customerType === 'langganan' && ! $this->selectedCustomerId) {
            $custQuery = Customer::query();
            if (! $isHQ) {
                $targetCabang = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
                if ($targetCabang) $custQuery->where('cabang_id', $targetCabang);
            }
            $customerSuggestions = $custQuery->where(function ($q) {
                $pattern = '%' . strtolower($this->customerSearch) . '%';
                $q->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                  ->orWhereRaw('LOWER(COALESCE(no_hp, \'\')) LIKE ?', [$pattern]);
            })->take(5)->get();
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

        // Query Utama Servis Masuk (pengecekan, perbaikan, ATAU selesai yang belum dicetak notanya)
        $query = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'details']);

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

        // Filter Status di Servis Masuk
        if ($this->statusFilter === 'antrean_masuk') {
            $query->where('status', 'antrean_masuk');
        } elseif ($this->statusFilter === 'pengecekan') {
            $query->where('status', 'pengecekan');
        } elseif ($this->statusFilter === 'menunggu_konfirmasi') {
            $query->where('status', 'menunggu_konfirmasi');
        } elseif ($this->statusFilter === 'perbaikan') {
            $query->whereIn('status', ['perbaikan', 'pengerjaan']);
        } elseif ($this->statusFilter === 'menunggu_sparepart') {
            $query->where('status', 'menunggu_sparepart');
        } elseif ($this->statusFilter === 'selesai') {
            $query->where('status', 'selesai')->where('is_cetak_nota', false);
        } else {
            $query->where(function ($q) {
                $q->whereIn('status', ['antrean_masuk', 'pengecekan', 'menunggu_konfirmasi', 'perbaikan', 'pengerjaan', 'menunggu_sparepart'])
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'selesai')->where('is_cetak_nota', false);
                  });
            });
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

        // Metrik Ringkasan Antrean Masuk
        $metricsBase = clone $query;
        $allCurrent = $metricsBase->get();

        $totalUnitMasuk = $allCurrent->count();
        $totalPengecekan = $allCurrent->whereIn('status', ['antrean_masuk', 'pengecekan', 'menunggu_konfirmasi'])->count();
        $totalPengerjaan = $allCurrent->whereIn('status', ['perbaikan', 'pengerjaan', 'menunggu_sparepart'])->count();
        $totalSiapDiambil = $allCurrent->where('status', 'selesai')->where('is_cetak_nota', false)->count();

        // Sorting: Prioritaskan yang sedang diproses / selesai
        $sortDir = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['tanggal_masuk', 'no_servis', 'tipe_unit', 'keluhan', 'status', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'tanggal_masuk';
        $query->orderBy($sortColumn, $sortDir);

        $totalItems = (clone $query)->count();
        $servisList = $query->take($this->perPage)->get();
        $hasMore = $totalItems > $this->perPage;

        return view('livewire.servis.masuk.index', [
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
