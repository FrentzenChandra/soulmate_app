<?php

namespace App\Livewire\Absensi;

use App\Models\Absensi;
use App\Models\Cabang;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Lazy]
#[Title('Riwayat Absensi')]
#[Layout('layouts.app')]
class Riwayat extends Component
{
    use WithPagination;

    // Active Tab: 'log_harian' | 'rekap_bulanan'
    public string $activeTab = 'log_harian';

    // Filter Periode 5 Mode (Log Harian)
    public string $periodeMode = 'hari_ini'; // semua, hari_ini, range, bulanan, tahunan
    public string $startDate = '';
    public string $endDate = '';
    public int $filterBulan = 0;
    public int $filterTahun = 0;

    // Search multi-kata
    public string $search = '';

    // Filter Cabang (Khusus Owner)
    public string $cabangFilter = 'semua';

    // Pagination Log Harian
    public int $perPage = 15;

    // Filter Rekap Bulanan
    public int $rekapBulan = 0;
    public int $rekapTahun = 0;

    // Modal Detail Keterlambatan
    public bool $modalDetailLateOpen = false;
    public ?int $selectedUserId = null;
    public string $selectedUserName = '';

    public function mount(): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $this->startDate = $now->toDateString();
        $this->endDate = $now->toDateString();
        $this->filterBulan = (int) $now->format('n');
        $this->filterTahun = (int) $now->format('Y');

