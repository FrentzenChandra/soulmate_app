<?php

namespace App\Livewire\Web;

use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.web')]
#[Title('Katalog Produk - Soulmate Komputer')]
class Produk extends Component
{
    use WithPagination;

    #[Url(except: 'semua')]
    public string $kategori = 'semua';

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'terbaru')]
    public string $sort = 'terbaru';

    public string $viewMode = 'grid'; // 'grid' | 'list'
    public int $perPage = 80;

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
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->kategori = 'semua';
        $this->search = '';
        $this->sort = 'terbaru';
        $this->resetPage();
    }

    // --- CART METHODS ---

    public function addToCart(int $productId): void
    {
        $products = $this->getAllProducts();
        $product = $products->firstWhere('id', $productId);

        if (! $product) {
            return;
        }

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty'] += 1;
        } else {
            $this->cart[$productId] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'formatted_price' => $product['formatted_price'],
                'image' => $product['image'],
                'brand' => $product['brand'],
                'qty' => 1,
            ];
        }

        session()->put('soulmate_cart', $this->cart);
        $this->toastMessage = 'Produk "' . $product['name'] . '" berhasil ditambahkan ke keranjang!';
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

        // Redirect to WhatsApp
        return redirect()->away($waUrl);
    }

    // --- PRODUCTS DATA REPOSITORY ---

    public function getAllProducts(): \Illuminate\Support\Collection
    {
        return collect([
            [
                'id' => 1,
                'name' => 'Acer Aspire 5 A514-56',
                'specs' => 'Intel Core i5-1235U | 8GB | 512GB SSD | 14" FHD',
                'price' => 8499000,
                'formatted_price' => '8.499.000',
                'image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500&q=80&fit=crop',
                'category' => 'laptop',
                'brand' => 'Acer',
                'badge' => ['text' => 'Baru', 'color' => 'bg-sky-500']
            ],
            [
                'id' => 2,
                'name' => 'Lenovo IdeaPad Slim 3',
                'specs' => 'Ryzen 5 7530U | 8GB | 512GB SSD | 14" FHD',
                'price' => 7299000,
                'formatted_price' => '7.299.000',
                'image' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=500&q=80&fit=crop',
                'category' => 'laptop',
                'brand' => 'Lenovo',
                'badge' => ['text' => 'Promo', 'color' => 'bg-rose-500']
            ],
            [
                'id' => 3,
                'name' => 'ASUS ROG Strix G16',
                'specs' => 'Intel Core i7-13650HX | 16GB | 1TB SSD | RTX 4060 | 16" FHD',
                'price' => 19999000,
                'formatted_price' => '19.999.000',
                'image' => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=500&q=80&fit=crop',
                'category' => 'laptop',
                'brand' => 'ASUS',
                'badge' => ['text' => 'Gaming', 'color' => 'bg-purple-600']
            ],
            [
                'id' => 4,
                'name' => 'HP 14s-fq1001AU',
                'specs' => 'Ryzen 5 5500U | 8GB | 512GB SSD | 14" FHD',
                'price' => 6899000,
                'formatted_price' => '6.899.000',
                'image' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=500&q=80&fit=crop',
                'category' => 'laptop',
                'brand' => 'HP',
                'badge' => null
            ],
            [
                'id' => 5,
                'name' => 'MacBook Air M2',
                'specs' => 'Apple M2 | 8GB | 256GB SSD | 13.6" Liquid Retina',
                'price' => 16499000,
                'formatted_price' => '16.499.000',
                'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500&q=80&fit=crop',
                'category' => 'apple',
                'brand' => 'Apple',
                'badge' => ['text' => 'Best Seller', 'color' => 'bg-emerald-500']
            ],
            [
                'id' => 6,
                'name' => 'PC Gaming Ryzen 5',
                'specs' => 'Ryzen 5 5600G | 16GB | 512GB SSD | Radeon Vega 7',
                'price' => 6799000,
                'formatted_price' => '6.799.000',
                'image' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?w=500&q=80&fit=crop',
                'category' => 'komputer',
                'brand' => 'ASUS',
                'badge' => ['text' => 'Rakitan', 'color' => 'bg-amber-500']
            ],
            [
                'id' => 7,
                'name' => 'ASUS VA24EHE',
                'specs' => '24" | IPS | 75Hz | Full HD | HDMI | VGA',
                'price' => 1799000,
                'formatted_price' => '1.799.000',
                'image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500&q=80&fit=crop',
                'category' => 'monitor',
                'brand' => 'ASUS',
                'badge' => null
            ],
            [
                'id' => 8,
                'name' => 'LG 27MP400',
                'specs' => '27" | IPS | 75Hz | Full HD | HDMI | VGA',
                'price' => 2299000,
                'formatted_price' => '2.299.000',
                'image' => 'https://images.unsplash.com/photo-1585792180666-f7347c490ee2?w=500&q=80&fit=crop',
                'category' => 'monitor',
                'brand' => 'LG',
                'badge' => null
            ],
            [
                'id' => 9,
                'name' => 'Epson L3250',
                'specs' => 'Print | Scan | Copy | WiFi | Ink Tank Original',
                'price' => 2799000,
                'formatted_price' => '2.799.000',
                'image' => 'https://images.unsplash.com/photo-1612815154858-60aa4c59eaa6?w=500&q=80&fit=crop',
                'category' => 'printer',
                'brand' => 'Epson',
                'badge' => ['text' => 'Hemat', 'color' => 'bg-teal-500']
            ],
            [
                'id' => 10,
                'name' => 'Canon G3020',
                'specs' => 'Print | Scan | Copy | WiFi | Ink Tank All-in-One',
                'price' => 2599000,
                'formatted_price' => '2.599.000',
                'image' => 'https://images.unsplash.com/photo-1562408590-e32931084e23?w=500&q=80&fit=crop',
                'category' => 'printer',
                'brand' => 'Canon',
                'badge' => null
            ],
            [
                'id' => 11,
                'name' => 'Logitech MK120',
                'specs' => 'Keyboard + Mouse USB | Spill Resistant | Black',
                'price' => 199000,
                'formatted_price' => '199.000',
                'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&q=80&fit=crop',
                'category' => 'aksesoris',
                'brand' => 'Logitech',
                'badge' => null
            ],
            [
                'id' => 12,
                'name' => 'Logitech G102',
                'specs' => 'Lightsync RGB | 8000 DPI | 6 Tombol | USB Gaming',
                'price' => 299000,
                'formatted_price' => '299.000',
                'image' => 'https://images.unsplash.com/photo-1615663245857-ac9310d5b1ff?w=500&q=80&fit=crop',
                'category' => 'gaming',
                'brand' => 'Logitech',
                'badge' => ['text' => 'Populer', 'color' => 'bg-indigo-500']
            ],
            [
                'id' => 13,
                'name' => 'Fantech HG24',
                'specs' => 'Gaming Headset 7.1 Surround | RGB Lighting | USB',
                'price' => 399000,
                'formatted_price' => '399.000',
                'image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500&q=80&fit=crop',
                'category' => 'gaming',
                'brand' => 'Fantech',
                'badge' => null
            ],
            [
                'id' => 14,
                'name' => 'TP-Link Archer C6',
                'specs' => 'AC1200 | Dual Band | 4 Antena High Gain | Gigabit',
                'price' => 699000,
                'formatted_price' => '699.000',
                'image' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=500&q=80&fit=crop',
                'category' => 'networking',
                'brand' => 'TP-Link',
                'badge' => null
            ],
            [
                'id' => 15,
                'name' => 'Microsoft Windows 11 Home',
                'specs' => 'Original License 64-bit | Retail Box | Lifetime',
                'price' => 1799000,
                'formatted_price' => '1.799.000',
                'image' => 'https://images.unsplash.com/photo-1628277613967-6abca504d0ac?w=500&q=80&fit=crop',
                'category' => 'software',
                'brand' => 'Microsoft',
                'badge' => ['text' => 'Original', 'color' => 'bg-blue-600']
            ],
            [
                'id' => 16,
                'name' => 'MacBook Pro 14 M3',
                'specs' => 'Apple M3 Pro | 18GB | 512GB SSD | 14.2" Liquid Retina XDR',
                'price' => 31999000,
                'formatted_price' => '31.999.000',
                'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500&q=80&fit=crop',
                'category' => 'apple',
                'brand' => 'Apple',
                'badge' => ['text' => 'Pro Flagship', 'color' => 'bg-neutral-800']
            ],
            [
                'id' => 17,
                'name' => 'PC All-in-One HP 24',
                'specs' => 'Intel Core i5-1335U | 8GB | 512GB SSD | 23.8" FHD IPS Touch',
                'price' => 11499000,
                'formatted_price' => '11.499.000',
                'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&q=80&fit=crop',
                'category' => 'komputer',
                'brand' => 'HP',
                'badge' => null
            ],
            [
                'id' => 18,
                'name' => 'Dell Vostro 3520',
                'specs' => 'Intel Core i5-1235U | 8GB | 512GB SSD | 15.6" FHD 120Hz',
                'price' => 8899000,
                'formatted_price' => '8.899.000',
                'image' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=500&q=80&fit=crop',
                'category' => 'laptop',
                'brand' => 'Dell',
                'badge' => null
            ],
            [
                'id' => 19,
                'name' => 'MSI Modern 14 C12M',
                'specs' => 'Intel Core i5-1235U | 16GB | 512GB SSD | 14" IPS FHD',
                'price' => 7999000,
                'formatted_price' => '7.999.000',
                'image' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=500&q=80&fit=crop',
                'category' => 'laptop',
                'brand' => 'MSI',
                'badge' => null
            ],
            [
                'id' => 20,
                'name' => 'Samsung Essential Monitor S3',
                'specs' => '24" | IPS | 75Hz | AMD FreeSync | Borderless Design',
                'price' => 1599000,
                'formatted_price' => '1.599.000',
                'image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500&q=80&fit=crop',
                'category' => 'monitor',
                'brand' => 'Samsung',
                'badge' => null
            ],
        ]);
    }

    public function render()
    {
        $categories = [
            ['slug' => 'semua', 'name' => 'Semua Produk'],
            ['slug' => 'laptop', 'name' => 'Laptop'],
            ['slug' => 'komputer', 'name' => 'Komputer PC'],
            ['slug' => 'monitor', 'name' => 'Monitor'],
            ['slug' => 'printer', 'name' => 'Printer'],
            ['slug' => 'aksesoris', 'name' => 'Aksesoris'],
            ['slug' => 'networking', 'name' => 'Networking'],
            ['slug' => 'software', 'name' => 'Software'],
            ['slug' => 'gaming', 'name' => 'Gaming'],
            ['slug' => 'apple', 'name' => 'Apple'],
        ];

        $allProducts = $this->getAllProducts();
        $totalCatalogCount = $allProducts->count();

        // Filter by category
        if ($this->kategori !== 'semua') {
            $allProducts = $allProducts->filter(function ($item) {
                return $item['category'] === $this->kategori;
            });
        }

        // Filter by search query (Tokenized multi-word loose search)
        if (! empty(trim($this->search))) {
            $tokens = array_filter(explode(' ', strtolower(trim($this->search))));
            $allProducts = $allProducts->filter(function ($item) use ($tokens) {
                $haystack = strtolower($item['name'] . ' ' . $item['specs'] . ' ' . $item['brand'] . ' ' . $item['category']);
                foreach ($tokens as $token) {
                    if (! str_contains($haystack, $token)) {
                        return false;
                    }
                }
                return true;
            });
        }

        // Sorting
        if ($this->sort === 'termurah') {
            $allProducts = $allProducts->sortBy('price');
        } elseif ($this->sort === 'termahal') {
            $allProducts = $allProducts->sortByDesc('price');
        } elseif ($this->sort === 'nama_asc') {
            $allProducts = $allProducts->sortBy('name');
        }

        $totalFilteredCount = $allProducts->count();

        // LengthAwarePaginator implementation
        $currentPage = (int) $this->getPage();
        $currentItems = $allProducts->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();

        $paginatedProducts = new LengthAwarePaginator(
            $currentItems,
            $totalFilteredCount,
            $this->perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Cart calculations
        $cartCount = array_sum(array_column($this->cart, 'qty'));
        $cartTotal = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $this->cart));

        $hasActiveFilters = $this->kategori !== 'semua' || ! empty($this->search);

        return view('livewire.web.produk', [
            'categories' => $categories,
            'products' => $paginatedProducts,
            'totalCatalogCount' => $totalCatalogCount,
            'totalFilteredCount' => $totalFilteredCount,
            'cartCount' => $cartCount,
            'cartTotal' => $cartTotal,
            'formattedCartTotal' => number_format($cartTotal, 0, ',', '.'),
            'hasActiveFilters' => $hasActiveFilters,
        ]);
    }
}
