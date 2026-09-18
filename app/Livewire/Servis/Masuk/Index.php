<?php

namespace App\Livewire\Servis\Masuk;

use App\Events\ServisStatusUpdated;
use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\Customer;
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
#[Title('Servis Masuk - Penerimaan Unit - Soulmate Komputer')]
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

    // State Modal Form Penerimaan Servis Baru
    public bool $modalCreateOpen = false;

    public string $customerType = 'langganan'; // 'langganan' | 'baru'
    public ?int $selectedCustomerId = null;
    public string $customerSearch = '';
    public string $newCustomerNama = '';
    public string $newCustomerNoHp = '';

    public string $kategoriPerangkat = 'laptop';
    public string $tipeUnit = '';
    public string $serialNumber = '';
    public array $kelengkapanChips = ['Unit'];
    public string $customKelengkapan = '';
    public string $keluhan = '';
    public string $kondisiBarang = '';
    public string $catatan = '';

    // State Modal Detail & Log Riwayat
    public bool $modalDetailOpen = false;
    public ?ServisMasuk $selectedServis = null;

    // State Modal Update Status Cepat
    public bool $modalStatusOpen = false;
    public ?ServisMasuk $servisToUpdate = null;
    public string $updateStatus = 'perbaikan';
    public string $updateCatatan = '';

    // State Modal Konfirmasi Sukses & Cetak
    public bool $modalSuccessOpen = false;
    public ?ServisMasuk $lastCreatedServis = null;

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
    public function onServisStatusUpdated($payload = []): void
    {
        // Re-render when status changes in real-time via Laravel Reverb
    }

    public function updatedCustomerSearch(): void
    {
        if ($this->selectedCustomerId) {
            $this->selectedCustomerId = null;
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
            $this->kelengkapanChips = array_values(array_filter($this->kelengkapanChips, fn ($c) => $c !== $chip));
        } else {
            $this->kelengkapanChips[] = $chip;
        }
    }

    private function resetCreateForm(): void
    {
        $this->customerType = 'langganan';
        $this->selectedCustomerId = null;
        $this->customerSearch = '';
        $this->newCustomerNama = '';
        $this->newCustomerNoHp = '';
        $this->kategoriPerangkat = 'laptop';
        $this->tipeUnit = '';
        $this->serialNumber = '';
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

        // Tentukan Cabang Operasional
        $targetCabangId = $isHQ
            ? ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter) ? (int) $this->cabangFilter : Cabang::where('status', 'aktif')->first()?->id)
            : ($isOwner ? (int) $activeBranchSession : (int) $user->cabang_id);

        $cabang = Cabang::find($targetCabangId);
        if (! $cabang) {
            $this->addError('general', 'Cabang operasional tidak valid atau belum dipilih.');
            return;
        }

        // Validasi Form
        $rules = [
            'kategoriPerangkat' => 'required|in:laptop,komputer,printer,lainnya',
            'tipeUnit' => 'required|string|max:150',
            'serialNumber' => 'nullable|string|max:100',
            'keluhan' => 'required|string|min:3',
            'kondisiBarang' => 'nullable|string|max:500',
            'catatan' => 'nullable|string|max:500',
        ];

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
        ]);

        try {
            DB::beginTransaction();

            // 1. Tangani Data Pelanggan
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

            // 2. Generate Nomor Servis Atomik Harian: INV-SV-[CABANG]-[YYMMDD]-[00001]
            $now = Carbon::now();
            $dateCode = $now->format('ymd');
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

            // 3. Susun Teks Kelengkapan
            $allKelengkapan = $this->kelengkapanChips;
            if (! empty(trim($this->customKelengkapan))) {
                $allKelengkapan[] = trim($this->customKelengkapan);
            }
            $kelengkapanString = ! empty($allKelengkapan) ? implode(', ', array_unique($allKelengkapan)) : 'Hanya Unit';

            // 4. Simpan Record Servis Masuk
            $servis = ServisMasuk::create([
                'no_servis' => $noServis,
                'cabang_id' => $cabang->id,
                'customer_id' => $finalCustomerId,
                'teknisi_id' => null,
                'created_by' => Auth::id(),
                'kategori_perangkat' => $this->kategoriPerangkat,
                'tipe_unit' => trim($this->tipeUnit),
                'serial_number' => trim((string) $this->serialNumber) ?: null,
                'kelengkapan' => $kelengkapanString,
                'keluhan' => trim($this->keluhan),
                'kondisi_barang' => trim((string) $this->kondisiBarang) ?: null,
                'status' => 'pengecekan',
                'tanggal_masuk' => $now,
                'estimasi_biaya' => 0,
                'total_biaya' => 0,
                'catatan' => trim((string) $this->catatan) ?: null,
            ]);

            // 5. Catat Log Status Awal
            ServisStatusLog::create([
                'servis_id' => $servis->id,
                'user_id' => Auth::id(),
                'status' => 'pengecekan',
                'tanggal' => $now,
                'catatan' => 'Penerimaan unit servis baru di toko (Tahap Pengecekan/Diagnosa).',
            ]);

            DB::commit();

            // Broadcast real-time Reverb event
            try {
                broadcast(new ServisStatusUpdated($servis->id, $servis->status, $servis->no_servis_frontend, $servis->cabang_id));
            } catch (\Throwable $th) {
                // Silently continue if broadcast driver is not running
            }

            $this->modalCreateOpen = false;
            $this->lastCreatedServis = $servis->fresh(['customer', 'cabang', 'pembuat']);
            $this->modalSuccessOpen = true;

            // Trigger auto open & print tanda terima in new tab
            $this->dispatch('open-print-receipt', url: route('servis.tanda-terima', $servis->id) . '?autoprint=1');

            $this->resetCreateForm();
            session()->flash('success', "Unit servis {$servis->no_servis_frontend} berhasil diterima!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Gagal menyimpan data servis: ' . $e->getMessage());
        }
    }

    public function openDetailModal(int $id): void
    {
        $this->selectedServis = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'statusLogs.user', 'details.produk'])->findOrFail($id);
        $this->modalDetailOpen = true;
    }

    public function closeDetailModal(): void
    {
        $this->modalDetailOpen = false;
        $this->selectedServis = null;
    }

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

            // Broadcast real-time Reverb event
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

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $cabangs = $isHQ ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();

        // Autocomplete Customer untuk modal create (Hanya jika belum memilih pelanggan)
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

        // Query Utama Servis Masuk
        $query = ServisMasuk::with(['customer', 'cabang', 'pembuat']);

        // Scoping Cabang
        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            if ($targetCabangId) {
                $query->where('cabang_id', $targetCabangId);
            }
        } elseif ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)) {
            $query->where('cabang_id', (int) $this->cabangFilter);
        }

        // Filter Status
        if ($this->statusFilter !== 'semua' && ! empty($this->statusFilter)) {
            if ($this->statusFilter === 'perbaikan' || $this->statusFilter === 'pengerjaan') {
                $query->whereIn('status', ['perbaikan', 'pengerjaan']);
            } elseif ($this->statusFilter === 'sudah_diambil' || $this->statusFilter === 'sudah_dibayar') {
                $query->whereIn('status', ['sudah_diambil', 'sudah_dibayar']);
            } else {
                $query->where('status', $this->statusFilter);
            }
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

        // Metrik Ringkasan Antrean
        $metricsBase = clone $query;
        $allCurrent = $metricsBase->get();

        $totalUnitMasuk = $allCurrent->count();
        $totalPengecekan = $allCurrent->where('status', 'pengecekan')->count();
        $totalPengerjaan = $allCurrent->whereIn('status', ['perbaikan', 'pengerjaan'])->count();
        $totalSelesai = $allCurrent->where('status', 'selesai')->count();

        // Sorting
        $sortDir = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['tanggal_masuk', 'no_servis', 'tipe_unit', 'status', 'id'];
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
            'totalSelesai' => $totalSelesai,
            'cabangs' => $cabangs,
            'customerSuggestions' => $customerSuggestions,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ]);
    }
}