        $this->rekapBulan = (int) $now->format('n');
        $this->rekapTahun = (int) $now->format('Y');
    }

    public function placeholder(): \Illuminate\Contracts\View\View
    {
        return view('livewire.absensi.riwayat-skeleton');
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function loadMore(): void
    {
        $this->perPage += 15;
    }

    public function updatingSearch(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingCabangFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function setPeriodeMode(string $mode): void
    {
        $this->perPage = 15;
        $this->periodeMode = $mode;
        $now = Carbon::now('Asia/Jakarta');

        switch ($mode) {
            case 'semua':
                $this->startDate = '';
                $this->endDate = '';
                break;
            case 'hari_ini':
                $this->startDate = $now->toDateString();
                $this->endDate = $now->toDateString();
                break;
            case 'range':
                $this->startDate = $now->startOfMonth()->toDateString();
                $this->endDate = $now->toDateString();
                break;
            case 'bulanan':
                if (! $this->filterBulan) {
                    $this->filterBulan = (int) $now->format('n');
                }
                if (! $this->filterTahun) {
                    $this->filterTahun = (int) $now->format('Y');
                }
                break;
            case 'tahunan':
                if (! $this->filterTahun) {
                    $this->filterTahun = (int) $now->format('Y');
                }
                break;
        }

        $this->resetPage();
    }

    protected function applyDateFilter($query)
    {
        if ($this->periodeMode === 'hari_ini' || $this->periodeMode === 'range') {
            if ($this->startDate && $this->endDate) {
                $query->whereDate('tanggal', '>=', $this->startDate)
                      ->whereDate('tanggal', '<=', $this->endDate);
            } elseif ($this->startDate) {
                $query->whereDate('tanggal', '>=', $this->startDate);
            } elseif ($this->endDate) {
                $query->whereDate('tanggal', '<=', $this->endDate);
            }
        } elseif ($this->periodeMode === 'bulanan') {
            $query->whereYear('tanggal', $this->filterTahun)
                  ->whereMonth('tanggal', $this->filterBulan);
        } elseif ($this->periodeMode === 'tahunan') {
            $query->whereYear('tanggal', $this->filterTahun);
        }

        return $query;
    }

    public function canViewRekapTim(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->isOwner() || $user->isSuperadmin() || $user->can('absensi.rekap.view');
    }

    public function openDetailLate(int $userId): void
    {
        $targetUser = User::withTrashed()->findOrFail($userId);
        $this->selectedUserId = $targetUser->id;
        $this->selectedUserName = $targetUser->name;
        $this->modalDetailLateOpen = true;
    }

    public function closeDetailLate(): void
    {
        $this->modalDetailLateOpen = false;
        $this->selectedUserId = null;
        $this->selectedUserName = '';
    }

    public function render()
    {
        $now = Carbon::now('Asia/Jakarta');
        if ($this->rekapBulan === 0 || $this->rekapTahun === 0) {
            $this->rekapBulan = (int) $now->format('n');
            $this->rekapTahun = (int) $now->format('Y');
        }
        if ($this->filterBulan === 0 || $this->filterTahun === 0) {
            $this->filterBulan = (int) $now->format('n');
            $this->filterTahun = (int) $now->format('Y');
        }

        $user = Auth::user();
        $isOwner = $user->isOwner();
        $isSuperadmin = $user->isSuperadmin();
        $canViewRekapTim = $this->canViewRekapTim();

        // Cabang list untuk dropdown Owner
        $cabangs = $isOwner ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();

        // -------------------------------------------------------------
        // TAB 1: LOG HARIAN
        // -------------------------------------------------------------
        $query = Absensi::with(['user', 'cabang'])->latest('waktu_absen');

        // Scoping peran
        if (! $isOwner) {
            if ($isSuperadmin) {
                $query->where('cabang_id', $user->cabang_id);
            } else {
                $query->where('user_id', $user->id);
            }
        } else {
            if ($this->cabangFilter !== 'semua') {
                $query->where('cabang_id', (int) $this->cabangFilter);
            }
        }

        // Filter Periode Tanggal
        $this->applyDateFilter($query);

        // Multi-Word Loose Search pada nama user
        if (! empty(trim($this->search))) {
            $tokens = array_filter(explode(' ', trim($this->search)));
            $query->whereHas('user', function ($q) use ($tokens) {
                foreach ($tokens as $token) {
                    $word = '%' . strtolower($token) . '%';
                    $q->whereRaw('LOWER(name) LIKE ?', [$word]);
                }
            });
        }

        $absensis = $query->paginate($this->perPage);

        // Metrik Ringkasan Log Harian
        $metricQuery = Absensi::query();
        if (! $isOwner) {
            if ($isSuperadmin) {
                $metricQuery->where('cabang_id', $user->cabang_id);
            } else {
                $metricQuery->where('user_id', $user->id);
            }
        } elseif ($this->cabangFilter !== 'semua') {
            $metricQuery->where('cabang_id', (int) $this->cabangFilter);
        }

        $todayString = Carbon::now('Asia/Jakarta')->toDateString();
        $todayCount = (clone $metricQuery)->whereDate('tanggal', $todayString)->count();
        $filteredCount = (clone $query)->count();

        // -------------------------------------------------------------
        // TAB 2: REKAP BULANAN PER ROLE
        // -------------------------------------------------------------
        $userQuery = User::query()->withTrashed();

        // Superadmin & Owner tidak melakukan absen, exclude dari baris staf
        $userQuery->whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', ['Owner', 'Superadmin']);
        });

        if (! $isOwner) {
            if ($canViewRekapTim) {
                $userQuery->where('cabang_id', $user->cabang_id);
            } else {
                // Staf biasa: hanya melihat rekap pribadi
                $userQuery->where('id', $user->id);
            }
        } else {
            if ($this->cabangFilter !== 'semua') {
                $userQuery->where('cabang_id', (int) $this->cabangFilter);
            }
        }

        $rekapUsers = $userQuery->with(['roles', 'cabang'])->get();
        $rekapUserIds = $rekapUsers->pluck('id')->all();

        // Single aggregation query GROUP BY user_id
        $lateStats = Absensi::whereIn('user_id', $rekapUserIds)
            ->whereYear('tanggal', $this->rekapTahun)
            ->whereMonth('tanggal', $this->rekapBulan)
            ->selectRaw("
                user_id,
                COUNT(*) as total_hadir,
                SUM(CASE WHEN status_kehadiran IN ('tepat_waktu', 'terlalu_awal') THEN 1 ELSE 0 END) as total_tepat_waktu,
                SUM(CASE WHEN status_kehadiran IN ('terlambat', 'terlambat_kritis') THEN 1 ELSE 0 END) as total_terlambat,
                SUM(CASE WHEN status_kehadiran IN ('terlambat', 'terlambat_kritis') THEN menit_keterlambatan ELSE 0 END) as total_menit_terlambat
            ")
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        // Pengelompokan 5 Role Utama (Urutan Wajib): Operasional -> Bendahara -> Teknisi -> Admin -> Magang
        $roleGroups = [
            'Operasional' => collect(),
            'Bendahara' => collect(),
            'Teknisi' => collect(),
            'Admin' => collect(),
            'Magang' => collect(),
            'Lainnya' => collect(),
        ];

        foreach ($rekapUsers as $u) {
            if ($u->isOperasional()) {
                $group = 'Operasional';
            } elseif ($u->isBendahara()) {
                $group = 'Bendahara';
            } elseif ($u->isTeknisi()) {
                $group = 'Teknisi';
            } elseif ($u->isAdmin() || $u->isKasir()) {
                $group = 'Admin';
            } elseif ($u->isMagang()) {
                $group = 'Magang';
            } else {
                $group = 'Lainnya';
            }

            $stat = $lateStats->get($u->id);
            $u->total_hadir = $stat ? (int) $stat->total_hadir : 0;
            $u->total_tepat_waktu = $stat ? (int) $stat->total_tepat_waktu : 0;
            $u->total_terlambat = $stat ? (int) $stat->total_terlambat : 0;
            $u->total_menit_terlambat = $stat ? (int) $stat->total_menit_terlambat : 0;

            $roleGroups[$group]->push($u);
        }

        // Sort users in each group: terlambat terbanyak dulu, lalu nama
        foreach ($roleGroups as $key => $collection) {
            $roleGroups[$key] = $collection->sortByDesc('total_terlambat')->values();
        }

        // Hapus grup kosong kecuali grup utama jika user memang tidak ada
        if ($roleGroups['Lainnya']->isEmpty()) {
            unset($roleGroups['Lainnya']);
        }

        // Modal detail riwayat terlambat spesifik staf terpilih
        $lateDetails = collect();
        if ($this->modalDetailLateOpen && $this->selectedUserId) {
            $lateDetails = Absensi::where('user_id', $this->selectedUserId)
                ->whereYear('tanggal', $this->rekapTahun)
                ->whereMonth('tanggal', $this->rekapBulan)
                ->whereIn('status_kehadiran', ['terlambat', 'terlambat_kritis'])
                ->latest('tanggal')
                ->get();
        }

        // Ringkasan Rekap Bulanan
        $rekapTotalStaf = $rekapUsers->count();
        $rekapTotalHadir = (int) $lateStats->sum('total_hadir');
        $rekapTotalTepatWaktu = (int) $lateStats->sum('total_tepat_waktu');
        $rekapTotalTerlambat = (int) $lateStats->sum('total_terlambat');

        return view('livewire.absensi.riwayat', [
            'absensis' => $absensis,
            'cabangs' => $cabangs,
            'isOwner' => $isOwner,
            'isSuperadmin' => $isSuperadmin,
            'canViewRekapTim' => $canViewRekapTim,
            'todayCount' => $todayCount,
            'filteredCount' => $filteredCount,
            'roleGroups' => $roleGroups,
            'lateDetails' => $lateDetails,
            'rekapTotalStaf' => $rekapTotalStaf,
            'rekapTotalHadir' => $rekapTotalHadir,
            'rekapTotalTepatWaktu' => $rekapTotalTepatWaktu,
            'rekapTotalTerlambat' => $rekapTotalTerlambat,
        ]);
    }
}
