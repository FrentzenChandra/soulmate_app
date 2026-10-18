<?php

namespace App\Livewire\Web;

use App\Models\KategoriProduk;
use App\Models\Produk as ProdukModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.web')]
#[Title('Katalog Produk - Soulmate Komputer')]
class Produk extends Component
{
    #[Url(except: 'semua')]
    public string $kategori = 'semua';

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'terbaru')]
    public string $sort = 'terbaru';

    public string $viewMode = 'grid'; // 'grid' | 'list'
    public int $perPage = 20;

    // Cart system state
    public array $cart = [];
    public bool $cartOpen = false;
    public string $customerName = '';
    public string $customerPhone = '';
    public string $customerNotes = '';
    public ?string $toastMessage = null;

    public function mount(): void
    {
        $this->cart = session()->get('soulmate_cart', []);
        if (request()->has('open_cart')) {
            $this->cartOpen = true;
        }
    }

    public function setKategori(string $cat): void
    {
        $this->kategori = $cat;
        $this->perPage = 20;
    }

    public function updatedSearch(): void
    {
        $this->perPage = 20;
    }

    public function updatedSort(): void
    {
        $this->perPage = 20;
    }

    public function loadMore(): void
    {
        $this->perPage += 20;
    }

    public function resetFilters(): void
    {
        $this->kategori = 'semua';
        $this->search = '';
        $this->sort = 'terbaru';
        $this->perPage = 20;
    }

    protected function resolveProductImage(?string $fotoUrl): string
    {
        if (! empty($fotoUrl)) {
            if (str_starts_with($fotoUrl, 'http://') || str_starts_with($fotoUrl, 'https://')) {
                return $fotoUrl;
            }
            $cleanPath = ltrim($fotoUrl, '/');
            return asset($cleanPath);
        }

        return asset('images/default-product.svg');
    }

    // --- CART METHODS ---

    public function addToCart(int $productId): void
    {
        $product = ProdukModel::with('kategori')
            ->where('ready_marketplace', true)
            ->find($productId);

        if (! $product) {
            return;
        }

        $price = (float) $product->harga_jual;
        $brand = $product->kategori?->nama_kategori ?: 'Soulmate';
        $image = $this->resolveProductImage($product->foto_url);

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty'] += 1;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->nama_produk,
                'price' => $price,
                'formatted_price' => number_format($price, 0, ',', '.'),
                'image' => $image,
                'brand' => $brand,
                'qty' => 1,
            ];
        }

        session()->put('soulmate_cart', $this->cart);
        $this->toastMessage = 'Produk "' . $product->nama_produk . '" berhasil ditambahkan ke keranjang!';
        $this->dispatch('cart-updated', count: array_sum(array_column($this->cart, 'qty')));
    }

    public function incrementQty(int $productId): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty'] += 1;
            session()->put('soulmate_cart', $this->cart);
            $this->dispatch('cart-updated', count: array_sum(array_column($this->cart, 'qty')));
        }
    }

    public function decrementQty(int $productId): void
    {
        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['qty'] > 1) {
                $this->cart[$productId]['qty'] -= 1;
            } else {
                unset($this->cart[$productId]);
            }
            session()->put('soulmate_cart', $this->cart);
            $this->dispatch('cart-updated', count: array_sum(array_column($this->cart, 'qty')));
        }
    }

    public function removeFromCart(int $productId): void
    {
        if (isset($this->cart[$productId])) {
            unset($this->cart[$productId]);
            session()->put('soulmate_cart', $this->cart);
            $this->dispatch('cart-updated', count: array_sum(array_column($this->cart, 'qty')));
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
        session()->forget('soulmate_cart');
        $this->dispatch('cart-updated', count: 0);
    }

    public function toggleCartModal(): void
    {
        $this->cartOpen = ! $this->cartOpen;
    }

    public function dismissToast(): void
    {
        $this->toastMessage = null;
    }

    public function orderViaWhatsApp()
    {
        $this->validate([
            'customerName' => 'required|min:2|max:100',
        ], [
            'customerName.required' => 'Nama lengkap wajib diisi untuk konfirmasi pesanan.',
        ]);

        if (empty($this->cart)) {
            $this->toastMessage = 'Keranjang belanja Anda masih kosong.';
            return null;
        }

        // Build WhatsApp Message
        $itemsText = "";
        $no = 1;
        $totalPrice = 0;

        foreach ($this->cart as $item) {
            $subtotal = $item['price'] * $item['qty'];
            $totalPrice += $subtotal;
            $itemsText .= "{$no}. *{$item['name']}*\n";
            $itemsText .= "   Jumlah: {$item['qty']} unit x Rp " . number_format($item['price'], 0, ',', '.') . "\n";
            $itemsText .= "   Subtotal: Rp " . number_format($subtotal, 0, ',', '.') . "\n\n";
            $no++;
        }

        $formattedTotal = number_format($totalPrice, 0, ',', '.');
        $nama = trim($this->customerName);
        $phone = trim($this->customerPhone);
        $notes = trim($this->customerNotes);

        $message = "Halo *Soulmate Komputer*, saya ingin memesan produk berikut dari website:\n\n";
        $message .= "🛒 *RINCIAN PESANAN:*\n";
        $message .= "------------------------------------\n";
        $message .= $itemsText;
        $message .= "💰 *TOTAL TRANSAKSI: Rp {$formattedTotal}*\n";
        $message .= "------------------------------------\n\n";
        $message .= "👤 *DATA PEMESAN:*\n";
        $message .= "• *Nama:* {$nama}\n";
        if (! empty($phone)) {
            $message .= "• *No. Kontak:* {$phone}\n";
        }
        if (! empty($notes)) {
            $message .= "• *Catatan / Alamat:* {$notes}\n";
        }
        $message .= "\nMohon informasi ketersediaan unit dan instruksi pembayarannya. Terima kasih!";

        $encodedText = rawurlencode($message);
        $waUrl = "https://wa.me/6281995466255?text={$encodedText}";

        return redirect()->away($waUrl);
    }

    public function render()
    {
        // 1. Kategori dinamis dari database (hanya yang memiliki produk ready_marketplace)
        // Cache 1 hari (86.400 detik)
        $dbCategories = Cache::remember('web_kategori_produks_ready', 86400, function () {
            return KategoriProduk::whereHas('produks', function ($q) {
                $q->where('ready_marketplace', true);
            })->orderBy('nama_kategori')->get(['id', 'nama_kategori', 'slug']);
        });

        $categories = collect([
            ['slug' => 'semua', 'name' => 'Semua Produk'],
        ])->concat($dbCategories->map(fn($c) => [
            'slug' => $c->slug ?: Str::slug($c->nama_kategori),
            'name' => $c->nama_kategori,
            'id' => $c->id,
        ]))->all();

        // 2. Query Produk riil database (ready_marketplace = true, tanpa cek stok)
        $query = ProdukModel::query()
            ->with('kategori')
            ->where('ready_marketplace', true);

        $totalCatalogCount = (clone $query)->count();

        // Filter Kategori
        if ($this->kategori !== 'semua') {
            $query->whereHas('kategori', function ($q) {
                $q->where('slug', $this->kategori)
                  ->orWhere('nama_kategori', $this->kategori);
            });
        }

        // Filter Pencarian Multi-Kata Tokenized (Rule 3)
        if (! empty(trim($this->search))) {
            $words = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(nama_produk) LIKE ?', [$pattern])
                           ->orWhereRaw('LOWER(COALESCE(deskripsi, "")) LIKE ?', [$pattern])
                           ->orWhereRaw('LOWER(COALESCE(barcode, "")) LIKE ?', [$pattern]);
                    });
                }
            });
        }

        // Sorting
        if ($this->sort === 'termurah') {
            $query->orderBy('harga_jual', 'asc');
        } elseif ($this->sort === 'termahal') {
            $query->orderBy('harga_jual', 'desc');
        } elseif ($this->sort === 'nama_asc') {
            $query->orderBy('nama_produk', 'asc');
        } else {
            $query->latest('id');
        }

        $totalFilteredCount = (clone $query)->count();

        // Infinite Scroll Take
        $dbProducts = $query->take($this->perPage)->get();
        $hasMorePages = $this->perPage < $totalFilteredCount;

        // Transform collection agar presisi 100% dengan struktur view
        $products = $dbProducts->map(function ($p) {
            $price = (float) $p->harga_jual;
            $brand = $p->kategori?->nama_kategori ?: 'Soulmate';

            return [
                'id' => (int) $p->id,
                'name' => $p->nama_produk,
                'specs' => $p->deskripsi ? Str::limit(strip_tags($p->deskripsi), 80) : '',
                'price' => $price,
                'formatted_price' => number_format($price, 0, ',', '.'),
                'image' => $this->resolveProductImage($p->foto_url),
                'category' => $p->kategori?->slug ?: Str::slug($p->kategori?->nama_kategori ?? 'lainnya'),
                'brand' => $brand,
                'badge' => null,
            ];
        });

        // Cart calculations
        $cartCount = array_sum(array_column($this->cart, 'qty'));
        $cartTotal = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $this->cart));

        $hasActiveFilters = $this->kategori !== 'semua' || ! empty($this->search);

        return view('livewire.web.produk', [
            'categories' => $categories,
            'products' => $products,
            'totalCatalogCount' => $totalCatalogCount,
            'totalFilteredCount' => $totalFilteredCount,
            'hasMorePages' => $hasMorePages,
            'cartCount' => $cartCount,
            'cartTotal' => $cartTotal,
            'formattedCartTotal' => number_format($cartTotal, 0, ',', '.'),
            'hasActiveFilters' => $hasActiveFilters,
        ]);
    }
}
