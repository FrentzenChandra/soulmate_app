<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\MasterServis;

class MasterServisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // JASA SERVIS (Laptop, Komputer, Printer, Umum)
            [
                'kode_item' => 'JS-001',
                'nama_item' => 'Install Ulang Windows + Office + Standar Apps',
                'jenis' => 'jasa',
                'kategori_perangkat' => 'semua',
                'harga_modal' => 0,
                'harga_jual' => 75000,
                'garansi_default_hari' => 14,
                'keterangan' => 'Instalasi sistem operasi Windows 10/11 & paket software standar',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'JS-002',
                'nama_item' => 'Cleaning Unit + Ganti Thermal Paste',
                'jenis' => 'jasa',
                'kategori_perangkat' => 'laptop',
                'harga_modal' => 15000,
                'harga_jual' => 85000,
                'garansi_default_hari' => 30,
                'keterangan' => 'Pembersihan debu kipas pendingin & ganti pasta prosesor',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'JS-003',
                'nama_item' => 'Servis Engsel Patah / Rekondisi Casing Laptop',
                'jenis' => 'jasa',
                'kategori_perangkat' => 'laptop',
                'harga_modal' => 20000,
                'harga_jual' => 150000,
                'garansi_default_hari' => 30,
                'keterangan' => 'Perbaikan struktur dudukan baut & engsel layar laptop',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'JS-004',
                'nama_item' => 'Jasa Pemasangan / Upgrade SSD + Cloning Windows',
                'jenis' => 'jasa',
                'kategori_perangkat' => 'semua',
                'harga_modal' => 0,
                'harga_jual' => 50000,
                'garansi_default_hari' => 30,
                'keterangan' => 'Pemasangan storage SSD dan migrasi sistem operasi',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'JS-005',
                'nama_item' => 'Jasa Ganti Keyboard Laptop',
                'jenis' => 'jasa',
                'kategori_perangkat' => 'laptop',
                'harga_modal' => 0,
                'harga_jual' => 50000,
                'garansi_default_hari' => 30,
                'keterangan' => 'Bongkar pasang keyboard laptop tanam / frame',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'JS-006',
                'nama_item' => 'Jasa Ganti Layar LCD Laptop',
                'jenis' => 'jasa',
                'kategori_perangkat' => 'laptop',
                'harga_modal' => 0,
                'harga_jual' => 75000,
                'garansi_default_hari' => 30,
                'keterangan' => 'Pemasangan panel display LCD / LED laptop baru',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'JS-007',
                'nama_item' => 'Servis Head Buntu / Cleaning Printer Inkjet',
                'jenis' => 'jasa',
                'kategori_perangkat' => 'printer',
                'harga_modal' => 10000,
                'harga_jual' => 65000,
                'garansi_default_hari' => 14,
                'keterangan' => 'Pembersihan nozel head & selang tinta printer buntu',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'JS-008',
                'nama_item' => 'Reset Counter Bantalan Tinta / Waste Ink Pad Printer',
                'jenis' => 'jasa',
                'kategori_perangkat' => 'printer',
                'harga_modal' => 0,
                'harga_jual' => 45000,
                'garansi_default_hari' => 30,
                'keterangan' => 'Software reset error waste ink pad counter full',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'JS-009',
                'nama_item' => 'Servis Motherboard / Power Supply Komputer',
                'jenis' => 'jasa',
                'kategori_perangkat' => 'komputer',
                'harga_modal' => 30000,
                'harga_jual' => 175000,
                'garansi_default_hari' => 30,
                'keterangan' => 'Perbaikan jalur kelistrikan & komponen IC daya mainboard',
                'is_aktif' => true,
            ],

            // SPAREPART SERVIS
            [
                'kode_item' => 'SP-001',
                'nama_item' => 'SSD NVMe PCIe 256GB Gen 3x4',
                'jenis' => 'sparepart',
                'kategori_perangkat' => 'semua',
                'harga_modal' => 260000,
                'harga_jual' => 350000,
                'garansi_default_hari' => 365,
                'keterangan' => 'Solid State Drive M.2 NVMe kecepatan tinggi',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'SP-002',
                'nama_item' => 'SSD NVMe PCIe 512GB Gen 3x4',
                'jenis' => 'sparepart',
                'kategori_perangkat' => 'semua',
                'harga_modal' => 420000,
                'harga_jual' => 550000,
                'garansi_default_hari' => 365,
                'keterangan' => 'Solid State Drive M.2 NVMe kapasitas 512GB',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'SP-003',
                'nama_item' => 'SSD SATA 2.5 Inch 256GB',
                'jenis' => 'sparepart',
                'kategori_perangkat' => 'semua',
                'harga_modal' => 230000,
                'harga_jual' => 320000,
                'garansi_default_hari' => 365,
                'keterangan' => 'SSD 2.5 inch pengganti Harddisk laptop & PC lama',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'SP-004',
                'nama_item' => 'RAM Sodimm DDR4 8GB 3200MHz',
                'jenis' => 'sparepart',
                'kategori_perangkat' => 'laptop',
                'harga_modal' => 210000,
                'harga_jual' => 295000,
                'garansi_default_hari' => 365,
                'keterangan' => 'Memori RAM Sodimm DDR4 untuk laptop',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'SP-005',
                'nama_item' => 'Baterai Laptop Universal / OEM Series',
                'jenis' => 'sparepart',
                'kategori_perangkat' => 'laptop',
                'harga_modal' => 220000,
                'harga_jual' => 350000,
                'garansi_default_hari' => 90,
                'keterangan' => 'Penggantian baterai internal laptop bergaransi',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'SP-006',
                'nama_item' => 'Charger / Adaptor Laptop Original 65W',
                'jenis' => 'sparepart',
                'kategori_perangkat' => 'laptop',
                'harga_modal' => 120000,
                'harga_jual' => 195000,
                'garansi_default_hari' => 90,
                'keterangan' => 'Adaptor power supply output 19V / 20V 65 Watt',
                'is_aktif' => true,
            ],
            [
                'kode_item' => 'SP-007',
                'nama_item' => 'Roller Penarik Kertas Printer (Pickup Roller)',
                'jenis' => 'sparepart',
                'kategori_perangkat' => 'printer',
                'harga_modal' => 25000,
                'harga_jual' => 65000,
                'garansi_default_hari' => 30,
                'keterangan' => 'Karet penarik kertas printer yang aus / paper jam',
                'is_aktif' => true,
            ],
        ];

        $cabangs = \App\Models\Cabang::all();

        foreach ($items as $item) {
            $ms = MasterServis::updateOrCreate(
                ['kode_item' => $item['kode_item']],
                $item
            );

            // Inisialisasi stok jika jenis sparepart
            if ($item['jenis'] === 'sparepart') {
                foreach ($cabangs as $c) {
                    \App\Models\MasterServisStok::updateOrCreate(
                        ['master_servis_id' => $ms->id, 'cabang_id' => $c->id],
                        [
                            'jumlah' => match ($c->kode_cabang) {
                                'CB-PKP' => 10,
                                'CB-SGL' => 5,
                                'CB-KBA' => 3,
                                default => 5,
                            },
                            'min_stok' => 3,
                            'is_aktif' => true,
                        ]
                    );
                }
            }
        }
    }
}
