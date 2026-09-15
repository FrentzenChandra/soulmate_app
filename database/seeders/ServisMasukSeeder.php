<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Customer;
use App\Models\Produk;
use App\Models\ServisDetail;
use App\Models\ServisMasuk;
use App\Models\ServisStatusLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ServisMasukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pkp = Cabang::where('kode_cabang', 'CB-PKP')->first();
        $teknisiPkp = User::where('username', 'teknisi_pkp')->first();
        $admin = User::where('username', 'superadmin')->first();
        $customer1 = Customer::where('nama', 'Hendra Setiawan')->first();
        $customer2 = Customer::where('nama', 'Mega Lestari')->first();
        $customer3 = Customer::where('nama', 'PT Bangka Media Utama (Bpk. Fajar)')->first();

        $lcd = Produk::where('barcode', '8991234560035')->first();
        $head = Produk::where('barcode', '8991234560066')->first();

        if (! $pkp || ! $customer1) {
            return;
        }

        $now = Carbon::now();
        $dateCode = $now->format('ymd');
        $kodeCabangClean = str_replace('-', '', $pkp->kode_cabang);
        $prefix = "INV-SV-{$kodeCabangClean}-{$dateCode}-";

        // 1. Laptop - Status Pengecekan
        $sv1 = ServisMasuk::updateOrCreate(
            ['no_servis' => "{$prefix}00001"],
            [
                'cabang_id' => $pkp->id,
                'customer_id' => $customer1->id,
                'teknisi_id' => $teknisiPkp?->id,
                'created_by' => $admin?->id ?? $teknisiPkp?->id,
                'kategori_perangkat' => 'laptop',
                'tipe_unit' => 'Asus TUF Gaming FX505',
                'serial_number' => 'ASN12389088',
                'kelengkapan' => 'Unit, Adaptor / Charger',
                'keluhan' => 'Laptop cepat panas lalu mati sendiri saat main game',
                'kondisi_barang' => 'Fisik mulus, baut bawah lengkap',
                'status' => 'pengecekan',
                'tanggal_masuk' => $now->copy()->subDays(2),
                'estimasi_biaya' => 0,
                'total_biaya' => 0,
                'catatan' => 'Diagnosa awal kemungkinan pasta kering dan kipas kotor',
            ]
        );
        ServisStatusLog::firstOrCreate([
            'servis_id' => $sv1->id,
            'user_id' => $admin?->id ?? $teknisiPkp?->id,
            'status' => 'pengecekan',
            'tanggal' => $now->copy()->subDays(2),
            'catatan' => 'Unit diterima dan masuk tahap pengecekan diagnosa teknisi',
        ]);

        // 2. Laptop - Status Pengerjaan (Ganti LCD)
        $sv2 = ServisMasuk::updateOrCreate(
            ['no_servis' => "{$prefix}00002"],
            [
                'cabang_id' => $pkp->id,
                'customer_id' => $customer2?->id ?? $customer1->id,
                'teknisi_id' => $teknisiPkp?->id,
                'created_by' => $admin?->id ?? $teknisiPkp?->id,
                'kategori_perangkat' => 'laptop',
                'tipe_unit' => 'Lenovo Ideapad Slim 3 14ITL',
                'serial_number' => 'PF288910',
                'kelengkapan' => 'Unit',
                'keluhan' => 'Layar bergaris warna-warni setelah terbentur',
                'kondisi_barang' => 'Body ada lecet sudut kanan atas',
                'status' => 'pengerjaan',
                'tanggal_masuk' => $now->copy()->subDays(3),
                'estimasi_biaya' => 0,
                'total_biaya' => 950000,
                'catatan' => 'Sudah konfirmasi customer untuk ganti LCD FHD IPS',
            ]
        );
        ServisDetail::firstOrCreate([
            'servis_id' => $sv2->id,
            'nama_item' => 'LED / LCD Laptop 14.0 Slim 30 Pin FHD IPS',
            'produk_id' => $lcd?->id,
            'qty' => 1,
            'harga' => 850000,
            'subtotal' => 850000,
        ]);
        ServisDetail::firstOrCreate([
            'servis_id' => $sv2->id,
            'nama_item' => 'Jasa Pasang & Kalibrasi Layar LCD',
            'produk_id' => null,
            'qty' => 1,
            'harga' => 100000,
            'subtotal' => 100000,
        ]);
        ServisStatusLog::firstOrCreate([
            'servis_id' => $sv2->id,
            'user_id' => $teknisiPkp?->id ?? $admin?->id,
            'status' => 'pengerjaan',
            'tanggal' => $now->copy()->subDays(1),
            'catatan' => 'Proses pembongkaran dan penggantian part panel layar baru',
        ]);

        // 3. Printer - Status Selesai (Siap Diambil / Ditagih)
        $sv3 = ServisMasuk::updateOrCreate(
            ['no_servis' => "{$prefix}00003"],
            [
                'cabang_id' => $pkp->id,
                'customer_id' => $customer3?->id ?? $customer1->id,
                'teknisi_id' => $teknisiPkp?->id,
                'created_by' => $admin?->id ?? $teknisiPkp?->id,
                'kategori_perangkat' => 'printer',
                'tipe_unit' => 'Epson EcoTank L3110',
                'serial_number' => 'EPS992100',
                'kelengkapan' => 'Unit, Kabel Power',
                'keluhan' => 'Hasil cetak teks putus-putus dan warna hitam tidak keluar sama sekali',
                'kondisi_barang' => 'Tabung tinta terisi penuh',
                'status' => 'selesai',
                'tanggal_masuk' => $now->copy()->subDays(4),
                'tanggal_selesai' => $now->copy()->subHours(5),
                'estimasi_biaya' => 0,
                'total_biaya' => 620000,
                'catatan' => 'Ganti printhead baru & kuras saluran infus selang tinta',
            ]
        );
        ServisDetail::firstOrCreate([
            'servis_id' => $sv3->id,
            'nama_item' => 'Printhead Epson L3110 / L1110 / L3210 Original',
            'produk_id' => $head?->id,
            'qty' => 1,
            'harga' => 520000,
            'subtotal' => 520000,
        ]);
        ServisDetail::firstOrCreate([
            'servis_id' => $sv3->id,
            'nama_item' => 'Jasa Cleaning Head & Service Mekanik Printer',
            'produk_id' => null,
            'qty' => 1,
            'harga' => 100000,
            'subtotal' => 100000,
        ]);
        ServisStatusLog::firstOrCreate([
            'servis_id' => $sv3->id,
            'user_id' => $teknisiPkp?->id ?? $admin?->id,
            'status' => 'selesai',
            'tanggal' => $now->copy()->subHours(5),
            'catatan' => 'Test print 10 lembar dokumen warna hasil 100% jernih, unit siap diambil',
        ]);

        // 4. Komputer PC - Status Pengecekan
        $sv4 = ServisMasuk::updateOrCreate(
            ['no_servis' => "{$prefix}00004"],
            [
                'cabang_id' => $pkp->id,
                'customer_id' => $customer1->id,
                'teknisi_id' => $teknisiPkp?->id,
                'created_by' => $admin?->id ?? $teknisiPkp?->id,
                'kategori_perangkat' => 'komputer',
                'tipe_unit' => 'PC Rakitan Core i5 Gen 10 / GTX 1650',
                'serial_number' => 'PC-CUST-001',
                'kelengkapan' => 'Unit, Kabel Power',
                'keluhan' => 'No display, kipas prosesor berputar kencang lalu restart berulang kali',
                'kondisi_barang' => 'Debu cukup tebal di heatsink',
                'status' => 'pengecekan',
                'tanggal_masuk' => $now->copy()->subDay(),
                'estimasi_biaya' => 0,
                'total_biaya' => 0,
                'catatan' => 'Testing RAM, PSU, dan motherboard',
            ]
        );
        ServisStatusLog::firstOrCreate([
            'servis_id' => $sv4->id,
            'user_id' => $admin?->id ?? $teknisiPkp?->id,
            'status' => 'pengecekan',
            'tanggal' => $now->copy()->subDay(),
            'catatan' => 'Unit masuk diagnosa hardware',
        ]);

        // 5. Laptop - Status Sudah Dibayar / Diambil (Dengan Garansi Toko 30 Hari)
        $sv5 = ServisMasuk::updateOrCreate(
            ['no_servis' => "{$prefix}00005"],
            [
                'cabang_id' => $pkp->id,
                'customer_id' => $customer2?->id ?? $customer1->id,
                'teknisi_id' => $teknisiPkp?->id,
                'kasir_id' => $admin?->id,
                'created_by' => $admin?->id ?? $teknisiPkp?->id,
                'kategori_perangkat' => 'laptop',
                'tipe_unit' => 'Acer Aspire 3 A314',
                'serial_number' => 'ACER-991288',
                'kelengkapan' => 'Unit, Adaptor / Charger',
                'keluhan' => 'Windows error bluescreen dan lambat',
                'kondisi_barang' => 'Mulus',
                'status' => 'sudah_dibayar',
                'tanggal_masuk' => $now->copy()->subDays(5),
                'tanggal_selesai' => $now->copy()->subDays(2),
                'tanggal_ambil' => $now->copy()->subDay(),
                'estimasi_biaya' => 0,
                'total_biaya' => 125000,
                'metode_pembayaran' => 'tunai',
                'bayar' => 150000,
                'kembalian' => 25000,
                'garansi_hari' => 30,
                'garansi_sampai' => $now->copy()->subDay()->addDays(30)->toDateString(),
                'catatan' => 'Install ulang Windows 11 + Software Standar',
            ]
        );
        ServisDetail::firstOrCreate([
            'servis_id' => $sv5->id,
            'nama_item' => 'Jasa Install Ulang Windows 11 & Optimasi Driver',
            'produk_id' => null,
            'qty' => 1,
            'harga' => 125000,
            'subtotal' => 125000,
        ]);
        ServisStatusLog::firstOrCreate([
            'servis_id' => $sv5->id,
            'user_id' => $admin?->id ?? $teknisiPkp?->id,
            'status' => 'sudah_dibayar',
            'tanggal' => $now->copy()->subDay(),
            'catatan' => 'Unit diambil pelanggan dan lunas tunai. Garansi toko 30 hari aktif.',
        ]);
    }
}
