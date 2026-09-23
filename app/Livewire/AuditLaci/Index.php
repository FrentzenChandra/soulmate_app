<?php

namespace App\Livewire\AuditLaci;

use App\Models\Cabang;
use App\Models\CekKasLog;
use App\Models\KasKeluar;
use App\Models\ModalAwal;
use App\Models\Penjualan;
use App\Models\ServisMasuk;
use App\Models\TransaksiFull;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $periodeMode = 'range'; // 'range', 'bulanan', 'tahunan'
    public ?string $startDate = null;
    public ?string $endDate = null;
    public string $filterBulan = '';
    public string $filterTahun = '';
    public string $statusFilter = 'semua'; // 'semua', 'klop', 'minus', 'lebih'

    // State Modal Audit Laci
    public bool $modalAuditOpen = false;
    public $kasFisik = 0;
    public string $catatanAudit = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! ($user->isOwner() || $user->isSuperAdmin() || $user->isAdmin() || $user->isTeknisi() || $user->hasRole('Kasir'))) {
            abort(403, 'Akses Histori Audit Laci dibatasi.');
        }

        $this->filterBulan = now()->format('m');
        $this->filterTahun = now()->format('Y');
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function setPeriodeMode(string $mode): void
    {
        $this->periodeMode = $mode;
        if ($mode === 'range') {
            $this->startDate = now()->startOfMonth()->toDateString();
            $this->endDate = now()->toDateString();
        } elseif ($mode === 'bulanan') {
            $this->applyBulanan();
        } elseif ($mode === 'tahunan') {
            $this->applyTahunan();
        }
    }

    public function updatedFilterBulan(): void
    {
        if ($this->periodeMode === 'bulanan') {
            $this->applyBulanan();
        }
    }

    public function updatedFilterTahun(): void
    {
        if ($this->periodeMode === 'bulanan') {
            $this->applyBulanan();
        } elseif ($this->periodeMode === 'tahunan') {
            $this->applyTahunan();
        }
    }

    public function applyBulanan(): void
    {
        $year = (int) ($this->filterTahun ?: now()->year);
        $month = (int) ($this->filterBulan ?: now()->month);
        $dt = Carbon::createFromDate($year, $month, 1);
        $this->startDate = $dt->copy()->startOfMonth()->toDateString();
        $this->endDate = $dt->copy()->endOfMonth()->toDateString();
    }

    public function applyTahunan(): void
    {
        $year = (int) ($this->filterTahun ?: now()->year);
        $dt = Carbon::createFromDate($year, 1, 1);
        $this->startDate = $dt->copy()->startOfYear()->toDateString();
        $this->endDate = $dt->copy()->endOfYear()->toDateString();
    }

    public function getActiveCabangProperty(): ?Cabang
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        if ($user->isOwner()) {
            $sess = session('active_branch_id', 'hq');
            if ($sess !== 'hq') {
                return Cabang::find((int) $sess);
            }
            return Cabang::where('status', 'aktif')->first();
        }

        return $user->cabang;
    }

    public function openAuditModal(): void
    {
        $this->kasFisik = 0;
        $this->catatanAudit = '';
        $this->modalAuditOpen = true;
    }

    public function closeAuditModal(): void
    {
        $this->modalAuditOpen = false;
        $this->kasFisik = 0;
        $this->catatanAudit = '';
    }

    public function prosesAudit(): void
    {
        $user = Auth::user();
        $cabang = $this->activeCabang ?: $user->cabang;

        if (! $cabang) {
            $this->dispatch('swal:error', [
                'title' => 'Gagal Audit',
                'text' => 'Silakan pilih cabang spesifik terlebih dahulu.',
            ]);
            return;
        }

        $today = now()->toDateString();
        $startToday = now()->startOfDay();
        $endToday = now()->endOfDay();

        // 1. Modal Awal Hari Ini
        $modalAwal = ModalAwal::where('cabang_id', $cabang->id)
            ->whereDate('tanggal', $today)
            ->first();
        $modalAwalNominal = (float) ($modalAwal?->nominal ?? 0);

        // 2. Kas Keluar Cash Hari Ini
        $totalKasKeluarCash = (float) KasKeluar::where('cabang_id', $cabang->id)
            ->whereBetween('tanggal', [$startToday, $endToday])
            ->whereHas('sumberDana', function ($sq) {
                $sq->whereRaw('LOWER(nama) LIKE ?', ['%cash%'])
                    ->orWhereRaw('LOWER(nama) LIKE ?', ['%tunai%'])
                    ->orWhereRaw('LOWER(nama) LIKE ?', ['%kasir%']);
            })
            ->sum('nominal');

        // 3. Penjualan Cash Hari Ini (Berdasarkan Tanggal Lunas Cash)
        $totalPenjualanCash = (float) TransaksiFull::where('cabang_id', $cabang->id)
            ->where('tipe_transaksi', 'penjualan')
            ->where('status', 'Lunas')
            ->whereBetween('tanggal_lunas', [$startToday, $endToday])
            ->where(function ($q) {
                $q->whereRaw('LOWER(metode_pembayaran) LIKE ?', ['%cash%'])
                    ->orWhereRaw('LOWER(metode_pembayaran) LIKE ?', ['%tunai%']);
            })
            ->sum('grand_total');

        // 4. Servis Cash Hari Ini (Berdasarkan Tanggal Lunas Cash)
        $totalServisCash = (float) TransaksiFull::where('cabang_id', $cabang->id)
            ->where('tipe_transaksi', 'servis')
            ->where('status', 'Lunas')
            ->whereBetween('tanggal_lunas', [$startToday, $endToday])
            ->where(function ($q) {
                $q->whereRaw('LOWER(metode_pembayaran) LIKE ?', ['%cash%'])
                    ->orWhereRaw('LOWER(metode_pembayaran) LIKE ?', ['%tunai%']);
            })
            ->sum('grand_total');

        $kasSistem = $modalAwalNominal + $totalPenjualanCash + $totalServisCash - $totalKasKeluarCash;
        $kasFisik = (float) $this->kasFisik;
        $selisih = $kasFisik - $kasSistem;

        $statusSelisih = 'klop';
        if ($selisih < 0) {
            $statusSelisih = 'minus';
        } elseif ($selisih > 0) {
            $statusSelisih = 'lebih';
        }

        CekKasLog::create([
            'cabang_id' => $cabang->id,
            'user_id' => $user->id,
            'tanggal' => $today,
            'waktu_cek' => now(),
            'modal_awal' => $modalAwalNominal,
            'penjualan_cash' => $totalPenjualanCash,
            'servis_cash' => $totalServisCash,
            'kas_keluar_cash' => $totalKasKeluarCash,
            'kas_sistem' => $kasSistem,
            'kas_fisik' => $kasFisik,
            'selisih' => $selisih,
            'status_selisih' => $statusSelisih,
            'catatan' => $this->catatanAudit ? trim($this->catatanAudit) : null,
        ]);

        $this->closeAuditModal();
        $this->dispatch('swal:success', [
            'title' => 'Audit Kas Berhasil!',
            'text' => 'Snapshot kas laci berhasil dicatat. Status: ' . strtoupper($statusSelisih),
        ]);
    }

    public function render()
    {
        $user = Auth::user();
        $cabang = $this->activeCabang;

        $query = CekKasLog::with(['user', 'cabang'])
            ->whereBetween('tanggal', [$this->startDate, $this->endDate]);

        if ($cabang) {
            $query->where('cabang_id', $cabang->id);
        } elseif (! $user->isOwner()) {
            $query->where('cabang_id', $user->cabang_id);
        }

        if ($this->statusFilter === 'klop') {
            $query->where('status_selisih', 'klop');
        } elseif ($this->statusFilter === 'minus') {
            $query->where(function ($q) {
                $q->where('status_selisih', 'minus')->orWhere('selisih', '<', 0);
            });
        } elseif ($this->statusFilter === 'lebih') {
            $query->where(function ($q) {
                $q->where('status_selisih', 'lebih')->orWhere('selisih', '>', 0);
            });
        }

        $allLogs = $query->orderByDesc('waktu_cek')->get();

        // Hitung Metrik
        $totalAudit = $allLogs->count();
        $totalKlop = $allLogs->where('status_selisih', 'klop')->count();
        
        $minusLogs = $allLogs->filter(fn ($l) => $l->status_selisih === 'minus' || (float) $l->selisih < 0);
        $totalMinusCount = $minusLogs->count();
        $totalMinusNominal = abs((float) $minusLogs->sum('selisih'));

        $lebihLogs = $allLogs->filter(fn ($l) => $l->status_selisih === 'lebih' || (float) $l->selisih > 0);
        $totalLebihCount = $lebihLogs->count();
        $totalLebihNominal = (float) $lebihLogs->sum('selisih');

        // Format data untuk Tabulator
        $tableData = [];
        foreach ($allLogs as $log) {
            $waktuCarbon = $log->waktu_cek ? Carbon::parse($log->waktu_cek) : Carbon::parse($log->created_at);
            
            $statusLabel = 'Klop';
            if ($log->status_selisih === 'minus' || (float) $log->selisih < 0) {
                $statusLabel = 'Minus';
            } elseif ($log->status_selisih === 'lebih' || (float) $log->selisih > 0) {
                $statusLabel = 'Lebih';
            }

            $tableData[] = [
                'id' => $log->id,
                'tanggal_group' => $this->formatIndonesianDate($waktuCarbon),
                'waktu' => $waktuCarbon->format('d/m/Y H:i'),
                'petugas' => $log->user?->name ?? 'Kasir',
                'cabang' => $log->cabang?->nama_cabang ?? '-',
                'modal_awal' => (float) $log->modal_awal,
                'penjualan_cash' => (float) $log->penjualan_cash,
                'servis_cash' => (float) $log->servis_cash,
                'kas_keluar_cash' => (float) $log->kas_keluar_cash,
                'kas_sistem' => (float) $log->kas_sistem,
                'kas_fisik' => (float) $log->kas_fisik,
                'selisih' => (float) $log->selisih,
                'status_selisih' => $statusLabel,
                'catatan' => $log->catatan ?? '-',
            ];
        }

        // Hitung kas live saat ini untuk preview modal audit
        $today = now()->toDateString();
        $startToday = now()->startOfDay();
        $endToday = now()->endOfDay();
        $cabangTarget = $cabang ?: $user->cabang;

        $modalAwalToday = 0;
        $penjualanCashToday = 0;
        $servisCashToday = 0;
        $kasKeluarCashToday = 0;

        if ($cabangTarget) {
            $modalAwalToday = (float) (ModalAwal::where('cabang_id', $cabangTarget->id)->whereDate('tanggal', $today)->value('nominal') ?? 0);
            
            $penjualanCashToday = (float) TransaksiFull::where('cabang_id', $cabangTarget->id)
                ->where('tipe_transaksi', 'penjualan')
                ->where('status', 'Lunas')
                ->whereBetween('tanggal_lunas', [$startToday, $endToday])
                ->where(function ($q) {
                    $q->whereRaw('LOWER(metode_pembayaran) LIKE ?', ['%cash%'])
                        ->orWhereRaw('LOWER(metode_pembayaran) LIKE ?', ['%tunai%']);
                })
                ->sum('grand_total');

            $servisCashToday = (float) TransaksiFull::where('cabang_id', $cabangTarget->id)
                ->where('tipe_transaksi', 'servis')
                ->where('status', 'Lunas')
                ->whereBetween('tanggal_lunas', [$startToday, $endToday])
                ->where(function ($q) {
                    $q->whereRaw('LOWER(metode_pembayaran) LIKE ?', ['%cash%'])
                        ->orWhereRaw('LOWER(metode_pembayaran) LIKE ?', ['%tunai%']);
                })
                ->sum('grand_total');

            $kasKeluarCashToday = (float) KasKeluar::where('cabang_id', $cabangTarget->id)
                ->whereBetween('tanggal', [$startToday, $endToday])
                ->whereHas('sumberDana', function ($sq) {
                    $sq->whereRaw('LOWER(nama) LIKE ?', ['%cash%'])
                        ->orWhereRaw('LOWER(nama) LIKE ?', ['%tunai%'])
                        ->orWhereRaw('LOWER(nama) LIKE ?', ['%kasir%']);
                })
                ->sum('nominal');
        }

        $liveKasSistem = $modalAwalToday + $penjualanCashToday + $servisCashToday - $kasKeluarCashToday;

        $this->dispatch('audit-table-updated', data: $tableData);

        return view('livewire.audit-laci.index', [
            'tableData' => $tableData,
            'totalAudit' => $totalAudit,
            'totalKlop' => $totalKlop,
            'totalMinusCount' => $totalMinusCount,
            'totalMinusNominal' => $totalMinusNominal,
            'totalLebihCount' => $totalLebihCount,
            'totalLebihNominal' => $totalLebihNominal,
            'liveKasSistem' => $liveKasSistem,
            'modalAwalToday' => $modalAwalToday,
            'penjualanCashToday' => $penjualanCashToday,
            'servisCashToday' => $servisCashToday,
            'kasKeluarCashToday' => $kasKeluarCashToday,
        ]);
    }

    private function formatIndonesianDate(?Carbon $date): string
    {
        if (! $date) {
            return '-';
        }

        $days = [
            'Sun' => 'Minggu',
            'Mon' => 'Senin',
            'Tue' => 'Selasa',
            'Wed' => 'Rabu',
            'Thu' => 'Kamis',
            'Fri' => 'Jumat',
            'Sat' => 'Sabtu',
        ];

        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        $dayName = $days[$date->format('D')] ?? $date->format('l');
        $dayNum = $date->format('d');
        $monthName = $months[(int) $date->format('n')] ?? $date->format('M');
        $year = $date->format('Y');

        return "{$dayName}, {$dayNum} {$monthName} {$year}";
    }
}
