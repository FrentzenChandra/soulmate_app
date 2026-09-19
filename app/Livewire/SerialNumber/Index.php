<?php

namespace App\Livewire\SerialNumber;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
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
    public string $nama = '';

    public string $merk_tipe_spek = '';

    public string $sn = '';

    public string $distributor = '';

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

    public function updatingSearch()
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['snId', 'nama', 'merk_tipe_spek', 'sn', 'distributor']);
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $item = SerialNumber::findOrFail($id);

        $this->snId = $item->id;
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

    public function save(): void
    {
        $cabang = $this->activeCabang;

        $this->validate([
            'nama' => 'required|string|max:255',
            'merk_tipe_spek' => 'required|string|max:255',
            'sn' => 'required|string|max:255',
            'distributor' => 'nullable|string|max:255',
        ]);

        if ($this->isEditMode && $this->snId) {
            $item = SerialNumber::findOrFail($this->snId);
            $item->update([
                'nama' => trim($this->nama),
                'merk_tipe_spek' => trim($this->merk_tipe_spek),
                'sn' => trim($this->sn),
                'distributor' => trim($this->distributor) ?: null,
            ]);

            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Data Serial Number berhasil diperbarui.']);
        } else {
            SerialNumber::create([
                'cabang_id' => $cabang?->id,
                'user_id' => Auth::id(),
                'nama' => trim($this->nama),
                'merk_tipe_spek' => trim($this->merk_tipe_spek),
                'sn' => trim($this->sn),
                'distributor' => trim($this->distributor) ?: null,
            ]);

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
        $query = SerialNumber::query()->with(['user']);

        if ($cabang) {
            $query->where('cabang_id', $cabang->id);
        }

        if (! empty(trim($this->search))) {
            $terms = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $pattern = '%' . strtolower($term) . '%';
                    $q->where(function ($sub) use ($pattern) {
                        $sub->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(merk_tipe_spek) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(sn) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(distributor) LIKE ?', [$pattern]);
                    });
                }
            });
        }

        $sortCol = in_array($this->sortField, ['nama', 'merk_tipe_spek', 'sn', 'distributor', 'created_at']) ? $this->sortField : 'created_at';
        $query->orderBy($sortCol, $this->sortDirection === 'asc' ? 'asc' : 'desc');

        $items = $query->paginate($this->perPage);

        return view('livewire.serial-number.index', [
            'items' => $items,
        ]);
    }
}
