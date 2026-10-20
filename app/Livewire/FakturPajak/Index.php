<?php

namespace App\Livewire\FakturPajak;

use App\Models\Cabang;
use App\Models\FakturPajak;
use App\Models\MasterKodePajak;
use App\Models\MasterOrangPajak;
use App\Models\MasterPph;
use App\Models\MasterViaPajak;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Faktur Pajak - Soulmate Komputer')]
class Index extends Component
{
    // Filter State (Rule 13 Standard)
    public string $periodeMode = 'range'; // 'semua' | 'hari_ini' | 'range' | 'bulanan' | 'tahunan'
    public string $startDate = '';
    public string $endDate = '';
    public string $filterBulan = '';
    public string $filterTahun = '';

    public string $search = '';
    public string $statusFilter = 'semua'; // 'semua' | 'selesai' | 'belum'
    public string $orangFilter = 'semua';
    public string $viaFilter = 'semua';
    public string $kodeFilter = 'semua';
    public string $cabangFilter = 'semua';

    // Modal Form State (Rule 21 Standard: sm:max-w-2xl)
    public bool $modalFormOpen = false;
    public bool $isEditMode = false;
    public ?int $fakturId = null;

    public string $formTanggal = '';
    public ?int $formMasterOrangPajakId = null;
    public string $formNamaPajak = '';
    public string $formBarang = '';
    public ?int $formMasterViaPajakId = null;
    public string $formViaPajak = 'E-Katalog';
    public ?int $formMasterKodePajakId = null;
    public string $formKodePajak = '';
    public string $formBruto = '0';
    public ?int $formMasterPphId = null;
    public string $formJenisPph = 'Non-PPh';
    public float $formPphPersen = 0.00;
    public string $formCatatan = '';

    // Search Autocomplete Rekanan Pajak
    public string $orangPajakSearch = '';
    public array $orangPajakSuggestions = [];

    // Modal Detail State
    public bool $modalDetailOpen = false;
    public ?FakturPajak $selectedFaktur = null;

