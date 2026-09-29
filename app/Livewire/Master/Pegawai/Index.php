<?php

namespace App\Livewire\Master\Pegawai;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Pegawai - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'semua')]
    public string $roleFilter = 'semua';

    #[Url(except: 'semua')]
    public string $statusFilter = 'semua';

    #[Url(except: 'semua')]
    public string $cabangFilter = 'semua';

    public int $perPage = 15;

    // State Modal Form (Create / Edit) - HANYA 2 FIELD: NAMA & ROLE
    public bool $modalOpen = false;
    public bool $isEditMode = false;
    public ?int $pegawaiId = null;
    public string $nama = '';
    public string $role = 'Teknisi';
    public ?int $cabang_id = null;

    // State Modal Delete
    public bool $modalDeleteOpen = false;
    public ?int $deleteId = null;
    public string $deleteNama = '';

    public array $allowedRoles = [
        'Superadmin',
        'Admin',
        'Teknisi',
        'Bendahara',
        'Kasir',
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole(['Superadmin', 'Admin']))) {
            abort(403, 'Akses modul Master Pegawai dibatasi.');
        }

        $this->sortField = 'nama';
        $this->sortDirection = 'asc';
    }

    private function getTargetCabangId(): int
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');

        if ($isOwner) {
            if ($activeBranchSession !== 'hq' && ! empty($activeBranchSession)) {
                return (int) $activeBranchSession;
            }
            if ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)) {
                return (int) $this->cabangFilter;
            }
            return (int) (Cabang::where('status', 'aktif')->first()?->id ?? 1);
        }

        return (int) ($user->cabang_id ?: 1);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCabangFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->isEditMode = false;
        $this->pegawaiId = null;
        $this->nama = '';
        $this->role = 'Teknisi';
        $this->cabang_id = $this->getTargetCabangId();
        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $pegawai = Pegawai::findOrFail($id);
        $this->resetValidation();
        $this->isEditMode = true;
        $this->pegawaiId = $pegawai->id;
        $this->nama = $pegawai->nama;
        $this->role = $pegawai->role;
        $this->cabang_id = $pegawai->cabang_id;
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->resetValidation();
        $this->reset(['pegawaiId', 'nama', 'role', 'isEditMode']);
    }

    public function simpanPegawai(): void
    {
        $this->validate([
            'nama' => 'required|string|min:2|max:150',
            'role' => 'required|in:' . implode(',', $this->allowedRoles),
        ], [
            'nama.required' => 'Nama pegawai wajib diisi.',
            'nama.min' => 'Nama pegawai minimal 2 karakter.',
            'role.required' => 'Role pegawai wajib dipilih.',
            'role.in' => 'Pilihan role tidak valid.',
        ]);

        $targetCabangId = $this->isEditMode ? ($this->cabang_id ?: $this->getTargetCabangId()) : $this->getTargetCabangId();

        if ($this->isEditMode && $this->pegawaiId) {
            $pegawai = Pegawai::findOrFail($this->pegawaiId);
            $pegawai->update([
                'nama' => trim($this->nama),
                'role' => $this->role,
            ]);
            session()->flash('success', "Data pegawai '{$pegawai->nama}' berhasil diperbarui!");
        } else {
            $pegawai = Pegawai::create([
                'cabang_id' => $targetCabangId,
                'nama' => trim($this->nama),
                'role' => $this->role,
                'is_aktif' => true,
            ]);
            session()->flash('success', "Pegawai baru '{$pegawai->nama}' berhasil ditambahkan!");
        }

        $this->closeModal();
    }

    public function toggleStatus(int $id): void
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->is_aktif = ! $pegawai->is_aktif;
        $pegawai->save();

        $statusText = $pegawai->is_aktif ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Status pegawai '{$pegawai->nama}' berhasil {$statusText}!");
    }

    public function openDeleteModal(int $id, string $nama): void
    {
        $this->deleteId = $id;
        $this->deleteNama = $nama;
        $this->modalDeleteOpen = true;
    }

    public function closeDeleteModal(): void
    {
        $this->modalDeleteOpen = false;
        $this->deleteId = null;
        $this->deleteNama = '';
    }

    public function konfirmasiHapus(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $pegawai = Pegawai::findOrFail($this->deleteId);
        $nama = $pegawai->nama;
        $pegawai->delete();

        $this->closeDeleteModal();
        session()->flash('success', "Data pegawai '{$nama}' berhasil dihapus!");
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $cabangs = $isHQ ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();

        $query = Pegawai::query()->with('cabang');

        // Filter Cabang
        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            if ($targetCabangId) {
                $query->where('cabang_id', $targetCabangId);
            }
        } elseif ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)) {
            $query->where('cabang_id', (int) $this->cabangFilter);
        }

        // Filter Role
        if ($this->roleFilter !== 'semua' && ! empty($this->roleFilter)) {
            $query->where('role', $this->roleFilter);
        }

        // Filter Status Aktif
        if ($this->statusFilter === 'aktif') {
            $query->where('is_aktif', true);
        } elseif ($this->statusFilter === 'nonaktif') {
            $query->where('is_aktif', false);
        }

        // Multi-word Tokenized Search
        if (! empty(trim($this->search))) {
            $words = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                           ->orWhereRaw('LOWER(role) LIKE ?', [$pattern]);
                    });
                }
            });
        }

        // Sorting
        $allowedSorts = ['nama', 'role', 'is_aktif', 'created_at', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'nama';
        $sortDir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortColumn, $sortDir);

        $pegawais = $query->paginate($this->perPage);

        return view('livewire.master.pegawai.index', [
            'pegawais' => $pegawais,
            'cabangs' => $cabangs,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ]);
    }
}
