<?php

namespace App\Livewire\Cabang\Stok;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public string $modeAdjust = 'opname'; // opname, aktifkan

    public string $hargaJual = '0';

    public string $hargaBeli = '0';

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
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->can('cabang.stok.view'))) {
            abort(403, 'Akses modul Stok Cabang dibatasi.');
        }

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
        $user = Auth::user();
        abort_if(! $user->isOwner() && ! $user->can('cabang.stok.adjust'), 403, 'Akses penyesuaian stok dibatasi.');

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
        $this->modeAdjust = $this->isAktif ? 'opname' : 'aktifkan';
        $this->hargaJual = number_format((float) $produk->harga_jual, 0, ',', '.');
        $this->hargaBeli = number_format((float) $produk->harga_beli, 0, ',', '.');
        $this->jenisPenyesuaian = 'opname';
        $this->deltaQty = 0;
        $this->catatan = $this->isAktif ? 'Hasil Stock Opname Fisik Toko' : '';
        $this->modalAdjustOpen = true;
    }

    public function quickDelta(int $delta): void
    {
        $this->newStock = max(0, $this->newStock + $delta);
    }

    public function saveAdjustment(): void
    {
        $user = Auth::user();
        abort_if(! $user->isOwner() && ! $user->can('cabang.stok.adjust'), 403, 'Akses penyesuaian stok dibatasi.');

        $isAktivasi = $this->modeAdjust === 'aktifkan';

        if ($isAktivasi) {
            // Input harga memakai format ribuan (titik) agar mudah dibaca; bersihkan sebelum validasi.
            $this->hargaJual = str_replace('.', '', $this->hargaJual);
            $this->hargaBeli = str_replace('.', '', $this->hargaBeli);
        }

        $rules = [
            'newStock' => ['required', 'integer', 'min:0'],
            'minStok' => ['required', 'integer', 'min:0'],
            'catatan' => ['required', 'string', 'max:255'],
        ];
        $messages = [
            'newStock.required' => 'Jumlah stok wajib diisi.',
            'newStock.min' => 'Stok tidak boleh kurang dari 0.',
            'minStok.required' => 'Batas minimum stok wajib diisi.',
            'catatan.required' => 'Alasan/catatan penyesuaian wajib dicatat.',
        ];

        if ($isAktivasi) {
            $rules['newStock'] = ['required', 'integer', 'min:1'];
            $rules['hargaBeli'] = ['required', 'numeric', 'min:0'];
            $rules['hargaJual'] = ['required', 'numeric', 'min:1'];
            $rules['catatan'] = ['nullable', 'string', 'max:255'];
            $messages['newStock.min'] = 'Jumlah stok minimal 1 unit agar produk tayang.';
            $messages['hargaBeli.required'] = 'Harga modal wajib diisi.';
            $messages['hargaJual.required'] = 'Harga jual wajib diisi.';
            $messages['hargaJual.min'] = 'Harga jual harus lebih dari 0.';
        }

        $this->validate($rules, $messages);

        $cabang = $this->activeCabang;
        if (! $cabang || ! $this->selectedProdukId) {
            session()->flash('error', 'Gagal memproses penyesuaian stok.');

            return;
        }

        $produk = Produk::findOrFail($this->selectedProdukId);

        if ($isAktivasi) {
            $produk->update([
                'harga_beli' => $this->hargaBeli,
                'harga_jual' => $this->hargaJual,
                'ready_marketplace' => true,
            ]);
        }

        Stok::updateOrCreate(
            ['produk_id' => $produk->id, 'cabang_id' => $cabang->id],
            [
                'jumlah' => $this->newStock,
                'min_stok' => max(0, $this->minStok),
                'is_aktif' => $isAktivasi ? true : $this->isAktif,
            ]
        );

        $this->dispatch('stockUpdated');
        $this->modalAdjustOpen = false;

        if (! $isAktivasi) {
            $selisih = $this->newStock - $this->currentStock;
            $tanda = $selisih >= 0 ? "+{$selisih}" : "{$selisih}";

            session()->flash('success', "Stok \"{$this->selectedProdukName}\" di {$cabang->nama_cabang} berhasil diperbarui menjadi {$this->newStock} unit ({$tanda}).");

            return;
        }

        session()->flash('success', "Produk \"{$this->selectedProdukName}\" aktif kembali dengan stok {$this->newStock} unit dan tayang di website.");

        if ($user->isOwner() || $user->can('master.produk.view')) {
            $this->redirect(route('master.produk', ['search' => $this->selectedProdukName]));

            return;
        }

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Produk Diaktifkan',
            'text' => "Produk \"{$this->selectedProdukName}\" aktif kembali dan tayang di website.",
        ]);
    }

    public function deleteProduk(int $id): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->can('cabang.stok.adjust'))) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'text' => 'Anda tidak memiliki hak akses untuk menghapus produk.',
            ]);

            return;
        }

        $produk = Produk::with(['penjualanDetails', 'servisDetails', 'transaksiFullDetails'])->find($id);
        if (! $produk) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'Gagal',
                'text' => 'Data produk tidak ditemukan.',
            ]);

            return;
        }

        // Pengecekan Riwayat Transaksi di tabel penjualan_details, servis_details, dan transaksi_full_details
        $hasPenjualan = $produk->penjualanDetails()->exists() || DB::table('penjualan_details')->where('produk_id', $produk->id)->exists();
        $hasServis = $produk->servisDetails()->exists() || DB::table('servis_details')->where('produk_id', $produk->id)->exists();
        $hasTransaksiFull = $produk->transaksiFullDetails()->exists() || DB::table('transaksi_full_details')->where('produk_id', $produk->id)->exists();

        if ($hasPenjualan || $hasServis || $hasTransaksiFull) {
            $reasons = [];
            if ($hasPenjualan) {
                $reasons[] = 'Penjualan Kasir (penjualan_details)';
            }
            if ($hasServis) {
                $reasons[] = 'Faktur Servis (servis_details)';
            }
            if ($hasTransaksiFull) {
                $reasons[] = 'Transaksi Toko (transaksi_full_details)';
            }

            $reasonText = implode(', ', $reasons);

            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Tidak Dapat Dihapus',
                'text' => "Produk \"{$produk->nama_produk}\" tidak dapat dihapus karena sudah memiliki riwayat data transaksi pada: {$reasonText}.",
            ]);

            return;
        }

        // Hapus data stok cabang dan produk
        Stok::where('produk_id', $produk->id)->delete();
        $namaProduk = $produk->nama_produk;
        $produk->delete();

        $this->dispatch('stockUpdated');
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Berhasil Dihapus',
            'text' => "Produk \"{$namaProduk}\" berhasil dihapus dari sistem.",
        ]);
        session()->flash('success', "Produk \"{$namaProduk}\" berhasil dihapus.");
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
                'totalModal' => 0,
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
                            ->orWhereRaw('LOWER(barcode) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(COALESCE(deskripsi, \'\')) LIKE ?', [$pattern])
                            ->orWhereHas('kategori', function ($kq) use ($pattern) {
                                $kq->whereRaw('LOWER(nama_kategori) LIKE ?', [$pattern]);
                            });
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

        // Hitung Total Modal Aset Stok (Hanya Produk Aktif Dijual: stoks.is_aktif = true)
        $totalModal = (float) DB::table('stoks')
            ->join('produks', 'produks.id', '=', 'stoks.produk_id')
            ->where('stoks.cabang_id', $cabang->id)
            ->where('stoks.is_aktif', true)
            ->whereNull('produks.deleted_at')
            ->selectRaw('COALESCE(SUM(stoks.jumlah * produks.harga_beli), 0) as total_modal')
            ->value('total_modal') ?? 0;

        return view('livewire.cabang.stok.index', [
            'cabang' => $cabang,
            'kategoris' => $kategoris,
            'stokItems' => $stokItems,
            'hasMore' => $hasMore,
            'totalItems' => $totalItems,
            'totalSku' => $totalSku,
            'totalUnit' => $totalUnit,
            'totalModal' => $totalModal,
            'totalKritis' => $totalKritis,
            'totalHabis' => $totalHabis,
        ]);
    }
}