    protected $queryString = [
        'periodeMode' => ['except' => 'range'],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'filterBulan' => ['except' => ''],
        'filterTahun' => ['except' => ''],
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'semua'],
        'orangFilter' => ['except' => 'semua'],
        'viaFilter' => ['except' => 'semua'],
        'kodeFilter' => ['except' => 'semua'],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->can('pajak.view'))) {
            abort(403, 'Akses modul Faktur Pajak dibatasi.');
        }

        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
        $this->filterBulan = now()->format('m');
        $this->filterTahun = now()->format('Y');

        if (! $user->isOwner()) {
            $this->cabangFilter = (string) $user->cabang_id;
        } else {
            $activeBranch = session('active_branch_id', 'hq');
            if ($activeBranch !== 'hq') {
                $this->cabangFilter = (string) $activeBranch;
            }
        }
    }

    public function setPeriodeMode(string $mode): void
    {
        $this->periodeMode = $mode;
        if ($mode === 'semua') {
            $this->startDate = '';
            $this->endDate = '';
        } elseif ($mode === 'hari_ini') {
            $this->startDate = now()->toDateString();
            $this->endDate = now()->toDateString();
        } elseif ($mode === 'range') {
            $this->startDate = now()->startOfMonth()->toDateString();
            $this->endDate = now()->toDateString();
        } elseif ($mode === 'bulanan') {
            $this->applyBulanan();
        } elseif ($mode === 'tahunan') {
            $this->applyTahunan();
        }
    }

    public function applyBulanan(): void
    {
        $y = $this->filterTahun ?: now()->format('Y');
        $m = $this->filterBulan ?: now()->format('m');
        $date = Carbon::createFromDate((int) $y, (int) $m, 1);
        $this->startDate = $date->copy()->startOfMonth()->toDateString();
        $this->endDate = $date->copy()->endOfMonth()->toDateString();
    }

    public function applyTahunan(): void
    {
        $y = $this->filterTahun ?: now()->format('Y');
        $date = Carbon::createFromDate((int) $y, 1, 1);
        $this->startDate = $date->copy()->startOfYear()->toDateString();
        $this->endDate = $date->copy()->endOfYear()->toDateString();
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

    public function applyDateFilter($query): void
    {
        if ($this->periodeMode === 'semua') {
            return;
        }

        if (! empty($this->startDate) && ! empty($this->endDate)) {
            $query->whereBetween('tanggal', [
                Carbon::parse($this->startDate)->toDateString(),
                Carbon::parse($this->endDate)->toDateString(),
            ]);
        }
    }

    // --- AUTOCOMPLETE ORANG PAJAK ---

    public function updatedOrangPajakSearch(): void
    {
        $search = trim($this->orangPajakSearch);
        if (strlen($search) < 2) {
            $this->orangPajakSuggestions = [];
            return;
        }

        $words = array_filter(explode(' ', $search));
        $this->orangPajakSuggestions = MasterOrangPajak::where('is_aktif', true)
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                           ->orWhereRaw("LOWER(COALESCE(npwp, '')) LIKE ?", [$pattern]);
                    });
                }
            })
            ->take(6)
            ->get(['id', 'nama', 'npwp', 'kontak'])
            ->toArray();
    }

    public function selectOrangPajak(int $id, string $nama): void
    {
        $this->formMasterOrangPajakId = $id;
        $this->formNamaPajak = $nama;
        $this->orangPajakSearch = $nama;
        $this->orangPajakSuggestions = [];
    }

    public function updatedFormMasterViaPajakId(): void
    {
        if ($this->formMasterViaPajakId) {
            $via = MasterViaPajak::find($this->formMasterViaPajakId);
            if ($via) {
                $this->formViaPajak = $via->nama;
            }
        }
    }

    public function updatedFormMasterKodePajakId(): void
    {
        if ($this->formMasterKodePajakId) {
            $kode = MasterKodePajak::find($this->formMasterKodePajakId);
            if ($kode) {
                $this->formKodePajak = $kode->kode;
            }
        } else {
            $this->formKodePajak = '';
        }
    }

    public function updatedFormMasterPphId(): void
    {
        if ($this->formMasterPphId) {
            $pph = MasterPph::find($this->formMasterPphId);
            if ($pph) {
                $this->formJenisPph = $pph->nama_pph;
                $this->formPphPersen = (float) $pph->persentase;
            }
        } else {
            $this->formJenisPph = 'Non-PPh';
            $this->formPphPersen = 0.00;
        }
    }

    // --- MODAL FORM ACTIONS ---

    public function openCreateModal(): void
    {
        $user = Auth::user();
        abort_if(! $user->isOwner() && ! $user->can('pajak.create'), 403, 'Akses input faktur pajak dibatasi.');

        $this->resetValidation();
        $this->reset([
            'fakturId', 'formMasterOrangPajakId', 'formNamaPajak', 'formBarang',
            'formMasterKodePajakId', 'formKodePajak', 'formBruto', 'formCatatan',
            'orangPajakSearch', 'orangPajakSuggestions',
        ]);

        $this->formTanggal = now()->toDateString();
        $this->isEditMode = false;

        // Default Via Pajak
        $defaultVia = MasterViaPajak::where('is_aktif', true)->first();
        $this->formMasterViaPajakId = $defaultVia?->id;
        $this->formViaPajak = $defaultVia?->nama ?? 'E-Katalog';

        // Default PPh
        $defaultPph = MasterPph::where('is_aktif', true)->first();
        $this->formMasterPphId = $defaultPph?->id;
        $this->formJenisPph = $defaultPph?->nama_pph ?? 'Non-PPh';
        $this->formPphPersen = $defaultPph ? (float) $defaultPph->persentase : 0.00;

        $this->modalFormOpen = true;
    }

    public function edit(int $id): void
    {
        $user = Auth::user();
        abort_if(! $user->isOwner() && ! $user->can('pajak.edit'), 403, 'Akses koreksi faktur pajak dibatasi.');

        $faktur = FakturPajak::findOrFail($id);
        $this->fakturId = $faktur->id;
        $this->formTanggal = $faktur->tanggal->toDateString();
        $this->formMasterOrangPajakId = $faktur->master_orang_pajak_id;
        $this->formNamaPajak = $faktur->nama_pajak_snapshot;
        $this->orangPajakSearch = $faktur->nama_pajak_snapshot;
        $this->formBarang = $faktur->barang_snapshot;
        $this->formMasterViaPajakId = $faktur->master_via_pajak_id;
        $this->formViaPajak = $faktur->via_pajak_snapshot;
        $this->formMasterKodePajakId = $faktur->master_kode_pajak_id;
        $this->formKodePajak = $faktur->kode_pajak_snapshot ?? '';
        $this->formBruto = number_format((float) $faktur->bruto, 0, ',', '.');
        $this->formMasterPphId = $faktur->master_pph_id;
        $this->formJenisPph = $faktur->jenis_pph_snapshot;
        $this->formPphPersen = (float) $faktur->pph_persen;
        $this->formCatatan = $faktur->catatan ?? '';

        $this->resetValidation();
        $this->isEditMode = true;
        $this->modalFormOpen = true;
    }

    public function save(): void
    {
        $user = Auth::user();
        $requiredPerm = $this->isEditMode ? 'pajak.edit' : 'pajak.create';
        abort_if(! $user->isOwner() && ! $user->can($requiredPerm), 403, 'Akses simpan faktur pajak dibatasi.');

        if (empty($this->formTanggal)) {
            $this->formTanggal = now()->toDateString();
        }

        $this->validate([
            'formTanggal' => 'nullable|date',
            'formNamaPajak' => 'required|string|max:150',
            'formBarang' => 'required|string',
            'formViaPajak' => 'required|string|max:100',
            'formBruto' => 'required',
        ], [
            'formNamaPajak.required' => 'Nama rekanan / instansi wajib diisi.',
            'formBarang.required' => 'Uraian barang / jasa wajib diisi.',
            'formViaPajak.required' => 'Saluran via pajak wajib dipilih.',
            'formBruto.required' => 'Nilai nominal bruto wajib diisi.',
        ]);

        // Clean & parse financial inputs
        $rawBruto = str_replace(['.', ','], ['', '.'], $this->formBruto);
        $bruto = (float) $rawBruto;
        if ($bruto <= 0) {
            $this->addError('formBruto', 'Nominal bruto harus lebih besar dari 0.');
            return;
        }

        // Kalkulasi Presisi
        $dpp = round($bruto / 1.11, 2);
        $dppLain = round($dpp * (11 / 12), 2);
        $ppnNominal = round($dppLain * 0.12, 2);
        $pphNominal = round($dpp * ($this->formPphPersen / 100), 2);
        $netto = round($bruto - $ppnNominal - $pphNominal, 2);

        // Auto create MasterOrangPajak jika diketik baru
        if (! $this->formMasterOrangPajakId && ! empty(trim($this->formNamaPajak))) {
            $newOrang = MasterOrangPajak::firstOrCreate(
                ['nama' => trim($this->formNamaPajak)],
                ['is_aktif' => true]
            );
            $this->formMasterOrangPajakId = $newOrang->id;
        }

        $cabangId = $user->isOwner()
            ? (int) (session('active_branch_id', 'hq') !== 'hq' ? session('active_branch_id') : (Cabang::where('status', 'aktif')->first()?->id ?? 1))
            : (int) $user->cabang_id;

        DB::transaction(function () use ($user, $cabangId, $bruto, $dpp, $dppLain, $ppnNominal, $pphNominal, $netto) {
            if ($this->isEditMode && $this->fakturId) {
                $faktur = FakturPajak::findOrFail($this->fakturId);
                $faktur->update([
                    'tanggal' => $this->formTanggal ?: now()->toDateString(),
                    'master_orang_pajak_id' => $this->formMasterOrangPajakId,
                    'master_via_pajak_id' => $this->formMasterViaPajakId,
                    'master_kode_pajak_id' => $this->formMasterKodePajakId,
                    'master_pph_id' => $this->formMasterPphId,
                    'nama_pajak_snapshot' => trim($this->formNamaPajak),
                    'barang_snapshot' => trim($this->formBarang),
                    'via_pajak_snapshot' => $this->formViaPajak,
                    'kode_pajak_snapshot' => $this->formKodePajak ?: null,
                    'bruto' => $bruto,
                    'dpp' => $dpp,
                    'dpp_lain' => $dppLain,
                    'ppn_persen' => 12.00,
                    'ppn_nominal' => $ppnNominal,
                    'jenis_pph_snapshot' => $this->formJenisPph,
                    'pph_persen' => $this->formPphPersen,
                    'pph_nominal' => $pphNominal,
                    'netto' => $netto,
                    'catatan' => trim($this->formCatatan) ?: null,
                ]);
                $this->dispatch('swal:toast', [
                    'type' => 'success',
                    'title' => 'Berhasil Diperbarui',
                    'text' => 'Faktur Pajak "' . $faktur->no_faktur . '" berhasil diperbarui.',
                ]);
            } else {
                $noFaktur = FakturPajak::generateNoFaktur($cabangId, $this->formTanggal);
                $faktur = FakturPajak::create([
                    'no_faktur' => $noFaktur,
                    'tanggal' => $this->formTanggal ?: now()->toDateString(),
                    'cabang_id' => $cabangId,
                    'user_id' => $user->id,
                    'is_selesai' => false,
                    'master_orang_pajak_id' => $this->formMasterOrangPajakId,
                    'master_via_pajak_id' => $this->formMasterViaPajakId,
                    'master_kode_pajak_id' => $this->formMasterKodePajakId,
                    'master_pph_id' => $this->formMasterPphId,
                    'nama_pajak_snapshot' => trim($this->formNamaPajak),
                    'barang_snapshot' => trim($this->formBarang),
                    'via_pajak_snapshot' => $this->formViaPajak,
                    'kode_pajak_snapshot' => $this->formKodePajak ?: null,
                    'bruto' => $bruto,
                    'dpp' => $dpp,
                    'dpp_lain' => $dppLain,
                    'ppn_persen' => 12.00,
                    'ppn_nominal' => $ppnNominal,
                    'jenis_pph_snapshot' => $this->formJenisPph,
                    'pph_persen' => $this->formPphPersen,
                    'pph_nominal' => $pphNominal,
                    'netto' => $netto,
                    'catatan' => trim($this->formCatatan) ?: null,
                ]);
                $this->dispatch('swal:toast', [
                    'type' => 'success',
                    'title' => 'Berhasil Disimpan',
                    'text' => 'Faktur Pajak "' . $faktur->no_faktur . '" berhasil dicatat.',
                ]);
            }
        });

        $this->modalFormOpen = false;
        $this->dispatch('table-reload');
    }

    public function toggleSelesai(int $id): void
    {
        $user = Auth::user();
        abort_if(! $user->isOwner() && ! $user->can('pajak.toggle_selesai') && ! $user->can('pajak.edit'), 403, 'Akses ubah status faktur dibatasi.');

        $faktur = FakturPajak::findOrFail($id);
        $faktur->is_selesai = ! $faktur->is_selesai;
        $faktur->selesai_at = $faktur->is_selesai ? now() : null;
        $faktur->selesai_by = $faktur->is_selesai ? $user->id : null;
        $faktur->save();

        $statusText = $faktur->is_selesai ? 'Selesai' : 'Belum Selesai';
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Status Diperbarui',
            'text' => "Status Faktur {$faktur->no_faktur} diubah menjadi {$statusText}.",
        ]);
        $this->dispatch('table-reload');
    }

    public function openDetail(int $id): void
    {
        $this->selectedFaktur = FakturPajak::with(['user', 'cabang', 'selesaiBy'])->findOrFail($id);
        $this->modalDetailOpen = true;
    }

    public function closeDetail(): void
    {
        $this->modalDetailOpen = false;
        $this->selectedFaktur = null;
    }

    public function delete(int $id): void
    {
        $user = Auth::user();
        abort_if(! $user->isOwner() && ! $user->can('pajak.delete'), 403, 'Akses hapus faktur pajak dibatasi.');

        $faktur = FakturPajak::findOrFail($id);
        $no = $faktur->no_faktur;
        $faktur->delete();
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Berhasil Dihapus',
            'text' => "Faktur Pajak {$no} berhasil dihapus.",
        ]);
        $this->dispatch('table-reload');
    }

    public function closeModal(): void
    {
        $this->modalFormOpen = false;
    }

    public function render()
    {
        $user = Auth::user();
        $query = FakturPajak::query()->with(['user', 'cabang']);

        // Scope Cabang
        if (! $user->isOwner()) {
            $query->where('cabang_id', $user->cabang_id);
        } elseif ($this->cabangFilter !== 'semua') {
            $query->where('cabang_id', $this->cabangFilter);
        }

        // Filter Periode
        $this->applyDateFilter($query);

        // Filter Status Selesai
        if ($this->statusFilter === 'selesai') {
            $query->where('is_selesai', true);
        } elseif ($this->statusFilter === 'belum') {
            $query->where('is_selesai', false);
        }

        // Filter Saluran & Kode
        if ($this->viaFilter !== 'semua') {
            $query->where('via_pajak_snapshot', $this->viaFilter);
        }
        if ($this->kodeFilter !== 'semua') {
            $query->where('kode_pajak_snapshot', $this->kodeFilter);
        }
        if ($this->orangFilter !== 'semua') {
            $query->where('master_orang_pajak_id', $this->orangFilter);
        }

        // Pencarian Bebas Multi-Kata Tokenized (Rule 3)
        if (! empty(trim($this->search))) {
            $words = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(no_faktur) LIKE ?', [$pattern])
                           ->orWhereRaw('LOWER(nama_pajak_snapshot) LIKE ?', [$pattern])
                           ->orWhereRaw('LOWER(barang_snapshot) LIKE ?', [$pattern])
                           ->orWhereRaw("LOWER(COALESCE(catatan, '')) LIKE ?", [$pattern]);
                    });
                }
            });
        }

        // Urutan Descending created_at
        $records = $query->orderByDesc('created_at')->get();
        $totalCount = $records->count();

        // Map data untuk Tabulator
        $tableData = $records->values()->map(function ($r, $idx) use ($totalCount) {
            $dateCarbon = Carbon::parse($r->tanggal);
            return [
                'id' => (int) $r->id,
                'no' => $totalCount - $idx, // Descending nomor urut
                'is_selesai' => (bool) $r->is_selesai,
                'no_faktur' => $r->no_faktur,
                'tanggal' => $dateCarbon->format('d/m/Y'),
                'tanggal_group' => $dateCarbon->translatedFormat('d M Y'),
                'waktu_epoch' => $r->created_at ? $r->created_at->timestamp : $dateCarbon->timestamp,
                'user_input' => $r->user?->name ?: 'Sistem',
                'jam' => $r->created_at ? $r->created_at->format('H:i') : '00:00',
                'nama' => $r->nama_pajak_snapshot,
                'barang' => $r->barang_snapshot,
                'via' => $r->via_pajak_snapshot,
                'kode' => $r->kode_pajak_snapshot ?: '-',
                'bruto' => (float) $r->bruto,
                'dpp' => (float) $r->dpp,
                'dpp_lain' => (float) $r->dpp_lain,
                'ppn_nominal' => (float) $r->ppn_nominal,
                'jenis_pph' => $r->jenis_pph_snapshot,
                'pph_persen' => (float) $r->pph_persen,
                'pph_nominal' => (float) $r->pph_nominal,
                'netto' => (float) $r->netto,
                'catatan' => $r->catatan ?: '',
            ];
        })->toArray();

        // Data opsi master untuk filter & modal
        $viaOptions = MasterViaPajak::where('is_aktif', true)->orderBy('nama')->get();
        $kodeOptions = MasterKodePajak::where('is_aktif', true)->orderBy('kode')->get();
        $pphOptions = MasterPph::where('is_aktif', true)->orderBy('persentase')->get();
        $orangOptions = MasterOrangPajak::where('is_aktif', true)->orderBy('nama')->get();
        $cabangs = $user->isOwner() ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect([]);

        // Rule 24: Isolasi modal dari table updates
        if (! $this->modalFormOpen && ! $this->modalDetailOpen) {
            $this->dispatch('faktur-pajak-table-updated', data: $tableData);
        }

        return view('livewire.faktur-pajak.index', [
            'tableData' => $tableData,
            'viaOptions' => $viaOptions,
            'kodeOptions' => $kodeOptions,
            'pphOptions' => $pphOptions,
            'orangOptions' => $orangOptions,
            'cabangs' => $cabangs,
            'isOwner' => $user->isOwner(),
            'isHQ' => $user->isOwner(),
        ]);
    }
}

