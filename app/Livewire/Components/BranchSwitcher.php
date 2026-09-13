<?php

namespace App\Livewire\Components;

use App\Models\Cabang;
use Livewire\Component;

class BranchSwitcher extends Component
{
    public function switchBranch($cabangId = null)
    {
        if (! auth()->user()->isOwner()) {
            return;
        }

        if ($cabangId && $cabangId !== 'hq') {
            $cabang = Cabang::find($cabangId);
            if ($cabang) {
                session(['active_branch_id' => $cabang->id]);
            }
        } else {
            session(['active_branch_id' => 'hq']);
        }

        return redirect(request()->header('Referer') ?? route('dashboard'));
    }

    public function render()
    {
        $user = auth()->user();
        $isOwner = $user ? $user->isOwner() : false;
        $activeBranchId = session('active_branch_id', 'hq');

        $activeCabang = null;
        if ($isOwner) {
            if ($activeBranchId && $activeBranchId !== 'hq') {
                $activeCabang = Cabang::find($activeBranchId);
            }
        } else {
            $activeCabang = $user?->cabang;
        }

        $allCabangs = $isOwner ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();

        return view('livewire.components.branch-switcher', [
            'isOwner' => $isOwner,
            'activeBranchId' => $activeBranchId,
            'activeCabang' => $activeCabang,
            'allCabangs' => $allCabangs,
        ]);
    }
}
