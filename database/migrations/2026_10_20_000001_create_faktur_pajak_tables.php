<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Master Orang Pajak (Rekanan / Instansi)
        Schema::create('master_orang_pajaks', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('npwp', 50)->nullable();
            $table->string('kontak', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        // 2. Master Via Pajak (E-Katalog, SIPLah, Langsung)
        Schema::create('master_via_pajaks', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        DB::table('master_via_pajaks')->insert([
            ['nama' => 'E-Katalog', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'SIPLah', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Langsung', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Master Kode Pajak (1 s/d 5)
        Schema::create('master_kode_pajaks', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50);
            $table->string('keterangan', 255)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        DB::table('master_kode_pajaks')->insert([
            ['kode' => 'Kode 1', 'keterangan' => 'Penyerahan kepada pihak selain pemungut PPN', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'Kode 2', 'keterangan' => 'Penyerahan kepada pemungut PPN Bendahara Pemerintah', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'Kode 3', 'keterangan' => 'Penyerahan kepada pemungut PPN lainnya', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'Kode 4', 'keterangan' => 'Penyerahan DPP Nilai Lain', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'Kode 5', 'keterangan' => 'Penyerahan lainnya / Custom', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 4. Master PPh (Non-PPh 0%, PPh 22 1.5%, PPh 23 2%)
        Schema::create('master_pphs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pph', 100);
            $table->decimal('persentase', 5, 2)->default(0.00);
            $table->string('deskripsi', 255)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        DB::table('master_pphs')->insert([
            ['nama_pph' => 'Non-PPh', 'persentase' => 0.00, 'deskripsi' => 'Tanpa pemotongan PPh (0%)', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama_pph' => 'PPh 22', 'persentase' => 1.50, 'deskripsi' => 'Pengadaan barang instansi pemerintah (1.5%)', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nama_pph' => 'PPh 23', 'persentase' => 2.00, 'deskripsi' => 'Jasa servis dan pemeliharaan (2.0%)', 'is_aktif' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 5. Tabel Utama Faktur Pajak
        Schema::create('faktur_pajaks', function (Blueprint $table) {
            $table->id();
            $table->string('no_faktur', 50)->unique();
            $table->date('tanggal');
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Status Selesai
            $table->boolean('is_selesai')->default(false);
            $table->timestamp('selesai_at')->nullable();
            $table->foreignId('selesai_by')->nullable()->constrained('users')->nullOnDelete();

            // Relasi Referensi (Nullable agar jika master dihapus, faktur tetap utuh)
            $table->foreignId('master_orang_pajak_id')->nullable()->constrained('master_orang_pajaks')->nullOnDelete();
            $table->foreignId('master_via_pajak_id')->nullable()->constrained('master_via_pajaks')->nullOnDelete();
            $table->foreignId('master_kode_pajak_id')->nullable()->constrained('master_kode_pajaks')->nullOnDelete();
            $table->foreignId('master_pph_id')->nullable()->constrained('master_pphs')->nullOnDelete();

            // Snapshot Fields (Frozen Data)
            $table->string('nama_pajak_snapshot', 150);
            $table->text('barang_snapshot');
            $table->string('via_pajak_snapshot', 100);
            $table->string('kode_pajak_snapshot', 50)->nullable();

            // Angka Finansial
            $table->decimal('bruto', 15, 2)->default(0);
            $table->decimal('dpp', 15, 2)->default(0);
            $table->decimal('dpp_lain', 15, 2)->default(0);
            $table->decimal('ppn_persen', 5, 2)->default(12.00);
            $table->decimal('ppn_nominal', 15, 2)->default(0);

            $table->string('jenis_pph_snapshot', 100);
            $table->decimal('pph_persen', 5, 2)->default(0);
            $table->decimal('pph_nominal', 15, 2)->default(0);

            $table->decimal('netto', 15, 2)->default(0);

            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 6. Sinkronisasi Izin Spatie untuk Modul Pajak
        if (Schema::hasTable('permissions')) {
            $pajakPermissions = [
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
            ];

            foreach ($pajakPermissions as $perm) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => $perm, 'guard_name' => 'web'],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            // Berikan izin default ke Superadmin & Bendahara jika tabel roles ada
            if (Schema::hasTable('roles') && Schema::hasTable('role_has_permissions')) {
                $targetRoles = ['Superadmin', 'Bendahara', 'Operasional', 'Admin'];
                $permIds = DB::table('permissions')->whereIn('name', $pajakPermissions)->pluck('id')->toArray();

                foreach ($targetRoles as $rName) {
                    $role = DB::table('roles')->where('name', $rName)->first();
                    if ($role) {
                        foreach ($permIds as $pId) {
                            DB::table('role_has_permissions')->updateOrInsert([
                                'permission_id' => $pId,
                                'role_id' => $role->id,
                            ]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faktur_pajaks');
        Schema::dropIfExists('master_pphs');
        Schema::dropIfExists('master_kode_pajaks');
        Schema::dropIfExists('master_via_pajaks');
        Schema::dropIfExists('master_orang_pajaks');
    }
};
