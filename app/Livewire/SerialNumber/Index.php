<?php

namespace App\Livewire\SerialNumber;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\SerialNumber;
use Illuminate\Support\Facades\Auth;
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
    public ?int $customer_id = null;

    public string $nama = '';

    public string $merk_tipe_spek = '';

    public string $sn = '';

    public string $distributor = '';

    // Quick Add Customer Form Modal
    public bool $customerModalOpen = false;

    public string $new_customer_nama = '';

    public string $new_customer_hp = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! ($user->isOwner() || $user->isSuperadmin() || $user->isAdmin() || $user->isTeknisi())) {
            abort(403, 'Akses menu Serial Number dibatasi.');
        }

        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
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

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['snId', 'nama', 'merk_tipe_spek', 'sn', 'distributor', 'customer_id']);
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $item = SerialNumber::findOrFail($id);

        $this->snId = $item->id;
        $this->customer_id = $item->customer_id;
        $this->nama = $item->nama;
        $this->merk_tipe_spek = $item->merk_tipe_spek;
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

    public function openCustomerModal(): void
    {
        $this->resetValidation();
        $this->new_customer_nama = '';
        $this->new_customer_hp = '';
        $this->customerModalOpen = true;
    }

    public function closeCustomerModal(): void
    {
        $this->customerModalOpen = false;
        $this->resetValidation();
    }

    public function saveNewCustomer(): void
    {
        $this->validate([
            'new_customer_nama' => 'required|string|max:150',
            'new_customer_hp' => 'nullable|string|max:30',
        ], [
            'new_customer_nama.required' => 'Nama pelanggan wajib diisi.',
        ]);

        $cabang = $this->activeCabang;
        $cabangId = $cabang?->id ?? Auth::user()?->cabang_id ?? Cabang::where('status', 'aktif')->value('id');

        $customer = Customer::create([
            'cabang_id' => $cabangId,
            'nama' => trim($this->new_customer_nama),
            'no_hp' => trim($this->new_customer_hp) ?: null,
            'tipe' => 'langganan',
        ]);

        $this->customer_id = $customer->id;
        $this->customerModalOpen = false;
        $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => "Pelanggan '{$customer->nama}' berhasil ditambahkan."]);
    }

    public function save(): void
    {
        $cabang = $this->activeCabang;

        $this->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'nama' => 'required|string|max:255',
            'merk_tipe_spek' => 'required|string|max:255',
            'sn' => 'required|string|max:255',
            'distributor' => 'nullable|string|max:255',
        ], [
            'nama.required' => 'Nama barang/unit wajib diisi.',
            'merk_tipe_spek.required' => 'Spesifikasi/tipe wajib diisi.',
            'sn.required' => 'Serial number wajib diisi.',
        ]);

        $data = [
            'customer_id' => $this->customer_id,
            'user_id' => Auth::id(),
            'nama' => trim($this->nama),
            'merk_tipe_spek' => trim($this->merk_tipe_spek),
            'sn' => trim($this->sn),
            'distributor' => trim($this->distributor) ?: null,
        ];

        if ($this->isEditMode && $this->snId) {
            $item = SerialNumber::findOrFail($this->snId);
            $item->update($data);

            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Data Serial Number berhasil diperbarui.']);
        } else {
            $data['cabang_id'] = $cabang?->id ?? Auth::user()?->cabang_id;
            SerialNumber::create($data);

            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Data Serial Number berhasil ditambahkan.']);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $item = SerialNumber::findOrFail($id);
        $item->delete();

        $this->dispatch('swal:success', ['title' => 'Dihapus!', 'text' => 'Data Serial Number berhasil dihapus.']);
    }

    public function render()
    {
        $cabang = $this->activeCabang;
        $query = SerialNumber::query()->with(['customer', 'cabang']);

        if ($cabang) {
            $query->where('cabang_id', $cabang->id);
        }

        if (! empty(trim($this->search))) {
            $terms = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $pattern = '%'.strtolower($term).'%';
                    $q->where(function ($sub) use ($pattern) {
                        $sub->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(merk_tipe_spek) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(sn) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(distributor) LIKE ?', [$pattern])
                            ->orWhereHas('customer', function ($cq) use ($pattern) {
                                $cq->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(no_hp) LIKE ?', [$pattern]);
                            });
                    });
                }
            });
        }

        $sortCol = in_array($this->sortField, ['nama', 'merk_tipe_spek', 'sn', 'distributor', 'created_at', 'customer_id']) ? $this->sortField : 'created_at';
        $query->orderBy($sortCol, $this->sortDirection === 'asc' ? 'asc' : 'desc');

        $items = $query->paginate($this->perPage);

        return view('livewire.serial-number.index', [
            'items' => $items,
            'customers' => $this->customers,
        ]);
    }
}
