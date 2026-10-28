<?php

namespace App\Livewire\SlipGaji;

use App\Models\Cabang;
use App\Models\KenaikanGaji;
use App\Models\SlipGaji;
use App\Models\TarifGaji;
use App\Services\SlipGajiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Slip Gaji - Penggajian & Rekapitulasi Staf - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'bulanan')]
    public string $periodeMode = 'bulanan';

    public string $startDate = '';
    public string $endDate = '';

    #[Url(except: '')]
    public string $filterBulan = '';

    #[Url(except: '')]
    public string $filterTahun = '';

    #[Url(except: 'semua')]
    public string $statusFilter = 'semua';

    #[Url(except: 'semua')]
    public string $cabangFilter = 'semua';

    // State Modal Atur Tarif
    public bool $modalTarifOpen = false;
    public float $tarifUangMakan = 20000;
    public float $tarifUangTransport = 20000;
    public float $tarifKenaikan6Bulan = 500000;

    // State Modal Salin Semua dari Bulan Lalu
    public bool $modalSalinSemuaOpen = false;
    public int $salinTargetBulan = 0;
    public int $salinTargetTahun = 0;
    public array $salinSummary = [];

    // State Modal Alert Kenaikan 6 Bulan
    public bool $modalAlertOpen = false;
    public array $daftarAlertKenaikan = [];

    // State Modal Buka Kembali (Reopen)
    public bool $modalReopenOpen = false;
    public ?int $reopenSlipId = null;
    public string $reopenSlipNo = '';
    public string $alasanBukaKembali = '';

    // State Modal Konfirmasi Hapus
    public bool $modalDeleteOpen = false;
    public ?int $deleteSlipId = null;
    public string $deleteSlipNo = '';

    // State Modal Preview Slip Gaji (1:1 Sesuai Gambar)
    public bool $modalPreviewOpen = false;
    public ?SlipGaji $previewSlip = null;

    public function showSlipPreview(int $slipId): void
    {
        $this->previewSlip = SlipGaji::with(['items', 'user', 'cabang'])->find($slipId);
        if ($this->previewSlip) {
            $this->modalPreviewOpen = true;
        }
    }

    public function closePreviewModal(): void
    {
        $this->modalPreviewOpen = false;
        $this->previewSlip = null;
    }

    public function mount(?SlipGajiService $service = null): void
    {
        $service = $service ?? app(SlipGajiService::class);
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.view')), 403, 'Akses Slip Gaji dibatasi.');

        $now = Carbon::now('Asia/Jakarta');
        if (empty($this->filterBulan)) {
            $this->filterBulan = (string) $now->month;
        }
        if (empty($this->filterTahun)) {
            $this->filterTahun = (string) $now->year;
        }

        $this->salinTargetBulan = (int) $this->filterBulan;
        $this->salinTargetTahun = (int) $this->filterTahun;

        // Muat tarif default
        $this->tarifUangMakan = TarifGaji::getNominal('uang_makan', 20000);
        $this->tarifUangTransport = TarifGaji::getNominal('uang_transport', 20000);
        $this->tarifKenaikan6Bulan = TarifGaji::getNominal('kenaikan_6_bulan', 500000);

        // Evaluasi milestone kenaikan jika memiliki izin gaji.alert
        if ($user->isOwner() || $user->can('gaji.alert')) {
            $currentCabangId = $this->getCurrentCabangId();
            $alerts = $service->evaluasiMilestoneKenaikan($currentCabangId);
            if ($alerts->count() > 0) {
                $this->daftarAlertKenaikan = $alerts->map(function ($k) {
                    return [
                        'id' => $k->id,
                        'nama' => $k->user ? $k->user->name : '-',
                        'milestone_bulan' => $k->milestone_bulan,
                        'nominal' => $k->nominal,
                    ];
                })->toArray();
                $this->modalAlertOpen = true;
            }
        }
    }

    public function setPeriodeMode(string $mode): void
    {
        $this->periodeMode = $mode;
        $now = Carbon::now('Asia/Jakarta');

        if ($mode === 'semua') {
            $this->startDate = '';
            $this->endDate = '';
        } elseif ($mode === 'hari_ini') {
            $this->startDate = $now->toDateString();
            $this->endDate = $now->toDateString();
            $this->filterBulan = (string) $now->month;
            $this->filterTahun = (string) $now->year;
        } elseif ($mode === 'range') {
            $this->startDate = $now->copy()->startOfMonth()->toDateString();
            $this->endDate = $now->toDateString();
        } elseif ($mode === 'bulanan') {
            if (empty($this->filterBulan)) {
                $this->filterBulan = (string) $now->month;
            }
            if (empty($this->filterTahun)) {
                $this->filterTahun = (string) $now->year;
            }
        } elseif ($mode === 'tahunan') {
            if (empty($this->filterTahun)) {
                $this->filterTahun = (string) $now->year;
            }
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCabangFilter(): void
    {
        $this->resetPage();
    }

    public function openTarifModal(): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.tarif.edit')), 403, 'Akses atur tarif dibatasi.');

        $this->tarifUangMakan = TarifGaji::getNominal('uang_makan', 20000);
        $this->tarifUangTransport = TarifGaji::getNominal('uang_transport', 20000);
        $this->tarifKenaikan6Bulan = TarifGaji::getNominal('kenaikan_6_bulan', 500000);
        $this->modalTarifOpen = true;
    }

    public function closeTarifModal(): void
    {
        $this->modalTarifOpen = false;
    }

    public function saveTarif(): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.tarif.edit')), 403, 'Akses atur tarif dibatasi.');

        $this->validate([
            'tarifUangMakan' => ['required', 'numeric', 'min:0'],
            'tarifUangTransport' => ['required', 'numeric', 'min:0'],
            'tarifKenaikan6Bulan' => ['required', 'numeric', 'min:0'],
        ]);

        TarifGaji::updateOrCreate(
            ['jenis' => 'uang_makan'],
            ['nominal' => $this->tarifUangMakan, 'updated_by' => $user->id]
        );
        TarifGaji::updateOrCreate(
            ['jenis' => 'uang_transport'],
            ['nominal' => $this->tarifUangTransport, 'updated_by' => $user->id]
        );
        TarifGaji::updateOrCreate(
            ['jenis' => 'kenaikan_6_bulan'],
            ['nominal' => $this->tarifKenaikan6Bulan, 'updated_by' => $user->id]
        );

        $this->modalTarifOpen = false;
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Tarif Diperbarui',
            'message' => 'Tarif global uang makan, uang transport, dan kenaikan berkala berhasil disimpan.',
        ]);
    }

    public function openSalinSemuaModal(SlipGajiService $service): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.create')), 403, 'Akses buat slip dibatasi.');

        $this->salinTargetBulan = (int) ($this->filterBulan ?: Carbon::now('Asia/Jakarta')->month);
        $this->salinTargetTahun = (int) ($this->filterTahun ?: Carbon::now('Asia/Jakarta')->year);

        $currentCabangId = $this->getCurrentCabangId();

        // Ambil rekapitulasi staf kenaikan menunggu
        $kenaikanMenunggu = KenaikanGaji::where('cabang_id', $currentCabangId)
            ->where('status', 'menunggu')
            ->with('user')
            ->get();

        $daftarNama = $kenaikanMenunggu->pluck('user.name')->filter()->unique()->values()->toArray();

        $this->salinSummary = [
            'kenaikan_menunggu_count' => count($daftarNama),
            'daftar_nama_kenaikan' => $daftarNama,
        ];

        $this->modalSalinSemuaOpen = true;
    }

    public function closeSalinSemuaModal(): void
    {
        $this->modalSalinSemuaOpen = false;
    }

    public function executeSalinSemua(?SlipGajiService $service = null): void
    {
        $service = $service ?? app(SlipGajiService::class);
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.create')), 403, 'Akses buat slip dibatasi.');

        $currentCabangId = $this->getCurrentCabangId();
        $result = $service->salinSemuaDariBulanLalu($currentCabangId, $this->salinTargetBulan, $this->salinTargetTahun, $user);

        $this->modalSalinSemuaOpen = false;
        $this->resetPage();

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Penyalinan Selesai',
            'message' => "Berhasil menyalin {$result['disalin_count']} slip baru ({$result['sudah_ada_count']} sudah ada sebelumnya).",
        ]);
    }

    public function closeAlertModal(): void
    {
        $this->modalAlertOpen = false;
    }

    public function confirmReopen(int $id, string $noSlip): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.reopen')), 403, 'Hanya Owner yang berhak membuka kembali slip Final.');

        $this->reopenSlipId = $id;
        $this->reopenSlipNo = $noSlip;
        $this->alasanBukaKembali = '';
        $this->modalReopenOpen = true;
    }

    public function closeReopenModal(): void
    {
        $this->modalReopenOpen = false;
        $this->reopenSlipId = null;
        $this->reopenSlipNo = '';
        $this->alasanBukaKembali = '';
    }

    public function executeReopen(): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.reopen')), 403, 'Hanya Owner yang berhak membuka kembali slip Final.');

        $slip = SlipGaji::findOrFail($this->reopenSlipId);
        $slip->update([
            'status' => 'draft',
            'alasan_buka_kembali' => $this->alasanBukaKembali,
            'reopened_by' => $user->id,
            'reopened_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $this->closeReopenModal();
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Status Diperbarui',
            'message' => "Slip Gaji {$slip->no_slip} berhasil dibuka kembali ke status Draft.",
        ]);
    }

    public function confirmDelete(int $id, string $noSlip): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.delete')), 403, 'Akses hapus slip dibatasi.');

        $slip = SlipGaji::findOrFail($id);
        abort_if($slip->isFinal(), 403, 'Slip Final tidak dapat dihapus.');

        $this->deleteSlipId = $id;
        $this->deleteSlipNo = $noSlip;
        $this->modalDeleteOpen = true;
    }

    public function closeDeleteModal(): void
    {
        $this->modalDeleteOpen = false;
        $this->deleteSlipId = null;
        $this->deleteSlipNo = '';
    }

    public function executeDelete(): void
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.delete')), 403, 'Akses hapus slip dibatasi.');

        $slip = SlipGaji::findOrFail($this->deleteSlipId);
        abort_if($slip->isFinal(), 403, 'Slip Final tidak dapat dihapus.');

        $no = $slip->no_slip;
        $slip->delete();

        $this->closeDeleteModal();
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Slip Dihapus',
            'message' => "Slip Gaji {$no} berhasil dihapus.",
        ]);
    }

    private function getCurrentCabangId(): int
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');

        if ($isOwner) {
            if ($activeBranchSession && $activeBranchSession !== 'hq') {
                return (int) $activeBranchSession;
            }
            if ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)) {
                return (int) $this->cabangFilter;
            }
            return 1;
        }

        return (int) ($user->cabang_id ?? 1);
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $query = SlipGaji::query()
            ->with(['user', 'cabang']);

        // Isolasi Cabang
        if ($isHQ) {
            if ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)) {
                $query->where('cabang_id', (int) $this->cabangFilter);
            }
        } else {
            $cabangId = $isOwner ? (int) $activeBranchSession : ($user->cabang_id ?? 1);
            $query->where('cabang_id', $cabangId);
        }

        // Filter Periode
        if ($this->periodeMode === 'bulanan') {
            if (! empty($this->filterBulan)) {
                $query->where('periode_bulan', (int) $this->filterBulan);
            }
            if (! empty($this->filterTahun)) {
                $query->where('periode_tahun', (int) $this->filterTahun);
            }
        } elseif ($this->periodeMode === 'tahunan') {
            if (! empty($this->filterTahun)) {
                $query->where('periode_tahun', (int) $this->filterTahun);
            }
        } elseif ($this->periodeMode === 'hari_ini' || $this->periodeMode === 'range') {
            if (! empty($this->startDate) && ! empty($this->endDate)) {
                $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            }
        }

        // Filter Status
        if ($this->statusFilter !== 'semua') {
            $query->where('status', $this->statusFilter);
        }

        // Search Multi-Kata
        if (strlen(trim($this->search)) >= 1) {
            $tokens = array_filter(explode(' ', trim($this->search)));
            foreach ($tokens as $token) {
                $word = '%' . strtolower($token) . '%';
                $query->where(function ($q) use ($word) {
                    $q->whereRaw('LOWER(nama_staf_snapshot) LIKE ?', [$word])
                      ->orWhereRaw('LOWER(no_slip) LIKE ?', [$word])
                      ->orWhereHas('user', function ($sub) use ($word) {
                          $sub->whereRaw('LOWER(name) LIKE ?', [$word]);
                      });
                });
            }
        }

        $slips = $query->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->orderByDesc('id')
            ->paginate(15);

        $cabangs = $isHQ ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get(['id', 'nama_cabang']) : collect();

        // Hitung staf menunggu kenaikan di cabang untuk badge button
        $currentCabangId = $this->getCurrentCabangId();
        $alertCount = KenaikanGaji::where('cabang_id', $currentCabangId)
            ->where('status', 'menunggu')
            ->count();

        return view('livewire.slip-gaji.index', [
            'slips' => $slips,
            'cabangs' => $cabangs,
            'isHQ' => $isHQ,
            'alertCount' => $alertCount,
        ]);
    }
}

