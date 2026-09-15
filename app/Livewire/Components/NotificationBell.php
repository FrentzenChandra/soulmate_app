<?php

namespace App\Livewire\Components;

use App\Models\Cabang;
use App\Models\Stok;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $emptyCount = 0;

    public int $criticalCount = 0;

    public int $totalAlerts = 0;

    protected $listeners = ['stockUpdated' => '$refresh', 'refreshNotification' => '$refresh'];

    public function render()
    {
        $user = Auth::user();
        if (! $user) {
            $this->emptyCount = 0;
            $this->criticalCount = 0;
            $this->totalAlerts = 0;

            return view('livewire.components.notification-bell', [
                'emptyCount' => 0,
                'criticalCount' => 0,
                'totalAlerts' => 0,
                'alertItems' => collect(),
                'contextName' => '',
                'isHQ' => false,
            ]);
        }

        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && $activeBranchSession === 'hq';

        $this->emptyCount = 0;
        $this->criticalCount = 0;
        $alertItems = collect();
        $contextName = 'Kantor Pusat (HQ)';

        if ($isHQ) {
            // Mode HQ: Memantau stok produk AKTIF di semua cabang yang jumlahnya <= min_stok
            $stoks = Stok::with(['produk', 'cabang'])
                ->where('is_aktif', true)
                ->whereHas('produk')
                ->whereHas('cabang', fn ($q) => $q->where('status', 'aktif'))
                ->whereColumn('jumlah', '<=', 'min_stok')
                ->orderBy('jumlah', 'asc')
                ->get();

            $this->emptyCount = $stoks->where('jumlah', '<=', 0)->count();
            $this->criticalCount = $stoks->where('jumlah', '>', 0)->count();
            $alertItems = $stoks->take(50)->map(function ($s) {
                return (object) [
                    'produk_id' => $s->produk_id,
                    'nama_produk' => $s->produk?->nama_produk ?? 'Produk',
                    'barcode' => $s->produk?->barcode,
                    'foto_url' => $s->produk?->foto_url,
                    'cabang_nama' => $s->cabang?->kode_cabang ?? 'CB',
                    'jumlah' => $s->jumlah,
                    'min_stok' => $s->min_stok,
                ];
            });
            $contextName = 'Semua Cabang (HQ)';
        } else {
            // Mode Cabang: Hanya memantau stok produk AKTIF di cabang ini saja
            $targetCabangId = $isOwner ? (int) $activeBranchSession : $user->cabang_id;
            $cabang = Cabang::find($targetCabangId) ?? Cabang::first();
            $targetCabangId = $cabang?->id ?? 1;
            $contextName = $isOwner ? ($cabang?->nama_cabang ?? 'Cabang') : 'Soulmate Komputer';

            $stoks = Stok::with('produk')
                ->where('cabang_id', $targetCabangId)
                ->where('is_aktif', true)
                ->whereHas('produk')
                ->whereColumn('jumlah', '<=', 'min_stok')
                ->orderBy('jumlah', 'asc')
                ->get();

            $this->emptyCount = $stoks->where('jumlah', '<=', 0)->count();
            $this->criticalCount = $stoks->where('jumlah', '>', 0)->count();

            $alertItems = $stoks->take(50)->map(function ($s) use ($cabang) {
                return (object) [
                    'produk_id' => $s->produk_id,
                    'nama_produk' => $s->produk?->nama_produk ?? 'Produk',
                    'barcode' => $s->produk?->barcode,
                    'foto_url' => $s->produk?->foto_url,
                    'cabang_nama' => $cabang?->kode_cabang ?? 'CB',
                    'jumlah' => $s->jumlah,
                    'min_stok' => $s->min_stok,
                ];
            });
        }

        $this->totalAlerts = $this->emptyCount + $this->criticalCount;

        return view('livewire.components.notification-bell', [
            'emptyCount' => $this->emptyCount,
            'criticalCount' => $this->criticalCount,
            'totalAlerts' => $this->totalAlerts,
            'alertItems' => $alertItems,
            'contextName' => $contextName,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ]);
    }
}
