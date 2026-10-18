<?php

namespace App\Livewire\Web;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.web')]
#[Title('Beranda - Soulmate Komputer')]
class Home extends Component
{
    public function render()
    {
        $categories = [
            [
                'name' => 'Laptop',
                'category' => 'laptop',
                'image' => asset('images/image_beranda/laptop.jpeg'),
            ],
            [
                'name' => 'Komputer',
                'category' => 'komputer',
                'image' => asset('images/image_beranda/komputer.jpg'),
            ],
            [
                'name' => 'Printer',
                'category' => 'printer',
                'image' => asset('images/image_beranda/printer.jpeg'),
            ],
            [
                'name' => 'Aksesoris',
                'category' => 'mouse-keyboard',
                'image' => asset('images/image_beranda/aksesoris.png'),
            ],
            [
                'name' => 'Gaming',
                'category' => 'gamepad',
                'image' => asset('images/image_beranda/gaming.webp'),
            ],
            [
                'name' => 'CCTV',
                'category' => 'cctv',
                'image' => asset('images/image_beranda/cctv.png'),
            ],
            [
                'name' => 'Jaringan',
                'category' => 'router',
                'image' => asset('images/image_beranda/jaringan.jpeg'),
            ],
            [
                'name' => 'Software',
                'category' => 'software',
                'image' => asset('images/image_beranda/software.png'),
            ],
        ];

        $platforms = [
            [
                'key' => 'inaproc',
                'title' => 'INAPROC',
                'subtitle' => 'Katalog Elektronik Pemerintah',
                'url' => 'https://katalog.inaproc.id/sm-pamen-sejahtera',
                'logo' => asset('images/platforms/inaproc.png'),
            ],
            [
                'key' => 'siplah_blibli',
                'title' => 'Siplah Blibli',
                'subtitle' => 'Solusi Pengadaan Sekolah',
                'url' => 'https://siplah.blibli.com/merchant-detail/SSKO-0102?itemPerPage=40&page=0&merchantId=SSKO-0102',
                'logo' => asset('images/platforms/siplah_blibli.svg'),
            ],
            [
                'key' => 'siplah_tokoladang',
                'title' => 'Siplah Toko Ladang',
                'subtitle' => 'Platform Pengadaan Sekolah',
                'url' => 'https://siplah.tokoladang.co.id/m/soulmate-komputer.19533?page=1&etalase=-1',
                'logo' => asset('images/platforms/siplah_tokoladang.svg'),
            ],
            [
                'key' => 'siplah_telkom',
                'title' => 'SIPLah Telkom',
                'subtitle' => 'Solusi Digital Pendidikan',
                'url' => 'https://siplahtelkom.com/store/211481-soulmate-komputer',
                'logo' => asset('images/platforms/siplah_telkom.svg'),
            ],
            [
                'key' => 'shopee',
                'title' => 'Shopee',
                'subtitle' => 'Belanja Online Terpercaya',
                'url' => 'https://shopee.co.id/enoharyono24?categoryId=100635&entryPoint=ShopByPDP&itemId=52054567975',
                'logo' => asset('images/platforms/shopee.svg'),
            ],
            [
                'key' => 'ayoomall',
                'title' => 'Ayoomall',
                'subtitle' => 'Marketplace Pengadaan',
                'url' => 'https://ayoomall.com/store/soulmate-komputer',
                'logo' => asset('images/platforms/ayoomall.png'),
            ],
        ];

        return view('livewire.web.home', [
            'categories' => $categories,
            'platforms' => $platforms,
        ]);
    }
}
