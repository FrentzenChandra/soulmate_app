<?php

namespace Tests\Unit;

use App\Services\PermissionService;
use Tests\TestCase;

class PermissionServiceTest extends TestCase
{
    /**
     * Uji struktur skema modul dan urutan 1-to-1 dengan menu sidebar.
     */
    public function test_module_schema_structure_and_group_order(): void
    {
        $schema = PermissionService::getModuleSchema();

        $this->assertIsArray($schema);

        // 7 Grup Modul dalam urutan persis sidebar
        $expectedGroups = [
            'absensi_kehadiran',
            'kasir_penjualan',
            'layanan_servis',
            'kas_keuangan',
            'master_data',
            'absensi_staf',
            'garansi',
        ];

        $this->assertEquals($expectedGroups, array_keys($schema), 'Urutan grup modul harus persis 1-to-1 dengan susunan sidebar.');

        foreach ($schema as $groupKey => $group) {
            $this->assertArrayHasKey('group_label', $group, "Grup {$groupKey} wajib memiliki group_label");
            $this->assertArrayHasKey('description', $group, "Grup {$groupKey} wajib memiliki description");
            $this->assertArrayHasKey('modules', $group, "Grup {$groupKey} wajib memiliki modules");
            $this->assertNotEmpty($group['modules'], "Grup {$groupKey} tidak boleh kosong");

            foreach ($group['modules'] as $moduleKey => $module) {
                $this->assertArrayHasKey('label', $module, "Modul {$moduleKey} wajib memiliki label");
                $this->assertArrayHasKey('route', $module, "Modul {$moduleKey} wajib memiliki route");
                $this->assertArrayHasKey('main_permission', $module, "Modul {$moduleKey} wajib memiliki main_permission");
                $this->assertArrayHasKey('actions', $module, "Modul {$moduleKey} wajib memiliki actions");
                $this->assertIsArray($module['actions'], "Actions modul {$moduleKey} harus bertipe array");
            }
        }
    }

    /**
     * Uji daftar permission names unik dan valid.
     */
    public function test_all_permission_names_are_unique_and_valid(): void
    {
        $permissions = PermissionService::getAllPermissionNames();

        $this->assertIsArray($permissions);
        $this->assertNotEmpty($permissions);

        // Tidak boleh ada duplikasi nama permission
        $this->assertCount(count($permissions), array_unique($permissions), 'Nama permission tidak boleh ada yang duplikat.');

        // Memastikan permission kunci terdaftar
        $criticalPermissions = [
            'dashboard.view',
            'absensi.view',
            'absensi.submit',
            'absensi.riwayat',
            'kasir.produk.view',
            'laporan.penjualan.view',
            'servis.masuk.view',
            'servis.keluar.view',
            'servis.batal.view',
            'servis.non_real.view',
            'transaksi.harian.view',
            'transaksi.full.view',
            'pajak.view',
            'kas.keluar.view',
            'saldo.view',
            'serial_number.view',
            'target.bulanan.view',
            'master.produk.view',
            'cabang.stok.view',
            'master.orang_pajak.view',
            'master.customer.view',
            'master.users.view',
            'master.servis.view',
            'master.pegawai.view',
            'master.settings.view',
            'master.distributor.view',
            'master.wifi_absensi.view',
            'klaim_garansi.view',
        ];

        foreach ($criticalPermissions as $perm) {
            $this->assertContains($perm, $permissions, "Permission kritis [{$perm}] wajib terdaftar.");
        }
    }

    /**
     * Uji preset default role non-owner.
     */
    public function test_default_role_presets_integrity(): void
    {
        $presets = PermissionService::getDefaultRolePresets();
        $allPermissions = PermissionService::getAllPermissionNames();

        $expectedRoles = ['Superadmin', 'Teknisi', 'Admin', 'Bendahara', 'Operasional', 'Magang'];
        $this->assertEquals($expectedRoles, array_keys($presets));

        // 1. Superadmin wajib memiliki 100% seluruh permissions
        $this->assertCount(count($allPermissions), $presets['Superadmin']);
        $this->assertEquals($allPermissions, $presets['Superadmin']);

        // 2. Setiap role hanya boleh memiliki permission yang terdaftar di schema
        foreach ($presets as $roleName => $perms) {
            foreach ($perms as $perm) {
                $this->assertContains($perm, $allPermissions, "Permission [{$perm}] pada role [{$roleName}] tidak terdaftar di skema.");
            }
        }

        // 3. Teknisi: Memiliki servis & klaim garansi, DILARANG memiliki transaksi full / kas keuangan
        $this->assertContains('servis.masuk.view', $presets['Teknisi']);
        $this->assertContains('klaim_garansi.view', $presets['Teknisi']);
        $this->assertNotContains('transaksi.full.view', $presets['Teknisi']);
        $this->assertNotContains('kas.keluar.view', $presets['Teknisi']);
        $this->assertNotContains('saldo.view', $presets['Teknisi']);

        // 4. Admin: Memiliki transaksi harian, kasir, servis, klaim garansi, DILARANG memiliki transaksi full (laba)
        $this->assertContains('transaksi.harian.view', $presets['Admin']);
        $this->assertContains('kasir.produk.view', $presets['Admin']);
        $this->assertContains('servis.non_real.view', $presets['Admin']);
        $this->assertNotContains('transaksi.full.view', $presets['Admin']);

        // 5. Bendahara: Memiliki kas keluar, saldo, pajak, DILARANG memiliki transaksi full
        $this->assertContains('kas.keluar.view', $presets['Bendahara']);
        $this->assertContains('saldo.view', $presets['Bendahara']);
        $this->assertContains('pajak.view', $presets['Bendahara']);
        $this->assertNotContains('transaksi.full.view', $presets['Bendahara']);

        // 6. Operasional: Memiliki kasir, absensi, DILARANG memiliki riwayat absensi & kas keuangan
        $this->assertContains('kasir.produk.view', $presets['Operasional']);
        $this->assertContains('absensi.view', $presets['Operasional']);
        $this->assertNotContains('absensi.riwayat', $presets['Operasional']);
        $this->assertNotContains('kas.keluar.view', $presets['Operasional']);

        // 7. Magang: Dilarang memiliki izin hapus (DELETE)
        foreach ($presets['Magang'] as $perm) {
            $this->assertStringNotContainsString('.delete', $perm, "Role Magang tidak boleh memiliki izin delete [{$perm}].");
        }
    }
}

