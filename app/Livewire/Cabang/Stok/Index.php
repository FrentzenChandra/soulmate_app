<?php

namespace App\Livewire\Cabang\Stok;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Stok Cabang - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    public string $search = '';

    public string $kategoriFilter = 'semua';

    public string $stokFilter = 'semua';

    public int $perPage = 15;

    // Modal Stock Opname / Penyesuaian
    public bool $modalAdjustOpen = false;

    public ?int $selectedProdukId = null;

    public ?string $selectedProdukName = '';

    public ?string $selectedProdukBarcode = '';

    public ?string $selectedProdukFoto = null;

    public int $currentStock = 0;

    public int $newStock = 0;

    public int $minStok = 3;

    public bool $isAktif = true;

    public string $jenisPenyesuaian = 'opname'; // opname, tambah, kurang

    public int $deltaQty = 0;

    public string $catatan = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'kategoriFilter' => ['except' => 'semua'],
        'stokFilter' => ['except' => 'semua'],
        'sortField' => ['except' => 'nama_produk'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(): void
    {
        $this->sortField = 'nama_produk';
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

    public function updatingStokFilter()
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function getActiveCabangProperty(): ?Cabang
    {
        $user = Auth::user();
        if ($user->isOwner()) {
            $activeBranchId = session('active_branch_id', 'hq');
            if ($activeBranchId === 'hq' || ! $activeBranchId) {
                // Default ke cabang pertama jika owner di HQ
                return Cabang::first();
            }

            return Cabang::find($activeBranchId);
        }

        return $user->cabang;
    }

    public function openAdjustModal(int $produkId): void
    {
        $this->resetValidation();
        $cabang = $this->activeCabang;
        if (! $cabang) {
            session()->flash('error', 'Cabang aktif tidak ditemukan.');

            return;
        }

        $produk = Produk::findOrFail($produkId);
        $stok = Stok::firstOrCreate(
            ['produk_id' => $produk->id, 'cabang_id' => $cabang->id],
            ['jumlah' => 0, 'min_stok' => 3, 'is_aktif' => true]
        );

        $this->selectedProdukId = $produk->id;
        $this->selectedProdukName = $produk->nama_produk;
        $this->selectedProdukBarcode = $produk->barcode ?? '-';
        $this->selectedProdukFoto = $produk->foto_url;
        $this->currentStock = $stok->jumlah;
        $this->newStock = $stok->jumlah;
        $this->minStok = $stok->min_stok ?? 3;
        $this->isAktif = (bool) ($stok->is_aktif ?? true);
        $this->jenisPenyesuaian = 'opname';
        $this->deltaQty = 0;
        $this->catatan = 'Hasil Stock Opname Fisik Toko';
        $this->modalAdjustOpen = true;
    }

    public function quickDelta(int $delta): void
    {
        $this->newStock = max(0, $this->newStock + $delta);
    }

    public function saveAdjustment(): void
    {
        $this->validate([
            'newStock' => ['required', 'integer', 'min:0'],
            'minStok' => ['required', 'integer', 'min:0'],
            'catatan' => ['required', 'string', 'max:255'],
        ], [
            'newStock.required' => 'Jumlah stok baru wajib diisi.',
            'newStock.min' => 'Stok tidak boleh kurang dari 0.',
            'minStok.required' => 'Batas minimum stok wajib diisi.',
            'catatan.required' => 'Alasan/catatan penyesuaian wajib dicatat.',
        ]);

        $cabang = $this->activeCabang;
        if (! $cabang || ! $this->selectedProdukId) {
            session()->flash('error', 'Gagal memproses penyesuaian stok.');

            return;
        }

        $stok = Stok::updateOrCreate(
            ['produk_id' => $this->selectedProdukId, 'cabang_id' => $cabang->id],
            [
                'jumlah' => $this->newStock,
                'min_stok' => max(0, $this->minStok),
                'is_aktif' => $this->isAktif,
            ]
        );

        $this->dispatch('stockUpdated');

        $selisih = $this->newStock - $this->currentStock;
        $tanda = $selisih >= 0 ? "+{$selisih}" : "{$selisih}";

        session()->flash('success', "Stok \"{$this->selectedProdukName}\" di {$cabang->nama_cabang} berhasil diperbarui menjadi {$this->newStock} unit ({$tanda}).");
        $this->modalAdjustOpen = false;
    }

    public function render()
    {
        $cabang = $this->activeCabang;
        $kategoris = KategoriProduk::orderBy('nama_kategori')->get();

        if (! $cabang) {
            return view('livewire.cabang.stok.index', [
                'cabang' => null,
                'kategoris' => $kategoris,
                'stokItems' => collect(),
                'hasMore' => false,
                'totalItems' => 0,
                'totalSku' => 0,
                'totalUnit' => 0,
                'totalKritis' => 0,
                'totalHabis' => 0,
            ]);
        }

        // Query produk beserta stok spesifik cabang ini
        $query = Produk::with(['kategori', 'stoks' => function ($q) use ($cabang) {
            $q->where('cabang_id', $cabang->id);
        }]);

        if (! empty($this->search)) {
            $cleanSearch = trim($this->search);
            $words = array_filter(explode(' ', $cleanSearch));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%'.strtolower($word).'%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(nama_produk) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(barcode) LIKE ?', [$pattern]);
                    });
                }
            });
        }

        if ($this->kategoriFilter !== 'semua') {
            $query->where('kategori_id', $this->kategoriFilter);
        }

        if ($this->stokFilter !== 'semua') {
            if ($this->stokFilter === 'aktif') {
                $query->whereHas('stoks', function ($q) use ($cabang) {
                    $q->where('cabang_id', $cabang->id)->where('is_aktif', true);
                });
            } elseif ($this->stokFilter === 'non_aktif') {
                $query->where(function ($sq) use ($cabang) {
                    $sq->whereDoesntHave('stoks', function ($q) use ($cabang) {
                        $q->where('cabang_id', $cabang->id);
                    })->orWhereHas('stoks', function ($q) use ($cabang) {
                        $q->where('cabang_id', $cabang->id)->where('is_aktif', false);
                    });
                });
            } elseif ($this->stokFilter === 'tersedia') {
                $query->whereHas('stoks', function ($q) use ($cabang) {
                    $q->where('cabang_id', $cabang->id)->where('is_aktif', true)->whereColumn('jumlah', '>', 'min_stok');
                });
            } elseif ($this->stokFilter === 'kritis') {
                $query->whereHas('stoks', function ($q) use ($cabang) {
                    $q->where('cabang_id', $cabang->id)->where('is_aktif', true)->where('jumlah', '>', 0)->whereColumn('jumlah', '<=', 'min_stok');
                });
            } elseif ($this->stokFilter === 'habis') {
                $query->whereHas('stoks', function ($q) use ($cabang) {
                    $q->where('cabang_id', $cabang->id)->where('is_aktif', true)->where('jumlah', '<=', 0);
                });
            }
        }

        $sortDir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';

        if ($this->sortField === 'stok_jumlah') {
            $query->orderBy(
                Stok::select('jumlah')
                    ->whereColumn('stoks.produk_id', 'produks.id')
                    ->where('stoks.cabang_id', $cabang->id)
                    ->limit(1),
                $sortDir
            );
        } elseif ($this->sortField === 'is_aktif') {
            $query->orderBy(
                Stok::select('is_aktif')
                    ->whereColumn('stoks.produk_id', 'produks.id')
                    ->where('stoks.cabang_id', $cabang->id)
                    ->limit(1),
                $sortDir
            );
        } else {
            $allowedSorts = ['nama_produk', 'barcode', 'kategori_id', 'harga_jual', 'created_at', 'id'];
            $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'nama_produk';
            $query->orderBy($sortColumn, $sortDir);
        }

        $totalItems = (clone $query)->count();
        $stokItems = $query->take($this->perPage)->get();
        $hasMore = $totalItems > $this->perPage;

        // Hitung Ringkasan Metrik Cabang
        $allStoksCabang = Stok::where('cabang_id', $cabang->id)->where('is_aktif', true)->get();
        $totalSku = $allStoksCabang->count();
        $totalUnit = $allStoksCabang->sum('jumlah');
        $totalKritis = $allStoksCabang->filter(fn ($s) => $s->jumlah > 0 && $s->jumlah <= $s->min_stok)->count();
        $totalHabis = $allStoksCabang->where('jumlah', '<=', 0)->count();

        return view('livewire.cabang.stok.index', [
            'cabang' => $cabang,
            'kategoris' => $kategoris,
            'stokItems' => $stokItems,
            'hasMore' => $hasMore,
            'totalItems' => $totalItems,
            'totalSku' => $totalSku,
            'totalUnit' => $totalUnit,
            'totalKritis' => $totalKritis,
            'totalHabis' => $totalHabis,
        ]);
    }
}
