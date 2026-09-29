<?php

namespace App\Livewire\Servis\Batal;

use App\Events\ServisStatusUpdated;
use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\ServisMasuk;
use App\Models\ServisStatusLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Servis Batal - Unit Dibatalkan & Pengembalian - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'semua')]
    public string $statusPengambilan = 'semua'; // 'semua' | 'belum_diambil' | 'sudah_diambil'

    #[Url(except: 'semua')]
    public string $tipeFilter = 'semua'; // 'semua' | 'real' | 'non_real'

    #[Url(except: 'semua')]
    public string $cabangFilter = 'semua';

    public int $perPage = 15;

    // State Modal Detail (Lihat)
    public bool $modalDetailOpen = false;
    public ?ServisMasuk $selectedServis = null;

    // State Modal Konfirmasi Serah Terima Pengembalian Unit
    public bool $modalAmbilBatalOpen = false;
    public ?ServisMasuk $servisToAmbilBatal = null;
    public string $catatanSerahTerima = '';

    // State Modal Ubah Status / Pulihkan
    public bool $modalUbahStatusOpen = false;
    public ?ServisMasuk $servisToUbahStatus = null;
    public string $targetStatus = 'pengecekan';
    public string $catatanUbahStatus = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole('Superadmin') && ! $user->hasRole('Admin') && ! $user->hasRole('Teknisi') && ! $user->hasRole('Kasir'))) {
            abort(403, 'Akses modul Servis Batal dibatasi.');
        }

        $this->sortField = 'default_batal';
        $this->sortDirection = 'asc';
    }

    #[On('echo:servis-updates,.ServisStatusUpdated')]
    #[On('echo:servis-updates,ServisStatusUpdated')]
    public function onServisStatusUpdated($payload = []): void
    {
        // Re-render automatically when status changes across any client
    }

    public function updatingSearch(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingStatusPengambilan(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingTipeFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingCabangFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function openDetailModal(int $id): void
    {
        $this->selectedServis = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'statusLogs.user', 'details'])->findOrFail($id);
        $this->modalDetailOpen = true;
    }

    public function closeDetailModal(): void
    {
        $this->modalDetailOpen = false;
        $this->selectedServis = null;
    }

    public function openAmbilBatalModal(int $id): void
    {
        $this->servisToAmbilBatal = ServisMasuk::with(['customer', 'cabang'])->findOrFail($id);
        $this->catatanSerahTerima = '';
        $this->modalAmbilBatalOpen = true;
    }

    public function closeAmbilBatalModal(): void
    {
        $this->modalAmbilBatalOpen = false;
        $this->servisToAmbilBatal = null;
    }

    public function openUbahStatusModal(int $id): void
    {
        $this->servisToUbahStatus = ServisMasuk::with(['customer', 'cabang'])->findOrFail($id);
        $this->targetStatus = 'pengecekan';
        $this->catatanUbahStatus = '';
        $this->modalUbahStatusOpen = true;
    }

    public function closeUbahStatusModal(): void
    {
        $this->modalUbahStatusOpen = false;
        $this->servisToUbahStatus = null;
        $this->catatanUbahStatus = '';
    }

    public function simpanUbahStatus(): void
    {
        if (! $this->servisToUbahStatus) return;

        $this->validate([
            'targetStatus' => 'required|in:pengecekan,perbaikan,selesai,batal',
            'catatanUbahStatus' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $now = Carbon::now();
            $newStatus = $this->targetStatus;
            $kodeFrontend = $this->servisToUbahStatus->no_servis_frontend;
            $cabangId = $this->servisToUbahStatus->cabang_id;
            $servisId = $this->servisToUbahStatus->id;

            $updateData = [
                'status' => $newStatus,
            ];

            // If restored to active service, reset tanggal_ambil so it continues workflow
            if ($newStatus !== 'sudah_diambil') {
                $updateData['tanggal_ambil'] = null;
            }
            if ($newStatus === 'selesai' && ! $this->servisToUbahStatus->tanggal_selesai) {
                $updateData['tanggal_selesai'] = $now;
            }

            $this->servisToUbahStatus->update($updateData);

            $statusLabel = match($newStatus) {
                'pengecekan' => 'Pengecekan',
                'perbaikan' => 'Perbaikan',
                'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                'selesai' => 'Selesai',
                default => ucfirst($newStatus),
            };

            ServisStatusLog::create([
                'servis_id' => $servisId,
                'user_id' => Auth::id(),
                'status' => $newStatus,
                'tanggal' => $now,
                'catatan' => "Status dipulihkan kembali dari Batal menjadi {$statusLabel}. " . ($this->catatanUbahStatus ?: ''),
            ]);

            DB::commit();

            try {
                broadcast(new ServisStatusUpdated($servisId, $newStatus, $kodeFrontend, $cabangId));
            } catch (\Throwable $th) {
                // Ignore broadcast error
            }

            $this->closeUbahStatusModal();
            session()->flash('success', "Status servis {$kodeFrontend} berhasil diubah kembali ke {$statusLabel}!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('ubahStatusError', 'Gagal memproses perubahan status: ' . $e->getMessage());
        }
    }

    public function serahkanUnitBatal(): void
    {
        if (! $this->servisToAmbilBatal) return;

        try {
            DB::beginTransaction();

            $now = Carbon::now();
            $this->servisToAmbilBatal->update([
                'tanggal_ambil' => $now,
            ]);

            ServisStatusLog::create([
                'servis_id' => $this->servisToAmbilBatal->id,
                'user_id' => Auth::id(),
                'status' => 'batal',
                'tanggal' => $now,
                'catatan' => 'Unit yang dibatalkan telah dikembalikan/diserahkan ke pelanggan (Tanpa biaya). ' . ($this->catatanSerahTerima ?: ''),
            ]);

            DB::commit();

            try {
                broadcast(new ServisStatusUpdated($this->servisToAmbilBatal->id, 'batal', $this->servisToAmbilBatal->no_servis_frontend, $this->servisToAmbilBatal->cabang_id));
            } catch (\Throwable $th) {
                // Ignore broadcast error
            }

            $kode = $this->servisToAmbilBatal->no_servis_frontend;
            $this->closeAmbilBatalModal();
            session()->flash('success', "Fisik unit servis batal {$kode} berhasil diserahterimakan kembali ke pelanggan!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('ambilBatalError', 'Gagal memproses serah terima: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $cabangs = $isHQ ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();

        // Query Utama Servis Batal (Status = 'batal')
        $query = ServisMasuk::with(['customer', 'cabang', 'pembuat', 'details'])
            ->where('status', 'batal');

        // Scoping Cabang
        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            if ($targetCabangId) {
                $query->where('cabang_id', $targetCabangId);
            }
        } elseif ($this->cabangFilter !== 'semua' && ! empty($this->cabangFilter)) {
            $query->where('cabang_id', (int) $this->cabangFilter);
        }

        // Tipe Transaksi Real Only
        $query->where('is_real', true);

        // Filter Status Pengambilan Fisik Unit
        if ($this->statusPengambilan === 'belum_diambil') {
            $query->whereNull('tanggal_ambil');
        } elseif ($this->statusPengambilan === 'sudah_diambil') {
            $query->whereNotNull('tanggal_ambil');
        }

        // Pencarian Multi-Kata Longgar
        if (! empty($this->search)) {
            $cleanSearch = trim($this->search);
            $words = array_filter(explode(' ', $cleanSearch));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(no_servis) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(tipe_unit) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(keluhan) LIKE ?', [$pattern])
                            ->orWhereHas('customer', function ($cq) use ($pattern) {
                                $cq->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                                    ->orWhereRaw('LOWER(COALESCE(no_hp, \'\')) LIKE ?', [$pattern]);
                            });
                    });
                }
            });
        }

        // Metrik Ringkasan Servis Batal
        $metricsBase = clone $query;
        $allCurrent = $metricsBase->get();

        $totalBatal = $allCurrent->count();
        $totalBatalBelumDiambil = $allCurrent->whereNull('tanggal_ambil')->count();
        $totalBatalSudahDiambil = $allCurrent->whereNotNull('tanggal_ambil')->count();

        // Sorting Logika: BELUM DIAMBIL (tanggal_ambil IS NULL) DI ATAS!
        if ($this->sortField === 'default_batal' || empty($this->sortField)) {
            $query->orderByRaw("CASE WHEN tanggal_ambil IS NULL THEN 0 ELSE 1 END")
                  ->orderByDesc('tanggal_masuk')
                  ->orderByDesc('id');
        } else {
            $sortDir = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';
            $allowedSorts = ['tanggal_masuk', 'tanggal_ambil', 'no_servis', 'tipe_unit', 'id'];
            $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'tanggal_masuk';
            $query->orderBy($sortColumn, $sortDir);
        }

        $totalItems = (clone $query)->count();
        $servisList = $query->take($this->perPage)->get();
        $hasMore = $totalItems > $this->perPage;

        return view('livewire.servis.batal.index', [
            'servisList' => $servisList,
            'totalItems' => $totalItems,
            'hasMore' => $hasMore,
            'totalBatal' => $totalBatal,
            'totalBatalBelumDiambil' => $totalBatalBelumDiambil,
            'totalBatalSudahDiambil' => $totalBatalSudahDiambil,
            'cabangs' => $cabangs,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ]);
    }
}
