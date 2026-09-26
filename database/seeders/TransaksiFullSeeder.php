<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Customer;
use App\Models\KasKeluar;
use App\Models\KategoriKasKeluar;
use App\Models\MasterPpn;
use App\Models\MetodePembayaran;
use App\Models\Produk;
use App\Models\SumberDana;
use App\Models\TransaksiFull;
use App\Models\TransaksiFullDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransaksiFullSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pkp = Cabang::where('kode_cabang', 'CB-PKP')->first() ?? Cabang::first();
        $sgl = Cabang::where('kode_cabang', 'CB-SGL')->first();
        $kba = Cabang::where('kode_cabang', 'CB-KBA')->first();

        $userPkp = User::where('cabang_id', $pkp?->id)->first() ?? User::first();
        $teknisiPkp = User::where('username', 'teknisi_pkp')->first() ?? $userPkp;
        $adminPkp = User::where('username', 'admin_pkp')->first() ?? $userPkp;

        $customers = Customer::all();
        $custHendra = $customers->where('nama', 'Hendra Setiawan')->first();
        $custMega = $customers->where('nama', 'Mega Lestari')->first();
        $custFajar = $customers->where('nama', 'PT Bangka Media Utama (Bpk. Fajar)')->first();
        $custRizky = $customers->where('nama', 'Rizky Pratama')->first();

        $ppnAktif = MasterPpn::getAktifCached();
        $ppnPersen = $ppnAktif ? (float) $ppnAktif->persen : 0;
        $ppnId = $ppnAktif?->id;

        $metodeCash = MetodePembayaran::where('nama', 'Cash')->first() ?? MetodePembayaran::first();
        $metodeTransfer = MetodePembayaran::where('nama', 'Transfer BCA')->first() ?? $metodeCash;
        $metodeQris = MetodePembayaran::where('nama', 'QRIS')->first() ?? $metodeCash;

        $allProduk = Produk::all();
        $ssd = $allProduk->where('barcode', '8991234560011')->first() ?? $allProduk->first();
        $ram = $allProduk->where('barcode', '8991234560028')->first() ?? $allProduk->skip(1)->first();
        $mouse = $allProduk->where('barcode', '8991234560080')->first() ?? $allProduk->skip(2)->first();
        $pasta = $allProduk->where('barcode', '8991234560059')->first() ?? $allProduk->skip(3)->first();

        $now = Carbon::now();
        $today = $now->format('ymd');

        // Pastikan Kategori Kas Keluar dan Sumber Dana ada untuk seeder Kas Keluar
        $kategoriKas = KategoriKasKeluar::firstOrCreate(
            ['nama' => 'Operasional & ATK'],
            ['is_aktif' => true]
        );
        $kategoriKonsumsi = KategoriKasKeluar::firstOrCreate(
            ['nama' => 'Konsumsi Staf'],
            ['is_aktif' => true]
        );

        $sumberKas = SumberDana::firstOrCreate(
            ['nama' => 'Kas Laci Kasir'],
            ['is_aktif' => true]
        );

        // =========================================================================
        // SEED KAS KELUAR CONTOH
        // =========================================================================
        if ($pkp && $userPkp) {
            KasKeluar::create([
                'cabang_id' => $pkp->id,
                'user_id' => $userPkp->id,
                'sumber_dana_id' => $sumberKas->id,
                'kategori_kas_keluar_id' => $kategoriKas->id,
                'tanggal' => $now->copy()->setTime(10, 30),
                'keterangan' => 'Beli Kertas Nota Kasir 5 Roll & Lakban Bening',
                'nominal' => 75000,
            ]);

            KasKeluar::create([
                'cabang_id' => $pkp->id,
                'user_id' => $userPkp->id,
                'sumber_dana_id' => $sumberKas->id,
                'kategori_kas_keluar_id' => $kategoriKonsumsi->id,
                'tanggal' => $now->copy()->setTime(12, 15),
                'keterangan' => 'Makan Siang & Kopi Teknisi Servis',
                'nominal' => 45000,
            ]);
        }

        // =========================================================================
        // DAFTAR TRANSAKSI CONTOH CABANG PKP (HARI INI)
        // =========================================================================
        $sampleTrx = [
            // 1. Penjualan Lunas - Upgrade SSD & RAM
            [
                'cabang_id' => $pkp->id,
                'user_id' => $adminPkp->id,
                'customer_id' => $custHendra?->id,
                'nama_pelanggan' => $custHendra?->nama ?? 'Hendra Setiawan',
                'no_hp' => $custHendra?->no_hp ?? '081234567890',
                'no_transaksi' => 'INV-PJ-CBPKP-' . $today . '-00001',
                'tipe_transaksi' => 'penjualan',
                'kategori_perangkat' => null,
                'tanggal' => $now->copy()->setTime(9, 15),
                'metode_pembayaran_id' => $metodeCash?->id,
                'metode_pembayaran' => $metodeCash?->nama ?? 'Cash',
                'status' => 'Lunas',
                'catatan' => 'Upgrade laptop Lenovo Thinkpad X280 milik pribadi',
                'items' => [
                    [
                        'produk_id' => $ssd?->id,
                        'nama_item' => $ssd?->nama_produk ?? 'SSD Kingston NV2 500GB PCIe 4.0 NVMe M.2',
                        'qty' => 1,
                        'harga_modal' => 380000,
                        'harga_jual' => 475000,
                    ],
                    [
                        'produk_id' => $ram?->id,
                        'nama_item' => $ram?->nama_produk ?? 'RAM SODIMM Corsair DDR4 8GB 3200MHz',
                        'qty' => 1,
                        'harga_modal' => 240000,
                        'harga_jual' => 320000,
                    ],
                ],
            ],

            // 2. Servis Lunas - Servis Laptop Overheat & Ganti Pasta
            [
                'cabang_id' => $pkp->id,
                'user_id' => $teknisiPkp->id,
                'customer_id' => $custMega?->id,
                'nama_pelanggan' => $custMega?->nama ?? 'Mega Lestari',
                'no_hp' => $custMega?->no_hp ?? '082198765432',
                'no_transaksi' => 'INV-SV-CBPKP-' . $today . '-00002',
                'tipe_transaksi' => 'servis',
                'kategori_perangkat' => 'laptop',
                'tanggal' => $now->copy()->setTime(10, 45),
                'metode_pembayaran_id' => $metodeQris?->id,
                'metode_pembayaran' => $metodeQris?->nama ?? 'QRIS',
                'status' => 'Lunas',
                'catatan' => 'Laptop Asus Vivobook sering mati mendadak saat render video. Fan dibersihkan & repaste.',
                'items' => [
                    [
                        'produk_id' => null,
                        'nama_item' => 'Deep Cleaning Fan, Heatsink & Maintenance Jalur Udara',
                        'qty' => 1,
                        'harga_modal' => 20000,
                        'harga_jual' => 100000,
                    ],
                    [
                        'produk_id' => $pasta?->id,
                        'nama_item' => 'Thermal Paste Arctic MX-4 High Performance (Repasting CPU & GPU)',
                        'qty' => 1,
                        'harga_modal' => 35000,
                        'harga_jual' => 85000,
                    ],
                ],
            ],

            // 3. Penjualan BON - Pengadaan Kantor PT Bangka Media Utama
            [
                'cabang_id' => $pkp->id,
                'user_id' => $adminPkp->id,
                'customer_id' => $custFajar?->id,
                'nama_pelanggan' => $custFajar?->nama ?? 'PT Bangka Media Utama (Bpk. Fajar)',
                'no_hp' => $custFajar?->no_hp ?? '081377889900',
                'no_transaksi' => 'INV-PJ-CBPKP-' . $today . '-00003',
                'tipe_transaksi' => 'penjualan',
                'kategori_perangkat' => null,
                'tanggal' => $now->copy()->setTime(11, 30),
                'metode_pembayaran_id' => $metodeTransfer?->id,
                'metode_pembayaran' => $metodeTransfer?->nama ?? 'Transfer BCA',
                'status' => 'BON',
                'catatan' => 'Pengadaan 3 unit mouse wireless Logitech & 2 unit flashdisk untuk tim liputan. PO No: BMU-09/26.',
                'items' => [
                    [
                        'produk_id' => $mouse?->id,
                        'nama_item' => 'Mouse Wireless Logitech M185 Silent Grey',
                        'qty' => 3,
                        'harga_modal' => 110000,
                        'harga_jual' => 155000,
                    ],
                    [
                        'produk_id' => null,
                        'nama_item' => 'Flashdisk SanDisk Ultra Dual Drive Type-C 64GB',
                        'qty' => 2,
                        'harga_modal' => 95000,
                        'harga_jual' => 145000,
                    ],
                ],
            ],

            // 4. Servis BON - Servis Motherboard Komputer (Pelanggan Rizky)
            [
                'cabang_id' => $pkp->id,
                'user_id' => $teknisiPkp->id,
                'customer_id' => $custRizky?->id,
                'nama_pelanggan' => $custRizky?->nama ?? 'Rizky Pratama',
                'no_hp' => $custRizky?->no_hp ?? '085266778899',
                'no_transaksi' => 'INV-SV-CBPKP-' . $today . '-00004',
                'tipe_transaksi' => 'servis',
                'kategori_perangkat' => 'komputer',
                'tanggal' => $now->copy()->setTime(13, 00),
                'metode_pembayaran_id' => $metodeCash?->id,
                'metode_pembayaran' => $metodeCash?->nama ?? 'Cash',
                'status' => 'BON',
                'catatan' => 'Servis motherboard PC short jalur 12V Vcore CPU + reballing chipset.',
                'items' => [
                    [
                        'produk_id' => null,
                        'nama_item' => 'Jasa Perbaikan Motherboard Short Jalur 12V VCore & Ganti Mosfet',
                        'qty' => 1,
                        'harga_modal' => 75000,
                        'harga_jual' => 350000,
                    ],
                ],
            ],

            // 5. Penjualan Lunas - Pelanggan Umum Walk-In
            [
                'cabang_id' => $pkp->id,
                'user_id' => $adminPkp->id,
                'customer_id' => null,
                'nama_pelanggan' => 'Pelanggan Umum (Bpk. Andre)',
                'no_hp' => '087811223344',
                'no_transaksi' => 'INV-PJ-CBPKP-' . $today . '-00005',
                'tipe_transaksi' => 'penjualan',
                'kategori_perangkat' => null,
                'tanggal' => $now->copy()->setTime(14, 20),
                'metode_pembayaran_id' => $metodeCash?->id,
                'metode_pembayaran' => $metodeCash?->nama ?? 'Cash',
                'status' => 'Lunas',
                'catatan' => 'Beli mousepad gaming & kabel HDMI gold plated',
                'items' => [
                    [
                        'produk_id' => null,
                        'nama_item' => 'Mousepad Gaming Speed Edition 80x30cm',
                        'qty' => 1,
                        'harga_modal' => 35000,
                        'harga_jual' => 65000,
                    ],
                    [
                        'produk_id' => null,
                        'nama_item' => 'Kabel HDMI 2.0 Ultra HD 4K 1.5 Meter',
                        'qty' => 1,
                        'harga_modal' => 25000,
                        'harga_jual' => 45000,
                    ],
                ],
            ],

            // 6. Servis Lunas - Servis Printer Head Clean & Rol Paper
            [
                'cabang_id' => $pkp->id,
                'user_id' => $teknisiPkp->id,
                'customer_id' => null,
                'nama_pelanggan' => 'Ibu Siti Khadijah',
                'no_hp' => '081955667788',
                'no_transaksi' => 'INV-SV-CBPKP-' . $today . '-00006',
                'tipe_transaksi' => 'servis',
                'kategori_perangkat' => 'printer',
                'tanggal' => $now->copy()->setTime(15, 10),
                'metode_pembayaran_id' => $metodeCash?->id,
                'metode_pembayaran' => $metodeCash?->nama ?? 'Cash',
                'status' => 'Lunas',
                'catatan' => 'Servis Printer Epson L3210 hasil cetak bergaris & roller aus',
                'items' => [
                    [
                        'produk_id' => null,
                        'nama_item' => 'Jasa Deep Head Cleaning & Pergantian Roller Penarik Kertas Epson',
                        'qty' => 1,
                        'harga_modal' => 30000,
                        'harga_jual' => 125000,
                    ],
                ],
            ],

            // 7. Penjualan BON Masa Lalu yang DILUNASI CASH HARI INI
            [
                'cabang_id' => $pkp->id,
                'user_id' => $adminPkp->id,
                'customer_id' => $custHendra?->id,
                'nama_pelanggan' => $custHendra?->nama ?? 'Hendra Setiawan',
                'no_hp' => $custHendra?->no_hp ?? '081234567890',
                'no_transaksi' => 'INV-PJ-CBPKP-' . $now->copy()->subDays(10)->format('ymd') . '-00007',
                'tipe_transaksi' => 'penjualan',
                'kategori_perangkat' => null,
                'tanggal' => $now->copy()->subDays(10)->setTime(11, 0),
                'tanggal_lunas' => $now->copy()->setTime(11, 15),
                'metode_pembayaran_id' => $metodeCash?->id,
                'metode_pembayaran' => $metodeCash?->nama ?? 'Cash',
                'status' => 'Lunas',
                'catatan' => 'Nota BON tanggal ' . $now->copy()->subDays(10)->format('d/m/Y') . ' (Beli Keyboard Logitech Wireless), baru dilunasi Tunai Cash hari ini.',
                'items' => [
                    [
                        'produk_id' => null,
                        'nama_item' => 'Keyboard Wireless Logitech K380 Multi-Device',
                        'qty' => 1,
                        'harga_modal' => 280000,
                        'harga_jual' => 385000,
                    ],
                ],
            ],

            // 8. Servis BON Masa Lalu
            [
                'cabang_id' => $pkp->id,
                'user_id' => $teknisiPkp->id,
                'customer_id' => $custFajar?->id,
                'nama_pelanggan' => $custFajar?->nama ?? 'PT Bangka Media Utama (Bpk. Fajar)',
                'no_hp' => $custFajar?->no_hp ?? '081377889900',
                'no_transaksi' => 'INV-SV-CBPKP-' . $now->copy()->subDays(15)->format('ymd') . '-00008',
                'tipe_transaksi' => 'servis',
                'kategori_perangkat' => 'printer',
                'tanggal' => $now->copy()->subDays(15)->setTime(16, 0),
                'tanggal_lunas' => null,
                'metode_pembayaran_id' => $metodeCash?->id,
                'metode_pembayaran' => $metodeCash?->nama ?? 'Cash',
                'status' => 'BON',
                'catatan' => 'Servis printer Epson L3110 ganti head, status masih BON dari kantor.',
                'items' => [
                    [
                        'produk_id' => null,
                        'nama_item' => 'Jasa Servis Head Clean & Pergantian Pompa Pembuangan Epson',
                        'qty' => 1,
                        'harga_modal' => 45000,
                        'harga_jual' => 150000,
                    ],
                ],
            ],
        ];

        // =========================================================================
        // TAMBAHKAN DATA REALISTIS BULANAN UNTUK TARGET BULANAN (BULAN 1 s/d BULAN INI)
        // =========================================================================
        $currentMonth = (int) $now->format('n');
        $currentYear = (int) $now->format('Y');

        for ($m = 1; $m <= $currentMonth; $m++) {
            // Setiap bulan tambahkan beberapa transaksi untuk Laptop, Printer, Komputer, dan Admin
            $categories = ['laptop', 'printer', 'komputer'];
            foreach ($categories as $cat) {
                $qtyTrx = rand(10, 22);
                for ($k = 1; $k <= $qtyTrx; $k++) {
                    $day = rand(1, 28);
                    $trxDate = Carbon::create($currentYear, $m, $day, rand(8, 17), rand(0, 59));
                    $sampleTrx[] = [
                        'cabang_id' => $pkp->id,
                        'user_id' => $teknisiPkp->id,
                        'customer_id' => $custHendra?->id,
                        'nama_pelanggan' => 'Pelanggan Servis ' . ucfirst($cat) . " M{$m}-#{$k}",
                        'no_hp' => '0812345678' . str_pad((string) $k, 2, '0', STR_PAD_LEFT),
                        'no_transaksi' => 'INV-SV-CBPKP-' . $trxDate->format('ymd') . '-' . str_pad((string) rand(100, 999), 5, '0', STR_PAD_LEFT),
                        'tipe_transaksi' => 'servis',
                        'kategori_perangkat' => $cat,
                        'tanggal' => $trxDate,
                        'metode_pembayaran_id' => $metodeCash?->id,
                        'metode_pembayaran' => 'Cash',
                        'status' => 'Lunas',
                        'catatan' => 'Pekerjaan servis ' . $cat . ' reguler bulan ' . $m,
                        'items' => [
                            [
                                'produk_id' => null,
                                'nama_item' => 'Jasa Perbaikan ' . ucfirst($cat),
                                'qty' => 1,
                                'harga_modal' => 30000,
                                'harga_jual' => 150000,
                            ],
                        ],
                    ];
                }
            }

            // Admin Sales transaksi per bulan
            $qtyAdmin = rand(18, 30);
            for ($k = 1; $k <= $qtyAdmin; $k++) {
                $day = rand(1, 28);
                $trxDate = Carbon::create($currentYear, $m, $day, rand(8, 17), rand(0, 59));
                $sampleTrx[] = [
                    'cabang_id' => $pkp->id,
                    'user_id' => $adminPkp->id,
                    'customer_id' => null,
                    'nama_pelanggan' => "Pembeli Retail M{$m}-#{$k}",
                    'no_hp' => '0819876543' . str_pad((string) $k, 2, '0', STR_PAD_LEFT),
                    'no_transaksi' => 'INV-PJ-CBPKP-' . $trxDate->format('ymd') . '-' . str_pad((string) rand(100, 999), 5, '0', STR_PAD_LEFT),
                    'tipe_transaksi' => 'penjualan',
                    'kategori_perangkat' => null,
                    'tanggal' => $trxDate,
                    'metode_pembayaran_id' => $metodeCash?->id,
                    'metode_pembayaran' => 'Cash',
                    'status' => 'Lunas',
                    'catatan' => 'Penjualan produk aksesoris bulan ' . $m,
                    'items' => [
                        [
                            'produk_id' => $mouse?->id,
                            'nama_item' => 'Mouse Logitech Wireless Retail',
                            'qty' => 1,
                            'harga_modal' => 100000,
                            'harga_jual' => 150000,
                        ],
                    ],
                ];
            }
        }

        // =========================================================================
        // EKSEKUSI PEMBUATAN TRANSAKSI
        // =========================================================================
        foreach ($sampleTrx as $trxData) {
            $items = $trxData['items'];
            unset($trxData['items']);

            $totalModal = 0;
            $totalJual = 0;

            foreach ($items as $it) {
                $q = (int) $it['qty'];
                $hm = (float) $it['harga_modal'];
                $hj = (float) $it['harga_jual'];

                $totalModal += ($hm * $q);
                $totalJual += ($hj * $q);
            }

            $totalLaba = $totalJual - $totalModal;
            $ppnNominal = ($totalJual * $ppnPersen) / 100;
            $grandTotal = $totalJual + $ppnNominal;

            $trxData['ppn_id'] = $ppnId;
            $trxData['ppn_persen'] = $ppnPersen;
            $trxData['ppn_nominal'] = $ppnNominal;
            $trxData['total_modal'] = $totalModal;
            $trxData['total_jual'] = $totalJual;
            $trxData['total_laba'] = $totalLaba;
            $trxData['grand_total'] = $grandTotal;
            $trxData['tanggal_lunas'] = $trxData['status'] === 'Lunas'
                ? ($trxData['tanggal_lunas'] ?? $trxData['tanggal'])
                : null;

            $created = TransaksiFull::create($trxData);

            foreach ($items as $it) {
                $q = (int) $it['qty'];
                $hm = (float) $it['harga_modal'];
                $hj = (float) $it['harga_jual'];

                TransaksiFullDetail::create([
                    'transaksi_full_id' => $created->id,
                    'produk_id' => $it['produk_id'],
                    'nama_item' => $it['nama_item'],
                    'qty' => $q,
                    'harga_modal' => $hm,
                    'harga_jual' => $hj,
                    'subtotal_modal' => $hm * $q,
                    'subtotal_jual' => $hj * $q,
                    'laba' => ($hj - $hm) * $q,
                ]);
            }
        }
    }
}
