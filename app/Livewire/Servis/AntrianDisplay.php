<?php

namespace App\Livewire\Servis;

use App\Models\Cabang;
use App\Models\ServisMasuk;
use Illuminate\Support\Facades\Auth;
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
        $cabangs = Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get();
        $selectedCabang = $this->cabangId ? Cabang::find($this->cabangId) : null;

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
            $query = ServisMasuk::query()->where('is_real', true);

            if ($this->cabangId) {
                $query->where('cabang_id', $this->cabangId);
            }

            $rawCounts = (clone $query)
                ->select('kategori_perangkat', 'status', DB::raw('count(*) as total'))
                ->whereIn('kategori_perangkat', ['laptop', 'printer', 'komputer'])
                ->whereIn('status', ['pengecekan', 'perbaikan', 'selesai'])
                ->groupBy('kategori_perangkat', 'status')
                ->get();

            $counts = [
                'laptop' => ['pengecekan' => 0, 'perbaikan' => 0, 'selesai' => 0],
                'printer' => ['pengecekan' => 0, 'perbaikan' => 0, 'selesai' => 0],
                'komputer' => ['pengecekan' => 0, 'perbaikan' => 0, 'selesai' => 0],
            ];

            foreach ($rawCounts as $row) {
                $cat = strtolower((string) $row->kategori_perangkat);
                $stat = strtolower((string) $row->status);
                if (isset($counts[$cat][$stat])) {
                    $counts[$cat][$stat] = (int) $row->total;
                }
            }
        }

        // Ambil data rincian unit jika modal terbuka
        $unitList = collect();
        if ($this->modalListOpen && $this->selectedCategory && $this->selectedStatus) {
            $listQuery = ServisMasuk::with(['customer', 'teknisi'])
                ->where('is_real', true)
                ->where('kategori_perangkat', $this->selectedCategory)
                ->where('status', $this->selectedStatus);

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
