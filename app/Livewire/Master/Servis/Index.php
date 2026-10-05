<?php

namespace App\Livewire\Master\Servis;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\MasterServis;
use App\Models\MasterServisStok;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Master Layanan Servis & Sparepart - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination, WithSorting;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'semua')]
    public string $jenisFilter = 'semua'; // 'semua' | 'jasa' | 'sparepart'

    #[Url(except: 'semua')]
    public string $kategoriFilter = 'semua'; // 'semua' | 'laptop' | 'printer' | 'komputer' | 'lainnya'

    #[Url(except: 'semua')]
    public string $statusFilter = 'semua'; // 'semua' | 'aktif' | 'nonaktif'

    #[Url(except: 'semua')]
    public string $stokFilter = 'semua'; // 'semua' | 'tersedia' | 'kritis' | 'habis'

    public int $perPage = 15;

    // State Modal Form Tambah / Edit
    public bool $modalFormOpen = false;
    public bool $isEditMode = false;
    public ?int $selectedItemId = null;

    public string $kode_item = '';
    public string $nama_item = '';
    public string $jenis = 'jasa';
    public string $kategori_perangkat = 'semua';
    public $harga_modal = 0;
    public $harga_jual = 0;
    public int $garansi_default_hari = 30;
    public string $keterangan = '';
    public bool $is_aktif = true;

    // Manajemen Stok Fisik Sparepart per Cabang
    public array $initialStock = []; // [cabang_id => jumlah]
    public array $minStock = []; // [cabang_id => min_stok threshold]
    public array $isAktifStock = []; // [cabang_id => boolean status]

    // State Modal Konfirmasi Hapus
    public bool $modalDeleteOpen = false;
    public ?int $deleteItemId = null;
    public ?string $deleteItemNama = '';
    public ?string $deleteItemJenis = '';
    public int $deleteItemUsageCount = 0;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->can('master.servis.view'))) {
            abort(403, 'Akses modul Master Servis dibatasi.');
        }

        $this->sortField = 'id';
        $this->sortDirection = 'desc';
    }

    public function updatingSearch(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingJenisFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingKategoriFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingStokFilter(): void
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function loadMore(): void
    {
        $this->perPage += 15;
    }

    public function generateNextKodeItem(string $jenis): string
    {
        $prefix = ($jenis === 'sparepart') ? 'SP-' : 'JS-';
        $existing = MasterServis::where('kode_item', 'LIKE', $prefix . '%')->pluck('kode_item')->toArray();

        $max = 0;
        foreach ($existing as $code) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $code, $matches)) {
                $num = (int) $matches[1];
                if ($num > $max) {
                    $max = $num;
                }
            }
        }

        $nextNum = $max + 1;
        return $prefix . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
    }

    public function setJenis(string $jenis): void
    {
        $this->jenis = $jenis;
        if (! $this->isEditMode) {
            $otherPrefix = $jenis === 'sparepart' ? 'JS-' : 'SP-';
            if (empty($this->kode_item) || str_starts_with($this->kode_item, $otherPrefix) || str_starts_with($this->kode_item, 'SRV-')) {
                $this->kode_item = $this->generateNextKodeItem($jenis);
            }
        }
    }

    public function updatedJenis($value): void
    {
        if (! $this->isEditMode) {
            $otherPrefix = $value === 'sparepart' ? 'JS-' : 'SP-';
            if (empty($this->kode_item) || str_starts_with($this->kode_item, $otherPrefix) || str_starts_with($this->kode_item, 'SRV-')) {
                $this->kode_item = $this->generateNextKodeItem((string) $value);
            }
        }
    }

    public function openCreateModal(): void
    {
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('master.servis.create'), 403, 'Akses tambah servis dibatasi.');

        $this->resetForm();
        $this->isEditMode = false;
        $this->selectedItemId = null;
        $this->jenis = 'jasa';

        // Auto generate kode item saran sesuai jenis (JS-001 / SP-001)
        $this->kode_item = $this->generateNextKodeItem('jasa');

        $cabangs = Cabang::where('status', 'aktif')->get();
        foreach ($cabangs as $c) {
            $this->initialStock[$c->id] = 0;
            $this->minStock[$c->id] = 3;
            $this->isAktifStock[$c->id] = true;
        }

        $this->modalFormOpen = true;
    }

    public function editItem(int $id): void
    {
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('master.servis.edit'), 403, 'Akses edit servis dibatasi.');

        $item = MasterServis::with('stoks')->findOrFail($id);
        $this->selectedItemId = $item->id;
        $this->isEditMode = true;

        $this->kode_item = (string) $item->kode_item;
        $this->nama_item = (string) $item->nama_item;
        $this->jenis = (string) $item->jenis;
        $this->kategori_perangkat = (string) $item->kategori_perangkat;
        $this->harga_modal = (float) $item->harga_modal;
        $this->harga_jual = (float) $item->harga_jual;
        $this->garansi_default_hari = (int) $item->garansi_default_hari;
        $this->keterangan = (string) $item->keterangan;
        $this->is_aktif = (bool) $item->is_aktif;

        $cabangs = Cabang::where('status', 'aktif')->get();
        foreach ($cabangs as $c) {
            $stokCab = $item->stoks->firstWhere('cabang_id', $c->id);
            $this->initialStock[$c->id] = $stokCab ? $stokCab->jumlah : 0;
            $this->minStock[$c->id] = $stokCab ? $stokCab->min_stok : 3;
            $this->isAktifStock[$c->id] = $stokCab ? (bool) $stokCab->is_aktif : true;
        }

        $this->resetValidation();
        $this->modalFormOpen = true;
    }

    public function closeFormModal(): void
    {
        $this->modalFormOpen = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->kode_item = '';
        $this->nama_item = '';
        $this->jenis = 'jasa';
        $this->kategori_perangkat = 'semua';
        $this->harga_modal = 0;
        $this->harga_jual = 0;
        $this->garansi_default_hari = 30;
        $this->keterangan = '';
        $this->is_aktif = true;
        $this->initialStock = [];
        $this->minStock = [];
        $this->isAktifStock = [];
        $this->resetValidation();
    }

    public function save(): void
    {
        if ($this->isEditMode) {
            abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('master.servis.edit'), 403, 'Akses edit servis dibatasi.');
        } else {
            abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('master.servis.create'), 403, 'Akses tambah servis dibatasi.');
        }

        $rules = [
            'nama_item' => 'required|string|min:2|max:200',
            'jenis' => 'required|in:jasa,sparepart',
            'kategori_perangkat' => 'required|in:semua,laptop,printer,komputer,lainnya',
            'harga_modal' => 'nullable|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'garansi_default_hari' => 'required|integer|min:0',
            'keterangan' => 'nullable|string|max:1000',
            'is_aktif' => 'boolean',
        ];

        if ($this->isEditMode && $this->selectedItemId) {
            $rules['kode_item'] = [
                'nullable',
                'string',
                'max:50',
                Rule::unique('master_servis', 'kode_item')->ignore($this->selectedItemId),
            ];
        } else {
            $rules['kode_item'] = [
                'nullable',
                'string',
                'max:50',
                Rule::unique('master_servis', 'kode_item'),
            ];
        }

        $this->validate($rules, [
            'nama_item.required' => 'Nama item sparepart wajib diisi.',
            'harga_jual.required' => 'harga jual servis wajib diisi.',
            'kode_item.unique' => 'Kode item servis sudah terdaftar, gunakan kode lain.',
        ]);

        $data = [
            'kode_item' => trim($this->kode_item) ?: null,
            'nama_item' => trim($this->nama_item),
            'jenis' => 'jasa', // Master Servis difokuskan murni untuk Layanan Jasa
            'kategori_perangkat' => $this->kategori_perangkat,
            'harga_modal' => (float) ($this->harga_modal ?: 0),
            'harga_jual' => (float) $this->harga_jual,
            'garansi_default_hari' => (int) $this->garansi_default_hari,
            'keterangan' => trim($this->keterangan) ?: null,
            'is_aktif' => $this->is_aktif,
        ];

        if ($this->isEditMode && $this->selectedItemId) {
            $item = MasterServis::findOrFail($this->selectedItemId);
            $item->update($data);
            session()->flash('success', "Item servis '{$item->nama_item}' berhasil diperbarui!");
        } else {
            $item = MasterServis::create($data);
            session()->flash('success', "Item servis '{$item->nama_item}' berhasil ditambahkan!");
        }

        /*
        // Simpan stok cabang jika jenis adalah sparepart (DINONAKTIFKAN / DI-HIDE: STOK DISATUKAN KE MASTER PRODUK)
        if ($this->jenis === 'sparepart') {
            $cabangs = Cabang::where('status', 'aktif')->get();
            foreach ($cabangs as $c) {
                MasterServisStok::updateOrCreate(
                    ['master_servis_id' => $item->id, 'cabang_id' => $c->id],
                    [
                        'jumlah' => (int) ($this->initialStock[$c->id] ?? 0),
                        'min_stok' => (int) ($this->minStock[$c->id] ?? 3),
                        'is_aktif' => (bool) ($this->isAktifStock[$c->id] ?? true),
                    ]
                );
            }
        }
        */

        $this->dispatch('stockUpdated');
        $this->closeFormModal();
    }

    public function toggleAktif(int $id): void
    {
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('master.servis.edit'), 403, 'Akses edit servis dibatasi.');

        $item = MasterServis::findOrFail($id);
        $item->is_aktif = ! $item->is_aktif;
        $item->save();

        $statusText = $item->is_aktif ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Item servis '{$item->nama_item}' berhasil {$statusText}.");
    }

    public function confirmDelete(int $id): void
    {
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('master.servis.delete'), 403, 'Akses hapus servis dibatasi.');

        $item = MasterServis::withCount('details')->findOrFail($id);
        $this->deleteItemId = $item->id;
        $this->deleteItemNama = $item->nama_item;
        $this->deleteItemJenis = $item->jenis_label;
        $this->deleteItemUsageCount = $item->details_count;
        $this->modalDeleteOpen = true;
    }

    public function cancelDelete(): void
    {
        $this->modalDeleteOpen = false;
        $this->deleteItemId = null;
        $this->deleteItemNama = '';
        $this->deleteItemJenis = '';
        $this->deleteItemUsageCount = 0;
    }

    public function deleteItem(): void
    {
        abort_if(! Auth::user()->isOwner() && ! Auth::user()->can('master.servis.delete'), 403, 'Akses hapus servis dibatasi.');

        if (! $this->deleteItemId) return;

        $item = MasterServis::withCount('details')->findOrFail($this->deleteItemId);

        if ($item->details_count > 0) {
            // Nonaktifkan saja jika sudah pernah digunakan pada nota servis
            $item->is_aktif = false;
            $item->save();
            session()->flash('warning', "Item '{$item->nama_item}' telah digunakan pada {$item->details_count} transaksi servis. Status item diubah menjadi non-aktif untuk menjaga riwayat nota.");
        } else {
            $item->delete();
            session()->flash('success', "Item servis '{$item->nama_item}' berhasil dihapus secara permanen.");
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');

        $targetCabangId = ($isOwner && $activeBranchSession !== 'hq') ? (int) $activeBranchSession : ($user->cabang_id ?? Cabang::first()?->id ?? 1);
        $activeCabang = Cabang::find($targetCabangId) ?? Cabang::first();

        // Query Master Servis (Murni Layanan Jasa)
        $query = MasterServis::where('jenis', 'jasa')->with(['stoks']);

        // Loose Multi-Word Search Standard
        if (! empty(trim($this->search))) {
            $words = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(nama_item) LIKE ?', [$pattern])
                           ->orWhereRaw('LOWER(COALESCE(kode_item, \'\')) LIKE ?', [$pattern])
                           ->orWhereRaw('LOWER(COALESCE(keterangan, \'\')) LIKE ?', [$pattern]);
                    });
                }
            });
        }

        // Filter Jenis
        if ($this->jenisFilter !== 'semua') {
            $query->where('jenis', $this->jenisFilter);
        }

        // Filter Kategori Perangkat
        if ($this->kategoriFilter !== 'semua') {
            $query->where('kategori_perangkat', $this->kategoriFilter);
        }

        // Filter Status
        if ($this->statusFilter === 'aktif') {
            $query->where('is_aktif', true);
        } elseif ($this->statusFilter === 'nonaktif') {
            $query->where('is_aktif', false);
        }

        // Filter Status Stok (khusus sparepart pada cabang aktif)
        if ($this->stokFilter !== 'semua') {
            $cabId = $activeCabang?->id;
            if ($this->stokFilter === 'tersedia') {
                $query->where(function ($q) use ($cabId) {
                    $q->where('jenis', 'jasa')
                      ->orWhereHas('stoks', function ($sq) use ($cabId) {
                          if ($cabId) $sq->where('cabang_id', $cabId);
                          $sq->where('is_aktif', true)->where('jumlah', '>', 3);
                      });
                });
            } elseif ($this->stokFilter === 'kritis') {
                $query->where('jenis', 'sparepart')->whereHas('stoks', function ($sq) use ($cabId) {
                    if ($cabId) $sq->where('cabang_id', $cabId);
                    $sq->where('is_aktif', true)->whereColumn('jumlah', '<=', 'min_stok')->where('jumlah', '>', 0);
                });
            } elseif ($this->stokFilter === 'habis') {
                $query->where('jenis', 'sparepart')->whereHas('stoks', function ($sq) use ($cabId) {
                    if ($cabId) $sq->where('cabang_id', $cabId);
                    $sq->where('is_aktif', true)->where('jumlah', '<=', 0);
                });
            }
        }

        // Sorting
        $sortDir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';

        if ($this->sortField === 'stok' || $this->sortField === 'stok_jumlah') {
            $cabId = $activeCabang?->id;
            $query->orderBy(
                MasterServisStok::select('jumlah')
                    ->whereColumn('master_servis_stoks.master_servis_id', 'master_servis.id')
                    ->where('master_servis_stoks.cabang_id', $cabId)
                    ->limit(1),
                $sortDir
            );
        } else {
            $allowedSorts = ['id', 'kode_item', 'nama_item', 'jenis', 'kategori_perangkat', 'harga_modal', 'harga_jual', 'garansi_default_hari', 'is_aktif', 'created_at'];
            $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'id';
            $query->orderBy($sortColumn, $sortDir);
        }

        $totalItems = (clone $query)->count();
        $items = $query->take($this->perPage)->get();
        $hasMore = $totalItems > $this->perPage;

        // Stats Ringkas
        $totalJasa = MasterServis::where('jenis', 'jasa')->where('is_aktif', true)->count();
        $totalJasaAll = MasterServis::where('jenis', 'jasa')->count();
        $totalKategori = MasterServis::where('jenis', 'jasa')->where('is_aktif', true)->distinct('kategori_perangkat')->count('kategori_perangkat');
        $totalSparepart = MasterServis::where('jenis', 'sparepart')->where('is_aktif', true)->count();

        return view('livewire.master.servis.index', [
            'items' => $items,
            'totalItems' => $totalItems,
            'hasMore' => $hasMore,
            'totalJasa' => $totalJasa,
            'totalJasaAll' => $totalJasaAll,
            'totalKategori' => $totalKategori,
            'totalSparepart' => $totalSparepart,
            'activeCabang' => $activeCabang,
        ]);
    }
}

