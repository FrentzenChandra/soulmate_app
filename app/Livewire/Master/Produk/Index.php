<?php

namespace App\Livewire\Master\Produk;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Produk & Katalog - Soulmate Komputer')]
class Index extends Component
{
    use WithFileUploads, WithPagination, WithSorting;

    public string $search = '';

    public string $kategoriFilter = 'semua';

    public string $stokFilter = 'semua';

    // Modal Produk
    public bool $modalProdukOpen = false;

    public bool $isEditMode = false;

    public ?int $produkId = null;

    // Form fields Produk
    public ?int $kategori_id = null;

    public string $barcode = '';

    public string $nama_produk = '';

    public $harga_beli = 0;

    public $harga_jual = 0;

    public bool $ready_marketplace = false;

    public string $deskripsi = '';

    public $foto = null;

    public ?string $existing_foto_url = null;

    public array $initialStock = []; // [cabang_id => jumlah]

    public array $minStock = []; // [cabang_id => min_stok threshold]

    public array $isAktifStock = []; // [cabang_id => boolean status]

    // Deteksi Cerdas Duplikasi Produk Master
    public ?int $duplicateMasterProductId = null;

    public ?Produk $duplicateMasterProduct = null;

    public $duplicateMasterProducts = [];

    // Modal Manajemen Kategori
    public bool $modalKategoriOpen = false;

    public string $new_kategori_name = '';

    public ?int $edit_kategori_id = null;

    public string $edit_kategori_name = '';

    // Modal Konfirmasi Hapus / Nonaktifkan Produk
    public bool $modalDeleteOpen = false;

    public ?int $deleteProdukId = null;

    public ?string $deleteProdukNama = '';

    public ?string $deleteProdukBarcode = '';

    public ?string $deleteProdukFoto = null;

    public ?string $deleteProdukKategori = '';

    public int $deleteProdukStok = 0;

    public bool $deleteProdukHasHistory = false;

