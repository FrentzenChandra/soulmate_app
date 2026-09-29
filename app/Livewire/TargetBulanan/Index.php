<?php

namespace App\Livewire\TargetBulanan;

use App\Models\Cabang;
use App\Models\Pegawai;
use App\Models\TargetDivisi;
use App\Models\TargetPegawai;
use App\Models\TransaksiFull;
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

    // State Modal Edit Target & Realisasi Per Cell Divisi
    public bool $modalEditCellDivisiOpen = false;
    public string $editDivisiKey = 'laptop';
    public string $editDivisiName = 'Laptop';
    public int $editDivisiBulan = 1;
    public int $formTargetSyaratDivisi = 0;
    public int $formRealisasiDivisi = 0;

    // State Modal Edit Target Per Cell Pegawai
    public bool $modalEditCellUserOpen = false;
    public ?int $editUserId = null;
    public string $editUserName = '';
    public int $editUserBulan = 1;
    public int $formTargetSyaratUser = 0;

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
    // MODAL & ACTION: EDIT TARGET & REALISASI PER CELL (DIVISI)
    // =========================================================================

    public function openEditCellDivisi(string $divisiKey, int $bulan): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $this->editDivisiKey = $divisiKey;
        $this->editDivisiName = match (strtolower($divisiKey)) {
            'laptop' => 'Laptop',
            'printer' => 'Printer',
            'komputer' => 'Komputer',
            'admin' => 'Admin',
            default => ucfirst($divisiKey),
        };
        $this->editDivisiBulan = $bulan;
        $cabangId = $this->getTargetCabangId();

        $targetDivisi = TargetDivisi::where('cabang_id', $cabangId)
            ->where('tahun', $this->filterTahun)
            ->where('divisi', $divisiKey)
            ->first();

        $monthlyTargets = $targetDivisi?->monthly_targets ?? [];
        $monthlyRealisasi = $targetDivisi?->monthly_realisasi ?? [];

        $this->formTargetSyaratDivisi = (int) ($monthlyTargets[(string)$bulan] ?? 0);
        $this->formRealisasiDivisi = (int) ($monthlyRealisasi[(string)$bulan] ?? 0);

        $this->modalEditCellDivisiOpen = true;
    }

    public function simpanEditCellDivisi(): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $this->validate([
            'editDivisiKey' => 'required|string|in:laptop,printer,komputer,admin',
            'editDivisiBulan' => 'required|integer|min:1|max:12',
            'formTargetSyaratDivisi' => 'required|integer|min:0',
            'formRealisasiDivisi' => 'required|integer|min:0',
        ]);

        $cabangId = $this->getTargetCabangId();
        $userId = Auth::id();

        $targetDivisi = TargetDivisi::firstOrCreate(
            [
                'cabang_id' => $cabangId,
                'tahun' => $this->filterTahun,
                'divisi' => $this->editDivisiKey,
            ],
            [
                'target_bulanan' => 0,
                'created_by' => $userId,
            ]
        );

        $monthlyTargets = $targetDivisi->monthly_targets ?? [];
        $monthlyRealisasi = $targetDivisi->monthly_realisasi ?? [];

        $monthlyTargets[(string)$this->editDivisiBulan] = (int) $this->formTargetSyaratDivisi;
        $monthlyRealisasi[(string)$this->editDivisiBulan] = (int) $this->formRealisasiDivisi;

        $targetDivisi->update([
            'monthly_targets' => $monthlyTargets,
            'monthly_realisasi' => $monthlyRealisasi,
        ]);

        $this->modalEditCellDivisiOpen = false;
        $namaBulan = Carbon::createFromDate($this->filterTahun, $this->editDivisiBulan, 1)->translatedFormat('F');
        session()->flash('success', "Target & Realisasi Divisi {$this->editDivisiName} bulan {$namaBulan} {$this->filterTahun} berhasil diperbarui!");
    }

    // =========================================================================
    // MODAL & ACTION: EDIT TARGET PER CELL (PEGAWAI)
    // =========================================================================

    public function openEditCellPegawai(int $pegawaiId, int $bulan): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $pegawai = Pegawai::findOrFail($pegawaiId);
        $this->editUserId = $pegawai->id;
        $this->editUserName = $pegawai->nama;
        $this->editUserBulan = $bulan;
        $cabangId = $this->getTargetCabangId();

        $targetPegawai = TargetPegawai::where('cabang_id', $cabangId)
            ->where('tahun', $this->filterTahun)
            ->where('pegawai_id', $pegawaiId)
            ->first();

        $monthlyTargets = $targetPegawai?->monthly_targets ?? [];
        $this->formTargetSyaratUser = (int) ($monthlyTargets[(string)$bulan] ?? 0);

        $this->modalEditCellUserOpen = true;
    }

    public function openEditCellUser(int $userId, int $bulan): void
    {
        $this->openEditCellPegawai($userId, $bulan);
    }

    public function simpanEditCellUser(): void
    {
        $this->simpanEditCellPegawai();
    }

    public function simpanEditCellPegawai(): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $this->validate([
            'editUserId' => 'required|exists:pegawais,id',
            'editUserBulan' => 'required|integer|min:1|max:12',
            'formTargetSyaratUser' => 'required|integer|min:0',
        ]);

        $cabangId = $this->getTargetCabangId();
        $creatorId = Auth::id();

        $targetPegawai = TargetPegawai::firstOrCreate(
            [
                'cabang_id' => $cabangId,
                'tahun' => $this->filterTahun,
                'pegawai_id' => $this->editUserId,
            ],
            [
                'target_bulanan' => 0,
                'created_by' => $creatorId,
            ]
        );

        $monthlyTargets = $targetPegawai->monthly_targets ?? [];
        $monthlyTargets[(string)$this->editUserBulan] = (int) $this->formTargetSyaratUser;
        $targetPegawai->update([
            'monthly_targets' => $monthlyTargets,
        ]);

        $this->modalEditCellUserOpen = false;
        $namaBulan = Carbon::createFromDate($this->filterTahun, $this->editUserBulan, 1)->translatedFormat('F');
        session()->flash('success', "Target Pegawai {$this->editUserName} bulan {$namaBulan} {$this->filterTahun} berhasil diperbarui!");
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

        $trxList = $trxQuery->get(['id', 'tipe_transaksi', 'kategori_perangkat', 'pegawai_id', 'tanggal']);

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
                'total_target_year' => $totalYtdTarget,
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
            'total_target_year' => $grandTotalDivisiTarget,
            'ytd_percent' => $grandDivisiPercent,
        ];

        // 4. Query & Bangun Matriks Data Individu (Pegawai - Realisasi Riil dari Transaksi FULL)
        $pegawais = Pegawai::query()
            ->where('is_aktif', true)
            ->when(! $isHQ && $user->cabang_id, fn ($q) => $q->where('cabang_id', $user->cabang_id))
            ->when($isHQ && $this->cabangFilter !== 'semua' && ! empty($this->cabangFilter), fn ($q) => $q->where('cabang_id', (int) $this->cabangFilter))
            ->orderBy('nama')
            ->get();

        $targetPegawaiModels = TargetPegawai::where('tahun', $tahun)
            ->when(! $isHQ || ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)), function ($q) use ($cabangId, $isHQ) {
                $targetCab = (! $isHQ) ? $cabangId : (int) $this->cabangFilter;
                $q->where('cabang_id', $targetCab);
            })
            ->get();

        $userMatrix = [];
        foreach ($pegawais as $p) {
            $targetPegawaiRow = $targetPegawaiModels->firstWhere('pegawai_id', $p->id);
            $monthlyTargets = $targetPegawaiRow?->monthly_targets ?? [];

            $monthlyData = [];
            $totalYtdRealisasi = 0;
            $totalYtdTarget = 0;

            for ($m = 1; $m <= 12; $m++) {
                // Realisasi TETAP OTOMATIS DARI TRANSAKSI FULL
                $count = $trxList->filter(function ($t) use ($m, $p) {
                    return (int) $t->tanggal->format('n') === $m && $t->pegawai_id === $p->id;
                })->count();

                $hasTarget = isset($monthlyTargets[(string)$m]);
                $targetSyarat = $hasTarget ? (int) $monthlyTargets[(string)$m] : 0;
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
                'pegawai' => $p,
                'user' => (object)[
                    'id' => $p->id,
                    'name' => $p->nama,
                    'role' => $p->role,
                ],
                'target_bulanan' => 0,
                'months' => $monthlyData,
                'total_ytd' => $totalYtdRealisasi,
                'total_target_ytd' => $totalYtdTarget,
                'total_target_year' => $totalYtdTarget,
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
            'total_target_year' => $grandTotalUserTarget,
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
