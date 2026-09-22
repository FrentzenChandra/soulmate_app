<?php

namespace App\Livewire\Components;

use App\Models\Cabang;
use App\Models\MasterServisStok;
use App\Models\ModalAwal;
use App\Models\Stok;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $emptyCount = 0;

    public int $criticalCount = 0;

    public int $modalAwalAlertCount = 0;

    public int $totalAlerts = 0;

    protected $listeners = [
        'stockUpdated' => '$refresh',
        'refreshNotification' => '$refresh',
        'modalAwalUpdated' => '$refresh',
    ];

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

        $this->emptyCount = 0;
        $this->criticalCount = 0;
        $this->modalAwalAlertCount = 0;
        $missingModalCabangs = collect();
        $contextName = 'Kantor Pusat (HQ)';

        $currentTime = now();
        $isPast10Am = $currentTime->hour >= 10;
        $todayDate = $currentTime->toDateString();

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

            $this->emptyCount = $emptyProduk->count() + $emptySparepart->count();
            $this->criticalCount = $criticalProduk->count() + $criticalSparepart->count();

            // [TEMP HIDE] Peringatan Modal Awal setelah jam 10:00 untuk semua cabang aktif
            // if ($isPast10Am) {
            //     $aktifCabangs = Cabang::where('status', 'aktif')->get();
            //     $existingModalCabangIds = ModalAwal::whereDate('tanggal', $todayDate)
            //         ->pluck('cabang_id')
            //         ->toArray();
            //
            //     $missingModalCabangs = $aktifCabangs->reject(fn ($c) => in_array($c->id, $existingModalCabangIds))->values();
            //     $this->modalAwalAlertCount = $missingModalCabangs->count();
            // }

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
            $targetCabangId = $isOwner ? (int) $activeBranchSession : $user->cabang_id;
            $cabang = Cabang::find($targetCabangId) ?? Cabang::first();
            $targetCabangId = $cabang?->id ?? 1;
            $contextName = $isOwner ? ($cabang?->nama_cabang ?? 'Cabang') : 'Soulmate Komputer';

            $stoksProduk = Stok::with('produk')
                ->where('cabang_id', $targetCabangId)
                ->where('is_aktif', true)
                ->whereHas('produk')
                ->whereColumn('jumlah', '<=', 'min_stok')
                ->get();

            $stoksSparepart = MasterServisStok::with('masterServis')
                ->where('cabang_id', $targetCabangId)
                ->where('is_aktif', true)
                ->whereHas('masterServis', fn ($q) => $q->where('jenis', 'sparepart')->where('is_aktif', true))
                ->whereColumn('jumlah', '<=', 'min_stok')
                ->get();

            $emptyProduk = $stoksProduk->where('jumlah', '<=', 0);
            $criticalProduk = $stoksProduk->where('jumlah', '>', 0);

            $emptySparepart = $stoksSparepart->where('jumlah', '<=', 0);
            $criticalSparepart = $stoksSparepart->where('jumlah', '>', 0);

            $this->emptyCount = $emptyProduk->count() + $emptySparepart->count();
            $this->criticalCount = $criticalProduk->count() + $criticalSparepart->count();

            // [TEMP HIDE] Peringatan Modal Awal setelah jam 10:00 untuk cabang ini
            // if ($isPast10Am) {
            //     $hasModal = ModalAwal::where('cabang_id', $targetCabangId)
            //         ->whereDate('tanggal', $todayDate)
            //         ->exists();
            //
            //     if (! $hasModal) {
            //         $this->modalAwalAlertCount = 1;
            //         if ($cabang) {
            //             $missingModalCabangs = collect([$cabang]);
            //         }
            //     }
            // }

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

        $this->totalAlerts = $this->emptyCount + $this->criticalCount + $this->modalAwalAlertCount;

        return view('livewire.components.notification-bell', [
            'emptyCount' => $this->emptyCount,
            'criticalCount' => $this->criticalCount,
            'modalAwalAlertCount' => $this->modalAwalAlertCount,
            'missingModalCabangs' => $missingModalCabangs,
            'totalAlerts' => $this->totalAlerts,
            'alertItems' => $alertItems,
            'contextName' => $contextName,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ]);
    }
}

