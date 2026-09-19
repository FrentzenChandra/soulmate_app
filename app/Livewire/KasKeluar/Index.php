<?php

namespace App\Livewire\KasKeluar;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\KasKeluar;
use App\Models\KategoriKasKeluar;
use App\Models\SumberDana;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Kas Keluar - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    public string $search = '';

    public ?string $sumberDanaFilter = 'semua';

    public ?string $kategoriFilter = 'semua';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public int $perPage = 15;

    public bool $modalOpen = false;

    public bool $isEditMode = false;

    public ?int $kasKeluarId = null;

    // Form fields
    public string $tanggal = '';

    public string $keterangan = '';

    public $nominal = 0;

    public ?int $sumber_dana_id = null;

    public ?int $kategori_kas_keluar_id = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sumberDanaFilter' => ['except' => 'semua'],
        'kategoriFilter' => ['except' => 'semua'],
        'startDate' => ['except' => null],
        'endDate' => ['except' => null],
        'sortField' => ['except' => 'tanggal'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! ($user->isOwner() || $user->isSuperadmin() || $user->isAdmin())) {
            abort(403, 'Akses kas keluar dibatasi.');
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

    public function updatingSumberDanaFilter()
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingKategoriFilter()
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['kasKeluarId', 'keterangan', 'nominal']);
        $this->tanggal = now()->format('Y-m-d\TH:i');
        $firstSumber = SumberDana::where('is_aktif', true)->first();
        $firstKategori = KategoriKasKeluar::where('is_aktif', true)->first();
        $this->sumber_dana_id = $firstSumber?->id;
        $this->kategori_kas_keluar_id = $firstKategori?->id;
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $kas = KasKeluar::findOrFail($id);

        $this->kasKeluarId = $kas->id;
        $this->tanggal = $kas->tanggal ? $kas->tanggal->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
        $this->keterangan = $kas->keterangan;
        $this->nominal = (float) $kas->nominal;
        $this->sumber_dana_id = $kas->sumber_dana_id;
        $this->kategori_kas_keluar_id = $kas->kategori_kas_keluar_id;

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
        if (is_string($this->nominal)) {
            $this->nominal = (float) preg_replace('/[^\d]/', '', $this->nominal);
        }

        $this->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:1',
            'sumber_dana_id' => 'required|exists:sumber_danas,id',
            'kategori_kas_keluar_id' => 'required|exists:kategori_kas_keluars,id',
        ]);

        $cabang = $this->activeCabang;
        if (! $cabang) {
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => 'Cabang tidak valid.']);

            return;
        }

        if ($this->isEditMode && $this->kasKeluarId) {
            $kas = KasKeluar::findOrFail($this->kasKeluarId);
            $kas->update([
                'tanggal' => $this->tanggal,
                'keterangan' => trim($this->keterangan),
                'nominal' => (float) $this->nominal,
                'sumber_dana_id' => $this->sumber_dana_id,
                'kategori_kas_keluar_id' => $this->kategori_kas_keluar_id,
            ]);

            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Catatan kas keluar berhasil diperbarui.']);
        } else {
            KasKeluar::create([
                'cabang_id' => $cabang->id,
                'user_id' => Auth::id(),
                'tanggal' => $this->tanggal,
                'keterangan' => trim($this->keterangan),
                'nominal' => (float) $this->nominal,
                'sumber_dana_id' => $this->sumber_dana_id,
                'kategori_kas_keluar_id' => $this->kategori_kas_keluar_id,
            ]);

            $this->dispatch('swal:success', ['title' => 'Berhasil!', 'text' => 'Catatan kas keluar berhasil disimpan.']);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $kas = KasKeluar::findOrFail($id);
        $kas->delete();

        $this->dispatch('swal:success', ['title' => 'Dihapus!', 'text' => 'Catatan kas keluar berhasil dihapus.']);
    }

    public function render()
    {
        $cabang = $this->activeCabang;
        $query = KasKeluar::query()->with(['sumberDana', 'kategori', 'user']);

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
                            ->orWhereHas('sumberDana', fn ($sq) => $sq->whereRaw('LOWER(nama) LIKE ?', [$pattern]))
                            ->orWhereHas('kategori', fn ($kq) => $kq->whereRaw('LOWER(nama) LIKE ?', [$pattern]));
                    });
                }
            });
        }

        if ($this->sumberDanaFilter !== 'semua') {
            $query->where('sumber_dana_id', (int) $this->sumberDanaFilter);
        }

        if ($this->kategoriFilter !== 'semua') {
            $query->where('kategori_kas_keluar_id', (int) $this->kategoriFilter);
        }

        // Summary Calculations
        $totalKasKeluar = (clone $query)->sum('nominal');
        $totalCash = (clone $query)->whereHas('sumberDana', fn ($sq) => $sq->whereRaw('LOWER(nama) LIKE ?', ['%cash%'])->orWhereRaw('LOWER(nama) LIKE ?', ['%tunai%']))->sum('nominal');
        $totalNonCash = $totalKasKeluar - $totalCash;

        $sortCol = in_array($this->sortField, ['tanggal', 'nominal', 'keterangan']) ? $this->sortField : 'tanggal';
        $query->orderBy($sortCol, $this->sortDirection === 'asc' ? 'asc' : 'desc');

        $items = $query->paginate($this->perPage);

        return view('livewire.kas-keluar.index', [
            'items' => $items,
            'totalKasKeluar' => $totalKasKeluar,
            'totalCash' => $totalCash,
            'totalNonCash' => $totalNonCash,
            'sumberDanas' => SumberDana::where('is_aktif', true)->orderBy('nama')->get(),
            'kategoriKasKeluars' => KategoriKasKeluar::where('is_aktif', true)->orderBy('nama')->get(),
        ]);
    }
}
