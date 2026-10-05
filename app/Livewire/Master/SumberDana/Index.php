<?php

namespace App\Livewire\Master\SumberDana;

use App\Livewire\Traits\WithSorting;
use App\Models\SumberDana;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Sumber Dana - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    public string $search = '';

    public string $statusFilter = 'semua';

    public int $perPage = 15;

    public bool $modalOpen = false;

    public bool $isEditMode = false;

    public ?int $sumberDanaId = null;

    public string $nama = '';

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
        if (! $user || (! $user->isOwner() && ! $user->can('master.settings.view'))) {
            abort(403, 'Akses terbatas untuk pengelolaan sumber dana.');
        }

        $this->sortField = 'nama';
        $this->sortDirection = 'asc';
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['sumberDanaId', 'nama']);
        $this->is_aktif = true;
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $item = SumberDana::findOrFail($id);

        $this->sumberDanaId = $item->id;
        $this->nama = $item->nama;
        $this->is_aktif = (bool) $item->is_aktif;

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
        $this->validate([
            'nama' => 'required|string|max:50',
            'is_aktif' => 'boolean',
        ]);

        if ($this->isEditMode && $this->sumberDanaId) {
            $item = SumberDana::findOrFail($this->sumberDanaId);
            $item->update([
                'nama' => trim($this->nama),
                'is_aktif' => $this->is_aktif,
            ]);

            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Sumber dana berhasil diperbarui.']);
        } else {
            SumberDana::create([
                'nama' => trim($this->nama),
                'is_aktif' => $this->is_aktif,
            ]);

            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Sumber dana baru berhasil ditambahkan.']);
        }

        $this->closeModal();
    }

    public function toggleStatus(int $id): void
    {
        $item = SumberDana::findOrFail($id);
        $item->is_aktif = ! $item->is_aktif;
        $item->save();

        $statusText = $item->is_aktif ? 'diaktifkan' : 'dinonaktifkan';
        $this->dispatch('swal:success', ['title' => 'Status Diperbarui', 'text' => "Sumber dana {$item->nama} berhasil {$statusText}."]);
    }

    public function delete(int $id): void
    {
        $item = SumberDana::findOrFail($id);
        $item->delete();

        $this->dispatch('swal:success', ['title' => 'Dihapus!', 'text' => 'Sumber dana berhasil dihapus.']);
    }

    public function render()
    {
        $query = SumberDana::query();

        if (! empty(trim($this->search))) {
            $terms = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $pattern = '%' . strtolower($term) . '%';
                    $q->whereRaw('LOWER(nama) LIKE ?', [$pattern]);
                }
            });
        }

        if ($this->statusFilter === 'aktif') {
            $query->where('is_aktif', true);
        } elseif ($this->statusFilter === 'nonaktif') {
            $query->where('is_aktif', false);
        }

        $sortCol = in_array($this->sortField, ['nama', 'is_aktif']) ? $this->sortField : 'nama';
        $query->orderBy($sortCol, $this->sortDirection === 'desc' ? 'desc' : 'asc');

        $items = $query->paginate($this->perPage);

        return view('livewire.master.sumber-dana.index', [
            'items' => $items,
        ]);
    }
}
