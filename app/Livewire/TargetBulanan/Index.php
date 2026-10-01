<?php

namespace App\Livewire\TargetBulanan;

use App\Models\Cabang;
use App\Models\Pegawai;
use App\Models\TargetDivisi;
use App\Models\TransaksiFull;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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

    // State Modal Atur Target Tahunan Divisi (5 Field: Tahun, Laptop, Printer, Komputer, Admin)
    public bool $modalSetTargetTahunanDivisiOpen = false;
    public int $formTahunTargetDivisi = 2026;
    public int $formTargetTahunanLaptop = 0;
    public int $formTargetTahunanPrinter = 0;
    public int $formTargetTahunanKomputer = 0;
    public int $formTargetTahunanAdmin = 0;

    // State Modal Edit Realisasi Per Cell Divisi
    public bool $modalEditCellDivisiOpen = false;
    public string $editDivisiKey = 'laptop';
    public string $editDivisiName = 'Laptop';
    public int $editDivisiBulan = 1;
    public int $targetSyaratBulananInfo = 0;
    public int $formRealisasiDivisi = 0;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole('Superadmin'))) {
            abort(403, 'Akses Target Bulanan dibatasi hanya untuk Owner dan Superadmin.');
        }

        $this->filterTahun = (int) now()->format('Y');
        $this->formTahunTargetDivisi = $this->filterTahun;
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
    // MODAL & ACTION: ATUR TARGET TAHUNAN DIVISI (5-IN-1: TAHUN + 4 DIVISI)
    // =========================================================================

    public function openSetTargetTahunanDivisi(): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $this->formTahunTargetDivisi = $this->filterTahun;
        $this->loadTargetTahunanDivisiFields();
        $this->modalSetTargetTahunanDivisiOpen = true;
    }

    public function updatedFormTahunTargetDivisi(): void
    {
        $this->loadTargetTahunanDivisiFields();
    }

    private function loadTargetTahunanDivisiFields(): void
    {
        $cabangId = $this->getTargetCabangId();
        $targets = TargetDivisi::where('cabang_id', $cabangId)
            ->where('tahun', $this->formTahunTargetDivisi)
            ->get()
            ->keyBy('divisi');

        $divisis = ['laptop', 'printer', 'komputer', 'admin'];
        foreach ($divisis as $div) {
            $row = $targets->get($div);
            $val = 0;
            if ($row) {
                $val = (int) ($row->target_bulanan * 12);
            }
            $prop = 'formTargetTahunan' . ucfirst($div);
            $this->{$prop} = $val;
        }
    }

    public function simpanTargetTahunanDivisi(): void
    {
        if (! $this->canManageTarget) {
            abort(403, 'Hanya Owner dan Superadmin yang diizinkan mengatur target.');
        }

        $this->validate([
            'formTahunTargetDivisi' => 'required|integer|min:2024|max:2099',
            'formTargetTahunanLaptop' => 'required|integer|min:0',
            'formTargetTahunanPrinter' => 'required|integer|min:0',
            'formTargetTahunanKomputer' => 'required|integer|min:0',
            'formTargetTahunanAdmin' => 'required|integer|min:0',
        ]);

        $cabangId = $this->getTargetCabangId();
        $creatorId = Auth::id();
        $tahun = $this->formTahunTargetDivisi;

        $divisis = [
            'laptop' => $this->formTargetTahunanLaptop,
            'printer' => $this->formTargetTahunanPrinter,
            'komputer' => $this->formTargetTahunanKomputer,
            'admin' => $this->formTargetTahunanAdmin,
        ];

        foreach ($divisis as $div => $targetTahunan) {
            $targetBulanan = (int) round($targetTahunan / 12);

            $targetDivisi = TargetDivisi::firstOrCreate(
                [
                    'cabang_id' => $cabangId,
                    'tahun' => $tahun,
                    'divisi' => $div,
                ],
                [
                    'target_bulanan' => $targetBulanan,
                    'created_by' => $creatorId,
                ]
            );

            $monthlyTargets = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthlyTargets[(string)$m] = $targetBulanan;
            }

            $targetDivisi->update([
                'target_bulanan' => $targetBulanan,
                'monthly_targets' => $monthlyTargets,
            ]);
        }

        $this->filterTahun = $tahun;
        $this->modalSetTargetTahunanDivisiOpen = false;
        session()->flash('success', "Target tahunan untuk 4 divisi tahun {$tahun} berhasil disimpan!");
    }

    // =========================================================================
    // MODAL & ACTION: EDIT REALISASI PER CELL (DIVISI)
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

        $monthlyRealisasi = $targetDivisi?->monthly_realisasi ?? [];
        $defaultBulanan = (int) ($targetDivisi?->target_bulanan ?? 0);

        $this->targetSyaratBulananInfo = $defaultBulanan;
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

        $monthlyRealisasi = $targetDivisi->monthly_realisasi ?? [];
        $monthlyRealisasi[(string)$this->editDivisiBulan] = (int) $this->formRealisasiDivisi;

        $targetDivisi->update([
            'monthly_realisasi' => $monthlyRealisasi,
        ]);

        $this->modalEditCellDivisiOpen = false;
        $namaBulan = Carbon::createFromDate($this->filterTahun, $this->editDivisiBulan, 1)->translatedFormat('F');
        session()->flash('success', "Realisasi Divisi {$this->editDivisiName} bulan {$namaBulan} {$this->filterTahun} berhasil diperbarui!");
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

        // 3. Bangun Matriks Data Divisi (Target Tahunan Murni Inputan + Realisasi)
        $divisiDefs = [
            'laptop' => ['name' => 'LAPTOP', 'icon' => 'laptop'],
            'printer' => ['name' => 'PRINTER', 'icon' => 'printer'],
            'komputer' => ['name' => 'KOMPUTER', 'icon' => 'komputer'],
            'admin' => ['name' => 'ADMIN', 'icon' => 'admin'],
        ];

        $divisiMatrix = [];
        $grandTotalTargetTahunanInput = 0;

        foreach ($divisiDefs as $key => $meta) {
            $targetDivisiRow = $targetDivisiModels->get($key);
            $defaultTarget = (int) ($targetDivisiRow?->target_bulanan ?? 0);
            $targetTahunanInput = $defaultTarget * 12;
            $grandTotalTargetTahunanInput += $targetTahunanInput;

            $monthlyRealisasi = $targetDivisiRow?->monthly_realisasi ?? [];

            $monthlyData = [];
            $totalYtdRealisasi = 0;
            $totalYtdTarget = 0;

            for ($m = 1; $m <= 12; $m++) {
                // Realisasi diutamakan dari override manual jika diset, jika tidak dihitung dari transaksi_fulls
                $hasManualRealisasi = isset($monthlyRealisasi[(string)$m]);
                if ($hasManualRealisasi) {
                    $count = (int) $monthlyRealisasi[(string)$m];
                } else {
                    $count = $trxList->filter(function ($t) use ($m, $key) {
                        $isMonth = (int) $t->tanggal->format('n') === $m;
                        if (! $isMonth) return false;
                        $kat = strtolower($t->kategori_perangkat ?? '');
                        $tipe = strtolower($t->tipe_transaksi ?? '');
                        if ($key === 'laptop') return $kat === 'laptop';
                        if ($key === 'printer') return $kat === 'printer';
                        if ($key === 'komputer') return in_array($kat, ['komputer', 'pc']);
                        if ($key === 'admin') return $tipe === 'penjualan' || $kat === 'admin' || (! in_array($kat, ['laptop', 'printer', 'komputer', 'pc']));
                        return false;
                    })->count();
                }

                $targetSyarat = $defaultTarget;
                $hasData = ($defaultTarget > 0 || $hasManualRealisasi) && ($targetSyarat > 0 || $count > 0);
                $percent = ($hasData && $targetSyarat > 0) ? (int) round(($count / $targetSyarat) * 100) : 0;

                // Status classification
                $status = 'belum';
                if (! $hasData || $targetSyarat <= 0) {
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

                $totalYtdRealisasi += $count;
                $totalYtdTarget += $targetSyarat;
            }

            $ytdPercent = $totalYtdTarget > 0 ? (int) round(($totalYtdRealisasi / $totalYtdTarget) * 100) : 0;

            $divisiMatrix[$key] = [
                'name' => $meta['name'],
                'icon' => $meta['icon'],
                'target_bulanan' => $defaultTarget,
                'target_tahunan_input' => $targetTahunanInput,
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

            $grandTotalDivisiCount += $mCount;
            $grandTotalDivisiTarget += $mTarget;
        }

        $grandDivisiPercent = $grandTotalDivisiTarget > 0 ? (int) round(($grandTotalDivisiCount / $grandTotalDivisiTarget) * 100) : 0;

        $divisiSummary = [
            'months' => $totalDivisiMonths,
            'total_target_tahunan_input' => $grandTotalTargetTahunanInput,
            'total_ytd' => $grandTotalDivisiCount,
            'total_target_ytd' => $grandTotalDivisiTarget,
            'total_target_year' => $grandTotalDivisiTarget,
            'ytd_percent' => $grandDivisiPercent,
        ];

        // 4. Query & Bangun Matriks Data Pegawai (MURNI TARIKAN DARI TRANSAKSI FULL - TANPA TARGET / BADGE MERAH)
        $pegawais = Pegawai::query()
            ->where('is_aktif', true)
            ->when(! $isHQ && $user->cabang_id, fn ($q) => $q->where('cabang_id', $user->cabang_id))
            ->when($isHQ && $this->cabangFilter !== 'semua' && ! empty($this->cabangFilter), fn ($q) => $q->where('cabang_id', (int) $this->cabangFilter))
            ->orderBy('nama')
            ->get();

        $userMatrix = [];
        foreach ($pegawais as $p) {
            $monthlyData = [];
            $totalYtdRealisasi = 0;

            for ($m = 1; $m <= 12; $m++) {
                $count = $trxList->filter(function ($t) use ($m, $p) {
                    return (int) $t->tanggal->format('n') === $m && $t->pegawai_id === $p->id;
                })->count();

                $monthlyData[$m] = [
                    'count' => $count,
                ];

                $totalYtdRealisasi += $count;
            }

            $userMatrix[] = [
                'pegawai' => $p,
                'user' => (object)[
                    'id' => $p->id,
                    'name' => $p->nama,
                    'role' => $p->role,
                ],
                'months' => $monthlyData,
                'total_ytd' => $totalYtdRealisasi,
            ];
        }

        // Summary Total Per Bulan Staf (Murni Realisasi Transaksi)
        $totalUserMonths = [];
        $grandTotalUserCount = 0;

        for ($m = 1; $m <= 12; $m++) {
            $mCount = 0;
            foreach ($userMatrix as $u) {
                $mCount += $u['months'][$m]['count'];
            }

            $totalUserMonths[$m] = [
                'count' => $mCount,
            ];

            $grandTotalUserCount += $mCount;
        }

        $userSummary = [
            'months' => $totalUserMonths,
            'total_ytd' => $grandTotalUserCount,
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
