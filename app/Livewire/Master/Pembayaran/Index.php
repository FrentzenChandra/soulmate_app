<?php

namespace App\Livewire\Master\Pembayaran;

use App\Livewire\Traits\WithSorting;
use App\Models\MetodePembayaran;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Metode Pembayaran - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    public string $search = '';

    public string $kategoriFilter = 'semua';

    public string $statusFilter = 'semua';

    public int $perPage = 15;

    public bool $modalOpen = false;

    public bool $isEditMode = false;

    public ?int $metodeId = null;

    // Form fields
    public string $nama = '';

    public string $kategori = 'tunai';

    public string $nomor_rekening = '';

    public string $atas_nama = '';

    public int $urutan = 0;

    public bool $is_aktif = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'kategoriFilter' => ['except' => 'semua'],
        'statusFilter' => ['except' => 'semua'],
        'sortField' => ['except' => 'urutan'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! ($user->isOwner() || $user->isSuperadmin())) {
            abort(403, 'Akses terbatas untuk pengelolaan metode pembayaran.');
        }

        $this->sortField = 'urutan';
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

    public function updatingKategoriFilter()
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
        $this->reset(['metodeId', 'nama', 'nomor_rekening', 'atas_nama']);
        $this->kategori = 'tunai';
        $this->urutan = MetodePembayaran::max('urutan') + 1;
        $this->is_aktif = true;
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $metode = MetodePembayaran::findOrFail($id);

        $this->metodeId = $metode->id;
        $this->nama = $metode->nama;
        $this->kategori = $metode->kategori ?: 'tunai';
        $this->nomor_rekening = $metode->nomor_rekening ?: '';
        $this->atas_nama = $metode->atas_nama ?: '';
        $this->urutan = (int) $metode->urutan;
        $this->is_aktif = (bool) $metode->is_aktif;

        $this->isEditMode = true;
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->resetValidation();
        $this->reset(['metodeId', 'nama', 'nomor_rekening', 'atas_nama', 'isEditMode']);
    }

    public function save(): void
    {
        $this->validate([
            'nama' => 'required|string|max:50',
            'kategori' => 'required|string|in:tunai,transfer,qris,debit,lainnya',
            'nomor_rekening' => 'nullable|string|max:50',
            'atas_nama' => 'nullable|string|max:100',
            'urutan' => 'required|integer|min:0',
            'is_aktif' => 'boolean',
        ]);

        if ($this->isEditMode && $this->metodeId) {
            $metode = MetodePembayaran::findOrFail($this->metodeId);
            $metode->update([
                'nama' => trim($this->nama),
                'kategori' => $this->kategori,
                'nomor_rekening' => trim($this->nomor_rekening) ?: null,
                'atas_nama' => trim($this->atas_nama) ?: null,
                'urutan' => (int) $this->urutan,
                'is_aktif' => $this->is_aktif,
            ]);

            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Metode pembayaran berhasil diperbarui.']);
        } else {
            MetodePembayaran::create([
                'nama' => trim($this->nama),
                'kategori' => $this->kategori,
                'nomor_rekening' => trim($this->nomor_rekening) ?: null,
                'atas_nama' => trim($this->atas_nama) ?: null,
                'urutan' => (int) $this->urutan,
                'is_aktif' => $this->is_aktif,
            ]);

            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Metode pembayaran baru berhasil ditambahkan.']);
        }

        $this->closeModal();
    }

    public function toggleStatus(int $id): void
    {
        $metode = MetodePembayaran::findOrFail($id);
        $metode->is_aktif = ! $metode->is_aktif;
        $metode->save();

        $statusText = $metode->is_aktif ? 'diaktifkan' : 'dinonaktifkan';
        $this->dispatch('swal:success', ['title' => 'Status Diperbarui', 'text' => "Metode pembayaran {$metode->nama} berhasil {$statusText}."]);
    }

    public function delete(int $id): void
    {
        $metode = MetodePembayaran::findOrFail($id);
        $metode->delete();

        $this->dispatch('swal:success', ['title' => 'Dihapus', 'text' => 'Metode pembayaran berhasil dihapus.']);
    }

    public function render()
    {
        $query = MetodePembayaran::query();

        // 1. Universal Multi-Word Loose Search
        if (! empty(trim($this->search))) {
            $terms = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $pattern = '%' . strtolower($term) . '%';
                    $q->where(function ($sub) use ($pattern) {
                        $sub->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(kategori) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(nomor_rekening) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(atas_nama) LIKE ?', [$pattern]);
                    });
                }
            });
        }

        // 2. Kategori Filter
        if ($this->kategoriFilter !== 'semua') {
            $query->where('kategori', $this->kategoriFilter);
        }

        // 3. Status Filter
        if ($this->statusFilter === 'aktif') {
            $query->where('is_aktif', true);
        } elseif ($this->statusFilter === 'nonaktif') {
            $query->where('is_aktif', false);
        }

        // 4. Dynamic Sorting
        $sortColumn = in_array($this->sortField, ['nama', 'kategori', 'urutan', 'is_aktif', 'created_at'])
            ? $this->sortField
            : 'urutan';
        $query->orderBy($sortColumn, $this->sortDirection === 'desc' ? 'desc' : 'asc');

        if ($sortColumn !== 'nama') {
            $query->orderBy('nama', 'asc');
        }

        $items = $query->paginate($this->perPage);

        return view('livewire.master.pembayaran.index', [
            'items' => $items,
        ]);
    }
}
