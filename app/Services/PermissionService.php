<?php

namespace App\Services;

class PermissionService
{
    /**
     * Definisi seluruh modul aplikasi, grouping, dan daftar granular permissions.
     */
    public static function getModuleSchema(): array
    {
        return [
            'absensi_kehadiran' => [
                'group_label' => 'Presensi & Kehadiran Harian',
                'description' => 'Akses presensi harian masuk berbasis jaringan WiFi cabang',
                'modules' => [
                    'absensi_harian' => [
                        'label' => 'Absensi Harian',
                        'route' => 'absensi.index',
                        'main_permission' => 'absensi.view',
                        'description' => 'Akses tombol absensi masuk harian menggunakan jaringan WiFi toko',
                        'actions' => [
                            'absensi.submit' => 'Lakukan Absen Masuk',
                        ],
                    ],
                ],
            ],

            'kasir_penjualan' => [
                'group_label' => 'Kasir POS & Penjualan',
                'description' => 'Akses transaksi kasir produk, penjualan langsung, dan laporan penjualan',
                'modules' => [
                    'kasir_pos' => [
                        'label' => 'Kasir POS',
                        'route' => 'kasir.produk',
                        'main_permission' => 'kasir.produk.view',
                        'description' => 'Layar POS kasir penjualan produk langsung dan cetak struk',
                        'actions' => [
                            'kasir.produk.create' => 'Proses Transaksi & Pembayaran',
                            'kasir.produk.print' => 'Cetak Sales Invoice / Nota',
                            'kasir.produk.customer' => 'Registrasi Pelanggan Baru Cepat',
                        ],
                    ],
                    'laporan_penjualan' => [
                        'label' => 'Riwayat & Laporan Penjualan',
                        'route' => 'laporan.penjualan',
                        'main_permission' => 'laporan.penjualan.view',
                        'description' => 'Riwayat seluruh penjualan real dan non-real per periode',
                        'actions' => [
                            'laporan.penjualan.detail' => 'Lihat Detail Item Transaksi',
                            'laporan.penjualan.print' => 'Cetak Ulang Nota Penjualan',
                            'laporan.penjualan.retur' => 'Retur Stok Barang (Transaksi Real)',
                        ],
                    ],
                    'servis_non_real' => [
                        'label' => 'Servis Non-Real',
                        'route' => 'servis.non-real',
                        'main_permission' => 'servis.non_real.view',
                        'description' => 'Pencatatan nota servis khusus non-real',
                        'actions' => [
                            'servis.non_real.detail' => 'Detail Servis Non-Real',
                            'servis.non_real.create' => 'Tambah Servis Non-Real',
                            'servis.non_real.edit' => 'Edit Data Servis Non-Real',
                            'servis.non_real.delete' => 'Hapus Servis Non-Real',
                            'servis.non_real.print' => 'Cetak Nota Non-Real',
                        ],
                    ],
                ],
            ],

            'layanan_servis' => [
                'group_label' => 'Layanan Servis & Reparasi',
                'description' => 'Akses modul pengerjaan unit servis, penerimaan, dan status faktur',
                'modules' => [
                    'servis_masuk' => [
                        'label' => 'Servis Masuk',
                        'route' => 'servis.masuk',
                        'main_permission' => 'servis.masuk.view',
                        'description' => 'Penerimaan unit masuk, antrian servis, dan tanda terima',
                        'actions' => [
                            'servis.masuk.detail' => 'Detail Unit & Riwayat',
                            'servis.masuk.create' => 'Tambah Servis Baru (POST)',
                            'servis.masuk.edit' => 'Edit / Perbarui Data (PUT)',
                            'servis.masuk.delete' => 'Hapus Data Servis (DELETE)',
                            'servis.masuk.print' => 'Cetak Tanda Terima & Nota',
                            'servis.masuk.tindakan' => 'Tindakan Jasa & Komponen',
                            'servis.masuk.status' => 'Ubah Status Pengerjaan',
                        ],
                    ],
                    'servis_keluar' => [
                        'label' => 'Servis Selesai / Keluar',
                        'route' => 'servis.keluar',
                        'main_permission' => 'servis.keluar.view',
                        'description' => 'Pengambilan unit selesai, faktur servis, dan pelunasan',
                        'actions' => [
                            'servis.keluar.detail' => 'Detail Faktur Selesai',
                            'servis.keluar.edit_nota' => 'Edit Data / Nota Selesai',
                            'servis.keluar.ambil' => 'Proses Penyerahan & Pelunasan',
                            'servis.keluar.print' => 'Cetak Faktur / Nota Servis',
                        ],
                    ],
                    'servis_batal' => [
                        'label' => 'Servis Batal',
                        'route' => 'servis.batal',
                        'main_permission' => 'servis.batal.view',
                        'description' => 'Pengarsipan unit servis yang dibatalkan oleh pelanggan',
                        'actions' => [
                            'servis.batal.detail' => 'Detail Alasan Pembatalan',
                            'servis.batal.ubah_status' => 'Pulihkan / Ubah Status Servis',
                            'servis.batal.ambil' => 'Serah Terima Unit Batal',
                            'servis.batal.print' => 'Cetak Nota / Tanda Terima Batal',
                        ],
                    ],
                ],
            ],

            'kas_keuangan' => [
                'group_label' => 'Kas, Transaksi & Keuangan',
                'description' => 'Pencatatan mutasi kas toko, transaksi harian, saldo, dan target',
                'modules' => [
                    'transaksi_harian' => [
                        'label' => 'Transaksi Harian',
                        'route' => 'transaksi.harian',
                        'main_permission' => 'transaksi.harian.view',
                        'description' => 'Tabel operasional transaksi harian (Zero Laba view)',
                        'actions' => [
                            'transaksi.harian.create' => 'Tambah Transaksi (POST)',
                            'transaksi.harian.edit' => 'Edit Transaksi (PUT)',
                            'transaksi.harian.status' => 'Pelunasan / Ubah Status BON',
                            'transaksi.harian.delete' => 'Hapus Transaksi (DELETE)',
                        ],
                    ],
                    'transaksi_full' => [
                        'label' => 'Transaksi FULL',
                        'route' => 'transaksi.full',
                        'main_permission' => 'transaksi.full.view',
                        'description' => 'Rekapitulasi transaksi lengkap dengan kalkulasi laba margin',
                        'actions' => [
                            'transaksi.full.create' => 'Tambah Transaksi FULL',
                            'transaksi.full.edit' => 'Edit Transaksi FULL',
                            'transaksi.full.status' => 'Pelunasan / Ubah Status BON',
                            'transaksi.full.delete' => 'Hapus Transaksi FULL',
                        ],
                    ],
                    'faktur_pajak' => [
                        'label' => 'Faktur Pajak',
                        'route' => 'faktur-pajak.index',
                        'main_permission' => 'pajak.view',
                        'description' => 'Pencatatan faktur pajak, PPN 12%, PPh, dan status penyelesaian',
                        'actions' => [
                            'pajak.detail' => 'Detail & Rincian Faktur',
                            'pajak.create' => 'Tambah Faktur Baru (POST)',
                            'pajak.edit' => 'Edit / Perbarui Data (PUT)',
                            'pajak.delete' => 'Hapus Data Faktur (DELETE)',
                            'pajak.toggle_selesai' => 'Ubah Status Selesai',
                            'pajak.export_excel' => 'Export Laporan Excel (.xlsx)',
                            'pajak.export_pdf' => 'Export Dokumen PDF',
                        ],
                    ],
                    'kas_keluar' => [
                        'label' => 'Kas Keluar',
                        'route' => 'kas-keluar',
                        'main_permission' => 'kas.keluar.view',
                        'description' => 'Pencatatan pengeluaran operasional toko dan bukti kas',
                        'actions' => [
                            'kas.keluar.create' => 'Catat Kas Keluar Baru',
                            'kas.keluar.edit' => 'Edit Catatan Kas Keluar',
                            'kas.keluar.delete' => 'Hapus Catatan Kas Keluar',
                        ],
                    ],
                    'saldo' => [
                        'label' => 'Saldo Toko',
                        'route' => 'saldo.index',
                        'main_permission' => 'saldo.view',
                        'description' => 'Rekap saldo modal dan ringkasan kas cabang',
                        'actions' => [
                            'saldo.create' => 'Input Saldo / Modal',
                            'saldo.edit' => 'Edit Mutasi Saldo',
                        ],
                    ],
                    'serial_number' => [
                        'label' => 'Serial Number',
                        'route' => 'serial-number',
                        'main_permission' => 'serial_number.view',
                        'description' => 'Tracking nomor seri produk, unit garansi, dan histori serial',
                        'actions' => [
                            'serial_number.create' => 'Tambah Serial Number',
                            'serial_number.edit' => 'Edit Serial Number',
                            'serial_number.delete' => 'Hapus Serial Number',
                        ],
                    ],
                    'target_bulanan' => [
                        'label' => 'Target Pegawai',
                        'route' => 'target.bulanan',
                        'main_permission' => 'target.bulanan.view',
                        'description' => 'Performance board target divisi 12 bulan dan realisasi staf',
                        'actions' => [
                            'target.bulanan.edit' => 'Atur Target Tahunan Divisi',
                        ],
                    ],
                ],
            ],

            'master_data' => [
                'group_label' => 'Master Data & Pengaturan Toko',
                'description' => 'Katalog produk, servis, data pelanggan, pegawai, dan referensi sistem',
                'modules' => [
                    'master_produk' => [
                        'label' => 'Master Produk',
                        'route' => 'master.produk',
                        'main_permission' => 'master.produk.view',
                        'description' => 'Katalog master produk, barcode, harga beli dan harga jual',
                        'actions' => [
                            'master.produk.create' => 'Tambah Produk Baru',
                            'master.produk.edit' => 'Edit Data Produk',
                            'master.produk.delete' => 'Hapus Produk',
                        ],
                    ],
                    'cabang_stok' => [
                        'label' => 'Manajemen Stok',
                        'route' => 'cabang.stok',
                        'main_permission' => 'cabang.stok.view',
                        'description' => 'Monitoring stok barang cabang dan penyesuaian kuantiti',
                        'actions' => [
                            'cabang.stok.adjust' => 'Penyesuaian / Koreksi Stok',
                        ],
                    ],
                    'klaim_garansi' => [
                        'label' => 'Klaim Garansi',
                        'route' => 'klaim-garansi.index',
                        'main_permission' => 'klaim_garansi.view',
                        'description' => 'Pencatatan dan pelacakan alur klaim garansi produk ke distributor',
                        'actions' => [
                            'klaim_garansi.detail' => 'Lihat Detail Klaim Garansi',
                            'klaim_garansi.create' => 'Input Klaim Garansi Baru (POST)',
                            'klaim_garansi.edit' => 'Edit / Perbarui Data Klaim (PUT)',
                            'klaim_garansi.delete' => 'Hapus Data Klaim Garansi (DELETE)',
                            'klaim_garansi.export_excel' => 'Export Laporan Excel (.xlsx)',
                        ],
                    ],
                    'master_orang_pajak' => [
                        'label' => 'Master WP',
                        'route' => 'master.orang-pajak',
                        'main_permission' => 'master.orang_pajak.view',
                        'description' => 'Database rekanan dan pihak wajib pajak (WP)',
                        'actions' => [
                            'master.orang_pajak.create' => 'Tambah Data WP',
                            'master.orang_pajak.edit' => 'Edit Data WP',
                            'master.orang_pajak.delete' => 'Hapus Data WP',
                        ],
                    ],
                    'master_customer' => [
                        'label' => 'Data Pelanggan',
                        'route' => 'master.customer',
                        'main_permission' => 'master.customer.view',
                        'description' => 'Database pelanggan dan riwayat nomor kontak WhatsApp',
                        'actions' => [
                            'master.customer.create' => 'Tambah Pelanggan Baru',
                            'master.customer.edit' => 'Edit Data Pelanggan',
                            'master.customer.delete' => 'Hapus Pelanggan',
                        ],
                    ],
                    'master_users' => [
                        'label' => 'Master Pengguna',
                        'route' => 'master.users',
                        'main_permission' => 'master.users.view',
                        'description' => 'Manajemen akun login, password, dan assignment cabang staf',
                        'actions' => [
                            'master.users.create' => 'Tambah Akun Login Baru',
                            'master.users.edit' => 'Edit Akun Pengguna',
                            'master.users.delete' => 'Hapus Akun Pengguna',
                        ],
                    ],
                    'master_servis' => [
                        'label' => 'Master Servis',
                        'route' => 'master.servis',
                        'main_permission' => 'master.servis.view',
                        'description' => 'Katalog tarif jasa servis, estimasi biaya, dan kategori servis',
                        'actions' => [
                            'master.servis.create' => 'Tambah Jasa Servis',
                            'master.servis.edit' => 'Edit Jasa Servis',
                            'master.servis.delete' => 'Hapus Jasa Servis',
                        ],
                    ],
                    'master_pegawai' => [
                        'label' => 'Master Pegawai',
                        'route' => 'master.pegawai',
                        'main_permission' => 'master.pegawai.view',
                        'description' => 'Daftar teknisi dan pegawai operasional toko',
                        'actions' => [
                            'master.pegawai.create' => 'Tambah Pegawai',
                            'master.pegawai.edit' => 'Edit Pegawai',
                            'master.pegawai.delete' => 'Hapus Pegawai',
                        ],
                    ],
                    'master_settings' => [
                        'label' => 'Setting (Kategori, Pembayaran & Kas)',
                        'route' => 'master.kategori',
                        'main_permission' => 'master.settings.view',
                        'description' => 'Kategori produk, metode pembayaran, sumber dana, dan kategori kas',
                        'actions' => [
                            'master.settings.create' => 'Tambah Opsi Referensi',
                            'master.settings.edit' => 'Edit Opsi Referensi',
                            'master.settings.delete' => 'Hapus Opsi Referensi',
                        ],
                    ],
                    'master_distributor' => [
                        'label' => 'Master Distributor',
                        'route' => 'master.distributor',
                        'main_permission' => 'master.distributor.view',
                        'description' => 'Daftar distributor dan vendor rekanan servis untuk klaim garansi',
                        'actions' => [
                            'master.distributor.create' => 'Tambah Distributor Baru',
                            'master.distributor.edit' => 'Edit Data Distributor',
                            'master.distributor.delete' => 'Hapus Data Distributor',
                        ],
                    ],
                    'wifi_absensi' => [
                        'label' => 'Master WiFi Absensi',
                        'route' => 'master.wifi-absen',
                        'main_permission' => 'master.wifi_absensi.view',
                        'description' => 'Konfigurasi whitelist IP jaringan WiFi yang diizinkan untuk absensi',
                        'actions' => [
                            'master.wifi_absensi.create' => 'Tambah IP WiFi',
                            'master.wifi_absensi.edit' => 'Edit Data WiFi',
                            'master.wifi_absensi.delete' => 'Hapus IP WiFi',
                        ],
                    ],
                ],
            ],

            'absensi_staf' => [
                'group_label' => 'Absensi & Gaji Staf',
                'description' => 'Monitoring kehadiran, rekapitulasi keterlambatan, dan manajemen penggajian staf',
                'modules' => [
                    'absensi_riwayat' => [
                        'label' => 'Riwayat Absensi',
                        'route' => 'absensi.riwayat',
                        'main_permission' => 'absensi.riwayat',
                        'description' => 'Monitoring dan rekapitulasi riwayat absensi staf',
                        'actions' => [
                            'absensi.rekap.view' => 'Akses Tab Rekap Keterlambatan Pegawai',
                            'absensi.riwayat.export' => 'Ekspor Rekap Absensi',
                        ],
                    ],
                    'slip_gaji' => [
                        'label' => 'Slip Gaji',
                        'route' => 'slip-gaji.index',
                        'main_permission' => 'gaji.view',
                        'description' => 'Pembuatan, penyalinan, finalisasi slip gaji staf, dan cetak PDF',
                        'actions' => [
                            'gaji.create' => 'Tambah / Buat Slip Gaji (POST)',
                            'gaji.edit' => 'Edit Komponen Slip Gaji (PUT)',
                            'gaji.export_pdf' => 'Cetak & Unduh Dokumen PDF',
                            'gaji.reopen' => 'Buka Kunci Slip Final (Reopen)',
                            'gaji.finalize' => 'Finalkan & Kunci Slip Gaji',
                            'gaji.delete' => 'Hapus Draft Slip Gaji (DELETE)',
                            'gaji.alert' => 'Lihat Alert Kenaikan Gaji 6 Bulan',
                            'gaji.tarif.edit' => 'Atur Tarif Global Toko',
                            'gaji.pokok.edit' => 'Ubah Gaji Pokok & Komponen Tetap',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Ambil seluruh string nama permission yang valid di sistem.
     */
    public static function getAllPermissionNames(): array
    {
        $permissions = ['dashboard.view'];
        $schema = self::getModuleSchema();

        foreach ($schema as $group) {
            foreach ($group['modules'] as $mod) {
                if (! empty($mod['main_permission'])) {
                    $permissions[] = $mod['main_permission'];
                }
                if (! empty($mod['actions'])) {
                    foreach (array_keys($mod['actions']) as $action) {
                        $permissions[] = $action;
                    }
                }
            }
        }

        return array_values(array_unique($permissions));
    }

    /**
     * Preset default permission per role non-owner.
     */
    public static function getDefaultRolePresets(): array
    {
        $all = self::getAllPermissionNames();

        return [
            'Superadmin' => array_values(array_filter($all, fn ($p) => ! str_starts_with($p, 'transaksi.harian.'))), // Superadmin menggunakan Transaksi FULL, bukan Transaksi Harian

            'Teknisi' => [
                'dashboard.view',
                'servis.masuk.view',
                'servis.masuk.detail',
                'servis.masuk.create',
                'servis.masuk.edit',
                'servis.masuk.print',
                'servis.masuk.tindakan',
                'servis.masuk.status',
                'servis.keluar.view',
                'servis.keluar.detail',
                'servis.keluar.edit_nota',
                'servis.keluar.ambil',
                'servis.keluar.print',
                'servis.batal.view',
                'servis.batal.detail',
                'servis.batal.ubah_status',
                'servis.batal.ambil',
                'servis.batal.print',
                'servis.non_real.view',
                'servis.non_real.detail',
                'servis.non_real.create',
                'servis.non_real.edit',
                'servis.non_real.print',
                'master.customer.view',
                'master.customer.create',
                'master.servis.view',
                'master.produk.view',
                'serial_number.view',
                'cabang.stok.view',
                'klaim_garansi.view',
                'klaim_garansi.detail',
                'klaim_garansi.create',
                'klaim_garansi.edit',
                'master.distributor.view',
                'absensi.view',
                'absensi.submit',
                'absensi.riwayat',
            ],

            'Admin' => [
                'dashboard.view',
                'kasir.produk.view',
                'kasir.produk.create',
                'kasir.produk.print',
                'kasir.produk.customer',
                'laporan.penjualan.view',
                'laporan.penjualan.detail',
                'laporan.penjualan.print',
                'laporan.penjualan.retur',
                'transaksi.harian.view',
                'transaksi.harian.create',
                'transaksi.harian.edit',
                'transaksi.harian.status',
                'pajak.view',
                'pajak.detail',
                'pajak.create',
                'pajak.edit',
                'pajak.toggle_selesai',
                'pajak.export_excel',
                'pajak.export_pdf',
                'master.orang_pajak.view',
                'master.orang_pajak.create',
                'klaim_garansi.view',
                'klaim_garansi.detail',
                'klaim_garansi.create',
                'klaim_garansi.edit',
                'klaim_garansi.delete',
                'klaim_garansi.export_excel',
                'master.distributor.view',
                'master.distributor.create',
                'master.distributor.edit',
                'master.distributor.delete',
                'servis.masuk.view',
                'servis.masuk.detail',
                'servis.masuk.create',
                'servis.masuk.edit',
                'servis.masuk.print',
                'servis.keluar.view',
                'servis.keluar.detail',
                'servis.keluar.edit_nota',
                'servis.keluar.ambil',
                'servis.keluar.print',
                'servis.batal.view',
                'servis.batal.detail',
                'servis.batal.ubah_status',
                'servis.batal.ambil',
                'servis.batal.print',
                'servis.non_real.view',
                'servis.non_real.detail',
                'servis.non_real.create',
                'servis.non_real.edit',
                'servis.non_real.print',
                'master.customer.view',
                'master.customer.create',
                'master.customer.edit',
                'cabang.stok.view',
                'serial_number.view',
                'serial_number.create',
                'master.produk.view',
                'master.servis.view',
                'absensi.view',
                'absensi.submit',
                'absensi.riwayat',
                'gaji.view',
                'gaji.create',
                'gaji.edit',
                'gaji.finalize',
                'gaji.delete',
                'gaji.export_pdf',
                'gaji.alert',
            ],

            'Bendahara' => [
                'dashboard.view',
                'kas.keluar.view',
                'kas.keluar.create',
                'kas.keluar.edit',
                'kas.keluar.delete',
                'saldo.view',
                'saldo.create',
                'saldo.edit',
                'transaksi.harian.view',
                'transaksi.harian.create',
                'transaksi.harian.edit',
                'transaksi.harian.status',
                'pajak.view',
                'pajak.detail',
                'pajak.create',
                'pajak.edit',
                'pajak.delete',
                'pajak.toggle_selesai',
                'pajak.export_excel',
                'pajak.export_pdf',
                'master.orang_pajak.view',
                'master.orang_pajak.create',
                'master.orang_pajak.edit',
                'master.orang_pajak.delete',
                'laporan.penjualan.view',
                'laporan.penjualan.detail',
                'laporan.penjualan.print',
                'kasir.produk.view',
                'kasir.produk.create',
                'kasir.produk.print',
                'servis.masuk.view',
                'servis.masuk.detail',
                'servis.keluar.view',
                'servis.keluar.detail',
                'servis.keluar.ambil',
                'servis.keluar.print',
                'master.customer.view',
                'cabang.stok.view',
                'absensi.view',
                'absensi.submit',
                'absensi.riwayat',
                'gaji.view',
                'gaji.create',
                'gaji.edit',
                'gaji.finalize',
                'gaji.delete',
                'gaji.export_pdf',
                'gaji.alert',
            ],

            'Operasional' => [
                'dashboard.view',
                'absensi.view',
                'absensi.submit',
                'kasir.produk.view',
                'kasir.produk.create',
                'kasir.produk.print',
                'kasir.produk.customer',
                'pajak.view',
                'pajak.detail',
                'pajak.create',
                'pajak.edit',
                'pajak.toggle_selesai',
                'pajak.export_excel',
                'pajak.export_pdf',
                'master.orang_pajak.view',
                'master.orang_pajak.create',
                'cabang.stok.view',
                'servis.masuk.view',
                'servis.masuk.detail',
                'servis.masuk.create',
                'servis.keluar.view',
                'servis.keluar.detail',
                'servis.keluar.ambil',
                'master.customer.view',
                'master.customer.create',
                'serial_number.view',
                'klaim_garansi.view',
                'klaim_garansi.detail',
                'klaim_garansi.create',
                'klaim_garansi.edit',
                'gaji.view',
                'gaji.create',
                'gaji.edit',
                'gaji.finalize',
                'gaji.delete',
                'gaji.export_pdf',
                'gaji.alert',
            ],

            'Magang' => [
                'dashboard.view',
                'servis.masuk.view',
                'servis.masuk.detail',
                'servis.masuk.tindakan',
                'servis.keluar.view',
                'servis.keluar.detail',
                'kasir.produk.view',
                'master.customer.view',
                'master.produk.view',
                'master.servis.view',
                'cabang.stok.view',
            ],
        ];
    }
}
