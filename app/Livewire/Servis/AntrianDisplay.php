<?php

namespace App\Livewire\Servis;

use App\Models\Cabang;
use App\Models\ServisMasuk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.display')]
#[Title('Antrian Servis - TV Monitor Display - Soulmate Komputer')]
class AntrianDisplay extends Component
{
    #[Url(as: 'cabang', except: null)]
    public ?int $cabangId = null;

    public bool $useDemoNumbers = false;

    // State Modal Daftar Unit (saat nomor diklik)
    public bool $modalListOpen = false;
    public ?string $selectedCategory = null;
    public ?string $selectedStatus = null;

    public function mount(): void
    {
        $user = Auth::user();
        if ($user && ! $user->isOwner()) {
            $this->cabangId = (int) $user->cabang_id;
        } elseif (! $this->cabangId) {
            $firstCabang = Cabang::where('status', 'aktif')->first();
            $this->cabangId = $firstCabang?->id;
        }
    }

    public function toggleDemo(): void
    {
        $this->useDemoNumbers = ! $this->useDemoNumbers;
    }

    public function showList(string $category, string $status): void
    {
        $this->selectedCategory = $category;
        $this->selectedStatus = $status;
        $this->modalListOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalListOpen = false;
        $this->selectedCategory = null;
        $this->selectedStatus = null;
    }

    public function render()
    {
        // 1. Cache data cabang aktif (statis) selama 1 jam
        $cabangs = Cache::remember('antrian_cabangs_aktif', 3600, function () {
            return Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get();
        });
        $selectedCabang = $this->cabangId ? $cabangs->firstWhere('id', $this->cabangId) : null;

        // 2. Data agregasi antrian (Short-Lived Cache 10s per cabang)
        if ($this->useDemoNumbers) {
            $counts = [
                'laptop' => [
                    'pengecekan' => 332,
                    'perbaikan' => 511,
                    'selesai' => 132,
                ],
                'printer' => [
                    'pengecekan' => 142,
                    'perbaikan' => 21,
                    'selesai' => 723,
                ],
                'komputer' => [
                    'pengecekan' => 24,
                    'perbaikan' => 334,
                    'selesai' => 623,
                ],
            ];
        } else {
            $cabangKey = $this->cabangId ?? 'all';
            $counts = Cache::remember("antrian_display_counts_{$cabangKey}", 10, function () {
                $query = ServisMasuk::query()->where('is_real', true);

                if ($this->cabangId) {
                    $query->where('cabang_id', $this->cabangId);
                }

                // Query agregasi memanfaatkan Covering Index idx_servis_antrian_covering
                $rawCounts = $query
                    ->select('kategori_perangkat', 'status', DB::raw('count(*) as total'))
                    ->whereIn('kategori_perangkat', ['laptop', 'printer', 'komputer'])
                    ->whereIn('status', ['antrean_masuk', 'pengecekan', 'menunggu_konfirmasi', 'perbaikan', 'pengerjaan', 'menunggu_sparepart', 'selesai'])
                    ->groupBy('kategori_perangkat', 'status')
                    ->get();

                $aggregated = [
                    'laptop' => ['pengecekan' => 0, 'perbaikan' => 0, 'selesai' => 0],
                    'printer' => ['pengecekan' => 0, 'perbaikan' => 0, 'selesai' => 0],
                    'komputer' => ['pengecekan' => 0, 'perbaikan' => 0, 'selesai' => 0],
                ];

                foreach ($rawCounts as $row) {
                    $cat = strtolower((string) $row->kategori_perangkat);
                    $stat = strtolower((string) $row->status);
                    if (isset($aggregated[$cat])) {
                        if (in_array($stat, ['antrean_masuk', 'pengecekan', 'menunggu_konfirmasi'])) {
                            $aggregated[$cat]['pengecekan'] += (int) $row->total;
                        } elseif (in_array($stat, ['perbaikan', 'pengerjaan', 'menunggu_sparepart'])) {
                            $aggregated[$cat]['perbaikan'] += (int) $row->total;
                        } elseif ($stat === 'selesai') {
                            $aggregated[$cat]['selesai'] += (int) $row->total;
                        }
                    }
                }

                return $aggregated;
            });
        }

        // 3. Ambil data rincian unit jika modal terbuka (Slim query & spesifik kolom)
        $unitList = collect();
        if ($this->modalListOpen && $this->selectedCategory && $this->selectedStatus) {
            $listQuery = ServisMasuk::select('id', 'no_servis', 'cabang_id', 'customer_id', 'teknisi_id', 'status', 'kategori_perangkat', 'tipe_unit', 'keluhan', 'tanggal_masuk')
                ->with([
                    'customer:id,nama,no_hp',
                    'teknisi:id,name',
                ])
                ->where('is_real', true)
                ->where('kategori_perangkat', $this->selectedCategory);

            if ($this->selectedStatus === 'pengecekan') {
                $listQuery->whereIn('status', ['antrean_masuk', 'pengecekan', 'menunggu_konfirmasi']);
            } elseif ($this->selectedStatus === 'perbaikan') {
                $listQuery->whereIn('status', ['perbaikan', 'pengerjaan', 'menunggu_sparepart']);
            } else {
                $listQuery->where('status', $this->selectedStatus);
            }

            if ($this->cabangId) {
                $listQuery->where('cabang_id', $this->cabangId);
            }

            $unitList = $listQuery->orderByDesc('tanggal_masuk')->take(20)->get();
        }

        return view('livewire.servis.antrian-display', [
            'cabangs' => $cabangs,
            'selectedCabang' => $selectedCabang,
            'counts' => $counts,
            'unitList' => $unitList,
        ]);
    }
}
