<?php

namespace App\Livewire\TutupKas;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\TutupKas;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Riwayat Tutup Kas (Closing) - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    public string $search = '';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public int $perPage = 15;

    public ?TutupKas $selectedDetail = null;

    public bool $modalDetailOpen = false;

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
        if (! $user || ! ($user->isOwner() || $user->isSuperadmin() || $user->isAdmin() || $user->hasRole('Kasir') || $user->isTeknisi())) {
            abort(403, 'Akses Riwayat Tutup Kas dibatasi.');
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

    public function updatingSearch()
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function openDetail(int $id): void
    {
        $this->selectedDetail = TutupKas::with(['user', 'cabang'])->findOrFail($id);
        $this->modalDetailOpen = true;
    }

    public function closeDetail(): void
    {
        $this->modalDetailOpen = false;
        $this->selectedDetail = null;
    }

    public function render()
    {
        $cabang = $this->activeCabang;
        $query = TutupKas::query()->with(['user', 'cabang']);

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
                        $sub->whereRaw('LOWER(catatan) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(status_selisih) LIKE ?', [$pattern])
                            ->orWhereHas('user', fn ($uq) => $uq->whereRaw('LOWER(name) LIKE ?', [$pattern]));
                    });
                }
            });
        }

        $sortCol = in_array($this->sortField, ['tanggal', 'waktu_tutup', 'kas_sistem', 'kas_fisik', 'selisih']) ? $this->sortField : 'tanggal';
        $query->orderBy($sortCol, $this->sortDirection === 'asc' ? 'asc' : 'desc');

        $items = $query->paginate($this->perPage);

        return view('livewire.tutup-kas.index', [
            'items' => $items,
        ]);
    }
}
