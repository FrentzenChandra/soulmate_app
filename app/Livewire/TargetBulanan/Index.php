<?php

namespace App\Livewire\TargetBulanan;

use App\Models\Cabang;
use App\Models\TargetDivisi;
use App\Models\TargetUser;
use App\Models\TransaksiFull;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Target Bulanan - Performance Board - Soulmate Komputer')]
class Index extends Component
{
    #[Url(except: '')]
    public int $filterTahun;

    #[Url(except: 'semua')]
    public string $cabangFilter = 'semua';

    // State Modal Input Target Divisi
    public bool $modalDivisiOpen = false;
    public int $formTahunDivisi;
    public array $targetDivisiForm = [
        'laptop' => 0,
        'printer' => 0,
        'komputer' => 0,
        'admin' => 0,
    ];

    // State Modal Edit Target & Realisasi Per Bulan (Divisi dari Header Bulan)
    public bool $modalEditBulanDivisiOpen = false;
    public int $editBulanDivisi = 1;
    public int $formTargetSyaratDivisi = 0;
    public int $formRealisasiLaptop = 0;
    public int $formRealisasiPrinter = 0;
    public int $formRealisasiKomputer = 0;
    public int $formRealisasiAdmin = 0;

    // State Modal Edit Target Staf Per Bulan (Staf dari Header Bulan)
    public bool $modalEditBulanUserOpen = false;
    public int $editBulanUser = 1;
    public int $formTargetSyaratUserGlobal = 0;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole('Superadmin'))) {
            abort(403, 'Akses Target Bulanan dibatasi hanya untuk Owner dan Superadmin.');
        }

        $this->filterTahun = (int) now()->format('Y');
    }

    public function getCanManageTargetProperty(): bool
    {
        $user = Auth::user();
        return $user && ($user->isOwner() || $user->hasRole('Superadmin'));
    }

    private function getTargetCabangId(): int
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');

        if ($isOwner) {
            if ($activeBranchSession !== 'hq' && ! empty($activeBranchSession)) {
                return (int) $activeBranchSession;
            }
            if ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)) {
                return (int) $this->cabangFilter;
            }
            return 1; // Default Cabang Pusat
        }

        return (int) ($user->cabang_id ?: 1);
    }

    // =========================================================================
    // MODAL & ACTION: EDIT TARGET & REALISASI PER BULAN (DIVISI - 5 INPUT)
    // =========================================================================

    public function openEditBulanDivisi(int $bulan): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $this->editBulanDivisi = $bulan;
        $cabangId = $this->getTargetCabangId();

        $divisis = TargetDivisi::where('cabang_id', $cabangId)
            ->where('tahun', $this->filterTahun)
            ->get()
            ->keyBy('divisi');

        $laptop = $divisis->get('laptop');
        $printer = $divisis->get('printer');
        $komputer = $divisis->get('komputer');
        $admin = $divisis->get('admin');

        $this->formTargetSyaratDivisi = (int) ($laptop?->monthly_targets[(string)$bulan] ?? $laptop?->target_bulanan ?? 0);
        $this->formRealisasiLaptop = (int) ($laptop?->monthly_realisasi[(string)$bulan] ?? 0);
        $this->formRealisasiPrinter = (int) ($printer?->monthly_realisasi[(string)$bulan] ?? 0);
        $this->formRealisasiKomputer = (int) ($komputer?->monthly_realisasi[(string)$bulan] ?? 0);
        $this->formRealisasiAdmin = (int) ($admin?->monthly_realisasi[(string)$bulan] ?? 0);

        $this->modalEditBulanDivisiOpen = true;
    }

    public function simpanEditBulanDivisi(): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $this->validate([
            'editBulanDivisi' => 'required|integer|min:1|max:12',
            'formTargetSyaratDivisi' => 'required|integer|min:0',
            'formRealisasiLaptop' => 'required|integer|min:0',
            'formRealisasiPrinter' => 'required|integer|min:0',
            'formRealisasiKomputer' => 'required|integer|min:0',
            'formRealisasiAdmin' => 'required|integer|min:0',
        ]);

        $cabangId = $this->getTargetCabangId();
        $userId = Auth::id();

        $realisasiMap = [
            'laptop' => (int) $this->formRealisasiLaptop,
            'printer' => (int) $this->formRealisasiPrinter,
            'komputer' => (int) $this->formRealisasiKomputer,
            'admin' => (int) $this->formRealisasiAdmin,
        ];

        foreach ($realisasiMap as $divKey => $realisasiVal) {
            $targetDivisi = TargetDivisi::firstOrCreate(
                [
                    'cabang_id' => $cabangId,
                    'tahun' => $this->filterTahun,
                    'divisi' => $divKey,
                ],
                [
                    'target_bulanan' => (int) $this->formTargetSyaratDivisi,
                    'created_by' => $userId,
                ]
            );

            $monthlyTargets = $targetDivisi->monthly_targets ?? [];
            $monthlyRealisasi = $targetDivisi->monthly_realisasi ?? [];

            $monthlyTargets[(string)$this->editBulanDivisi] = (int) $this->formTargetSyaratDivisi;
            $monthlyRealisasi[(string)$this->editBulanDivisi] = $realisasiVal;

            $targetDivisi->update([
                'monthly_targets' => $monthlyTargets,
                'monthly_realisasi' => $monthlyRealisasi,
            ]);
        }

        $this->modalEditBulanDivisiOpen = false;
        $namaBulan = Carbon::createFromDate($this->filterTahun, $this->editBulanDivisi, 1)->translatedFormat('F');
        session()->flash('success', "Target & Realisasi Divisi bulan {$namaBulan} {$this->filterTahun} berhasil diperbarui!");
    }

    // =========================================================================
    // MODAL & ACTION: EDIT TARGET STAF PER BULAN (1 INPUT TARGET SYARAT SERAGAM)
    // =========================================================================

    public function openEditBulanUser(int $bulan): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $this->editBulanUser = $bulan;
        $cabangId = $this->getTargetCabangId();

        $targetUser = TargetUser::where('cabang_id', $cabangId)
            ->where('tahun', $this->filterTahun)
            ->first();

        $monthlyTargets = $targetUser?->monthly_targets ?? [];
        $this->formTargetSyaratUserGlobal = isset($monthlyTargets[(string)$bulan])
            ? (int) $monthlyTargets[(string)$bulan]
            : (int) ($targetUser?->target_bulanan ?? 0);

        $this->modalEditBulanUserOpen = true;
    }

    public function simpanEditBulanUser(): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $this->validate([
            'editBulanUser' => 'required|integer|min:1|max:12',
            'formTargetSyaratUserGlobal' => 'required|integer|min:0',
        ]);

        $cabangId = $this->getTargetCabangId();
        $creatorId = Auth::id();

        $users = User::query()
            ->where('status', 'aktif')
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->get();

        foreach ($users as $u) {
            $targetUser = TargetUser::firstOrCreate(
                [
                    'cabang_id' => $cabangId,
                    'tahun' => $this->filterTahun,
                    'user_id' => $u->id,
                ],
                [
                    'target_bulanan' => (int) $this->formTargetSyaratUserGlobal,
                    'created_by' => $creatorId,
                ]
            );

            $monthlyTargets = $targetUser->monthly_targets ?? [];
            $monthlyTargets[(string)$this->editBulanUser] = (int) $this->formTargetSyaratUserGlobal;
            $targetUser->update([
                'monthly_targets' => $monthlyTargets,
            ]);
        }

        $this->modalEditBulanUserOpen = false;
        $namaBulan = Carbon::createFromDate($this->filterTahun, $this->editBulanUser, 1)->translatedFormat('F');
        session()->flash('success', "Target Syarat Staf bulan {$namaBulan} {$this->filterTahun} berhasil diperbarui!");
    }

    // =========================================================================
    // RENDER & MATRIX DATA BUILDER
    // =========================================================================

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $cabangs = $isHQ ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();
        $cabangId = $this->getTargetCabangId();

        $tahun = $this->filterTahun;

        // 1. Query Realisasi dari Transaksi FULL
        $trxQuery = TransaksiFull::query()
            ->whereYear('tanggal', $tahun)
            ->where('status', '!=', 'Batal');

        if (! $isHQ) {
            $trxQuery->where('cabang_id', $cabangId);
        } elseif ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)) {
            $trxQuery->where('cabang_id', (int) $this->cabangFilter);
        }

        $trxList = $trxQuery->get(['id', 'tipe_transaksi', 'kategori_perangkat', 'user_id', 'tanggal']);

        // 2. Query Target Divisi Models
        $targetDivisiModels = TargetDivisi::where('tahun', $tahun)
            ->when(! $isHQ || ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)), function ($q) use ($cabangId, $isHQ) {
                $targetCab = (! $isHQ) ? $cabangId : (int) $this->cabangFilter;
                $q->where('cabang_id', $targetCab);
            })
            ->get()
            ->keyBy('divisi');

        // 3. Bangun Matriks Data Divisi (100% Granular Manual Per Bulan dari Header)
        $divisiDefs = [
            'laptop' => ['name' => 'LAPTOP', 'icon' => 'laptop'],
            'printer' => ['name' => 'PRINTER', 'icon' => 'printer'],
            'komputer' => ['name' => 'KOMPUTER', 'icon' => 'komputer'],
            'admin' => ['name' => 'ADMIN', 'icon' => 'admin'],
        ];

        $divisiMatrix = [];
        foreach ($divisiDefs as $key => $meta) {
            $targetDivisiRow = $targetDivisiModels->get($key);
            $defaultTarget = (int) ($targetDivisiRow?->target_bulanan ?? 0);
            $monthlyTargets = $targetDivisiRow?->monthly_targets ?? [];
            $monthlyRealisasi = $targetDivisiRow?->monthly_realisasi ?? [];

            $monthlyData = [];
            $totalYtdRealisasi = 0;
            $totalYtdTarget = 0;

            for ($m = 1; $m <= 12; $m++) {
                $hasTarget = isset($monthlyTargets[(string)$m]);
                $hasRealisasi = isset($monthlyRealisasi[(string)$m]);
                $hasData = $hasTarget || $hasRealisasi;

                $targetSyarat = $hasTarget ? (int) $monthlyTargets[(string)$m] : 0;
                $count = $hasRealisasi ? (int) $monthlyRealisasi[(string)$m] : 0;

                $percent = ($hasData && $targetSyarat > 0) ? (int) round(($count / $targetSyarat) * 100) : 0;

                // Status classification
                $status = 'belum';
                if (! $hasData) {
                    $status = 'empty';
                } elseif ($percent >= 150) {
                    $status = 'over';
                } elseif ($percent >= 100) {
                    $status = 'tercapai';
                }

                $monthlyData[$m] = [
                    'count' => $count,
                    'target' => $targetSyarat,
                    'percent' => $percent,
                    'status' => $status,
                    'has_data' => $hasData,
                ];

                if ($hasData) {
                    $totalYtdRealisasi += $count;
                    $totalYtdTarget += $targetSyarat;
                }
            }

            $ytdPercent = $totalYtdTarget > 0 ? (int) round(($totalYtdRealisasi / $totalYtdTarget) * 100) : 0;

            $divisiMatrix[$key] = [
                'name' => $meta['name'],
                'icon' => $meta['icon'],
                'target_bulanan' => $defaultTarget,
                'months' => $monthlyData,
                'total_ytd' => $totalYtdRealisasi,
                'total_target_ytd' => $totalYtdTarget,
                'ytd_percent' => $ytdPercent,
            ];
        }

        // Summary Total Per Bulan Divisi
        $totalDivisiMonths = [];
        $grandTotalDivisiCount = 0;
        $grandTotalDivisiTarget = 0;

        for ($m = 1; $m <= 12; $m++) {
            $mCount = 0;
            $mTarget = 0;
            $hasAnyData = false;

            foreach ($divisiMatrix as $d) {
                $mCount += $d['months'][$m]['count'];
                $mTarget += $d['months'][$m]['target'];
                if ($d['months'][$m]['has_data']) {
                    $hasAnyData = true;
                }
            }

            $mPercent = ($hasAnyData && $mTarget > 0) ? (int) round(($mCount / $mTarget) * 100) : 0;
            $status = 'belum';
            if (! $hasAnyData) {
                $status = 'empty';
            } elseif ($mPercent >= 150) {
                $status = 'over';
            } elseif ($mPercent >= 100) {
                $status = 'tercapai';
            }

            $totalDivisiMonths[$m] = [
                'count' => $mCount,
                'target' => $mTarget,
                'percent' => $mPercent,
                'status' => $status,
                'has_data' => $hasAnyData,
            ];

            if ($hasAnyData) {
                $grandTotalDivisiCount += $mCount;
                $grandTotalDivisiTarget += $mTarget;
            }
        }

        $grandDivisiPercent = $grandTotalDivisiTarget > 0 ? (int) round(($grandTotalDivisiCount / $grandTotalDivisiTarget) * 100) : 0;

        $divisiSummary = [
            'months' => $totalDivisiMonths,
            'total_ytd' => $grandTotalDivisiCount,
            'total_target_ytd' => $grandTotalDivisiTarget,
            'ytd_percent' => $grandDivisiPercent,
        ];

        // 4. Query & Bangun Matriks Data Individu (Staf - Realisasi Riil dari Transaksi FULL)
        $users = User::query()
            ->with('roles')
            ->where('status', 'aktif')
            ->when(! $isHQ && $user->cabang_id, fn ($q) => $q->where('cabang_id', $user->cabang_id))
            ->when($isHQ && $this->cabangFilter !== 'semua' && ! empty($this->cabangFilter), fn ($q) => $q->where('cabang_id', (int) $this->cabangFilter))
            ->orderBy('name')
            ->get();

        $targetUserModels = TargetUser::where('tahun', $tahun)
            ->when(! $isHQ || ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)), function ($q) use ($cabangId, $isHQ) {
                $targetCab = (! $isHQ) ? $cabangId : (int) $this->cabangFilter;
                $q->where('cabang_id', $targetCab);
            })
            ->get();

        $masterTargetUser = $targetUserModels->firstWhere('user_id', null);
        $masterMonthlyTargets = $masterTargetUser?->monthly_targets ?? [];

        $userMatrix = [];
        foreach ($users as $u) {
            $targetUserRow = $targetUserModels->firstWhere('user_id', $u->id);
            $defaultTarget = (int) ($targetUserRow?->target_bulanan ?? $masterTargetUser?->target_bulanan ?? 0);
            $monthlyTargets = $targetUserRow?->monthly_targets ?? [];

            $monthlyData = [];
            $totalYtdRealisasi = 0;
            $totalYtdTarget = 0;

            for ($m = 1; $m <= 12; $m++) {
                // Realisasi TETAP OTOMATIS DARI TRANSAKSI FULL
                $count = $trxList->filter(function ($t) use ($m, $u) {
                    return (int) $t->tanggal->format('n') === $m && $t->user_id === $u->id;
                })->count();

                $targetSyarat = 0;
                $hasTarget = false;

                if (isset($masterMonthlyTargets[(string)$m])) {
                    $targetSyarat = (int) $masterMonthlyTargets[(string)$m];
                    $hasTarget = true;
                } elseif (isset($monthlyTargets[(string)$m])) {
                    $targetSyarat = (int) $monthlyTargets[(string)$m];
                    $hasTarget = true;
                } elseif ($defaultTarget > 0) {
                    $targetSyarat = $defaultTarget;
                    $hasTarget = true;
                }

                $hasData = $hasTarget;
                $percent = ($hasTarget && $targetSyarat > 0) ? (int) round(($count / $targetSyarat) * 100) : 0;

                // Status classification
                $status = 'belum';
                if (! $hasData) {
                    $status = 'empty';
                } elseif ($percent >= 150) {
                    $status = 'over';
                } elseif ($percent >= 100) {
                    $status = 'tercapai';
                }

                $monthlyData[$m] = [
                    'count' => $count,
                    'target' => $targetSyarat,
                    'percent' => $percent,
                    'status' => $status,
                    'has_data' => $hasData,
                ];

                if ($hasData || $count > 0) {
                    $totalYtdRealisasi += $count;
                    $totalYtdTarget += $targetSyarat;
                }
            }

            $ytdPercent = $totalYtdTarget > 0 ? (int) round(($totalYtdRealisasi / $totalYtdTarget) * 100) : 0;

            $userMatrix[] = [
                'user' => $u,
                'target_bulanan' => $defaultTarget,
                'months' => $monthlyData,
                'total_ytd' => $totalYtdRealisasi,
                'total_target_ytd' => $totalYtdTarget,
                'ytd_percent' => $ytdPercent,
            ];
        }

        // Summary Total Per Bulan Staf
        $totalUserMonths = [];
        $grandTotalUserCount = 0;
        $grandTotalUserTarget = 0;

        for ($m = 1; $m <= 12; $m++) {
            $mCount = 0;
            $mTarget = 0;
            $hasAnyData = false;

            foreach ($userMatrix as $u) {
                $mCount += $u['months'][$m]['count'];
                $mTarget += $u['months'][$m]['target'];
                if ($u['months'][$m]['has_data'] || $u['months'][$m]['count'] > 0) {
                    $hasAnyData = true;
                }
            }

            $mPercent = ($hasAnyData && $mTarget > 0) ? (int) round(($mCount / $mTarget) * 100) : 0;
            $status = 'belum';
            if (! $hasAnyData) {
                $status = 'empty';
            } elseif ($mPercent >= 150) {
                $status = 'over';
            } elseif ($mPercent >= 100) {
                $status = 'tercapai';
            }

            $totalUserMonths[$m] = [
                'count' => $mCount,
                'target' => $mTarget,
                'percent' => $mPercent,
                'status' => $status,
                'has_data' => $hasAnyData,
            ];

            if ($hasAnyData) {
                $grandTotalUserCount += $mCount;
                $grandTotalUserTarget += $mTarget;
            }
        }

        $grandUserPercent = $grandTotalUserTarget > 0 ? (int) round(($grandTotalUserCount / $grandTotalUserTarget) * 100) : 0;

        $userSummary = [
            'months' => $totalUserMonths,
            'total_ytd' => $grandTotalUserCount,
            'total_target_ytd' => $grandTotalUserTarget,
            'ytd_percent' => $grandUserPercent,
        ];

        return view('livewire.target-bulanan.index', [
            'isHQ' => $isHQ,
            'cabangs' => $cabangs,
            'divisiMatrix' => $divisiMatrix,
            'divisiSummary' => $divisiSummary,
            'userMatrix' => $userMatrix,
            'userSummary' => $userSummary,
            'canManageTarget' => $this->canManageTarget,
        ]);
    }
}
