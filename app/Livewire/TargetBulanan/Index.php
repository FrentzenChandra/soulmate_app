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

    // State Modal Input Target Staf / User
    public bool $modalUserOpen = false;
    public int $formTahunUser;
    public array $targetUserForm = [];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole('Superadmin'))) {
            abort(403, 'Akses Target Bulanan dibatasi hanya untuk Owner dan Superadmin.');
        }

        $this->filterTahun = (int) now()->format('Y');
        $this->formTahunDivisi = $this->filterTahun;
        $this->formTahunUser = $this->filterTahun;
    }

    public function updatedFilterTahun(): void
    {
        $this->formTahunDivisi = $this->filterTahun;
        $this->formTahunUser = $this->filterTahun;
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
    // MODAL & ACTION: TARGET DIVISI
    // =========================================================================

    public function openModalDivisi(): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $cabangId = $this->getTargetCabangId();
        $this->formTahunDivisi = $this->filterTahun;

        $existing = TargetDivisi::where('cabang_id', $cabangId)
            ->where('tahun', $this->formTahunDivisi)
            ->pluck('target_bulanan', 'divisi')
            ->toArray();

        $this->targetDivisiForm = [
            'laptop' => (int) ($existing['laptop'] ?? 0),
            'printer' => (int) ($existing['printer'] ?? 0),
            'komputer' => (int) ($existing['komputer'] ?? 0),
            'admin' => (int) ($existing['admin'] ?? 0),
        ];

        $this->modalDivisiOpen = true;
    }

    public function updatedFormTahunDivisi(): void
    {
        $cabangId = $this->getTargetCabangId();
        $existing = TargetDivisi::where('cabang_id', $cabangId)
            ->where('tahun', $this->formTahunDivisi)
            ->pluck('target_bulanan', 'divisi')
            ->toArray();

        $this->targetDivisiForm = [
            'laptop' => (int) ($existing['laptop'] ?? 0),
            'printer' => (int) ($existing['printer'] ?? 0),
            'komputer' => (int) ($existing['komputer'] ?? 0),
            'admin' => (int) ($existing['admin'] ?? 0),
        ];
    }

    public function simpanTargetDivisi(): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $this->validate([
            'formTahunDivisi' => 'required|integer|min:2020|max:2099',
            'targetDivisiForm.laptop' => 'required|integer|min:0',
            'targetDivisiForm.printer' => 'required|integer|min:0',
            'targetDivisiForm.komputer' => 'required|integer|min:0',
            'targetDivisiForm.admin' => 'required|integer|min:0',
        ]);

        $cabangId = $this->getTargetCabangId();
        $userId = Auth::id();

        foreach (['laptop', 'printer', 'komputer', 'admin'] as $div) {
            TargetDivisi::updateOrCreate(
                [
                    'cabang_id' => $cabangId,
                    'tahun' => $this->formTahunDivisi,
                    'divisi' => $div,
                ],
                [
                    'target_bulanan' => (int) ($this->targetDivisiForm[$div] ?? 0),
                    'created_by' => $userId,
                ]
            );
        }

        $this->modalDivisiOpen = false;
        session()->flash('success', "Target Divisi tahun {$this->formTahunDivisi} berhasil disimpan!");
    }

    // =========================================================================
    // MODAL & ACTION: TARGET INDIVIDU (STAF)
    // =========================================================================

    public function openModalUser(): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $cabangId = $this->getTargetCabangId();
        $this->formTahunUser = $this->filterTahun;
        $this->loadTargetUserFormData($cabangId, $this->formTahunUser);

        $this->modalUserOpen = true;
    }

    public function updatedFormTahunUser(): void
    {
        $cabangId = $this->getTargetCabangId();
        $this->loadTargetUserFormData($cabangId, $this->formTahunUser);
    }

    private function loadTargetUserFormData(int $cabangId, int $tahun): void
    {
        $users = User::query()
            ->with('roles')
            ->where('status', 'aktif')
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->orderBy('name')
            ->get();

        $existing = TargetUser::where('cabang_id', $cabangId)
            ->where('tahun', $tahun)
            ->pluck('target_bulanan', 'user_id')
            ->toArray();

        $this->targetUserForm = [];
        foreach ($users as $u) {
            $this->targetUserForm[] = [
                'user_id' => $u->id,
                'name' => $u->name,
                'role' => $u->roles->first()?->name ?? 'Staf',
                'target' => (int) ($existing[$u->id] ?? 0),
            ];
        }
    }

    public function simpanTargetUser(): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $this->validate([
            'formTahunUser' => 'required|integer|min:2020|max:2099',
            'targetUserForm.*.user_id' => 'required|exists:users,id',
            'targetUserForm.*.target' => 'required|integer|min:0',
        ]);

        $cabangId = $this->getTargetCabangId();
        $userId = Auth::id();

        foreach ($this->targetUserForm as $row) {
            TargetUser::updateOrCreate(
                [
                    'cabang_id' => $cabangId,
                    'user_id' => $row['user_id'],
                    'tahun' => $this->formTahunUser,
                ],
                [
                    'target_bulanan' => (int) ($row['target'] ?? 0),
                    'created_by' => $userId,
                ]
            );
        }

        $this->modalUserOpen = false;
        session()->flash('success', "Target Staf tahun {$this->formTahunUser} berhasil disimpan!");
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
        $currentYear = (int) now()->format('Y');
        $currentMonth = (int) now()->format('n');

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

        // 2. Query Target Divisi
        $targetDivisiMap = TargetDivisi::where('tahun', $tahun)
            ->when(! $isHQ || ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)), function ($q) use ($cabangId, $isHQ) {
                $targetCab = (! $isHQ) ? $cabangId : (int) $this->cabangFilter;
                $q->where('cabang_id', $targetCab);
            })
            ->get()
            ->groupBy('divisi')
            ->map(fn ($g) => $g->sum('target_bulanan'))
            ->toArray();

        // 3. Bangun Matriks Data Divisi
        $divisiDefs = [
            'laptop' => ['name' => 'LAPTOP', 'icon' => 'laptop'],
            'printer' => ['name' => 'PRINTER', 'icon' => 'printer'],
            'komputer' => ['name' => 'KOMPUTER', 'icon' => 'komputer'],
            'admin' => ['name' => 'ADMIN', 'icon' => 'admin'],
        ];

        $divisiMatrix = [];
        foreach ($divisiDefs as $key => $meta) {
            $targetBulanan = (int) ($targetDivisiMap[$key] ?? 0);
            $monthlyData = [];
            $totalYtdRealisasi = 0;
            $totalYtdTarget = 0;

            for ($m = 1; $m <= 12; $m++) {
                $count = $trxList->filter(function ($t) use ($m, $key) {
                    if ((int) $t->tanggal->format('n') !== $m) {
                        return false;
                    }
                    if ($key === 'admin') {
                        return $t->tipe_transaksi === 'penjualan';
                    }
                    return $t->tipe_transaksi === 'servis' && strtolower($t->kategori_perangkat ?? '') === $key;
                })->count();

                $isFuture = ($tahun > $currentYear) || ($tahun === $currentYear && $m > $currentMonth && $count === 0);
                $percent = $targetBulanan > 0 ? (int) round(($count / $targetBulanan) * 100) : 0;

                // Status classification
                $status = 'belum';
                if ($isFuture) {
                    $status = 'future';
                } elseif ($percent >= 150) {
                    $status = 'over';
                } elseif ($percent >= 100) {
                    $status = 'tercapai';
                }

                $monthlyData[$m] = [
                    'count' => $count,
                    'percent' => $percent,
                    'status' => $status,
                    'is_future' => $isFuture,
                ];

                if (! $isFuture) {
                    $totalYtdRealisasi += $count;
                    $totalYtdTarget += $targetBulanan;
                }
            }

            $ytdPercent = $totalYtdTarget > 0 ? (int) round(($totalYtdRealisasi / $totalYtdTarget) * 100) : 0;

            $divisiMatrix[$key] = [
                'name' => $meta['name'],
                'icon' => $meta['icon'],
                'target_bulanan' => $targetBulanan,
                'months' => $monthlyData,
                'total_ytd' => $totalYtdRealisasi,
                'ytd_percent' => $ytdPercent,
            ];
        }

        // 4. Query & Bangun Matriks Data Individu (Staf)
        $users = User::query()
            ->with('roles')
            ->where('status', 'aktif')
            ->when(! $isHQ && $user->cabang_id, fn ($q) => $q->where('cabang_id', $user->cabang_id))
            ->when($isHQ && $this->cabangFilter !== 'semua' && ! empty($this->cabangFilter), fn ($q) => $q->where('cabang_id', (int) $this->cabangFilter))
            ->orderBy('name')
            ->get();

        $targetUserMap = TargetUser::where('tahun', $tahun)
            ->whereIn('user_id', $users->pluck('id'))
            ->pluck('target_bulanan', 'user_id')
            ->toArray();

        $userMatrix = [];
        foreach ($users as $u) {
            $targetBulanan = (int) ($targetUserMap[$u->id] ?? 0);
            $monthlyData = [];
            $totalYtdRealisasi = 0;
            $totalYtdTarget = 0;

            for ($m = 1; $m <= 12; $m++) {
                $count = $trxList->filter(function ($t) use ($m, $u) {
                    return (int) $t->tanggal->format('n') === $m && $t->user_id === $u->id;
                })->count();

                $isFuture = ($tahun > $currentYear) || ($tahun === $currentYear && $m > $currentMonth && $count === 0);
                $percent = $targetBulanan > 0 ? (int) round(($count / $targetBulanan) * 100) : 0;

                $status = 'belum';
                if ($isFuture) {
                    $status = 'future';
                } elseif ($percent >= 150) {
                    $status = 'over';
                } elseif ($percent >= 100) {
                    $status = 'tercapai';
                }

                $monthlyData[$m] = [
                    'count' => $count,
                    'percent' => $percent,
                    'status' => $status,
                    'is_future' => $isFuture,
                ];

                if (! $isFuture) {
                    $totalYtdRealisasi += $count;
                    $totalYtdTarget += $targetBulanan;
                }
            }

            $ytdPercent = $totalYtdTarget > 0 ? (int) round(($totalYtdRealisasi / $totalYtdTarget) * 100) : 0;

            $userMatrix[] = [
                'user' => $u,
                'target_bulanan' => $targetBulanan,
                'months' => $monthlyData,
                'total_ytd' => $totalYtdRealisasi,
                'ytd_percent' => $ytdPercent,
            ];
        }

        return view('livewire.target-bulanan.index', [
            'isHQ' => $isHQ,
            'cabangs' => $cabangs,
            'divisiMatrix' => $divisiMatrix,
            'userMatrix' => $userMatrix,
            'canManageTarget' => $this->canManageTarget,
        ]);
    }
}
