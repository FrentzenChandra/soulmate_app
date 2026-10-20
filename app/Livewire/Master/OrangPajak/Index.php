<?php

namespace App\Livewire\Master\OrangPajak;

use App\Models\MasterOrangPajak;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Orang Pajak - Soulmate Komputer')]
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
    public string $npwp = '';
    public string $kontak = '';
    public bool $is_aktif = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'semua'],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->can('master.orang_pajak.view'))) {
            abort(403, 'Akses modul Master Orang Pajak dibatasi.');
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
        abort_if(! $user->isOwner() && ! $user->can('master.orang_pajak.create'), 403, 'Akses tambah rekanan pajak dibatasi.');

        $this->resetValidation();
        $this->reset(['selectedId', 'nama', 'npwp', 'kontak']);
        $this->is_aktif = true;
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function edit(int $id): void
    {
        $user = Auth::user();
        abort_if(! $user->isOwner() && ! $user->can('master.orang_pajak.edit'), 403, 'Akses edit rekanan pajak dibatasi.');

        $item = MasterOrangPajak::findOrFail($id);
        $this->selectedId = $item->id;
        $this->nama = $item->nama;
        $this->npwp = $item->npwp ?? '';
        $this->kontak = $item->kontak ?? '';
        $this->is_aktif = (bool) $item->is_aktif;

        $this->resetValidation();
        $this->isEditMode = true;
        $this->modalOpen = true;
    }

    public function save(): void
    {
        $user = Auth::user();
        $requiredPerm = $this->isEditMode ? 'master.orang_pajak.edit' : 'master.orang_pajak.create';
        abort_if(! $user->isOwner() && ! $user->can($requiredPerm), 403, 'Akses simpan rekanan pajak dibatasi.');

        $this->validate([
            'nama' => 'required|string|max:150',
            'npwp' => 'nullable|string|max:50',
            'kontak' => 'nullable|string|max:50',
            'is_aktif' => 'boolean',
        ], [
            'nama.required' => 'Nama rekanan / instansi pajak wajib diisi.',
        ]);

        if ($this->isEditMode && $this->selectedId) {
            $item = MasterOrangPajak::findOrFail($this->selectedId);
            $item->update([
                'nama' => trim($this->nama),
                'npwp' => trim($this->npwp) ?: null,
                'kontak' => trim($this->kontak) ?: null,
                'is_aktif' => $this->is_aktif,
            ]);
            session()->flash('success', 'Data Orang Pajak berhasil diperbarui.');
        } else {
            MasterOrangPajak::create([
                'nama' => trim($this->nama),
                'npwp' => trim($this->npwp) ?: null,
                'kontak' => trim($this->kontak) ?: null,
                'is_aktif' => $this->is_aktif,
            ]);
            session()->flash('success', 'Data Orang Pajak baru berhasil ditambahkan.');
        }

        $this->modalOpen = false;
    }

    public function delete(int $id): void
    {
        $user = Auth::user();
        abort_if(! $user->isOwner() && ! $user->can('master.orang_pajak.delete'), 403, 'Akses hapus rekanan pajak dibatasi.');

        $item = MasterOrangPajak::findOrFail($id);
        $item->delete();
        session()->flash('success', 'Data Orang Pajak berhasil dihapus.');
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
    }

    public function render()
    {
        $query = MasterOrangPajak::query();

        // Multi-Word Tokenized Substring Matching (Rule 3)
        if (! empty(trim($this->search))) {
            $words = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                           ->orWhereRaw("LOWER(COALESCE(npwp, '')) LIKE ?", [$pattern])
                           ->orWhereRaw("LOWER(COALESCE(kontak, '')) LIKE ?", [$pattern]);
                    });
                }
            });
        }

        if ($this->statusFilter === 'aktif') {
            $query->where('is_aktif', true);
        } elseif ($this->statusFilter === 'nonaktif') {
            $query->where('is_aktif', false);
        }

        $items = $query->orderBy('nama')->paginate($this->perPage);

        return view('livewire.master.orang-pajak.index', [
            'items' => $items,
        ]);
    }
}

