<?php

namespace App\Livewire\Master\Kategori;

use App\Livewire\Traits\WithSorting;
use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Kategori Produk - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    public string $search = '';

    public int $perPage = 15;

    // Modal Create / Edit
    public bool $modalOpen = false;

    public bool $isEditMode = false;

    public ?int $kategoriId = null;

    // Form fields
    public string $nama_kategori = '';

    public string $slug = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'nama_kategori'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->can('master.settings.view'))) {
            abort(403, 'Akses terbatas untuk pengelolaan kategori produk.');
        }

        $this->sortField = 'nama_kategori';
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

    public function updatedNamaKategori($value)
    {
        if (! $this->isEditMode || empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['kategoriId', 'nama_kategori', 'slug']);
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $kategori = KategoriProduk::findOrFail($id);

        $this->kategoriId = $kategori->id;
        $this->nama_kategori = $kategori->nama_kategori;
        $this->slug = $kategori->slug ?? Str::slug($kategori->nama_kategori);

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
        $validated = $this->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kategori_produks', 'nama_kategori')->ignore($this->kategoriId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:120',
                Rule::unique('kategori_produks', 'slug')->ignore($this->kategoriId),
            ],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Nama kategori ini sudah terdaftar.',
            'slug.unique' => 'Slug kategori ini sudah digunakan.',
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['nama_kategori']);

        if ($this->isEditMode && $this->kategoriId) {
            $kategori = KategoriProduk::findOrFail($this->kategoriId);
            $kategori->update([
                'nama_kategori' => trim($validated['nama_kategori']),
                'slug' => $slug,
            ]);

            session()->flash('success', "Kategori \"{$kategori->nama_kategori}\" berhasil diperbarui.");
        } else {
            $kategori = KategoriProduk::create([
                'nama_kategori' => trim($validated['nama_kategori']),
                'slug' => $slug,
            ]);

            session()->flash('success', "Kategori baru \"{$kategori->nama_kategori}\" berhasil ditambahkan.");
        }

        $this->closeModal();
    }

    public function deleteKategori(int $id): void
    {
        $kategori = KategoriProduk::withCount('produks')->findOrFail($id);

        if ($kategori->produks_count > 0) {
            session()->flash('error', "Kategori \"{$kategori->nama_kategori}\" tidak dapat dihapus karena masih digunakan oleh {$kategori->produks_count} produk.");

            return;
        }

        $nama = $kategori->nama_kategori;
        $kategori->delete();

        session()->flash('success', "Kategori \"{$nama}\" berhasil dihapus.");
    }

    public function render()
    {
        $kategoriQuery = KategoriProduk::withCount('produks')
            ->when(! empty($this->search), function ($q) {
                $cleanSearch = trim($this->search);
                $words = array_filter(explode(' ', $cleanSearch));
                $q->where(function ($sub) use ($words) {
                    foreach ($words as $word) {
                        $pattern = '%'.strtolower($word).'%';
                        $sub->where(function ($sq) use ($pattern) {
                            $sq->whereRaw('LOWER(nama_kategori) LIKE ?', [$pattern])
                                ->orWhereRaw('LOWER(slug) LIKE ?', [$pattern]);
                        });
                    }
                });
            });

        $allowedSorts = ['nama_kategori', 'slug', 'produks_count', 'created_at', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'nama_kategori';
        $sortDir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';

        $totalCount = (clone $kategoriQuery)->count();
        $kategoris = $kategoriQuery->orderBy($sortColumn, $sortDir)->take($this->perPage)->get();
        $hasMore = $totalCount > $this->perPage;

        $totalKategori = KategoriProduk::count();
        $totalProduk = Produk::count();
        $topKategori = KategoriProduk::withCount('produks')->orderBy('produks_count', 'desc')->first();

        return view('livewire.master.kategori.index', [
            'kategoris' => $kategoris,
            'hasMore' => $hasMore,
            'totalFiltered' => $totalCount,
            'totalKategori' => $totalKategori,
            'totalProduk' => $totalProduk,
            'topKategori' => $topKategori,
        ]);
    }
}
