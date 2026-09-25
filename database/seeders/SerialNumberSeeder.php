<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Customer;
use App\Models\SerialNumber;
use App\Models\User;
use Illuminate\Database\Seeder;

class SerialNumberSeeder extends Seeder
{
    public function run(): void
    {
        $cabang = Cabang::where('status', 'aktif')->first() ?? Cabang::first();
        $user = User::where('email', 'admin.pkp@soulmate.test')->first() ?? User::first();
        $customers = Customer::all();

        $brandsData = [
            [
                'merk' => 'Asus',
                'models' => [
                    ['tipe' => 'ROG Strix G15 G513RM (Ryzen 7 6800H / RTX 3060 / 16GB / 512GB)', 'sn' => 'SN-ASUS-ROG513-8821', 'distri' => 'PT Asus Service Indonesia'],
                    ['tipe' => 'TUF Gaming A15 FA506 (Ryzen 5 4600H / GTX 1650 / 8GB / 512GB)', 'sn' => 'SN-ASUS-TUF15-9932', 'distri' => 'Synnex Metrodata Indonesia'],
                    ['tipe' => 'Vivobook 14 A1404 (Core i3-1215U / 8GB / 512GB SSD)', 'sn' => 'SN-ASUS-VIVO14-1044', 'distri' => 'PT Datascrip'],
                    ['tipe' => 'Zenbook 14 OLED UX3402 (Core i7-1360P / 16GB / 1TB SSD)', 'sn' => 'SN-ASUS-ZEN14-7731', 'distri' => 'PT Asus Service Indonesia'],
                ],
            ],
            [
                'merk' => 'Lenovo',
                'models' => [
                    ['tipe' => 'ThinkPad X1 Carbon Gen 10 (Core i7-1260P / 16GB / 1TB SSD)', 'sn' => 'SN-LNV-TPX1-4412', 'distri' => 'Lenovo Indonesia Official'],
                    ['tipe' => 'Legion 5 Pro 16ACH6H (Ryzen 7 5800H / RTX 3070 / 16GB / 1TB)', 'sn' => 'SN-LNV-LEG5P-5523', 'distri' => 'PT Synnex Metrodata'],
                    ['tipe' => 'IdeaPad Slim 3 14IAU7 (Core i3-1215U / 8GB / 512GB SSD)', 'sn' => 'SN-LNV-IPS3-6634', 'distri' => 'PT Metrodata Electronics'],
                    ['tipe' => 'Yoga 7i 14IRL8 (Core i7-1360P / 16GB / 1TB OLED 2-in-1)', 'sn' => 'SN-LNV-YOG7-7745', 'distri' => 'Lenovo Indonesia Official'],
                ],
            ],
            [
                'merk' => 'Acer',
                'models' => [
                    ['tipe' => 'Predator Helios 300 PH315 (Core i7-11800H / RTX 3060 / 16GB / 512GB)', 'sn' => 'SN-ACR-PH300-3319', 'distri' => 'PT Acer Indonesia'],
                    ['tipe' => 'Nitro 5 AN515-58 (Core i5-12500H / RTX 3050 / 8GB / 512GB)', 'sn' => 'SN-ACR-NIT5-4428', 'distri' => 'PT Dragon Computer'],
                    ['tipe' => 'Swift 3 Infinity 4 (Core i5-1135G7 / 16GB / 512GB SSD)', 'sn' => 'SN-ACR-SWF3-5537', 'distri' => 'PT Acer Indonesia'],
                    ['tipe' => 'Aspire 5 A514-55 (Core i3-1215U / 8GB / 512GB SSD)', 'sn' => 'SN-ACR-ASP5-6646', 'distri' => 'PT Datascrip'],
                ],
            ],
            [
                'merk' => 'HP',
                'models' => [
                    ['tipe' => 'Victus 15-fb0009AX (Ryzen 5 5600H / RX 6500M / 8GB / 512GB)', 'sn' => 'SN-HP-VIC15-1192', 'distri' => 'HP Indonesia Authorized'],
                    ['tipe' => 'Pavilion 14-dv2004TX (Core i7-1255U / MX550 / 16GB / 512GB)', 'sn' => 'SN-HP-PAV14-2283', 'distri' => 'PT Synnex Metrodata'],
                    ['tipe' => 'Omen 16-wf0004TX (Core i7-13700HX / RTX 4060 / 16GB / 1TB)', 'sn' => 'SN-HP-OMEN16-3374', 'distri' => 'HP Indonesia Authorized'],
                    ['tipe' => 'HP 14s-dq2614TU (Core i3-1115G4 / 8GB / 512GB SSD)', 'sn' => 'SN-HP-14S-4465', 'distri' => 'PT Tera Data Indonusa'],
                ],
            ],
            [
                'merk' => 'MSI',
                'models' => [
                    ['tipe' => 'Katana 15 B13VFK (Core i7-13620H / RTX 4060 / 16GB / 1TB)', 'sn' => 'SN-MSI-KAT15-9981', 'distri' => 'MSI Mega Perkasa'],
                    ['tipe' => 'Thin GF63 12VE (Core i5-12450H / RTX 4050 / 8GB / 512GB)', 'sn' => 'SN-MSI-GF63-8872', 'distri' => 'PT Nusantara Jaya Teknologi'],
                    ['tipe' => 'Modern 14 C12M (Core i3-1215U / 8GB / 512GB SSD)', 'sn' => 'SN-MSI-MOD14-7763', 'distri' => 'PT Alfa Artha Andhaya'],
                ],
            ],
            [
                'merk' => 'Advan',
                'models' => [
                    ['tipe' => 'Workplus (Ryzen 5 6600H / 16GB LPDDR5 / 512GB SSD / 14 Inch FHD)', 'sn' => 'SN-ADV-WRKPL-0012', 'distri' => 'PT Bangga Teknologi Indonesia'],
                    ['tipe' => 'Soulmate 14 (Celeron N4020 / 4GB / 128GB eMMC + 256GB SSD)', 'sn' => 'SN-ADV-SLM14-0023', 'distri' => 'PT Bangga Teknologi Indonesia'],
                    ['tipe' => 'Workpro (Core i5-1035G7 / 8GB / 512GB SSD Metal Body)', 'sn' => 'SN-ADV-WRKPR-0034', 'distri' => 'PT Bangga Teknologi Indonesia'],
                ],
            ],
            [
                'merk' => 'Axioo',
                'models' => [
                    ['tipe' => 'Hype 5 AMD (Ryzen 5 5500U / 8GB / 512GB SSD / Backlit Keyboard)', 'sn' => 'SN-AXI-HYP5-5501', 'distri' => 'PT Tera Data Indonusa'],
                    ['tipe' => 'Pongo 725 (Core i7-12650H / RTX 2050 / 16GB / 512GB Gaming)', 'sn' => 'SN-AXI-PNG725-5502', 'distri' => 'PT Tera Data Indonusa'],
                    ['tipe' => 'MyBook Hype 3 (Core i3-1005G1 / 8GB / 256GB SSD)', 'sn' => 'SN-AXI-MBH3-5503', 'distri' => 'PT Tera Data Indonusa'],
                ],
            ],
            [
                'merk' => 'Apple',
                'models' => [
                    ['tipe' => 'MacBook Air M2 13.6 Inch (8-Core CPU / 8-Core GPU / 8GB / 256GB Midnight)', 'sn' => 'SN-APL-MBAM2-9901', 'distri' => 'PT Erajaya Swasembada (iBox)'],
                    ['tipe' => 'MacBook Pro M3 14 Inch (8-Core CPU / 10-Core GPU / 8GB / 512GB Space Gray)', 'sn' => 'SN-APL-MBPM3-9902', 'distri' => 'PT Mitra Adiperkasa (Digimap)'],
                    ['tipe' => 'MacBook Air M1 13.3 Inch (8-Core CPU / 7-Core GPU / 8GB / 256GB Space Gray)', 'sn' => 'SN-APL-MBAM1-9903', 'distri' => 'PT Erajaya Swasembada (iBox)'],
                ],
            ],
        ];

        $custIndex = 0;
        $totalCustomers = $customers->count();

        foreach ($brandsData as $brandGroup) {
            $merk = $brandGroup['merk'];

            foreach ($brandGroup['models'] as $m) {
                $customer = $totalCustomers > 0 ? $customers[$custIndex % $totalCustomers] : null;
                $custIndex++;

                SerialNumber::updateOrCreate(
                    ['sn' => $m['sn']],
                    [
                        'cabang_id' => $cabang?->id,
                        'user_id' => $user?->id,
                        'customer_id' => $customer?->id,
                        'merk' => $merk,
                        'tipe_spek' => $m['tipe'],
                        'nama' => $merk . ' ' . $m['tipe'],
                        'merk_tipe_spek' => $merk . ' ' . $m['tipe'],
                        'distributor' => $m['distri'],
                    ]
                );
            }
        }
    }
}
