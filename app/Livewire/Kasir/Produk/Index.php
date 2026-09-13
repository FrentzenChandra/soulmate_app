<?php

namespace App\Livewire\Kasir\Produk;

use App\Models\Cabang;
use App\Models\Customer;
use App\Models\KategoriProduk;
use App\Models\MasterPpn;
use App\Models\NomorUrutTransaksi;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Kasir POS Produk - Soulmate Komputer')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

    // Search & Filter Katalog (Hanya Pencarian Nama/Kata Kunci)
    public string $search = '';

    public string $kategoriFilter = 'semua';

    // Dedicated Scanner Modal (Barcode Scanner USB & Kamera HP/Webcam)
    public bool $modalScanOpen = false;

    public string $scanTab = 'scanner'; // 'scanner' atau 'camera'

    public string $scanBarcode = '';

    public ?string $lastScannedName = null;

    // Keranjang Belanja (Cart)
    public array $cart = [];

    // Customer Selection (umum / langganan)
    public string $customerType = 'umum';

    public ?int $selectedCustomerId = null;

    public string $customerSearch = '';

    public bool $modalNewCustomerOpen = false;

    public string $newCustomerNama = '';

    public string $newCustomerHp = '';

    public string $newCustomerAlamat = '';

    public $newCustomerFoto = null;

    // Diskon & PPN
    public float $diskon = 0;

    public ?int $selectedPpnId = null;

    // Mode Transaksi (Real vs Non-Real / Cetak Bon Formalitas Saja)
    public bool $isReal = true;

    // Modal Pembayaran
    public bool $modalBayarOpen = false;

    public ?string $metodeBayar = null;

    public float $jumlahBayar = 0;

    public string $catatan = '';

    // Modal Struk & Nota Selesai
    public bool $modalSuccessOpen = false;

    public ?int $lastPenjualanId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'kategoriFilter' => ['except' => 'semua'],
    ];

    public int $perPage = 12;

    public function mount()
    {
        $defaultPpn = MasterPpn::where('is_aktif', true)->where('persen', '>', 0)->first()
                   ?? MasterPpn::where('is_aktif', true)->first();
        $this->selectedPpnId = $defaultPpn?->id;
    }

    public function loadMore(): void
    {
        $this->perPage += 12;
    }

    public function updatingSearch()
    {
        $this->perPage = 12;
        $this->resetPage();
    }

    public function updatingKategoriFilter()
    {
        $this->perPage = 12;
        $this->resetPage();
    }

    public function updatedDiskon(): void
    {
        if ($this->modalBayarOpen && $this->metodeBayar === 'cash') {
            $this->jumlahBayar = $this->grandTotal;
        }
    }

    public function updatedSelectedPpnId(): void
    {
        if ($this->modalBayarOpen && $this->metodeBayar === 'cash') {
            $this->jumlahBayar = $this->grandTotal;
        }
    }

    public function getActiveCabangProperty(): ?Cabang
    {
        $user = Auth::user();
        if ($user->isOwner()) {
            $activeBranchId = session('active_branch_id', 'hq');
            if ($activeBranchId === 'hq' || ! $activeBranchId) {
                return Cabang::where('status', 'aktif')->first();
            }

            return Cabang::find($activeBranchId);
        }

        return $user->cabang;
    }

    /**
     * Modal Scanner Barcode Dedicated (USB Scanner & Kamera)
     */
    public function openScanModal(string $tab = 'scanner'): void
    {
        $this->scanTab = $tab;
        $this->scanBarcode = '';
        $this->lastScannedName = null;
        $this->modalScanOpen = true;
    }

    public function closeScanModal(): void
    {
        $this->modalScanOpen = false;
        $this->scanBarcode = '';
    }

    public function switchScanTab(string $tab): void
    {
        $this->scanTab = $tab;
        $this->scanBarcode = '';
    }

    public function processScanBarcode(?string $customBarcode = null): void
    {
        $raw = $customBarcode ?? $this->scanBarcode;
        $term = strtolower(trim($raw, "` \t\n\r\0\x0B"));
        if (empty($term)) {
            return;
        }

        $cabang = $this->activeCabang;
        if (! $cabang) {
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => 'Cabang aktif tidak ditemukan.']);

            return;
        }

        // Cari berdasarkan barcode atau nama produk exact
        $produk = Produk::whereRaw('LOWER(barcode) = ?', [$term])
            ->orWhereRaw('LOWER(nama_produk) = ?', [$term])
            ->first();

        if ($produk) {
            $this->addToCart($produk->id);
            $this->lastScannedName = $produk->nama_produk;
            $this->scanBarcode = '';
            $this->dispatch('swal:toast', [
                'title' => 'Produk Ditambahkan',
                'text' => "{$produk->nama_produk} berhasil masuk keranjang.",
            ]);
        } else {
            $this->dispatch('swal:warning', [
                'title' => 'Barcode Tidak Terdaftar',
                'text' => "Barcode '{$raw}' tidak ditemukan dalam katalog.",
            ]);
            $this->scanBarcode = '';
        }
    }

    /**
     * Cari produk via Filter Kata Kunci Nama Produk (Bebas Huruf Besar/Kecil)
     */
    public function handleSearchOrScan(?string $inputQuery = null): void
    {
        $rawTerm = trim($inputQuery ?? $this->search);
        if (empty($rawTerm)) {
            $this->search = '';
            $this->resetPage();

            return;
        }

        $this->search = $rawTerm;
        $this->resetPage();

        $cleanTerm = strtolower($rawTerm);
        $words = array_filter(explode(' ', $cleanTerm));
        $matchingQuery = Produk::query();
        foreach ($words as $word) {
            $pattern = '%'.$word.'%';
            $matchingQuery->where(function ($sq) use ($pattern) {
                $sq->whereRaw('LOWER(nama_produk) LIKE ?', [$pattern]);
            });
        }

        $matchingProduks = $matchingQuery->get();

        if ($matchingProduks->count() === 1) {
            $this->addToCart($matchingProduks->first()->id);
            $this->search = '';
            $this->resetPage();
        } elseif ($matchingProduks->count() === 0) {
            $this->dispatch('swal:warning', [
                'title' => 'Produk Tidak Ditemukan',
                'text' => "Tidak ada produk dengan kata kunci '{$rawTerm}'.",
            ]);
        }
    }

    public function searchBarcode(): void
    {
        $this->handleSearchOrScan();
    }

    /**
     * Tambah Produk ke Keranjang (Cart)
     */
    public function toggleRealMode(): void
    {
        $this->isReal = ! $this->isReal;

        if ($this->isReal) {
            $cabang = $this->activeCabang;
            foreach ($this->cart as $pId => $item) {
                $stok = Stok::where('produk_id', $pId)->where('cabang_id', $cabang?->id)->first();
                $stokFisik = $stok?->jumlah ?? 0;
                if ($stokFisik <= 0) {
                    unset($this->cart[$pId]);
                } elseif ($item['qty'] > $stokFisik) {
                    $this->cart[$pId]['qty'] = $stokFisik;
                    $this->cart[$pId]['stok_max'] = $stokFisik;
                    $this->cart[$pId]['subtotal'] = $stokFisik * $this->cart[$pId]['harga'];
                } else {
                    $this->cart[$pId]['stok_max'] = $stokFisik;
                }
            }
        } else {
            foreach ($this->cart as $pId => $item) {
                $this->cart[$pId]['stok_max'] = 99999;
            }
        }
    }

    public function addToCart(int $produkId): void
    {
        $cabang = $this->activeCabang;
        if (! $cabang) {
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => 'Cabang aktif belum dipilih.']);

            return;
        }

        $produk = Produk::findOrFail($produkId);
        $stok = Stok::where('produk_id', $produk->id)
            ->where('cabang_id', $cabang->id)
            ->first();

        $stokFisik = $stok?->jumlah ?? 0;

        // Validasi stok fisik hanya jika transaksi Real
        if ($this->isReal && $stokFisik <= 0) {
            $this->dispatch('swal:warning', [
                'title' => 'Stok Habis!',
                'text' => "Stok produk '{$produk->nama_produk}' kosong di cabang {$cabang->nama_cabang}.",
            ]);

            return;
        }

        $effectiveMax = $this->isReal ? $stokFisik : 99999;

        if (isset($this->cart[$produkId])) {
            $currentQty = $this->cart[$produkId]['qty'];
            if ($this->isReal && $currentQty + 1 > $stokFisik) {
                $this->dispatch('swal:warning', [
                    'title' => 'Batas Stok Tercapai',
                    'text' => "Maksimal stok tersedia hanya {$stokFisik} unit.",
                ]);

                return;
            }
            $this->cart[$produkId]['qty'] += 1;
            $this->cart[$produkId]['subtotal'] = $this->cart[$produkId]['qty'] * $this->cart[$produkId]['harga'];
        } else {
            $this->cart[$produkId] = [
                'id' => $produk->id,
                'nama' => $produk->nama_produk,
                'barcode' => $produk->barcode ?? '-',
                'foto_url' => $produk->foto_url,
                'harga' => (float) $produk->harga_jual,
                'qty' => 1,
                'stok_max' => $effectiveMax,
                'subtotal' => (float) $produk->harga_jual,
            ];
        }
    }

    public function updateQty(int $produkId, int|string $qty): void
    {
        $qty = (int) $qty;

        if (! isset($this->cart[$produkId])) {
            return;
        }

        if ($qty <= 0) {
            $this->removeFromCart($produkId);

            return;
        }

        $stokMax = $this->cart[$produkId]['stok_max'];
        if ($this->isReal && $qty > $stokMax) {
            $qty = $stokMax;
            $this->dispatch('swal:warning', [
                'title' => 'Batas Stok',
                'text' => "Kuantiti disesuaikan ke stok maksimal ({$stokMax} unit).",
            ]);
        }

        $this->cart[$produkId]['qty'] = $qty;
        $this->cart[$produkId]['subtotal'] = $qty * $this->cart[$produkId]['harga'];
    }

    public function adjustQty(int $produkId, int $delta): void
    {
        if (! isset($this->cart[$produkId])) {
            return;
        }

        $newQty = $this->cart[$produkId]['qty'] + $delta;
        $this->updateQty($produkId, $newQty);
    }

    public function removeFromCart(int $produkId): void
    {
        unset($this->cart[$produkId]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->diskon = 0;
    }

    // Kalkulasi Finansial
    public function getSubtotalProperty(): float
    {
        return collect($this->cart)->sum('subtotal');
    }

    public function getPpnPersenProperty(): float
    {
        if (! $this->selectedPpnId) {
            return 0;
        }
        $ppn = MasterPpn::find($this->selectedPpnId);

        return $ppn ? (float) $ppn->persen : 0;
    }

    public function getPpnNominalProperty(): float
    {
        $afterDiskon = max(0, $this->subtotal - $this->diskon);

        return ($afterDiskon * $this->ppnPersen) / 100;
    }

    public function getGrandTotalProperty(): float
    {
        $afterDiskon = max(0, $this->subtotal - $this->diskon);

        return $afterDiskon + $this->ppnNominal;
    }

    public function getKembalianProperty(): float
    {
        if ($this->metodeBayar !== 'cash') {
            return 0;
        }

        return max(0, $this->jumlahBayar - $this->grandTotal);
    }

    protected function processAndSaveCustomerWebp($uploadedFile): string
    {
        $imagePath = $uploadedFile->getRealPath();
        $imageInfo = @getimagesize($imagePath);

        if ($imageInfo && function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $source = @imagecreatefromstring(file_get_contents($imagePath));
            if ($source !== false) {
                $filename = 'cust_'.time().'_'.Str::random(6).'.webp';

                $width = imagesx($source);
                $height = imagesy($source);

                $maxDim = 500;
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
                    Storage::disk('public')->put('customers/'.$filename, $webContent);

                    return 'storage/customers/'.$filename;
                }
            }
        }

        $fallbackName = 'cust_'.time().'_'.Str::random(6).'.'.($uploadedFile->getClientOriginalExtension() ?: 'jpg');
        $storedPath = Storage::disk('public')->putFileAs('customers', $uploadedFile, $fallbackName);

        return 'storage/'.$storedPath;
    }

    // Pelanggan
    public function selectCustomer(int $customerId): void
    {
        $this->selectedCustomerId = $customerId;
        $this->customerType = 'langganan';
        $this->customerSearch = '';
    }

    public function setCustomerUmum(): void
    {
        $this->customerType = 'umum';
        $this->selectedCustomerId = null;
        $this->customerSearch = '';
    }

    public function openNewCustomerModal(): void
    {
        $this->reset(['newCustomerNama', 'newCustomerHp', 'newCustomerAlamat', 'newCustomerFoto']);
        $this->modalNewCustomerOpen = true;
    }

    public function saveNewCustomer(): void
    {
        $this->validate([
            'newCustomerNama' => 'required|string|max:100',
            'newCustomerHp' => 'nullable|string|max:20',
            'newCustomerAlamat' => 'nullable|string|max:255',
            'newCustomerFoto' => 'nullable|image|max:4096',
        ], [
            'newCustomerNama.required' => 'Nama pelanggan wajib diisi.',
            'newCustomerFoto.image' => 'File foto harus berupa gambar valid.',
        ]);

        $cabang = $this->activeCabang;
        if (! $cabang) {
            return;
        }

        $fotoUrl = null;
        if ($this->newCustomerFoto) {
            $fotoUrl = $this->processAndSaveCustomerWebp($this->newCustomerFoto);
        }

        $customer = Customer::create([
            'cabang_id' => $cabang->id,
            'nama' => trim($this->newCustomerNama),
            'no_hp' => trim($this->newCustomerHp) ?: null,
            'alamat' => trim($this->newCustomerAlamat) ?: null,
            'tipe' => 'langganan',
            'foto_url' => $fotoUrl,
        ]);

        $this->selectedCustomerId = $customer->id;
        $this->customerType = 'langganan';
        $this->modalNewCustomerOpen = false;
        $this->newCustomerFoto = null;
        $this->dispatch('swal:success', ['title' => 'Sukses', 'text' => "Pelanggan langganan '{$customer->nama}' berhasil ditambahkan."]);
    }

    // Checkout
    public function openPaymentModal(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('swal:warning', ['title' => 'Keranjang Kosong', 'text' => 'Pilih produk terlebih dahulu untuk melakukan transaksi.']);

            return;
        }

        $this->jumlahBayar = $this->grandTotal;
        $this->catatan = '';
        $this->modalBayarOpen = true;
    }

    public function setUangPas(): void
    {
        $this->jumlahBayar = $this->grandTotal;
    }

    public function setQuickNominal(float $nominal): void
    {
        $this->jumlahBayar = $nominal;
    }

    public function prosesPembayaran(): void
    {
        if (empty($this->cart)) {
            $this->modalBayarOpen = false;

            return;
        }

        $cabang = $this->activeCabang;
        if (! $cabang) {
            $this->dispatch('swal:error', ['title' => 'Error', 'text' => 'Cabang aktif tidak valid.']);

            return;
        }

        if ($this->jumlahBayar > 0 && $this->jumlahBayar < $this->grandTotal) {
            $this->addError('jumlahBayar', 'Nominal uang kurang dari total transaksi.');

            return;
        }

        if ($this->jumlahBayar <= 0) {
            $this->jumlahBayar = $this->grandTotal;
        }

        try {
            DB::beginTransaction();

            $now = now();
            $year = (int) $now->format('Y');
            $month = (int) $now->format('m');

            // 1. Lock & Generate Nomor Transaksi
            $counter = NomorUrutTransaksi::where('cabang_id', $cabang->id)
                ->where('tipe', 'produk')
                ->where('tahun', $year)
                ->where('bulan', $month)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                $counter = NomorUrutTransaksi::create([
                    'cabang_id' => $cabang->id,
                    'tipe' => 'produk',
                    'tahun' => $year,
                    'bulan' => $month,
                    'last_number' => 0,
                ]);
            }

            $counter->increment('last_number');
            $noUrut = str_pad($counter->last_number, 5, '0', STR_PAD_LEFT);
            $kodeCabangClean = str_replace('-', '', $cabang->kode_cabang);
            $noTransaksi = "INV-PJ-{$kodeCabangClean}-{$now->format('ym')}-{$noUrut}";

            // 2. Simpan Data Penjualan
            $penjualan = Penjualan::create([
                'no_transaksi' => $noTransaksi,
                'cabang_id' => $cabang->id,
                'kasir_id' => Auth::id(),
                'customer_id' => $this->customerType === 'langganan' ? $this->selectedCustomerId : null,
                'servis_id' => null,
                'tipe_transaksi' => 'penjualan_barang',
                'tanggal' => $now,
                'subtotal' => round((float) $this->subtotal, 2),
                'diskon' => round((float) $this->diskon, 2),
                'ppn_id' => $this->selectedPpnId,
                'ppn_persen' => round((float) $this->ppnPersen, 2),
                'ppn_nominal' => round((float) $this->ppnNominal, 2),
                'total' => round((float) $this->grandTotal, 2),
                'jumlah_bayar' => round((float) $this->jumlahBayar, 2),
                'kembalian' => round((float) $this->kembalian, 2),
                'metode_bayar' => $this->metodeBayar ?: null,
                'status' => 'selesai',
                'is_real' => $this->isReal,
                'catatan' => $this->catatan ?: null,
            ]);

            // 3. Simpan Detail Item & Kurangi Stok (Hanya Jika Transaksi Riil)
            foreach ($this->cart as $item) {
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'tipe_item' => 'barang',
                    'produk_id' => $item['id'],
                    'nama_item' => $item['nama'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'subtotal' => $item['subtotal'],
                ]);

                if ($this->isReal) {
                    $stok = Stok::where('produk_id', $item['id'])
                        ->where('cabang_id', $cabang->id)
                        ->first();

                    if ($stok) {
                        $stok->decrement('jumlah', $item['qty']);
                    }
                }
            }

            DB::commit();

            if ($this->isReal) {
                $this->dispatch('stockUpdated');
            }

            $this->lastPenjualanId = $penjualan->id;
            $this->modalBayarOpen = false;
            $this->modalSuccessOpen = true;
            $this->clearCart();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('swal:error', [
                'title' => 'Gagal Memproses Transaksi',
                'text' => $e->getMessage(),
            ]);
        }
    }

    public function newTransaction(): void
    {
        $this->modalSuccessOpen = false;
        $this->lastPenjualanId = null;
        $this->clearCart();
        $this->setCustomerUmum();
    }

    public function render()
    {
        $cabang = $this->activeCabang;
        $kategoris = KategoriProduk::orderBy('nama_kategori')->get();
        $masterPpns = MasterPpn::where('is_aktif', true)->orderBy('persen')->get();

        $customers = collect();
        if ($this->customerType === 'langganan' && ! empty($this->customerSearch) && $cabang) {
            $term = '%'.strtolower(trim($this->customerSearch)).'%';
            $customers = Customer::where('cabang_id', $cabang->id)
                ->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(nama) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(no_hp) LIKE ?', [$term]);
                })
                ->take(6)
                ->get();
        }

        $selectedCustomer = $this->selectedCustomerId ? Customer::find($this->selectedCustomerId) : null;
        $lastPenjualan = $this->lastPenjualanId ? Penjualan::with(['details.produk', 'cabang', 'kasir', 'customer', 'ppn'])->find($this->lastPenjualanId) : null;

        if (! $cabang) {
            return view('livewire.kasir.produk.index', [
                'cabang' => null,
                'produks' => collect(),
                'hasMore' => false,
                'totalProduk' => 0,
                'kategoris' => $kategoris,
                'masterPpns' => $masterPpns,
                'customers' => $customers,
                'selectedCustomer' => null,
                'lastPenjualan' => null,
            ]);
        }

        $query = Produk::whereHas('stoks', function ($q) use ($cabang) {
            $q->where('cabang_id', $cabang->id)->where('is_aktif', true);
        })->with(['kategori', 'stoks' => function ($q) use ($cabang) {
            $q->where('cabang_id', $cabang->id);
        }]);

        if (! empty($this->search)) {
            $cleanSearch = trim($this->search);
            $words = array_filter(explode(' ', $cleanSearch));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%'.strtolower($word).'%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(nama_produk) LIKE ?', [$pattern]);
                    });
                }
            });
        }

        if ($this->kategoriFilter !== 'semua') {
            $query->where('kategori_id', $this->kategoriFilter);
        }

        $totalProduk = (clone $query)->count();
        $produks = $query->orderBy('nama_produk', 'asc')->take($this->perPage)->get();
        $hasMore = $totalProduk > $this->perPage;

        return view('livewire.kasir.produk.index', [
            'cabang' => $cabang,
            'produks' => $produks,
            'hasMore' => $hasMore,
            'totalProduk' => $totalProduk,
            'kategoris' => $kategoris,
            'masterPpns' => $masterPpns,
            'customers' => $customers,
            'selectedCustomer' => $selectedCustomer,
            'lastPenjualan' => $lastPenjualan,
        ]);
    }
}
