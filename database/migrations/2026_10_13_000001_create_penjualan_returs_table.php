<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('penjualan_details') && ! Schema::hasColumn('penjualan_details', 'qty_retur')) {
            Schema::table('penjualan_details', function (Blueprint $table) {
                $table->unsignedInteger('qty_retur')->default(0)->after('qty');
            });
        }

        if (! Schema::hasTable('penjualan_returs')) {
            Schema::create('penjualan_returs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('penjualan_id')->constrained('penjualans')->cascadeOnDelete();
                $table->foreignId('penjualan_detail_id')->constrained('penjualan_details')->cascadeOnDelete();
                $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
                $table->foreignId('produk_id')->nullable()->constrained('produks')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedInteger('qty_retur');
                $table->integer('stok_sebelum')->default(0);
                $table->integer('stok_sesudah')->default(0);
                $table->string('alasan');
                $table->timestamps();

                $table->index(['cabang_id', 'created_at']);
                $table->index(['penjualan_id']);
            });
        }

        if (Schema::hasTable('permissions')) {
            $perm = Permission::firstOrCreate([
                'name' => 'laporan.penjualan.retur',
                'guard_name' => 'web',
            ]);

            foreach (['Superadmin', 'Admin'] as $roleName) {
                $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
                if ($role && ! $role->hasPermissionTo($perm)) {
                    $role->givePermissionTo($perm);
                }
            }

            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan_returs');

        if (Schema::hasTable('penjualan_details') && Schema::hasColumn('penjualan_details', 'qty_retur')) {
            Schema::table('penjualan_details', function (Blueprint $table) {
                $table->dropColumn('qty_retur');
            });
        }
    }
};

