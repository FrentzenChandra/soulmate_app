<?php

namespace App\Livewire\Master\Distributor;

use App\Models\MasterDistributor;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Distributor - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'semua'; // 'semua' | 'aktif' | 'nonaktif'
    public int $perPage = 15;

    public bool $modalOpen = false;
    public bool $isEditMode = false;
    public ?int $selectedId = null;

    // Form fields
    public string $nama = '';
    public bool $is_aktif = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'semua'],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->can('master.distributor.view'))) {
            abort(403, 'Akses modul Master Distributor dibatasi.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $user = Auth::user();
        abort_if(! $user->isOwner() && ! $user->can('master.distributor.create'), 403, 'Akses tambah distributor dibatasi.');

        $this->resetValidation();
        $this->reset(['selectedId', 'nama']);
        $this->is_aktif = true;
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function edit(int $id): void
    {
        $user = Auth::user();
        abort_if(! $user->isOwner() && ! $user->can('master.distributor.edit'), 403, 'Akses edit distributor dibatasi.');

        $item = MasterDistributor::findOrFail($id);
        $this->selectedId = $item->id;
        $this->nama = $item->nama;
        $this->is_aktif = (bool) $item->is_aktif;

        $this->resetValidation();
        $this->isEditMode = true;
        $this->modalOpen = true;
    }

    public function save(): void
    {
        $user = Auth::user();
        $requiredPerm = $this->isEditMode ? 'master.distributor.edit' : 'master.distributor.create';
        abort_if(! $user->isOwner() && ! $user->can($requiredPerm), 403, 'Akses simpan distributor dibatasi.');

        $this->validate([
            'nama' => 'required|string|max:150',
            'is_aktif' => 'boolean',
        ], [
            'nama.required' => 'Nama distributor wajib diisi.',
        ]);

        if ($this->isEditMode && $this->selectedId) {
            $item = MasterDistributor::findOrFail($this->selectedId);
            $item->update([
                'nama' => trim($this->nama),
                'is_aktif' => $this->is_aktif,
            ]);
            $this->dispatch('swal:toast', [
                'type' => 'success',
                'title' => 'Berhasil Diperbarui',
                'text' => "Data Distributor \"{$item->nama}\" berhasil diperbarui.",
            ]);
        } else {
            $item = MasterDistributor::create([
                'nama' => trim($this->nama),
                'is_aktif' => $this->is_aktif,
            ]);
            $this->dispatch('swal:toast', [
                'type' => 'success',
                'title' => 'Berhasil Ditambahkan',
                'text' => "Data Distributor \"{$item->nama}\" berhasil ditambahkan.",
            ]);
        }

        $this->modalOpen = false;
    }

    public function delete(int $id): void
    {
        $user = Auth::user();
        abort_if(! $user->isOwner() && ! $user->can('master.distributor.delete'), 403, 'Akses hapus distributor dibatasi.');

        $item = MasterDistributor::findOrFail($id);
        $nama = $item->nama;
        $item->delete();
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Berhasil Dihapus',
            'text' => "Data Distributor \"{$nama}\" berhasil dihapus.",
        ]);
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
    }

    public function render()
    {
        $query = MasterDistributor::query();

        // Multi-Word Tokenized Substring Matching (Rule 3)
        if (! empty(trim($this->search))) {
            $words = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->whereRaw('LOWER(nama) LIKE ?', [$pattern]);
                }
            });
        }

        if ($this->statusFilter === 'aktif') {
            $query->where('is_aktif', true);
        } elseif ($this->statusFilter === 'nonaktif') {
            $query->where('is_aktif', false);
        }

        $items = $query->orderBy('nama')->paginate($this->perPage);

        return view('livewire.master.distributor.index', [
            'items' => $items,
        ]);
    }
}

