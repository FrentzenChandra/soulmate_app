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
        // 1. Tabel Master Tarif Gaji Default
        Schema::create('tarif_gajis', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 50)->unique(); // uang_makan, uang_transport, kenaikan_6_bulan
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('keterangan', 255)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('tarif_gajis')->insert([
            ['jenis' => 'uang_makan', 'nominal' => 20000.00, 'keterangan' => 'Tarif uang makan per hari hadir', 'created_at' => now(), 'updated_at' => now()],
            ['jenis' => 'uang_transport', 'nominal' => 20000.00, 'keterangan' => 'Tarif uang transportasi per hari hadir', 'created_at' => now(), 'updated_at' => now()],
            ['jenis' => 'kenaikan_6_bulan', 'nominal' => 500000.00, 'keterangan' => 'Standar kenaikan gaji pokok berkala per 6 bulan', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Tabel Utama Slip Gaji
        Schema::create('slip_gajis', function (Blueprint $table) {
            $table->id();
            $table->string('no_slip', 50)->unique();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->string('status', 20)->default('draft'); // draft, final
            $table->foreignId('source_slip_id')->nullable()->constrained('slip_gajis')->nullOnDelete();

            // Snapshot Staf (Dibekukan saat Final)
            $table->string('nama_staf_snapshot', 150);
            $table->string('bagian_snapshot', 100)->nullable();
            $table->date('tanggal_lahir_snapshot')->nullable();
            $table->date('tanggal_mulai_bekerja_snapshot')->nullable();
            $table->string('masa_kerja_snapshot', 50)->nullable();
            $table->string('usia_snapshot', 50)->nullable();

            // Komponen Finansial Penerimaan
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->unsignedInteger('uang_makan_hari')->default(0);
            $table->decimal('uang_makan_per_hari', 15, 2)->default(0);
            $table->decimal('uang_makan_total', 15, 2)->default(0);

            $table->unsignedInteger('uang_transport_hari')->default(0);
            $table->decimal('uang_transport_per_hari', 15, 2)->default(0);
            $table->decimal('uang_transport_total', 15, 2)->default(0);

            $table->string('tunjangan_nama', 150)->default('Tunjangan Kepala Teknisi Laptop');
            $table->decimal('tunjangan_nominal', 15, 2)->default(0);
            $table->decimal('bonus_nominal', 15, 2)->default(0);
            $table->decimal('lembur_performa_nominal', 15, 2)->default(0);
            $table->decimal('servisan_selesai_nominal', 15, 2)->default(0);

            // Ringkasan Finansial
            $table->decimal('total_bruto', 15, 2)->default(0);
            $table->decimal('total_pengurangan', 15, 2)->default(0);
            $table->decimal('total_diterima', 15, 2)->default(0);

            // Jejak Audit Nilai Tetap (Hanya terisi jika Gaji Pokok atau Per Hari diubah berbeda dari slip sumber)
            $table->foreignId('diubah_nilai_tetap_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diubah_nilai_tetap_at')->nullable();

            // Audit Trail Alur Slip
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->text('alasan_buka_kembali')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reopened_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'periode_tahun', 'periode_bulan']);
            $table->index(['cabang_id', 'periode_tahun', 'periode_bulan']);
        });

        // 3. Tabel Detail Pengurangan / Potongan Slip Gaji
        Schema::create('slip_gaji_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slip_gaji_id')->constrained('slip_gajis')->cascadeOnDelete();
            $table->string('tipe', 20)->default('pengurangan');
            $table->string('kategori', 50); // Hutang, Kasbon, Denda, Lainnya
            $table->string('nama_item', 150);
            $table->decimal('nominal', 15, 2)->default(0);
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });

        // 4. Tabel Milestone Kenaikan Gaji Pokok Berkala 6 Bulan
        Schema::create('kenaikan_gajis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->unsignedInteger('milestone_bulan'); // 6, 12, 18, 24, ...
            $table->date('jatuh_tempo');
            $table->decimal('nominal', 15, 2)->default(500000.00);
            $table->string('status', 20)->default('menunggu'); // menunggu, diterapkan, ditandai, dilewati
            $table->foreignId('slip_gaji_id')->nullable()->constrained('slip_gajis')->nullOnDelete();
            $table->foreignId('ditangani_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditangani_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'milestone_bulan']);
            $table->index(['cabang_id', 'status']);
        });

        // 5. Penanaman Izin Spatie untuk Modul Slip Gaji
        if (Schema::hasTable('permissions')) {
            $gajiPermissions = [
                'gaji.view',
                'gaji.create',
                'gaji.edit',
                'gaji.finalize',
                'gaji.delete',
                'gaji.export_pdf',
                'gaji.alert',
                'gaji.reopen',
                'gaji.tarif.edit',
                'gaji.pokok.edit',
            ];

            foreach ($gajiPermissions as $perm) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => $perm, 'guard_name' => 'web'],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            if (Schema::hasTable('roles') && Schema::hasTable('role_has_permissions')) {
                // Izin operasional diberikan ke Superadmin, Bendahara, Operasional
                $operasionalPermissions = [
                    'gaji.view',
                    'gaji.create',
                    'gaji.edit',
                    'gaji.finalize',
                    'gaji.delete',
                    'gaji.export_pdf',
                    'gaji.alert',
                ];

                $targetRoles = ['Superadmin', 'Bendahara', 'Operasional'];
                $permIds = DB::table('permissions')->whereIn('name', $operasionalPermissions)->pluck('id')->toArray();

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

                // gaji.reopen, gaji.tarif.edit, gaji.pokok.edit default khusus Owner
                // Catatan: Owner juga memiliki global bypass via Gate::before
                $ownerRole = DB::table('roles')->where('name', 'Owner')->first();
                if ($ownerRole) {
                    $ownerSpecificPermIds = DB::table('permissions')->whereIn('name', [
                        'gaji.reopen',
                        'gaji.tarif.edit',
                        'gaji.pokok.edit',
                    ])->pluck('id')->toArray();

                    foreach ($ownerSpecificPermIds as $opId) {
                        DB::table('role_has_permissions')->updateOrInsert([
                            'permission_id' => $opId,
                            'role_id' => $ownerRole->id,
                        ]);
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
        Schema::dropIfExists('kenaikan_gajis');
        Schema::dropIfExists('slip_gaji_items');
        Schema::dropIfExists('slip_gajis');
        Schema::dropIfExists('tarif_gajis');
    }
};

