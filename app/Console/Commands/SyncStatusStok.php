<?php

namespace App\Console\Commands;

use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Console\Command;

class SyncStatusStok extends Command
{
    protected $signature = 'stok:sync-status';

    protected $description = 'Nonaktifkan baris stok kosong dan sembunyikan produk tanpa stok dari marketplace (satu arah, idempotent)';

    public function handle(): int
    {
        $stokDiubah = Stok::where('jumlah', '<=', 0)
            ->where('is_aktif', true)
            ->update(['is_aktif' => false]);

        $produkKosong = Stok::where('jumlah', '<=', 0)->distinct()->pluck('produk_id');

        $produkDiubah = Produk::whereIn('id', $produkKosong)
            ->where('ready_marketplace', true)
            ->update(['ready_marketplace' => false]);

        $produkDiubah += Produk::whereDoesntHave('stoks')
            ->where('ready_marketplace', true)
            ->update(['ready_marketplace' => false]);

        $this->info("Stok dinonaktifkan: {$stokDiubah} baris. Produk disembunyikan dari marketplace: {$produkDiubah}.");

        return self::SUCCESS;
    }
}
