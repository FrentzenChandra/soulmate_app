<?php

namespace App\Livewire\ModalAwal;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\ModalAwal;
use App\Models\TutupKas;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Modal Awal Kasir - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    public string $search = '';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public int $perPage = 15;

    public bool $modalOpen = false;

    public bool $isEditMode = false;

    public ?int $modalAwalId = null;

    // Form fields
    public string $tanggal = '';

    public $nominal = 0;

    public string $keterangan = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'startDate' => ['except' => null],
        'endDate' => ['except' => null],
        'sortField' => ['except' => 'tanggal'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! ($user->isOwner() || $user->isSuperadmin() || $user->isAdmin() || $user->hasRole('Kasir'))) {
            abort(403, 'Akses modal awal kasir dibatasi.');
        }

        $this->startDate = $this->startDate ?: now()->startOfMonth()->toDateString();
        $this->endDate = $this->endDate ?: now()->toDateString();
        $this->sortField = 'tanggal';
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

    public function getTodayModalAwalProperty(): ?ModalAwal
    {
        $cabang = $this->activeCabang;
        if (! $cabang) {
            return null;
        }

        return ModalAwal::with('user')
            ->where('cabang_id', $cabang->id)
            ->whereDate('tanggal', now()->toDateString())
            ->first();
    }

    public function updatingSearch()
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $user = Auth::user();
        $cabang = $this->activeCabang;

        // If user is regular Admin / Kasir, check if modal awal for today already exists
        if ($user && ! ($user->isOwner() || $user->isSuperadmin()) && $cabang) {
            $existingToday = ModalAwal::with('user')
                ->where('cabang_id', $cabang->id)
                ->whereDate('tanggal', now()->toDateString())
                ->first();

            if ($existingToday) {
                $namaPetugas = $existingToday->user?->name ?? 'Kasir';
                $nominalFmt = 'Rp ' . number_format($existingToday->nominal, 0, ',', '.');
                $this->dispatch('swal:error', [
                    'title' => 'Modal Awal Sudah Ada',
                    'text' => "Modal awal hari ini sudah dicatat oleh {$namaPetugas} ({$nominalFmt}). Hubungi Superadmin jika perlu melakukan koreksi.",
                ]);

                return;
            }
        }

        $this->resetValidation();
        $this->reset(['modalAwalId', 'keterangan', 'nominal']);
        $this->tanggal = now()->toDateString();
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $user = Auth::user();
        if (! $user || ! ($user->isOwner() || $user->isSuperadmin())) {
            $this->dispatch('swal:error', [
                'title' => 'Akses Ditolak',
                'text' => 'Hanya Superadmin dan Owner yang berwenang mengubah modal awal.',
            ]);

            return;
        }

        $this->resetValidation();
        $item = ModalAwal::findOrFail($id);

        $this->modalAwalId = $item->id;
        $this->tanggal = $item->tanggal ? $item->tanggal->format('Y-m-d') : now()->toDateString();
        $this->nominal = (float) $item->nominal;
        $this->keterangan = $item->keterangan ?: '';

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
        $user = Auth::user();
        $cabang = $this->activeCabang;
        if (! $cabang) {
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => 'Cabang tidak valid.']);

            return;
        }

        $isSuperOrOwner = $user && ($user->isOwner() || $user->isSuperadmin());

        // Regular admin can only input for today
        if (! $isSuperOrOwner) {
            if ($this->tanggal && $this->tanggal !== now()->toDateString()) {
                $this->addError('tanggal', 'Admin hanya diizinkan mencatat modal awal untuk tanggal hari ini.');

                return;
            }
            $this->tanggal = now()->toDateString();
        }

        if (is_string($this->nominal)) {
            $this->nominal = (float) preg_replace('/[^\d]/', '', $this->nominal);
        }

        $this->validate([
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nominal.min' => 'Nominal modal awal harus minimal Rp 1.',
            'nominal.required' => 'Nominal modal awal wajib diisi.',
        ]);

        if ($this->isEditMode && $this->modalAwalId) {
            if (! $isSuperOrOwner) {
                $this->dispatch('swal:error', [
                    'title' => 'Akses Ditolak',
                    'text' => 'Hanya Superadmin dan Owner yang berwenang mengubah modal awal.',
                ]);

                return;
            }

            $item = ModalAwal::findOrFail($this->modalAwalId);
            $item->update([
                'tanggal' => $this->tanggal,
                'nominal' => (float) $this->nominal,
                'keterangan' => trim($this->keterangan) ?: null,
            ]);

            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Modal awal berhasil diperbarui.']);
        } else {
            // Check if already set for this date on this branch
            $existing = ModalAwal::where('cabang_id', $cabang->id)
                ->whereDate('tanggal', $this->tanggal)
                ->first();

            if ($existing) {
                if (! $isSuperOrOwner) {
                    $this->dispatch('swal:error', [
                        'title' => 'Akses Ditolak',
                        'text' => 'Modal awal tanggal ini sudah ada. Hanya Superadmin yang dapat melakukan koreksi.',
                    ]);

                    return;
                }

                $existing->update([
                    'nominal' => (float) $this->nominal,
                    'keterangan' => trim($this->keterangan) ?: null,
                    'user_id' => Auth::id(),
                ]);
                $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Modal awal tanggal ini berhasil diperbarui.']);
            } else {
                ModalAwal::create([
                    'cabang_id' => $cabang->id,
                    'user_id' => Auth::id(),
                    'tanggal' => $this->tanggal,
                    'nominal' => (float) $this->nominal,
                    'keterangan' => trim($this->keterangan) ?: null,
                ]);
                $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Modal awal kasir berhasil disimpan.']);
            }
        }

        $this->dispatch('refreshNotification');
        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $user = Auth::user();
        if (! $user || ! ($user->isOwner() || $user->isSuperadmin())) {
            $this->dispatch('swal:error', [
                'title' => 'Akses Ditolak',
                'text' => 'Hanya Superadmin dan Owner yang berwenang menghapus catatan modal awal.',
            ]);

            return;
        }

        $item = ModalAwal::findOrFail($id);
        $item->delete();

        $this->dispatch('refreshNotification');
        $this->dispatch('swal:success', ['title' => 'Dihapus!', 'text' => 'Catatan modal awal berhasil dihapus.']);
    }

    public function render()
    {
        $cabang = $this->activeCabang;
        $query = ModalAwal::query()->with(['user']);

        if ($cabang) {
            $query->where('cabang_id', $cabang->id);
        }

        if ($this->startDate) {
            $query->whereDate('tanggal', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('tanggal', '<=', $this->endDate);
        }

        if (! empty(trim($this->search))) {
            $terms = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $pattern = '%' . strtolower($term) . '%';
                    $q->where(function ($sub) use ($pattern) {
                        $sub->whereRaw('LOWER(keterangan) LIKE ?', [$pattern])
                            ->orWhereHas('user', fn ($uq) => $uq->whereRaw('LOWER(name) LIKE ?', [$pattern]));
                    });
                }
            });
        }

        $sortCol = in_array($this->sortField, ['tanggal', 'nominal']) ? $this->sortField : 'tanggal';
        $query->orderBy($sortCol, $this->sortDirection === 'asc' ? 'asc' : 'desc');

        $items = $query->paginate($this->perPage);

        return view('livewire.modal-awal.index', [
            'items' => $items,
        ]);
    }
}
