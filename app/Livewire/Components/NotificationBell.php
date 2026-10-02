<?php

namespace App\Livewire\Components;

use App\Models\Cabang;
use App\Models\MasterServisStok;
use App\Models\ModalAwal;
use App\Models\Stok;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $emptyCount = 0;

    public int $criticalCount = 0;

    public int $modalAwalAlertCount = 0;

    public int $totalAlerts = 0;

    protected $listeners = [
        'stockUpdated' => 'handleNotificationRefresh',
        'refreshNotification' => 'handleNotificationRefresh',
        'modalAwalUpdated' => 'handleNotificationRefresh',
    ];

    public function handleNotificationRefresh(): void
    {
        $this->clearNotificationCache();
    }

    public static function clearNotificationCache(): void
    {
        Cache::forget('notification_bell_hq');
        try {
            $cabangIds = Cabang::pluck('id');
            foreach ($cabangIds as $cid) {
                Cache::forget("notification_bell_cabang_{$cid}");
            }
        } catch (\Throwable $e) {
            // Silently ignore if DB is unmigrated or inaccessible
        }
    }

    public function render()
    {
        $user = Auth::user();
        if (! $user) {
            $this->emptyCount = 0;
            $this->criticalCount = 0;
            $this->modalAwalAlertCount = 0;
            $this->totalAlerts = 0;

            return view('livewire.components.notification-bell', [
                'emptyCount' => 0,
                'criticalCount' => 0,
                'modalAwalAlertCount' => 0,
                'missingModalCabangs' => collect(),
                'totalAlerts' => 0,
                'alertItems' => collect(),
                'contextName' => '',
                'isHQ' => false,
                'isOwner' => false,
            ]);
        }

        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && $activeBranchSession === 'hq';
        $targetCabangId = $isOwner ? (int) $activeBranchSession : $user->cabang_id;

        $cacheKey = $isHQ ? 'notification_bell_hq' : "notification_bell_cabang_{$targetCabangId}";

        $data = Cache::remember($cacheKey, 60, function () use ($isHQ, $isOwner, $targetCabangId) {
            return $this->fetchNotificationData($isHQ, $isOwner, $targetCabangId);
        });

        $this->emptyCount = $data['emptyCount'];
        $this->criticalCount = $data['criticalCount'];
        $this->modalAwalAlertCount = $data['modalAwalAlertCount'];
        $this->totalAlerts = $data['totalAlerts'];

        return view('livewire.components.notification-bell', $data);
    }

    private function fetchNotificationData(bool $isHQ, bool $isOwner, mixed $targetCabangId): array
    {
        $emptyCount = 0;
        $criticalCount = 0;
        $modalAwalAlertCount = 0;
        $missingModalCabangs = collect();

        if ($isHQ) {
            // Mode HQ: Memantau stok produk AKTIF & sparepart di semua cabang yang jumlahnya <= min_stok
            $stoksProduk = Stok::with(['produk', 'cabang'])
                ->where('is_aktif', true)
                ->whereHas('produk')
                ->whereHas('cabang', fn ($q) => $q->where('status', 'aktif'))
                ->whereColumn('jumlah', '<=', 'min_stok')
                ->get();

            $stoksSparepart = MasterServisStok::with(['masterServis', 'cabang'])
                ->where('is_aktif', true)
                ->whereHas('masterServis', fn ($q) => $q->where('jenis', 'sparepart')->where('is_aktif', true))
                ->whereHas('cabang', fn ($q) => $q->where('status', 'aktif'))
                ->whereColumn('jumlah', '<=', 'min_stok')
                ->get();

            $emptyProduk = $stoksProduk->where('jumlah', '<=', 0);
            $criticalProduk = $stoksProduk->where('jumlah', '>', 0);

            $emptySparepart = $stoksSparepart->where('jumlah', '<=', 0);
            $criticalSparepart = $stoksSparepart->where('jumlah', '>', 0);

            $emptyCount = $emptyProduk->count() + $emptySparepart->count();
            $criticalCount = $criticalProduk->count() + $criticalSparepart->count();

            $itemsProduk = $stoksProduk->map(function ($s) {
                return (object) [
                    'type' => 'produk',
                    'type_label' => 'Produk Retail',
                    'id' => $s->produk_id,
                    'nama' => $s->produk?->nama_produk ?? 'Produk',
                    'kode' => $s->produk?->barcode,
                    'foto_url' => $s->produk?->foto_url,
                    'cabang_nama' => $s->cabang?->kode_cabang ?? 'CB',
                    'jumlah' => $s->jumlah,
                    'min_stok' => $s->min_stok,
                ];
            });

            $itemsSparepart = $stoksSparepart->map(function ($s) {
                return (object) [
                    'type' => 'sparepart',
                    'type_label' => 'Sparepart Servis',
                    'id' => $s->master_servis_id,
                    'nama' => $s->masterServis?->nama_item ?? 'Sparepart',
                    'kode' => $s->masterServis?->kode_item,
                    'foto_url' => null,
                    'cabang_nama' => $s->cabang?->kode_cabang ?? 'CB',
                    'jumlah' => $s->jumlah,
                    'min_stok' => $s->min_stok,
                ];
            });

            $alertItems = $itemsProduk->concat($itemsSparepart)->sortBy('jumlah')->take(50);
            $contextName = 'Semua Cabang (HQ)';
        } else {
            // Mode Cabang: Hanya memantau stok produk AKTIF & sparepart di cabang ini saja
            $cabang = Cabang::find($targetCabangId) ?? Cabang::first();
            $realCabangId = $cabang?->id ?? 1;
            $contextName = $isOwner ? ($cabang?->nama_cabang ?? 'Cabang') : 'Soulmate Komputer';

            $stoksProduk = Stok::with('produk')
                ->where('cabang_id', $realCabangId)
                ->where('is_aktif', true)
                ->whereHas('produk')
                ->whereColumn('jumlah', '<=', 'min_stok')
                ->get();

            $stoksSparepart = MasterServisStok::with('masterServis')
                ->where('cabang_id', $realCabangId)
                ->where('is_aktif', true)
                ->whereHas('masterServis', fn ($q) => $q->where('jenis', 'sparepart')->where('is_aktif', true))
                ->whereColumn('jumlah', '<=', 'min_stok')
                ->get();

            $emptyProduk = $stoksProduk->where('jumlah', '<=', 0);
            $criticalProduk = $stoksProduk->where('jumlah', '>', 0);

            $emptySparepart = $stoksSparepart->where('jumlah', '<=', 0);
            $criticalSparepart = $stoksSparepart->where('jumlah', '>', 0);

            $emptyCount = $emptyProduk->count() + $emptySparepart->count();
            $criticalCount = $criticalProduk->count() + $criticalSparepart->count();

            $itemsProduk = $stoksProduk->map(function ($s) use ($cabang) {
                return (object) [
                    'type' => 'produk',
                    'type_label' => 'Produk Retail',
                    'id' => $s->produk_id,
                    'nama' => $s->produk?->nama_produk ?? 'Produk',
                    'kode' => $s->produk?->barcode,
                    'foto_url' => $s->produk?->foto_url,
                    'cabang_nama' => $cabang?->kode_cabang ?? 'CB',
                    'jumlah' => $s->jumlah,
                    'min_stok' => $s->min_stok,
                ];
            });

            $itemsSparepart = $stoksSparepart->map(function ($s) use ($cabang) {
                return (object) [
                    'type' => 'sparepart',
                    'type_label' => 'Sparepart Servis',
                    'id' => $s->master_servis_id,
                    'nama' => $s->masterServis?->nama_item ?? 'Sparepart',
                    'kode' => $s->masterServis?->kode_item,
                    'foto_url' => null,
                    'cabang_nama' => $cabang?->kode_cabang ?? 'CB',
                    'jumlah' => $s->jumlah,
                    'min_stok' => $s->min_stok,
                ];
            });

            $alertItems = $itemsProduk->concat($itemsSparepart)->sortBy('jumlah')->take(50);
        }

        $totalAlerts = $emptyCount + $criticalCount + $modalAwalAlertCount;

        return [
            'emptyCount' => $emptyCount,
            'criticalCount' => $criticalCount,
            'modalAwalAlertCount' => $modalAwalAlertCount,
            'missingModalCabangs' => $missingModalCabangs,
            'totalAlerts' => $totalAlerts,
            'alertItems' => $alertItems,
            'contextName' => $contextName,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ];
    }
}

