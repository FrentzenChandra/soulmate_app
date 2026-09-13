<?php

namespace App\Livewire\Master\Ppn;

use App\Livewire\Traits\WithSorting;
use App\Models\MasterPpn;
use App\Models\Penjualan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Tarif PPN - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    public string $search = '';

    public string $statusFilter = 'semua';

    public int $perPage = 15;

    public bool $modalOpen = false;

    public bool $isEditMode = false;

    public ?int $ppnId = null;

    // Form fields
    public string $nama = '';

    public $persen = 0.00;

    public bool $is_aktif = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'semua'],
        'sortField' => ['except' => 'nama'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isOwner()) {
            abort(403, 'Hanya Owner (Kantor Pusat) yang memiliki otoritas mengelola tarif PPN.');
        }

        $this->sortField = 'nama';
        $this->sortDirection = 'asc';
    }

    public function loadMore(): void
    {
        $this->perPage += 15;
    }

    public function updatingSearch()
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['ppnId', 'nama', 'persen']);
        $this->is_aktif = true;
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $ppn = MasterPpn::findOrFail($id);

        $this->ppnId = $ppn->id;
        $this->nama = $ppn->nama;
        $this->persen = (float) $ppn->persen;
        $this->is_aktif = (bool) $ppn->is_aktif;

        $this->isEditMode = true;
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->resetValidation();
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:50'],
            'persen' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_aktif' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama tarif PPN wajib diisi.',
            'persen.required' => 'Persentase PPN wajib diisi.',
            'persen.numeric' => 'Persentase harus berupa angka.',
            'persen.min' => 'Persentase minimal 0%.',
            'persen.max' => 'Persentase maksimal 100%.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'nama' => trim($this->nama),
            'persen' => (float) $this->persen,
            'is_aktif' => (bool) $this->is_aktif,
        ];

        if ($this->isEditMode && $this->ppnId) {
            $ppn = MasterPpn::findOrFail($this->ppnId);
            $ppn->update($data);
            session()->flash('success', "Tarif PPN \"{$ppn->nama}\" berhasil diperbarui.");
        } else {
            $ppn = MasterPpn::create($data);
            session()->flash('success', "Tarif PPN baru \"{$ppn->nama}\" berhasil ditambahkan.");
        }

        $this->closeModal();
    }

    public function toggleStatus(int $id): void
    {
        $ppn = MasterPpn::findOrFail($id);
        $ppn->update(['is_aktif' => ! $ppn->is_aktif]);

        $statusText = $ppn->is_aktif ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Tarif PPN \"{$ppn->nama}\" berhasil {$statusText}.");
    }

    public function deletePpn(int $id): void
    {
        $ppn = MasterPpn::findOrFail($id);

        $isUsed = Penjualan::where('ppn_id', $id)->exists();
        if ($isUsed) {
            session()->flash('error', "Tarif PPN \"{$ppn->nama}\" tidak dapat dihapus karena sudah tercatat dalam transaksi penjualan. Anda dapat menonaktifkannya.");

            return;
        }

        $ppn->delete();
        session()->flash('success', "Tarif PPN \"{$ppn->nama}\" berhasil dihapus.");
    }

    public function render()
    {
        $query = MasterPpn::query()
            ->when($this->search, function ($q) {
                $q->where('nama', 'like', '%'.$this->search.'%');
            })
            ->when($this->statusFilter !== 'semua', function ($q) {
                $q->where('is_aktif', $this->statusFilter === 'aktif');
            });

        $allowedSorts = ['nama', 'persen', 'is_aktif', 'id', 'created_at'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'nama';
        $sortDir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';

        $totalPpn = (clone $query)->count();
        $ppns = $query->orderBy($sortColumn, $sortDir)->take($this->perPage)->get();
        $hasMore = $totalPpn > $this->perPage;

        return view('livewire.master.ppn.index', [
            'ppns' => $ppns,
            'hasMore' => $hasMore,
            'totalPpn' => $totalPpn,
        ]);
    }
}
