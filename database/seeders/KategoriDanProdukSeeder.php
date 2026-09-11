<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KategoriDanProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pkp = Cabang::where('kode_cabang', 'CB-PKP')->first();
        $sgl = Cabang::where('kode_cabang', 'CB-SGL')->first();
        $kba = Cabang::where('kode_cabang', 'CB-KBA')->first();

        // 1. Kategori
        $kategoris = [
            'Sparepart Laptop',
            'Sparepart Printer',
            'Komputer & PC',
            'Aksesoris & Peripheral',
            'Penyimpanan & Memori',
            'Jasa & Layanan Servis',
        ];

        $katModels = [];
        foreach ($kategoris as $namaKat) {
            $katModels[$namaKat] = KategoriProduk::firstOrCreate(
                ['nama_kategori' => $namaKat],
                ['slug' => Str::slug($namaKat)]
            );
        }

        // 2. Daftar Produk Global
        $produkList = [
            [
                'kategori' => 'Penyimpanan & Memori',
                'barcode' => '8991234560011',
                'nama_produk' => 'SSD NVMe M.2 Kingston NV2 512GB PCIe 4.0',
                'harga_beli' => 450000,
                'harga_jual' => 600000,
                'ready_marketplace' => true,
                'deskripsi' => 'SSD NVMe M.2 kecepatan baca up to 3500MB/s, garansi resmi 3 tahun.',
                'stok_pkp' => 15,
                'stok_sgl' => 8,
                'stok_kba' => 4,
            ],
            [
                'kategori' => 'Penyimpanan & Memori',
                'barcode' => '8991234560028',
                'nama_produk' => 'RAM Sodimm DDR4 Kingston 8GB 3200MHz',
                'harga_beli' => 240000,
                'harga_jual' => 325000,
                'ready_marketplace' => true,
                'deskripsi' => 'RAM laptop Sodimm DDR4 8GB 3200MHz cocok untuk upgrade laptop Asus, Lenovo, Acer, HP.',
                'stok_pkp' => 20,
                'stok_sgl' => 10,
                'stok_kba' => 5,
            ],
            [
                'kategori' => 'Sparepart Laptop',
                'barcode' => '8991234560035',
                'nama_produk' => 'LED / LCD Laptop 14.0 Slim 30 Pin FHD IPS',
                'harga_beli' => 580000,
                'harga_jual' => 850000,
                'ready_marketplace' => false,
                'deskripsi' => 'Panel layar LCD 14 inch slim 30 pin Full HD IPS (Non-touch).',
                'stok_pkp' => 6,
                'stok_sgl' => 3,
                'stok_kba' => 1,
            ],
            [
                'kategori' => 'Sparepart Laptop',
                'barcode' => '8991234560042',
                'nama_produk' => 'Baterai Laptop Asus Vivobook A412 / A412D C21N1818',
                'harga_beli' => 280000,
                'harga_jual' => 420000,
                'ready_marketplace' => false,
                'deskripsi' => 'Baterai internal OEM Asus Vivobook original quality, garansi 3 bulan.',
                'stok_pkp' => 4,
                'stok_sgl' => 2,
                'stok_kba' => 0,
            ],
            [
                'kategori' => 'Sparepart Laptop',
                'barcode' => '8991234560059',
                'nama_produk' => 'Thermal Paste Arctic MX-4 4 Gram Original',
                'harga_beli' => 65000,
                'harga_jual' => 95000,
                'ready_marketplace' => true,
                'deskripsi' => 'Pasta pendingin prosesor & GPU laptop/PC dengan konduktivitas tinggi.',
                'stok_pkp' => 25,
                'stok_sgl' => 12,
                'stok_kba' => 6,
            ],
            [
                'kategori' => 'Sparepart Printer',
                'barcode' => '8991234560066',
                'nama_produk' => 'Printhead Epson L3110 / L1110 / L3210 Original',
                'harga_beli' => 370000,
                'harga_jual' => 520000,
                'ready_marketplace' => false,
                'deskripsi' => 'Head print original Epson seri eco-tank L-series.',
                'stok_pkp' => 5,
                'stok_sgl' => 2,
                'stok_kba' => 1,
            ],
            [
                'kategori' => 'Sparepart Printer',
                'barcode' => '8991234560073',
                'nama_produk' => 'Tinta Epson 003 Original 4 Warna (CMYK)',
                'harga_beli' => 75000,
                'harga_jual' => 95000,
                'ready_marketplace' => true,
                'deskripsi' => 'Tinta botol original Epson 003 untuk L3110, L3150, L3210, L5190 dll.',
                'stok_pkp' => 40,
                'stok_sgl' => 25,
                'stok_kba' => 15,
            ],
            [
                'kategori' => 'Aksesoris & Peripheral',
                'barcode' => '8991234560080',
                'nama_produk' => 'Mouse Wireless Silent Click Logitech M220',
                'harga_beli' => 145000,
                'harga_jual' => 195000,
                'ready_marketplace' => true,
                'deskripsi' => 'Mouse tanpa kabel suara hening dengan ketahanan baterai hingga 18 bulan.',
                'stok_pkp' => 18,
                'stok_sgl' => 10,
                'stok_kba' => 5,
            ],
            [
                'kategori' => 'Aksesoris & Peripheral',
                'barcode' => '8991234560097',
                'nama_produk' => 'Charger Laptop Universal Type-C 65W GaN Fast Charging',
                'harga_beli' => 160000,
                'harga_jual' => 250000,
                'ready_marketplace' => true,
                'deskripsi' => 'Adaptor charger type-c compact GaN 65W untuk laptop kekinian & smartphone.',
                'stok_pkp' => 10,
                'stok_sgl' => 5,
                'stok_kba' => 3,
            ],
        ];

        foreach ($produkList as $item) {
            $katId = $katModels[$item['kategori']]->id;

            $produk = Produk::updateOrCreate(
                ['barcode' => $item['barcode']],
                [
                    'kategori_id' => $katId,
                    'nama_produk' => $item['nama_produk'],
                    'harga_beli' => $item['harga_beli'],
                    'harga_jual' => $item['harga_jual'],
                    'ready_marketplace' => $item['ready_marketplace'],
                    'deskripsi' => $item['deskripsi'],
                ]
            );

            // Inisialisasi Stok tiap cabang
            if ($pkp) {
                Stok::updateOrCreate(
                    ['produk_id' => $produk->id, 'cabang_id' => $pkp->id],
                    ['jumlah' => $item['stok_pkp']]
                );
            }
            if ($sgl) {
                Stok::updateOrCreate(
                    ['produk_id' => $produk->id, 'cabang_id' => $sgl->id],
                    ['jumlah' => $item['stok_sgl']]
                );
            }
            if ($kba) {
                Stok::updateOrCreate(
                    ['produk_id' => $produk->id, 'cabang_id' => $kba->id],
                    ['jumlah' => $item['stok_kba']]
                );
            }
        }
    }
}
