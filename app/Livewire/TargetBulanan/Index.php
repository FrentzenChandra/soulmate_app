<?php

namespace App\Livewire\TargetBulanan;

use App\Models\Cabang;
use App\Models\Pegawai;
use App\Models\TargetDivisi;
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
                $val = (int) $row->target_bulanan;
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
            $targetBulanan = (int) $targetTahunan;

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

        // 1. Query Realisasi Agregasi dari Transaksi FULL via SQL GROUP BY (Super Cepat & Hemat RAM)
        $driver = DB::connection()->getDriverName();
        $monthExpr = match ($driver) {
            'pgsql', 'postgres' => 'EXTRACT(MONTH FROM tanggal)::integer',
            'sqlite' => "CAST(strftime('%m', tanggal) AS INTEGER)",
            default => 'MONTH(tanggal)',
        };

        $cabangScope = function ($q) use ($isHQ, $cabangId) {
            if (! $isHQ) {
                $q->where('cabang_id', $cabangId);
            } elseif ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)) {
                $q->where('cabang_id', (int) $this->cabangFilter);
            }
        };

        // Rekap Pegawai: Group by bulan & pegawai_id
        $pegawaiRawCounts = TransaksiFull::query()
            ->selectRaw("{$monthExpr} as bulan, pegawai_id, COUNT(*) as total")
            ->whereYear('tanggal', $tahun)
            ->where('status', '!=', 'Batal')
            ->whereNotNull('pegawai_id')
            ->tap($cabangScope)
            ->groupByRaw("{$monthExpr}, pegawai_id")
            ->get();

        $pegawaiMap = [];
        foreach ($pegawaiRawCounts as $row) {
            $pegawaiMap[$row->pegawai_id][(int) $row->bulan] = (int) $row->total;
        }

        // Rekap Divisi: Group by bulan, tipe_transaksi, kategori_perangkat
        $divisiRawCounts = TransaksiFull::query()
            ->selectRaw("{$monthExpr} as bulan, tipe_transaksi, LOWER(COALESCE(kategori_perangkat, '')) as kategori, COUNT(*) as total")
            ->whereYear('tanggal', $tahun)
            ->where('status', '!=', 'Batal')
            ->tap($cabangScope)
            ->groupByRaw("{$monthExpr}, tipe_transaksi, LOWER(COALESCE(kategori_perangkat, ''))")
            ->get();

        $divisiMap = [
            'laptop' => [],
            'printer' => [],
            'komputer' => [],
            'admin' => [],
        ];

        foreach ($divisiRawCounts as $row) {
            $m = (int) $row->bulan;
            $tipe = strtolower($row->tipe_transaksi ?? '');
            $kat = strtolower($row->kategori ?? '');
            $total = (int) $row->total;

            if ($kat === 'laptop') {
                $divisiMap['laptop'][$m] = ($divisiMap['laptop'][$m] ?? 0) + $total;
            }
            if ($kat === 'printer') {
                $divisiMap['printer'][$m] = ($divisiMap['printer'][$m] ?? 0) + $total;
            }
            if (in_array($kat, ['komputer', 'pc'])) {
                $divisiMap['komputer'][$m] = ($divisiMap['komputer'][$m] ?? 0) + $total;
            }
            if ($tipe === 'penjualan' || $kat === 'admin' || (! in_array($kat, ['laptop', 'printer', 'komputer', 'pc']))) {
                $divisiMap['admin'][$m] = ($divisiMap['admin'][$m] ?? 0) + $total;
            }
        }

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
            $targetTahunanInput = $defaultTarget;
            $grandTotalTargetTahunanInput += $targetTahunanInput;

            $monthlyRealisasi = $targetDivisiRow?->monthly_realisasi ?? [];

            $monthlyData = [];
            $totalYtdRealisasi = 0;
            $totalYtdTarget = 0;

            for ($m = 1; $m <= 12; $m++) {
                // Realisasi diutamakan dari override manual jika diset, jika tidak diambil dari hasil SQL agregasi
                $hasManualRealisasi = isset($monthlyRealisasi[(string)$m]);
                if ($hasManualRealisasi) {
                    $count = (int) $monthlyRealisasi[(string)$m];
                } else {
                    $count = (int) ($divisiMap[$key][$m] ?? 0);
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
                $count = (int) ($pegawaiMap[$p->id][$m] ?? 0);

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
