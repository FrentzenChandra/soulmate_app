<?php

namespace App\Livewire\Absensi;

use App\Models\Absensi;
use App\Models\Cabang;
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

    // Filter Periode 5 Mode
    public string $periodeMode = 'hari_ini'; // semua, hari_ini, range, bulanan, tahunan
    public string $startDate = '';
    public string $endDate = '';
    public int $filterBulan;
    public int $filterTahun;

    // Search multi-kata
    public string $search = '';

    // Filter Cabang (Khusus Owner)
    public string $cabangFilter = 'semua';

    // Pagination
    public int $perPage = 15;

    public function mount(): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $this->startDate = $now->toDateString();
        $this->endDate = $now->toDateString();
        $this->filterBulan = (int) $now->format('n');
        $this->filterTahun = (int) $now->format('Y');
    }

    public function placeholder(): \Illuminate\Contracts\View\View
    {
        return view('livewire.absensi.riwayat-skeleton');
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

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $isSuperadmin = $user->isSuperadmin();

        $query = Absensi::with(['user', 'cabang'])->latest('waktu_absen');

        // Scoping peran
        if (! $isOwner) {
            if ($isSuperadmin) {
                // Superadmin melihat data cabangnya
                $query->where('cabang_id', $user->cabang_id);
            } else {
                // Staf operasional (Teknisi, Admin, Bendahara) hanya melihat datanya sendiri
                $query->where('user_id', $user->id);
            }
        } else {
            // Owner dengan opsi filter cabang
            if ($this->cabangFilter !== 'semua') {
                $query->where('cabang_id', $this->cabangFilter);
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

        // Cabang list untuk dropdown Owner
        $cabangs = $isOwner ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();

        // Metrik Ringkasan
        $metricQuery = Absensi::query();
        if (! $isOwner) {
            if ($isSuperadmin) {
                $metricQuery->where('cabang_id', $user->cabang_id);
            } else {
                $metricQuery->where('user_id', $user->id);
            }
        } elseif ($this->cabangFilter !== 'semua') {
            $metricQuery->where('cabang_id', $this->cabangFilter);
        }

        $todayString = Carbon::now('Asia/Jakarta')->toDateString();
        $todayCount = (clone $metricQuery)->whereDate('tanggal', $todayString)->count();
        $filteredCount = (clone $query)->count();

        return view('livewire.absensi.riwayat', [
            'absensis' => $absensis,
            'cabangs' => $cabangs,
            'isOwner' => $isOwner,
            'isSuperadmin' => $isSuperadmin,
            'todayCount' => $todayCount,
            'filteredCount' => $filteredCount,
        ]);
    }
}
