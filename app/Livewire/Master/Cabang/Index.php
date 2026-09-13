<?php

namespace App\Livewire\Master\Cabang;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Cabang - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination;
    use WithSorting;

    public string $search = '';

    public string $statusFilter = 'semua';

    public int $perPage = 15;

    public bool $modalOpen = false;

    public bool $isEditMode = false;

    public ?int $cabangId = null;

    // Form fields
    public string $kode_cabang = '';

    public string $nama_cabang = '';

    public string $alamat = '';

    public string $no_telp = '';

    public string $no_wa = '';

    public string $status = 'aktif';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'semua'],
        'sortField' => ['except' => 'kode_cabang'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount()
    {
        $this->sortField = 'kode_cabang';
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

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['cabangId', 'kode_cabang', 'nama_cabang', 'alamat', 'no_telp', 'no_wa']);
        $this->status = 'aktif';
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id)
    {
        $this->resetValidation();
        $cabang = Cabang::findOrFail($id);

        $this->cabangId = $cabang->id;
        $this->kode_cabang = $cabang->kode_cabang;
        $this->nama_cabang = $cabang->nama_cabang;
        $this->alamat = $cabang->alamat ?? '';
        $this->no_telp = $cabang->no_telp ?? '';
        $this->no_wa = $cabang->no_wa ?? '';
        $this->status = $cabang->status;

        $this->isEditMode = true;
        $this->modalOpen = true;
    }

    public function closeModal()
    {
        $this->modalOpen = false;
        $this->resetValidation();
    }

    public function save()
    {
        $validated = $this->validate([
            'kode_cabang' => [
                'required',
                'string',
                'max:20',
                'unique:cabangs,kode_cabang,'.($this->cabangId ?? 'NULL').',id',
            ],
            'nama_cabang' => 'required|string|max:100',
            'alamat' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:20',
            'no_wa' => 'required|string|max:20',
            'status' => 'required|in:aktif,nonaktif',
        ], [
            'kode_cabang.required' => 'Kode cabang wajib diisi.',
            'kode_cabang.unique' => 'Kode cabang ini sudah digunakan oleh cabang lain.',
            'nama_cabang.required' => 'Nama cabang wajib diisi.',
            'no_wa.required' => 'Nomor WhatsApp pesanan cabang wajib diisi.',
        ]);

        if ($this->isEditMode && $this->cabangId) {
            $cabang = Cabang::findOrFail($this->cabangId);
            $cabang->update($validated);
            session()->flash('success', "Data cabang {$cabang->nama_cabang} berhasil diperbarui.");
        } else {
            Cabang::create($validated);
            session()->flash('success', "Cabang baru {$this->nama_cabang} berhasil ditambahkan.");
        }

        $this->closeModal();
    }

    public function toggleStatus(int $id)
    {
        $cabang = Cabang::findOrFail($id);
        $newStatus = $cabang->status === 'aktif' ? 'nonaktif' : 'aktif';
        $cabang->update(['status' => $newStatus]);

        session()->flash('success', "Status cabang {$cabang->nama_cabang} diubah menjadi ".ucfirst($newStatus).'.');
    }

    public function render()
    {
        $query = Cabang::withCount(['users', 'stoks', 'servisMasuks'])
            ->with(['users' => function ($q) {
                $q->role('Superadmin');
            }])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nama_cabang', 'like', '%'.$this->search.'%')
                        ->orWhere('kode_cabang', 'like', '%'.$this->search.'%')
                        ->orWhere('alamat', 'like', '%'.$this->search.'%')
                        ->orWhere('no_wa', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== 'semua', function ($q) {
                $q->where('status', $this->statusFilter);
            });

        $allowedSorts = ['kode_cabang', 'nama_cabang', 'no_wa', 'status', 'created_at', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'kode_cabang';
        $sortDir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';

        $totalCabang = (clone $query)->count();
        $cabangs = $query->orderBy($sortColumn, $sortDir)->take($this->perPage)->get();
        $hasMore = $totalCabang > $this->perPage;

        return view('livewire.master.cabang.index', [
            'cabangs' => $cabangs,
            'hasMore' => $hasMore,
            'totalCabang' => $totalCabang,
        ]);
    }
}
