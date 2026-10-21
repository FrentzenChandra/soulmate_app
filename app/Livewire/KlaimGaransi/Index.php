<?php

namespace App\Livewire\KlaimGaransi;

use App\Models\Cabang;
use App\Models\Customer;
use App\Models\KlaimGaransi;
use App\Models\MasterDistributor;
use App\Models\Pegawai;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Klaim Garansi - Manajemen Servis & Klaim Distributor - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'semua')]
    public string $distributorFilter = 'semua';

    #[Url(except: 'semua')]
    public string $cabangFilter = 'semua';

    public string $periodeMode = 'bulanan'; // Default per bulan jalan
    public string $startDate = '';
    public string $endDate = '';
    public string $filterBulan = '';
    public string $filterTahun = '';

    public int $perPage = 200;

    // State Modal Form (Create / Edit)
    public bool $modalFormOpen = false;
    public ?int $klaimId = null;
    public string $noKlaim = '';
    public string $tanggalMasuk = '';

    // Data Pelanggan
    public string $customerType = 'langganan'; // 'langganan' | 'baru'
    public ?int $selectedCustomerId = null;
    public string $customerSearch = '';
    public string $selectedCustomerNama = '';
    public string $selectedCustomerKontak = '';
    public string $newCustomerNama = '';
    public string $newCustomerKontak = '';

    // Data Barang & Keluhan
    public string $barangSearch = '';
    public string $formBarang = '';
    public string $formKeluhan = '';

    // Distributor & Alur
    public ?int $formMasterDistributorId = null;
    public string $formTanggalKirim = '';
    public string $formTindakan = '';
    public string $formTanggalSelesaiKlaim = '';
    public string $formTanggalBarangKembali = '';
    public ?int $formPenerimaPegawaiId = null;

    // State Modal Detail
    public bool $modalDetailOpen = false;
    public ?object $selectedKlaim = null;

    // State Modal Hapus
    public bool $modalDeleteOpen = false;
    public ?int $deleteId = null;
    public string $deleteNoKlaim = '';

    public function mount(): void
    {
        $now = Carbon::now();
        $this->filterBulan = $now->format('m');
        $this->filterTahun = (string) $now->year;
        $this->tanggalMasuk = $now->toDateString();
        $this->setPeriodeDates();
    }

    public function setPeriodeMode(string $mode): void
    {
        $this->periodeMode = $mode;
        $this->setPeriodeDates();
        $this->resetPage();
    }

    public function updatedFilterBulan(): void
    {
        if ($this->periodeMode === 'bulanan') {
            $this->setPeriodeDates();
            $this->resetPage();
        }
    }

    public function updatedFilterTahun(): void
    {
        if (in_array($this->periodeMode, ['bulanan', 'tahunan'], true)) {
            $this->setPeriodeDates();
            $this->resetPage();
        }
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDistributorFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCabangFilter(): void
    {
        $this->resetPage();
    }

    private function setPeriodeDates(): void
    {
        $now = Carbon::now();
        if ($this->periodeMode === 'hari_ini') {
            $this->startDate = $now->toDateString();
            $this->endDate = $now->toDateString();
        } elseif ($this->periodeMode === 'range') {
            $this->startDate = $now->copy()->startOfMonth()->toDateString();
            $this->endDate = $now->toDateString();
        } elseif ($this->periodeMode === 'bulanan') {
            $bulan = (int) ($this->filterBulan ?: $now->format('m'));
            $tahun = (int) ($this->filterTahun ?: $now->year);
            $start = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
            $this->startDate = $start->toDateString();
            $this->endDate = $start->copy()->endOfMonth()->toDateString();
        } elseif ($this->periodeMode === 'tahunan') {
            $tahun = (int) ($this->filterTahun ?: $now->year);
            $start = Carbon::createFromDate($tahun, 1, 1)->startOfYear();
            $this->startDate = $start->toDateString();
            $this->endDate = $start->copy()->endOfYear()->toDateString();
        } else {
            // 'semua'
            $this->startDate = '';
            $this->endDate = '';
        }
    }

    // =========================================================================
    // CUSTOMER SELECTION & AUTOCOMPLETE (Rule 5)
    // =========================================================================

    public function selectCustomer(int $id, string $nama, ?string $kontak): void
    {
        $this->selectedCustomerId = $id;
        $this->selectedCustomerNama = $nama;
        $this->selectedCustomerKontak = $kontak ?? '';
        $this->customerSearch = '';
    }

    public function resetSelectedCustomer(): void
    {
        $this->selectedCustomerId = null;
        $this->selectedCustomerNama = '';
        $this->selectedCustomerKontak = '';
        $this->customerSearch = '';
    }

    // =========================================================================
    // PRODUCT SELECTION (Varchar string only, zero stock mutation)
    // =========================================================================

    public function selectProduct(string $namaProduk): void
    {
        $this->formBarang = $namaProduk;
        $this->barangSearch = '';
    }

    // =========================================================================
    // MODAL FORM (CREATE / EDIT)
    // =========================================================================

    public function openCreateModal(): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('klaim_garansi.create')), 403, 'Akses input klaim garansi dibatasi.');

        $this->resetForm();
        $this->tanggalMasuk = Carbon::today()->toDateString();
        $this->modalFormOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('klaim_garansi.edit')), 403, 'Akses edit klaim garansi dibatasi.');

        $klaim = KlaimGaransi::findOrFail($id);
        if (! $user->isOwner() && $klaim->cabang_id !== $user->cabang_id) {
            abort(403, 'Akses data cabang lain dibatasi.');
        }

        $this->resetForm();
        $this->klaimId = $klaim->id;
        $this->noKlaim = $klaim->no_klaim;
        $this->tanggalMasuk = $klaim->tanggal_masuk ? $klaim->tanggal_masuk->format('Y-m-d') : '';

        $this->selectedCustomerId = $klaim->customer_id;
        $this->selectedCustomerNama = $klaim->customer_nama;
        $this->selectedCustomerKontak = $klaim->customer_kontak ?? '';
        $this->customerType = 'langganan';

        $this->formBarang = $klaim->barang;
        $this->formKeluhan = $klaim->keluhan;
        $this->formMasterDistributorId = $klaim->master_distributor_id;

        $this->formTanggalKirim = $klaim->tanggal_kirim ? $klaim->tanggal_kirim->format('Y-m-d') : '';
        $this->formTindakan = $klaim->tindakan ?? '';
        $this->formTanggalSelesaiKlaim = $klaim->tanggal_selesai_klaim ? $klaim->tanggal_selesai_klaim->format('Y-m-d') : '';
        $this->formTanggalBarangKembali = $klaim->tanggal_barang_kembali ? $klaim->tanggal_barang_kembali->format('Y-m-d') : '';
        $this->formPenerimaPegawaiId = $klaim->penerima_pegawai_id;

        $this->modalFormOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalFormOpen = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->klaimId = null;
        $this->noKlaim = '';
        $this->tanggalMasuk = Carbon::today()->toDateString();

        $this->customerType = 'langganan';
        $this->selectedCustomerId = null;
        $this->customerSearch = '';
        $this->selectedCustomerNama = '';
        $this->selectedCustomerKontak = '';
        $this->newCustomerNama = '';
        $this->newCustomerKontak = '';

        $this->barangSearch = '';
        $this->formBarang = '';
        $this->formKeluhan = '';

        $this->formMasterDistributorId = null;
        $this->formTanggalKirim = '';
        $this->formTindakan = '';
        $this->formTanggalSelesaiKlaim = '';
        $this->formTanggalBarangKembali = '';
        $this->formPenerimaPegawaiId = null;

        $this->resetErrorBag();
    }

    public function save(): void
    {
        $user = Auth::user();
        if ($this->klaimId) {
            abort_if(! $user || (! $user->isOwner() && ! $user->can('klaim_garansi.edit')), 403, 'Akses edit klaim dibatasi.');
        } else {
            abort_if(! $user || (! $user->isOwner() && ! $user->can('klaim_garansi.create')), 403, 'Akses input klaim dibatasi.');
        }

        // Tentukan cabang_id aktif
        $activeBranchSession = session('active_branch_id', 'hq');
        if ($user->isOwner()) {
            $cabangId = ($activeBranchSession === 'hq' || ! $activeBranchSession)
                ? (Cabang::where('status', 'aktif')->orderBy('id')->value('id') ?? 1)
                : (int) $activeBranchSession;
        } else {
            $cabangId = $user->cabang_id ?? 1;
        }

        // Validasi
        $rules = [
            'tanggalMasuk' => 'required|date',
            'formBarang' => 'required|string|max:255',
            'formKeluhan' => 'required|string',
            'formMasterDistributorId' => 'nullable|exists:master_distributors,id',
            'formPenerimaPegawaiId' => 'nullable|exists:pegawais,id',
            'formTanggalKirim' => 'nullable|date',
            'formTanggalSelesaiKlaim' => 'nullable|date',
            'formTanggalBarangKembali' => 'nullable|date',
        ];

        if ($this->customerType === 'baru') {
            $rules['newCustomerNama'] = 'required|string|min:2|max:150';
            $rules['newCustomerKontak'] = 'nullable|string|max:50';
        } else {
            if (! $this->selectedCustomerId && ! $this->selectedCustomerNama) {
                $this->addError('customerSearch', 'Silakan pilih pelanggan terdaftar atau buat baru.');
                return;
            }
        }

        $this->validate($rules);

        // Proses Pelanggan
        $customerId = $this->selectedCustomerId;
        $customerNama = $this->selectedCustomerNama;
        $customerKontak = $this->selectedCustomerKontak;

        if ($this->customerType === 'baru') {
            $customer = Customer::create([
                'cabang_id' => $cabangId,
                'nama' => trim($this->newCustomerNama),
                'no_hp' => trim($this->newCustomerKontak) ?: null,
            ]);
            $customerId = $customer->id;
            $customerNama = $customer->nama;
            $customerKontak = $customer->no_hp ?? '';
        }

        // Snapshot nama distributor & penerima
        $distributorNama = null;
        if ($this->formMasterDistributorId) {
            $distributorNama = MasterDistributor::where('id', $this->formMasterDistributorId)->value('nama');
        }

        $penerimaNama = null;
        if ($this->formPenerimaPegawaiId) {
            $penerimaNama = Pegawai::where('id', $this->formPenerimaPegawaiId)->value('nama');
        }

        $payload = [
            'tanggal_masuk' => $this->tanggalMasuk,
            'customer_id' => $customerId,
            'customer_nama' => $customerNama,
            'customer_kontak' => $customerKontak ?: null,
            'barang' => trim($this->formBarang),
            'keluhan' => trim($this->formKeluhan),
            'master_distributor_id' => $this->formMasterDistributorId ?: null,
            'distributor_nama' => $distributorNama,
            'tanggal_kirim' => $this->formTanggalKirim ?: null,
            'tindakan' => trim($this->formTindakan) ?: null,
            'tanggal_selesai_klaim' => $this->formTanggalSelesaiKlaim ?: null,
            'tanggal_barang_kembali' => $this->formTanggalBarangKembali ?: null,
            'penerima_pegawai_id' => $this->formPenerimaPegawaiId ?: null,
            'penerima_nama' => $penerimaNama,
        ];

        if ($this->klaimId) {
            $klaim = KlaimGaransi::findOrFail($this->klaimId);
            $klaim->update($payload);
            session()->flash('success', "Klaim Garansi {$klaim->no_klaim} berhasil diperbarui.");
        } else {
            $payload['cabang_id'] = $cabangId;
            $payload['user_id'] = $user->id;
            $payload['no_klaim'] = KlaimGaransi::generateNoKlaim($cabangId, $this->tanggalMasuk);
            $klaim = KlaimGaransi::create($payload);
            session()->flash('success', "Klaim Garansi {$klaim->no_klaim} berhasil ditambahkan.");
        }

        $this->closeModal();
    }

    // =========================================================================
    // MODAL DETAIL & DELETE
    // =========================================================================

    public function openDetailModal(int $id): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('klaim_garansi.detail')), 403, 'Akses detail klaim dibatasi.');

        $this->selectedKlaim = DB::table('klaim_garansis')
            ->leftJoin('cabangs', 'cabangs.id', '=', 'klaim_garansis.cabang_id')
            ->where('klaim_garansis.id', $id)
            ->whereNull('klaim_garansis.deleted_at')
            ->select([
                'klaim_garansis.*',
                'cabangs.nama_cabang',
            ])
            ->first();

        $this->modalDetailOpen = true;
    }

    public function closeDetailModal(): void
    {
        $this->modalDetailOpen = false;
        $this->selectedKlaim = null;
    }

    public function confirmDelete(int $id, string $noKlaim): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('klaim_garansi.delete')), 403, 'Akses hapus klaim dibatasi.');

        $this->deleteId = $id;
        $this->deleteNoKlaim = $noKlaim;
        $this->modalDeleteOpen = true;
    }

    public function deleteKlaim(): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('klaim_garansi.delete')), 403, 'Akses hapus klaim dibatasi.');

        if (! $this->deleteId) return;

        $klaim = KlaimGaransi::find($this->deleteId);
        if ($klaim) {
            $no = $klaim->no_klaim;
            $klaim->delete();
            session()->flash('success', "Klaim Garansi {$no} berhasil dihapus.");
        }

        $this->modalDeleteOpen = false;
        $this->deleteId = null;
        $this->deleteNoKlaim = '';
    }

    // =========================================================================
    // EXPORT EXCEL
    // =========================================================================

    public function exportExcel()
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('klaim_garansi.export_excel')), 403, 'Akses export excel dibatasi.');

        $data = $this->buildQuery()->get();

        $csvFileName = 'Klaim_Garansi_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$csvFileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM
            fputs($handle, "\xEF\xBB\xBF");

            // Header kolom
            fputcsv($handle, [
                'No',
                'No Klaim',
                'Tanggal Selesai Klaim',
                'Tanggal Barang Masuk',
                'Customer',
                'Kontak',
                'Barang',
                'Keluhan',
                'Distributor',
                'Tanggal Kirim',
                'Tindakan',
                'Tanggal Barang Kembali',
                'Penerima Barang',
            ]);

            $no = 1;
            foreach ($data as $row) {
                fputcsv($handle, [
                    $no++,
                    $row->no_klaim,
                    $row->tanggal_selesai_klaim ?: '-',
                    $row->tanggal_masuk ?: '-',
                    $row->customer_nama ?: '-',
                    $row->customer_kontak ?: '-',
                    $row->barang,
                    $row->keluhan,
                    $row->distributor_nama ?: '-',
                    $row->tanggal_kirim ?: '-',
                    $row->tindakan ?: '-',
                    $row->tanggal_barang_kembali ?: '-',
                    $row->penerima_nama ?: '-',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    // =========================================================================
    // QUERY BUILDER (Slim Query + toBase)
    // =========================================================================

    private function buildQuery()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $query = DB::table('klaim_garansis')
            ->whereNull('deleted_at');

        // Filter Cabang
        if ($isHQ) {
            if ($this->cabangFilter !== 'semua') {
                $query->where('cabang_id', (int) $this->cabangFilter);
            }
        } else {
            $cabangId = $isOwner ? (int) $activeBranchSession : ($user->cabang_id ?? 1);
            $query->where('cabang_id', $cabangId);
        }

        // Filter Distributor
        if ($this->distributorFilter !== 'semua') {
            $query->where('master_distributor_id', (int) $this->distributorFilter);
        }

        // Filter Tanggal Periode
        if ($this->startDate && $this->endDate) {
            $query->where(function ($q) {
                $q->whereBetween('tanggal_masuk', [$this->startDate, $this->endDate])
                  ->orWhereBetween('tanggal_selesai_klaim', [$this->startDate, $this->endDate]);
            });
        }

        // Non-Strict Multi-Word Search (Rule 3)
        if ($this->search = trim($this->search)) {
            $tokens = array_filter(explode(' ', $this->search));
            foreach ($tokens as $token) {
                $word = '%' . strtolower($token) . '%';
                $query->where(function ($q) use ($word) {
                    $q->whereRaw('LOWER(no_klaim) LIKE ?', [$word])
                      ->orWhereRaw('LOWER(customer_nama) LIKE ?', [$word])
                      ->orWhereRaw('LOWER(barang) LIKE ?', [$word])
                      ->orWhereRaw('LOWER(keluhan) LIKE ?', [$word])
                      ->orWhereRaw('LOWER(distributor_nama) LIKE ?', [$word])
                      ->orWhereRaw('LOWER(tindakan) LIKE ?', [$word])
                      ->orWhereRaw('LOWER(penerima_nama) LIKE ?', [$word]);
                });
            }
        }

        // Slim Select Payload
        $query->select([
            'id',
            'cabang_id',
            'no_klaim',
            'tanggal_masuk',
            'customer_nama',
            'customer_kontak',
            'barang',
            'keluhan',
            'distributor_nama',
            'tanggal_kirim',
            'tindakan',
            'tanggal_selesai_klaim',
            'tanggal_barang_kembali',
            'penerima_nama',
        ]);

        // Default Sort: Tanggal Selesai Klaim Descended (yang baru selesai di atas, lalu null di bawah), lalu tanggal_masuk desc
        $query->orderByRaw('CASE WHEN tanggal_selesai_klaim IS NULL THEN 1 ELSE 0 END ASC')
              ->orderByDesc('tanggal_selesai_klaim')
              ->orderByDesc('tanggal_masuk')
              ->orderByDesc('id');

        return $query;
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $cabangs = $isHQ ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get(['id', 'nama_cabang']) : collect();
        $distributors = MasterDistributor::aktif()->orderBy('nama')->get(['id', 'nama']);

        $currentCabangId = $isHQ ? 1 : ($isOwner ? (int) $activeBranchSession : ($user->cabang_id ?? 1));
        $pegawais = Pegawai::where('is_aktif', true)
            ->when(! $isHQ, fn($q) => $q->where(fn($sub) => $sub->where('cabang_id', $currentCabangId)->orWhereNull('cabang_id')))
            ->orderBy('nama')
            ->get(['id', 'nama']);

        // Pelanggan suggestions saat ketik di modal form
        $customerSuggestions = collect();
        if ($this->modalFormOpen && $this->customerType === 'langganan' && strlen(trim($this->customerSearch)) >= 1) {
            $cSearch = trim($this->customerSearch);
            $cTokens = array_filter(explode(' ', $cSearch));
            $cQuery = Customer::query()->where('cabang_id', $currentCabangId);
            foreach ($cTokens as $token) {
                $word = '%' . strtolower($token) . '%';
                $cQuery->where(function ($q) use ($word) {
                    $q->whereRaw('LOWER(nama) LIKE ?', [$word])
                      ->orWhereRaw('LOWER(no_hp) LIKE ?', [$word]);
                });
            }
            $customerSuggestions = $cQuery->orderBy('nama')->limit(8)->get(['id', 'nama', 'no_hp']);
        }

        // Produk suggestions saat ketik nama barang di modal form (tanpa mutasi stok)
        $productSuggestions = collect();
        if ($this->modalFormOpen && strlen(trim($this->barangSearch)) >= 2) {
            $pSearch = trim($this->barangSearch);
            $pTokens = array_filter(explode(' ', $pSearch));
            $pQuery = Produk::query();
            foreach ($pTokens as $token) {
                $word = '%' . strtolower($token) . '%';
                $pQuery->where(function ($q) use ($word) {
                    $q->whereRaw('LOWER(nama_produk) LIKE ?', [$word])
                      ->orWhereRaw('LOWER(barcode) LIKE ?', [$word]);
                });
            }
            $productSuggestions = $pQuery->orderBy('nama_produk')->limit(8)->get(['id', 'nama_produk']);
        }

        // Query Utama dengan toBase() (Ponytail requirement: slim memory, no model hydration overhead)
        $klaimList = $this->buildQuery()->paginate($this->perPage);

        return view('livewire.klaim-garansi.index', [
            'klaimList' => $klaimList,
            'cabangs' => $cabangs,
            'distributors' => $distributors,
            'pegawais' => $pegawais,
            'customerSuggestions' => $customerSuggestions,
            'productSuggestions' => $productSuggestions,
            'isHQ' => $isHQ,
        ]);
    }
}

