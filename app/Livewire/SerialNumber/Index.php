<?php

namespace App\Livewire\SerialNumber;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\SerialNumber;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Serial Number (SN) - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    public string $search = '';

    public int $perPage = 15;

    public bool $modalOpen = false;

    public bool $isEditMode = false;

    public ?int $snId = null;

    // Form fields
    public ?int $selectedCustomerId = null;

    public string $customerSearch = 'Konsumen';

    public string $newCustomerNoHp = '';

    public string $merk = '';

    public string $custom_merk = '';

    public string $tipe_spek = '';

    public string $nama = '';

    public string $merk_tipe_spek = '';

    public string $sn = '';

    public string $distributor = '';

    // Filter Periode (Mirip Transaksi FULL)
    public string $periodeMode = 'range'; // 'semua' | 'hari_ini' | 'range' | 'bulanan' | 'tahunan'

    public string $startDate = '';

    public string $endDate = '';

    public string $filterBulan = '';

    public string $filterTahun = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'periodeMode' => ['except' => 'range'],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'filterBulan' => ['except' => ''],
        'filterTahun' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole('Superadmin') && ! $user->hasRole('Admin') && ! $user->hasRole('Kasir'))) {
            abort(403, 'Akses menu Serial Number dibatasi.');
        }

        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
        $this->filterBulan = now()->format('m');
        $this->filterTahun = now()->format('Y');

        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';

        $this->clearSnCache();
    }

    public function setPeriodeMode(string $mode): void
    {
        $this->periodeMode = $mode;
        if ($mode === 'semua') {
            $this->startDate = '';
            $this->endDate = '';
        } elseif ($mode === 'hari_ini') {
            $this->startDate = now()->toDateString();
            $this->endDate = now()->toDateString();
        } elseif ($mode === 'range') {
            $this->startDate = now()->startOfMonth()->toDateString();
            $this->endDate = now()->toDateString();
        } elseif ($mode === 'bulanan') {
            $this->applyBulanan();
        } elseif ($mode === 'tahunan') {
            $this->applyTahunan();
        }
        $this->resetPage();
    }

    public function updatedFilterBulan(): void
    {
        if ($this->periodeMode === 'bulanan') {
            $this->applyBulanan();
        }
        $this->resetPage();
    }

    public function updatedFilterTahun(): void
    {
        if ($this->periodeMode === 'bulanan') {
            $this->applyBulanan();
        } elseif ($this->periodeMode === 'tahunan') {
            $this->applyTahunan();
        }
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
    }

    private function applyBulanan(): void
    {
        $year = (int) ($this->filterTahun ?: now()->year);
        $month = (int) ($this->filterBulan ?: now()->month);
        $date = Carbon::createFromDate($year, $month, 1);
        $this->startDate = $date->copy()->startOfMonth()->toDateString();
        $this->endDate = $date->copy()->endOfMonth()->toDateString();
    }

    private function applyTahunan(): void
    {
        $year = (int) ($this->filterTahun ?: now()->year);
        $date = Carbon::createFromDate($year, 1, 1);
        $this->startDate = $date->copy()->startOfYear()->toDateString();
        $this->endDate = $date->copy()->endOfYear()->toDateString();
    }

    public function applyDateFilter($query): void
    {
        if ($this->periodeMode === 'semua') {
            return;
        }

        if (! empty($this->startDate) && ! empty($this->endDate)) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ]);
        }
    }

    public function getBrandStatsProperty(): array
    {
        $cabang = $this->activeCabang;
        $cabangId = $cabang ? $cabang->id : 'all';

        $v = Cache::get('sn_brand_stats_version', 1);
        $paramHash = md5("{$this->periodeMode}_{$this->startDate}_{$this->endDate}_{$this->filterBulan}_{$this->filterTahun}");
        $cacheKey = "sn_brand_stats_v{$v}_{$cabangId}_{$paramHash}";

        return Cache::remember($cacheKey, 3600, function () use ($cabang) {
            $query = SerialNumber::query();

            if ($cabang) {
                $query->where('cabang_id', $cabang->id);
            }

            $this->applyDateFilter($query);

            $stats = $query->selectRaw("COALESCE(NULLIF(merk, ''), 'Lainnya') as brand, COUNT(*) as total")
                ->groupBy('brand')
                ->orderByDesc('total')
                ->pluck('total', 'brand')
                ->toArray();

            $totalUnit = array_sum($stats);

            return [
                'by_brand' => $stats,
                'total_unit' => $totalUnit,
            ];
        });
    }

    protected function clearSnCache(): void
    {
        Cache::forget('serial_number_merks_list');

        if (! Cache::has('sn_brand_stats_version')) {
            Cache::put('sn_brand_stats_version', 1, 86400 * 30);
        } else {
            Cache::increment('sn_brand_stats_version');
        }
    }

    public function getMerkListProperty(): array
    {
        return Cache::remember('serial_number_merks_list', 3600, function () {
            $defaultMerks = ['Asus', 'Lenovo', 'Acer', 'HP', 'MSI', 'Advan', 'Axioo', 'Apple'];
            $dbMerks = [];

            if (\Illuminate\Support\Facades\Schema::hasColumn('serial_numbers', 'merk')) {
                $dbMerks = SerialNumber::whereNotNull('merk')
                    ->where('merk', '!=', '')
                    ->distinct()
                    ->pluck('merk')
                    ->toArray();
            }

            $allMerks = array_unique(array_merge($defaultMerks, $dbMerks));
            sort($allMerks);

            return array_values($allMerks);
        });
    }

    public function getActiveCabangProperty(): ?Cabang
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        if ($user->isOwner()) {
            $sess = session('active_branch_id', 'hq');
            if ($sess !== 'hq') {
                return Cabang::find((int) $sess);
            }

            return Cabang::where('status', 'aktif')->first();
        }

        return $user->cabang;
    }

    public function getCustomersProperty()
    {
        $cabang = $this->activeCabang;
        $query = Customer::query();
        if ($cabang && ! Auth::user()?->isOwner()) {
            $query->where('cabang_id', $cabang->id);
        }

        return $query->orderBy('nama')->get();
    }

    public function updatingSearch(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function selectCustomer(int $id, string $nama, ?string $noHp = null): void
    {
        $this->selectedCustomerId = $id;
        $this->customerSearch = $nama;
        if ($noHp) {
            $this->newCustomerNoHp = $noHp;
        }
    }

    public function resetCustomer(): void
    {
        $this->selectedCustomerId = null;
        $this->customerSearch = 'Konsumen';
        $this->newCustomerNoHp = '';
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['snId', 'nama', 'merk', 'custom_merk', 'tipe_spek', 'merk_tipe_spek', 'sn', 'distributor', 'selectedCustomerId', 'newCustomerNoHp']);
        $this->customerSearch = 'Konsumen';
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $item = SerialNumber::findOrFail($id);

        $this->snId = $item->id;
        $this->selectedCustomerId = $item->customer_id;
        $this->customerSearch = $item->customer ? $item->customer->nama : '';
        $this->newCustomerNoHp = $item->customer ? ($item->customer->no_hp ?? '') : '';
        $this->merk = $item->merk ?: '';
        $this->custom_merk = '';
        $this->tipe_spek = $item->tipe_spek ?: $item->merk_tipe_spek ?: '';
        $this->nama = $item->nama ?: '';
        $this->sn = $item->sn;
        $this->distributor = $item->distributor ?: '';

        $this->isEditMode = true;
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $cabang = $this->activeCabang;
        $finalCustomerId = null;

        if ($this->selectedCustomerId) {
            $finalCustomerId = $this->selectedCustomerId;
            if (! empty($this->newCustomerNoHp)) {
                $cleanHp = preg_replace('/[^0-9]/', '', $this->newCustomerNoHp);
                $cust = Customer::find($this->selectedCustomerId);
                if ($cust && empty($cust->no_hp) && $cleanHp) {
                    $cust->update(['no_hp' => $cleanHp]);
                }
            }
        } elseif (! empty(trim($this->customerSearch))) {
            $cabangId = $cabang?->id ?? Auth::user()?->cabang_id ?? Cabang::where('status', 'aktif')->value('id');
            $cleanHp = preg_replace('/[^0-9]/', '', $this->newCustomerNoHp);
            $customer = Customer::firstOrCreate(
                [
                    'cabang_id' => $cabangId,
                    'nama' => trim($this->customerSearch),
                ],
                [
                    'no_hp' => $cleanHp ?: null,
                    'tipe' => 'langganan',
                ]
            );
            if ($cleanHp && empty($customer->no_hp)) {
                $customer->update(['no_hp' => $cleanHp]);
            }
            $finalCustomerId = $customer->id;
        }

        $this->validate([
            'merk' => 'required|string|max:100',
            'custom_merk' => 'required_if:merk,__custom__|nullable|string|max:100',
            'tipe_spek' => 'required|string|max:255',
            'sn' => 'required|string|max:255',
            'distributor' => 'nullable|string|max:255',
        ], [
            'merk.required' => 'Merk laptop wajib dipilih.',
            'custom_merk.required_if' => 'Nama merk baru wajib diisi.',
            'tipe_spek.required' => 'Tipe spek wajib diisi.',
            'sn.required' => 'Serial number wajib diisi.',
        ]);

        $finalMerk = $this->merk === '__custom__' ? trim($this->custom_merk) : trim($this->merk);
        $finalTipeSpek = trim($this->tipe_spek);
        $finalNama = trim($finalMerk . ' ' . $finalTipeSpek);

        $data = [
            'customer_id' => $finalCustomerId,
            'user_id' => Auth::id(),
            'nama' => $finalNama,
            'merk' => $finalMerk,
            'tipe_spek' => $finalTipeSpek,
            'merk_tipe_spek' => $finalNama,
            'sn' => trim($this->sn),
            'distributor' => trim($this->distributor) ?: null,
        ];

        if ($this->isEditMode && $this->snId) {
            $item = SerialNumber::findOrFail($this->snId);
            $item->update($data);

            $this->clearSnCache();
            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Data Serial Number berhasil diperbarui.']);
        } else {
            $data['cabang_id'] = $cabang?->id ?? Auth::user()?->cabang_id;
            SerialNumber::create($data);

            $this->clearSnCache();
            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Data Serial Number berhasil ditambahkan.']);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $item = SerialNumber::findOrFail($id);
        $item->delete();

        $this->clearSnCache();
        $this->dispatch('swal:success', ['title' => 'Dihapus!', 'text' => 'Data Serial Number berhasil dihapus.']);
    }

    public function render()
    {
        $cabang = $this->activeCabang;

        // Customer suggestions for modal
        $customerSuggestions = [];
        if ($this->modalOpen && ! empty(trim($this->customerSearch)) && ! $this->selectedCustomerId) {
            $custQuery = Customer::query();
            if ($cabang && ! Auth::user()?->isOwner()) {
                $custQuery->where('cabang_id', $cabang->id);
            }
            $cleanSearch = trim($this->customerSearch);
            $words = array_filter(explode(' ', $cleanSearch));
            $customerSuggestions = $custQuery->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                          ->orWhereRaw('LOWER(COALESCE(no_hp, \'\')) LIKE ?', [$pattern]);
                    });
                }
            })->take(5)->get();
        }

        $query = SerialNumber::query()->with(['customer', 'cabang']);

        if ($cabang) {
            $query->where('cabang_id', $cabang->id);
        }

        $this->applyDateFilter($query);

        if (! empty(trim($this->search))) {
            $terms = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $pattern = '%'.strtolower($term).'%';
                    $q->where(function ($sub) use ($pattern) {
                        $sub->whereRaw("LOWER(COALESCE(merk, '')) LIKE ?", [$pattern])
                            ->orWhereRaw("LOWER(COALESCE(tipe_spek, '')) LIKE ?", [$pattern])
                            ->orWhereRaw("LOWER(COALESCE(nama, '')) LIKE ?", [$pattern])
                            ->orWhereRaw("LOWER(COALESCE(merk_tipe_spek, '')) LIKE ?", [$pattern])
                            ->orWhereRaw("LOWER(COALESCE(sn, '')) LIKE ?", [$pattern])
                            ->orWhereRaw("LOWER(COALESCE(distributor, '')) LIKE ?", [$pattern])
                            ->orWhereHas('customer', function ($cq) use ($pattern) {
                                $cq->whereRaw("LOWER(COALESCE(nama, '')) LIKE ?", [$pattern])
                                    ->orWhereRaw("LOWER(COALESCE(no_hp, '')) LIKE ?", [$pattern]);
                            });
                    });
                }
            });
        }

        $sortCol = in_array($this->sortField, ['customer_id', 'merk', 'tipe_spek', 'nama', 'sn', 'distributor', 'created_at']) ? $this->sortField : 'created_at';
        $query->orderBy($sortCol, $this->sortDirection === 'asc' ? 'asc' : 'desc');

        $items = $query->paginate($this->perPage);

        return view('livewire.serial-number.index', [
            'items' => $items,
            'customerSuggestions' => $customerSuggestions,
            'merkList' => $this->merkList,
            'brandStats' => $this->brandStats,
        ]);
    }
}