    public bool $deleteIsHQ = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'kategoriFilter' => ['except' => 'semua'],
        'stokFilter' => ['except' => 'semua'],
        'sortField' => ['except' => 'nama_produk'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public int $perPage = 15;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->can('master.produk.view'))) {
            abort(403, 'Akses modul Master Produk dibatasi.');
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

    public function rules(): array
    {
        return [
            'nama_produk' => ['required', 'string', 'max:200'],
            'kategori_id' => ['nullable', 'exists:kategori_produks,id'],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('produks', 'barcode')->ignore($this->produkId),
            ],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'ready_marketplace' => ['boolean'],
            'deskripsi' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'barcode.unique' => 'Barcode atau SKU ini sudah terdaftar pada produk lain.',
            'harga_beli.required' => 'Harga modal/beli wajib diisi.',
            'harga_beli.min' => 'Harga modal tidak boleh negatif.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'harga_jual.min' => 'Harga jual tidak boleh negatif.',
            'foto.image' => 'File harus berupa gambar (JPG, PNG, WEBP).',
            'foto.max' => 'Ukuran gambar maksimal 4MB.',
        ];
    }

    public function checkDuplicateMaster(): void
    {
        // Fitur produk serupa di-hide sementara per instruksi
        $this->duplicateMasterProduct = null;
        $this->duplicateMasterProductId = null;
        $this->duplicateMasterProducts = [];

        /*
        if ($this->isEditMode) {
            $this->duplicateMasterProductId = null;
            $this->duplicateMasterProduct = null;
            $this->duplicateMasterProducts = [];

            return;
        }

        $barcode = trim($this->barcode);
        $nama = trim($this->nama_produk);

        if (empty($barcode) && (empty($nama) || strlen($nama) < 2)) {
            $this->duplicateMasterProduct = null;
            $this->duplicateMasterProductId = null;
            $this->duplicateMasterProducts = [];

            return;
        }

        $query = Produk::with(['stoks.cabang', 'kategori']);

        $query->where(function ($q) use ($barcode, $nama) {
            if (! empty($barcode)) {
                $q->orWhere('barcode', 'LIKE', "%{$barcode}%");
            }

            if (! empty($nama) && strlen($nama) >= 2) {
                // Pencarian fleksibel per kata kunci (tokenized)
                $words = preg_split('/\s+/', $nama);
                $words = array_filter($words, fn ($w) => strlen($w) >= 2);

                if (! empty($words)) {
                    $q->orWhere(function ($subQ) use ($words) {
                        foreach ($words as $w) {
                            $subQ->whereRaw('LOWER(nama_produk) LIKE ?', ['%'.strtolower($w).'%']);
                        }
                    });
                }
            }
        });

        $matches = $query->take(6)->get();

        $this->duplicateMasterProducts = $matches;
        $this->duplicateMasterProduct = $matches->first();
        $this->duplicateMasterProductId = $matches->first()?->id;
        */
    }

    public function selectExistingProduct(int $id): void
    {
        $this->openEditModal($id);
        session()->flash('info', 'Produk dipilih dari katalog master. Anda dapat langsung menambah atau menyesuaikan alokasi stok.');
    }

    public function updatedBarcode($val): void
    {
        $this->checkDuplicateMaster();
    }

    public function updatedNamaProduk($val): void
    {
        $this->checkDuplicateMaster();
    }

    public function generateBarcode(): void
    {
        $this->barcode = '899'.str_pad((string) mt_rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
        $this->checkDuplicateMaster();
    }

    public function openCreateModal(): void
    {
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('master.produk.create'), 403, 'Akses tambah produk dibatasi.');

        $this->resetValidation();
        $this->reset(['produkId', 'kategori_id', 'barcode', 'nama_produk', 'deskripsi', 'foto', 'existing_foto_url', 'duplicateMasterProductId', 'duplicateMasterProduct', 'duplicateMasterProducts']);
        $this->harga_beli = 0;
        $this->harga_jual = 0;
        $this->ready_marketplace = false;
        $this->isEditMode = false;

        $user = Auth::user();
        $activeBranchId = session('active_branch_id', 'hq');
        $userCabangId = $user->cabang_id ?? ($activeBranchId !== 'hq' ? (int) $activeBranchId : null);

        $cabangs = Cabang::where('status', 'aktif')->get();
        $this->initialStock = [];
        $this->minStock = [];
        $this->isAktifStock = [];
        foreach ($cabangs as $c) {
            $this->initialStock[$c->id] = 0;
            $this->minStock[$c->id] = 3;
            $this->isAktifStock[$c->id] = $userCabangId ? ($c->id === $userCabangId) : false;
        }

        $this->modalProdukOpen = true;
    }

    public function openEditModal(int $id): void
    {
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('master.produk.edit'), 403, 'Akses edit produk dibatasi.');

        $this->resetValidation();
        $this->reset(['duplicateMasterProductId', 'duplicateMasterProduct']);
        $this->foto = null;
        $produk = Produk::with('stoks')->findOrFail($id);

        $this->produkId = $produk->id;
        $this->kategori_id = $produk->kategori_id;
        $this->barcode = $produk->barcode ?? '';
        $this->nama_produk = $produk->nama_produk;
        $this->harga_beli = (float) $produk->harga_beli;
        $this->harga_jual = (float) $produk->harga_jual;
        $this->ready_marketplace = (bool) $produk->ready_marketplace;
        $this->deskripsi = $produk->deskripsi ?? '';
        $this->existing_foto_url = $produk->foto_url;

        $cabangs = Cabang::where('status', 'aktif')->get();
        $this->initialStock = [];
        $this->minStock = [];
        $this->isAktifStock = [];
        foreach ($cabangs as $c) {
            $stok = $produk->stoks->firstWhere('cabang_id', $c->id);
            $this->initialStock[$c->id] = $stok ? $stok->jumlah : 0;
            $this->minStock[$c->id] = $stok ? $stok->min_stok : 3;
            $this->isAktifStock[$c->id] = $stok ? (bool) $stok->is_aktif : false;
        }

        $this->isEditMode = true;
        $this->modalProdukOpen = true;
    }

    protected function processAndSaveWebp($uploadedFile): string
    {
        $imagePath = $uploadedFile->getRealPath();
        $imageInfo = @getimagesize($imagePath);

        if ($imageInfo && function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $source = @imagecreatefromstring(file_get_contents($imagePath));
            if ($source !== false) {
                $filename = 'prod_'.time().'_'.Str::random(6).'.webp';

                $width = imagesx($source);
                $height = imagesy($source);

                $maxDim = 600;
                if ($width > $maxDim || $height > $maxDim) {
                    if ($width > $height) {
                        $newWidth = $maxDim;
                        $newHeight = (int) ($height * ($maxDim / $width));
                    } else {
                        $newHeight = $maxDim;
                        $newWidth = (int) ($width * ($maxDim / $height));
                    }
                    $resized = imagecreatetruecolor($newWidth, $newHeight);
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    ob_start();
                    imagewebp($resized, null, 80);
                    $webContent = ob_get_clean();
                    imagedestroy($resized);
                } else {
                    ob_start();
                    imagewebp($source, null, 80);
                    $webContent = ob_get_clean();
                }
                imagedestroy($source);

                if ($webContent !== false) {
                    Storage::disk('public')->put('products/'.$filename, $webContent);

                    return 'storage/products/'.$filename;
                }
            }
        }

        // Fallback jika GD webp tidak aktif
        $fallbackName = 'prod_'.time().'_'.Str::random(6).'.'.($uploadedFile->getClientOriginalExtension() ?: 'jpg');
        $storedPath = Storage::disk('public')->putFileAs('products', $uploadedFile, $fallbackName);

        return 'storage/'.$storedPath;
    }

    public function cancelUploadFoto(): void
    {
        $this->foto = null;
    }

    public function removeExistingFoto(): void
    {
        $this->existing_foto_url = null;
        $this->foto = null;
    }

    public function saveProduk(): void
    {
        $this->validate();

        $data = [
            'kategori_id' => $this->kategori_id ?: null,
            'barcode' => ! empty($this->barcode) ? trim($this->barcode) : null,
            'nama_produk' => trim($this->nama_produk),
            'harga_beli' => $this->harga_beli,
            'harga_jual' => $this->harga_jual,
            'ready_marketplace' => $this->ready_marketplace,
            'deskripsi' => ! empty($this->deskripsi) ? trim($this->deskripsi) : null,
        ];

        if ($this->foto) {
            $data['foto_url'] = $this->processAndSaveWebp($this->foto);
        } elseif ($this->isEditMode && $this->existing_foto_url === null) {
            $data['foto_url'] = null;
        }

        if ($this->isEditMode && $this->produkId) {
            abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('master.produk.edit'), 403, 'Akses edit produk dibatasi.');

            $produk = Produk::findOrFail($this->produkId);

            // Hapus file lama jika foto diubah atau dihapus
            if (isset($data['foto_url']) && $produk->foto_url && $produk->foto_url !== $data['foto_url']) {
                if (str_starts_with($produk->foto_url, 'storage/')) {
                    $oldPath = Str::after($produk->foto_url, 'storage/');
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $produk->update($data);

            // Update stok per cabang
            foreach (Cabang::all() as $c) {
                if (isset($this->initialStock[$c->id])) {
                    $qty = max(0, (int) $this->initialStock[$c->id]);
                    $minStok = isset($this->minStock[$c->id]) ? max(1, (int) $this->minStock[$c->id]) : 3;
                    $isAktif = ! empty($this->isAktifStock[$c->id]);
                    Stok::updateOrCreate(
                        ['produk_id' => $produk->id, 'cabang_id' => $c->id],
                        ['jumlah' => $qty, 'min_stok' => $minStok, 'is_aktif' => $isAktif]
                    );
                }
            }

            session()->flash('success', "Produk \"{$produk->nama_produk}\" berhasil diperbarui.");
        } else {
            abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('master.produk.create'), 403, 'Akses tambah produk dibatasi.');

            $produk = Produk::create($data);

            // Simpan stok awal tiap cabang
            $allCabangs = Cabang::all();
            foreach ($allCabangs as $c) {
                $qty = isset($this->initialStock[$c->id]) ? max(0, (int) $this->initialStock[$c->id]) : 0;
                $minStok = isset($this->minStock[$c->id]) ? max(1, (int) $this->minStock[$c->id]) : 3;
                $isAktif = ! empty($this->isAktifStock[$c->id]);
                Stok::create([
                    'produk_id' => $produk->id,
                    'cabang_id' => $c->id,
                    'jumlah' => $qty,
                    'min_stok' => $minStok,
                    'is_aktif' => $isAktif,
                ]);
            }

            session()->flash('success', "Produk baru \"{$produk->nama_produk}\" berhasil ditambahkan.");
        }

        $this->modalProdukOpen = false;
        $this->foto = null;
    }

    public function openDeleteConfirmation(int $id): void
    {
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('master.produk.delete'), 403, 'Akses hapus produk dibatasi.');

        $user = Auth::user();
        $activeBranchId = session('active_branch_id', 'hq');
        $this->deleteIsHQ = $user->isOwner() && ($activeBranchId === 'hq' || ! $activeBranchId);

        $produk = Produk::with(['kategori', 'stoks'])->findOrFail($id);

        $this->deleteProdukId = $produk->id;
        $this->deleteProdukNama = $produk->nama_produk;
        $this->deleteProdukBarcode = $produk->barcode ?? '-';
        $this->deleteProdukFoto = $produk->foto_url;
        $this->deleteProdukKategori = $produk->kategori?->nama_kategori ?? 'Umum';

        $targetCabangId = $user->isOwner() ? (int) $activeBranchId : (int) $user->cabang_id;
        $targetCabangId = $targetCabangId ?: 1;

        if ($this->deleteIsHQ) {
            $this->deleteProdukStok = (int) $produk->stoks->sum('jumlah');
            $this->deleteProdukHasHistory = $produk->penjualanDetails()->exists() || $produk->servisDetails()->exists();
        } else {
            $stok = $produk->stoks->where('cabang_id', $targetCabangId)->first();
            $this->deleteProdukStok = (int) ($stok?->jumlah ?? 0);
            $this->deleteProdukHasHistory = false;
        }

        $this->modalDeleteOpen = true;
    }

    public function confirmDelete(): void
    {
        if (! $this->deleteProdukId) {
            $this->modalDeleteOpen = false;

            return;
        }

        $this->deleteProduk($this->deleteProdukId);
        $this->modalDeleteOpen = false;
        $this->deleteProdukId = null;
    }

    public function deleteProduk(int $id): void
    {
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('master.produk.delete'), 403, 'Akses hapus produk dibatasi.');
        $user = Auth::user();
        $activeBranchId = session('active_branch_id', 'hq');
        $isHQ = $user->isOwner() && ($activeBranchId === 'hq' || ! $activeBranchId);

        $produk = Produk::findOrFail($id);

        if (! $isHQ) {
            $targetCabangId = $user->isOwner() ? (int) $activeBranchId : (int) $user->cabang_id;
            $targetCabangId = $targetCabangId ?: 1;

            Stok::where('produk_id', $id)
                ->where('cabang_id', $targetCabangId)
                ->update(['is_aktif' => false]);

            session()->flash('success', "Status penjualan produk \"{$produk->nama_produk}\" berhasil dinonaktifkan di toko.");

            return;
        }

        if ($produk->penjualanDetails()->exists() || $produk->servisDetails()->exists()) {
            session()->flash('error', "Produk \"{$produk->nama_produk}\" tidak dapat dihapus karena memiliki riwayat transaksi kasir atau servis.");

            return;
        }

        $nama = $produk->nama_produk;
        $produk->delete();

        session()->flash('success', "Produk \"{$nama}\" berhasil dihapus dari katalog master.");
    }

    // ==========================================
    // MANAJEMEN KATEGORI
    // ==========================================

    public function openKategoriModal(): void
    {
        $this->resetValidation();
        $this->new_kategori_name = '';
        $this->edit_kategori_id = null;
        $this->edit_kategori_name = '';
        $this->modalKategoriOpen = true;
    }

    public function saveNewKategori(): void
    {
        $this->validate([
            'new_kategori_name' => ['required', 'string', 'max:100', 'unique:kategori_produks,nama_kategori'],
        ], [
            'new_kategori_name.required' => 'Nama kategori wajib diisi.',
            'new_kategori_name.unique' => 'Kategori ini sudah ada.',
        ]);

        KategoriProduk::create([
            'nama_kategori' => trim($this->new_kategori_name),
            'slug' => Str::slug($this->new_kategori_name),
        ]);

        $this->new_kategori_name = '';
        session()->flash('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function startEditKategori(int $id): void
    {
        $kat = KategoriProduk::findOrFail($id);
        $this->edit_kategori_id = $kat->id;
        $this->edit_kategori_name = $kat->nama_kategori;
    }

    public function saveEditKategori(): void
    {
        $this->validate([
            'edit_kategori_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kategori_produks', 'nama_kategori')->ignore($this->edit_kategori_id),
            ],
        ], [
            'edit_kategori_name.required' => 'Nama kategori tidak boleh kosong.',
            'edit_kategori_name.unique' => 'Nama kategori ini sudah digunakan.',
        ]);

        $kat = KategoriProduk::findOrFail($this->edit_kategori_id);
        $kat->update([
            'nama_kategori' => trim($this->edit_kategori_name),
            'slug' => Str::slug($this->edit_kategori_name),
        ]);

        $this->edit_kategori_id = null;
        $this->edit_kategori_name = '';
        session()->flash('success', 'Kategori berhasil diperbarui.');
    }

    public function cancelEditKategori(): void
    {
        $this->edit_kategori_id = null;
        $this->edit_kategori_name = '';
    }

    public function deleteKategori(int $id): void
    {
        $kat = KategoriProduk::findOrFail($id);

        if ($kat->produks()->exists()) {
            session()->flash('error', "Kategori \"{$kat->nama_kategori}\" masih digunakan oleh beberapa produk.");

            return;
        }

        $nama = $kat->nama_kategori;
        $kat->delete();

        session()->flash('success', "Kategori \"{$nama}\" berhasil dihapus.");
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchId = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchId === 'hq' || ! $activeBranchId);

        $cabangs = Cabang::where('status', 'aktif')->orderBy('id')->get();
        $activeCabang = null;
        if (! $isHQ) {
            $activeCabang = $isOwner ? Cabang::find($activeBranchId) : $user->cabang;
            if (! $activeCabang) {
                $activeCabang = Cabang::first();
            }
        }

        $kategoris = KategoriProduk::withCount('produks')->orderBy('nama_kategori')->get();

        $query = Produk::with(['kategori', 'stoks']);

        if (! $isHQ && $activeCabang) {
            $query->whereHas('stoks', function ($q) use ($activeCabang) {
                $q->where('cabang_id', $activeCabang->id)->where('is_aktif', true);
            });
        }

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
            if ($isHQ) {
                if ($this->stokFilter === 'tersedia') {
                    $query->whereHas('stoks', function ($q) {
                        $q->where('is_aktif', true)->havingRaw('SUM(jumlah) > SUM(min_stok)');
                    });
                } elseif ($this->stokFilter === 'kritis') {
                    $query->whereHas('stoks', function ($q) {
                        $q->where('is_aktif', true)->havingRaw('SUM(jumlah) > 0 AND SUM(jumlah) <= SUM(min_stok)');
                    });
                } elseif ($this->stokFilter === 'habis') {
                    $query->whereHas('stoks', function ($q) {
                        $q->where('is_aktif', true)->havingRaw('SUM(jumlah) <= 0');
                    });
                }
            } else {
                $targetCabangId = $activeCabang?->id ?? 1;
                if ($this->stokFilter === 'tersedia') {
                    $query->whereHas('stoks', function ($q) use ($targetCabangId) {
                        $q->where('cabang_id', $targetCabangId)->where('is_aktif', true)->whereColumn('jumlah', '>', 'min_stok');
                    });
                } elseif ($this->stokFilter === 'kritis') {
                    $query->whereHas('stoks', function ($q) use ($targetCabangId) {
                        $q->where('cabang_id', $targetCabangId)->where('is_aktif', true)->where('jumlah', '>', 0)->whereColumn('jumlah', '<=', 'min_stok');
                    });
                } elseif ($this->stokFilter === 'habis') {
                    $query->whereHas('stoks', function ($q) use ($targetCabangId) {
                        $q->where('cabang_id', $targetCabangId)->where('is_aktif', true)->where('jumlah', '<=', 0);
                    });
                }
            }
        }

        $sortDir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';

        if ($this->sortField === 'stok_total') {
            $query->withSum('stoks as total_stok', 'jumlah')->orderBy('total_stok', $sortDir);
        } else {
            $allowedSorts = ['nama_produk', 'barcode', 'kategori_id', 'harga_beli', 'harga_jual', 'created_at', 'id'];
            $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'nama_produk';
            $query->orderBy($sortColumn, $sortDir);
        }

        $totalProduk = (clone $query)->count();
        $produks = $query->take($this->perPage)->get();
        $hasMore = $totalProduk > $this->perPage;

        return view('livewire.master.produk.index', [
            'produks' => $produks,
            'hasMore' => $hasMore,
            'totalProduk' => $totalProduk,
            'kategoris' => $kategoris,
            'cabangs' => $cabangs,
            'activeCabang' => $activeCabang,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
            'activeBranchId' => $activeBranchId,
        ]);
    }
}
