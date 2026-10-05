<?php

namespace App\Livewire\Transaksi;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\KasKeluar;
use App\Models\MasterPpn;
use App\Models\MasterServis;
use App\Models\MetodePembayaran;
use App\Models\ModalAwal;
use App\Models\Pegawai;
use App\Models\Produk;
use App\Models\TransaksiFull;
use App\Models\TransaksiFullDetail;
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
#[Title('Transaksi Harian - Soulmate Komputer')]
class Harian extends Component
{
    use WithPagination, WithSorting;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'semua')]
    public string $tipeFilter = 'semua'; // 'semua' | 'penjualan' | 'servis'

    #[Url(except: 'semua')]
    public string $statusFilter = 'semua'; // 'semua' | 'Lunas' | 'BON' | 'Belum Lunas' | 'Batal'

    #[Url(except: 'semua')]
    public string $metodeFilter = 'semua';

    public string $periodeMode = 'hari_ini'; // 'hari_ini' | 'range' | 'bulanan' | 'tahunan' | 'semua'
    public string $startDate = '';
    public string $endDate = '';
    public string $filterBulan = '';
    public string $filterTahun = '';
    public int $perPage = 15;

    // State Modal Form (Create / Edit)
    public bool $modalFormOpen = false;
    public ?int $transaksiId = null;
    public string $formNoTransaksi = '';
    public string $formTipeTransaksi = 'penjualan';
    public ?string $formKategoriPerangkat = 'laptop';
    public string $formTanggal = '';
    public string $formTanggalOnly = '';
    public string $formJam = '';
    public ?int $formPegawaiId = null;
    public string $formCustomerType = 'umum';
    public ?int $formCustomerId = null;
    public string $formCustomerSearch = '';
    public string $formNamaPelanggan = 'Konsumen';
    public string $formNoHp = '';
    public ?int $formMetodePembayaranId = null;
    public string $formMetodePembayaran = 'Cash';
    public ?int $formPpnId = null;
    public float $formPpnPersen = 0;
    public string $formStatus = 'Lunas';
    public string $formCatatan = '';
    public array $items = [];
    public array $productSearchQueries = [];

    // State Modal Detail
    public bool $modalDetailOpen = false;
    public ?TransaksiFull $selectedTransaksi = null;

    // State Modal Delete
    public bool $modalDeleteOpen = false;
    public ?int $deleteId = null;
    public ?string $deleteCode = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->can('transaksi.harian.view'))) {
            abort(403, 'Akses modul Transaksi Harian dibatasi.');
        }

        $this->startDate = now()->toDateString();
        $this->endDate = now()->toDateString();
        $this->filterBulan = now()->format('m');
        $this->filterTahun = now()->format('Y');

        $this->sortField = 'tanggal';
        $this->sortDirection = 'desc';
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
        $this->resetPage();
    }

    public function updatedFilterBulan(): void
    {
        if ($this->periodeMode === 'bulanan') {
            $this->applyBulanan();
        }
        $this->resetPage();
    }

    public function updatedFilterTahun(): void
    {
        if ($this->periodeMode === 'bulanan') {
            $this->applyBulanan();
        } elseif ($this->periodeMode === 'tahunan') {
            $this->applyTahunan();
        }
        $this->resetPage();
    }

    private function applyBulanan(): void
    {
        $year = (int) ($this->filterTahun ?: now()->year);
        $month = (int) ($this->filterBulan ?: now()->month);
        $date = Carbon::createFromDate($year, $month, 1);
        $this->startDate = $date->copy()->startOfMonth()->toDateString();
        $this->endDate = $date->copy()->endOfMonth()->toDateString();
    }

    private function applyTahunan(): void
    {
        $year = (int) ($this->filterTahun ?: now()->year);
        $date = Carbon::createFromDate($year, 1, 1);
        $this->startDate = $date->copy()->startOfYear()->toDateString();
        $this->endDate = $date->copy()->endOfYear()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTipeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingMetodeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
    }

    // =========================================================================
    // REALTIME REVERB WEBSOCKET LISTENER (Rule 24 Form Isolation Guard)
    // =========================================================================

    #[On('echo:transaksi-updates,TransaksiUpdated')]
    public function handleRealtimeTransaksiUpdate($event = null): void
    {
        // Proteksi Rule 24: Jangan ganggu saat user sedang membuka / mengetik form
        if ($this->modalFormOpen || $this->modalDetailOpen || $this->modalDeleteOpen) {
            return;
        }

        // Livewire akan me-render kartu ringkasan keuangan dan menyinkronkan data jika perlu
    }

    // =========================================================================
    // MODAL FORM (CREATE / EDIT)
    // =========================================================================

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->transaksiId = null;
        $this->formTanggal = now()->format('Y-m-d\TH:i');
        $this->formTanggalOnly = now()->toDateString();
        $this->formJam = now()->format('H:i');
        $this->formTipeTransaksi = 'penjualan';
        $this->formKategoriPerangkat = 'laptop';
        $this->formNamaPelanggan = 'Konsumen';
        $this->formPegawaiId = null;
        $this->generateDefaultNoTransaksi();

        $activePpn = MasterPpn::getAktifCached();
        if ($activePpn) {
            $this->formPpnId = $activePpn->id;
            $this->formPpnPersen = (float) $activePpn->persen;
        }

        $defaultMetode = MetodePembayaran::where('is_aktif', true)->first();
        if ($defaultMetode) {
            $this->formMetodePembayaranId = $defaultMetode->id;
            $this->formMetodePembayaran = $defaultMetode->nama;
        }

        $this->addItemRow();
        $this->modalFormOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $transaksi = TransaksiFull::with(['details', 'customer'])->findOrFail($id);
        $this->resetForm();

        $this->transaksiId = $transaksi->id;
        $this->formNoTransaksi = $transaksi->no_transaksi_frontend;
        $this->formTipeTransaksi = $transaksi->tipe_transaksi;
        $this->formKategoriPerangkat = $transaksi->kategori_perangkat ?? ($transaksi->tipe_transaksi === 'servis' ? 'laptop' : null);
        $this->formTanggal = $transaksi->tanggal ? $transaksi->tanggal->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
        $this->formTanggalOnly = $transaksi->tanggal ? $transaksi->tanggal->toDateString() : now()->toDateString();
        $this->formJam = $transaksi->tanggal ? $transaksi->tanggal->format('H:i') : now()->format('H:i');
        $this->formPegawaiId = $transaksi->pegawai_id;
        $this->formCustomerType = $transaksi->customer_id ? 'langganan' : 'umum';
        $this->formCustomerId = $transaksi->customer_id;
        $this->formNamaPelanggan = $transaksi->nama_pelanggan ?: 'Konsumen';
        $this->formNoHp = $transaksi->no_hp ?? '';
        $this->formMetodePembayaranId = $transaksi->metode_pembayaran_id;
        $this->formMetodePembayaran = $transaksi->metode_pembayaran;
        $this->formPpnId = $transaksi->ppn_id;
        $this->formPpnPersen = (float) $transaksi->ppn_persen;
        $this->formStatus = $transaksi->status;
        $this->formCatatan = $transaksi->catatan ?? '';

        $this->items = [];
        foreach ($transaksi->details as $idx => $d) {
            $this->items[] = [
                'produk_id' => $d->produk_id,
                'nama_item' => $d->nama_item,
                'qty' => $d->qty,
                'harga_modal' => (float) $d->harga_modal,
                'harga_jual' => (float) $d->harga_jual,
                'subtotal_modal' => (float) $d->subtotal_modal,
                'subtotal_jual' => (float) $d->subtotal_jual,
                'laba' => (float) $d->laba,
                'ppn_id' => $d->ppn_id,
                'ppn_persen' => (float) ($d->ppn_persen ?? 0),
                'ppn_nominal' => (float) ($d->ppn_nominal ?? 0),
            ];
            $this->productSearchQueries[$idx] = $d->nama_item;
        }

        if (empty($this->items)) {
            $this->addItemRow();
        }

        $this->modalFormOpen = true;
    }

    public function closeFormModal(): void
    {
        $this->modalFormOpen = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->transaksiId = null;
        $this->formNoTransaksi = '';
        $this->formTipeTransaksi = 'penjualan';
        $this->formKategoriPerangkat = 'laptop';
        $this->formTanggal = '';
        $this->formTanggalOnly = '';
        $this->formJam = '';
        $this->formPegawaiId = null;
        $this->formCustomerType = 'umum';
        $this->formCustomerId = null;
        $this->formCustomerSearch = '';
        $this->formNamaPelanggan = 'Konsumen';
        $this->formNoHp = '';
        $this->formMetodePembayaranId = null;
        $this->formMetodePembayaran = 'Cash';
        $this->formPpnId = null;
        $this->formPpnPersen = 0;
        $this->formStatus = 'Lunas';
        $this->formCatatan = '';
        $this->items = [];
        $this->productSearchQueries = [];
        $this->resetValidation();
    }

    public function updatedFormTipeTransaksi(): void
    {
        if ($this->formTipeTransaksi === 'servis' && empty($this->formKategoriPerangkat)) {
            $this->formKategoriPerangkat = 'laptop';
        }

        if (! $this->transaksiId) {
            $this->generateDefaultNoTransaksi();
        }
    }

    public function updatedFormTanggal(): void
    {
        if (! $this->transaksiId) {
            $this->generateDefaultNoTransaksi();
        }
    }

    public function generateDefaultNoTransaksi(): void
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');

        $cabangId = $isOwner && $activeBranchSession !== 'hq' ? (int) $activeBranchSession : ($user->cabang_id ?: 1);

        $prefix = $this->formTipeTransaksi === 'servis' ? 'SV' : 'PJ';
        $date = ! empty($this->formTanggal) ? Carbon::parse($this->formTanggal) : now();
        $yymmdd = $date->format('ymd');

        $countToday = TransaksiFull::where('cabang_id', $cabangId)
            ->whereDate('tanggal', $date->toDateString())
            ->where('tipe_transaksi', $this->formTipeTransaksi)
            ->count() + 1;

        $sequence = str_pad($countToday, 5, '0', STR_PAD_LEFT);
        $this->formNoTransaksi = "{$prefix}-{$yymmdd}-{$sequence}";
    }

    public function selectCustomer(int $id, string $nama, ?string $noHp): void
    {
        $this->formCustomerId = $id;
        $this->formNamaPelanggan = $nama;
        $this->formNoHp = $noHp ?? '';
        $this->formCustomerSearch = $nama;
    }

    public function updatedFormPpnId($value): void
    {
        if ($value) {
            $ppn = MasterPpn::find($value);
            $this->formPpnPersen = $ppn ? (float) $ppn->persen : 0;
        } else {
            $this->formPpnPersen = 0;
        }
    }

    public function updatedFormMetodePembayaranId($value): void
    {
        if ($value) {
            $metode = MetodePembayaran::find($value);
            $this->formMetodePembayaran = $metode ? $metode->nama : 'Cash';
        }
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'items.')) {
            $parts = explode('.', $propertyName);
            if (count($parts) >= 3 && $parts[2] === 'nama_item') {
                $idx = (int) $parts[1];
                $this->items[$idx]['produk_id'] = null;
                $this->productSearchQueries[$idx] = $this->items[$idx]['nama_item'] ?? '';
            }
            if (count($parts) >= 3 && $parts[2] === 'ppn_id') {
                $idx = (int) $parts[1];
                $val = $this->items[$idx]['ppn_id'] ?? null;
                if ($val) {
                    $ppn = MasterPpn::find($val);
                    $this->items[$idx]['ppn_persen'] = $ppn ? (float) $ppn->persen : 0;
                } else {
                    $this->items[$idx]['ppn_persen'] = 0;
                }
            }
            if (count($parts) >= 2 && is_numeric($parts[1])) {
                $idx = (int) $parts[1];
                $this->recalculateRow($idx);
            }
        }
    }

    public function addItemRow(): void
    {
        $idx = count($this->items);
        $activePpn = MasterPpn::getAktifCached();
        $this->items[] = [
            'produk_id' => null,
            'nama_item' => '',
            'qty' => 1,
            'harga_modal' => 0,
            'harga_jual' => 0,
            'subtotal_modal' => 0,
            'subtotal_jual' => 0,
            'laba' => 0,
            'ppn_id' => $activePpn ? $activePpn->id : null,
            'ppn_persen' => $activePpn ? (float) $activePpn->persen : 0,
            'ppn_nominal' => 0,
        ];
        $this->productSearchQueries[$idx] = '';
    }

    public function removeItemRow(int $index): void
    {
        unset($this->items[$index]);
        unset($this->productSearchQueries[$index]);
        $this->items = array_values($this->items);
        $this->productSearchQueries = array_values($this->productSearchQueries);

        if (empty($this->items)) {
            $this->addItemRow();
        }
    }

    public function selectItemSuggestion(int $index, string $nama, float $hargaModal, float $hargaJual, ?int $produkId = null): void
    {
        if (! isset($this->items[$index])) return;

        $qty = (int) ($this->items[$index]['qty'] ?? 1);
        if ($qty < 1) $qty = 1;

        $this->items[$index]['produk_id'] = $produkId;
        $this->items[$index]['nama_item'] = $nama;
        $this->items[$index]['harga_modal'] = $hargaModal;
        $this->items[$index]['harga_jual'] = $hargaJual;
        $this->items[$index]['qty'] = $qty;
        $this->items[$index]['subtotal_modal'] = $hargaModal * $qty;
        $this->items[$index]['subtotal_jual'] = $hargaJual * $qty;
        $this->items[$index]['laba'] = ($hargaJual * $qty) - ($hargaModal * $qty);

        $this->productSearchQueries[$index] = $nama;
    }

    public function selectProductForItem(int $index, int $produkId, string $nama, float $hargaBeli, float $hargaJual): void
    {
        $this->selectItemSuggestion($index, $nama, $hargaBeli, $hargaJual, $produkId);
    }

    public function recalculateRow(int $index): void
    {
        if (! isset($this->items[$index])) return;

        $qty = (int) ($this->items[$index]['qty'] ?? 1);
        if ($qty < 1) $qty = 1;
        $this->items[$index]['qty'] = $qty;

        $rawModal = $this->items[$index]['harga_modal'] ?? 0;
        $rawJual = $this->items[$index]['harga_jual'] ?? 0;

        $hargaModal = is_numeric($rawModal) ? (float) $rawModal : (float) preg_replace('/[^0-9]/', '', (string) $rawModal);
        $hargaJual = is_numeric($rawJual) ? (float) $rawJual : (float) preg_replace('/[^0-9]/', '', (string) $rawJual);
        $ppnPersen = (float) ($this->items[$index]['ppn_persen'] ?? 0);

        $subJual = $hargaJual * $qty;
        $ppnNominal = ($subJual * $ppnPersen) / 100;

        $this->items[$index]['harga_modal'] = $hargaModal;
        $this->items[$index]['harga_jual'] = $hargaJual;
        $this->items[$index]['subtotal_modal'] = $hargaModal * $qty;
        $this->items[$index]['subtotal_jual'] = $subJual;
        $this->items[$index]['laba'] = $subJual - ($hargaModal * $qty);
        $this->items[$index]['ppn_nominal'] = $ppnNominal;
    }

    public function simpanTransaksi(): void
    {
        $this->validate([
            'formNoTransaksi' => 'required|string|max:100',
            'formTipeTransaksi' => 'required|in:penjualan,servis',
            'formKategoriPerangkat' => 'nullable|in:laptop,komputer,printer,lainnya',
            'formPegawaiId' => 'nullable|exists:pegawais,id',
            'formNamaPelanggan' => 'required|string|max:150',
            'formMetodePembayaran' => 'required|string|max:50',
            'formStatus' => 'required|in:Lunas,Bon,BON',
            'items' => 'required|array|min:1',
            'items.*.nama_item' => 'required|string|max:200',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga_modal' => 'required|numeric|min:0',
            'items.*.harga_jual' => 'required|numeric|min:0',
        ], [
            'formNoTransaksi.required' => 'Nomor transaksi wajib diisi.',
            'formNamaPelanggan.required' => 'Nama pelanggan wajib diisi.',
            'items.required' => 'Minimal 1 item transaksi wajib diinput.',
            'items.min' => 'Minimal 1 item transaksi wajib diinput.',
            'items.*.nama_item.required' => 'Nama barang/layanan wajib diisi.',
            'items.*.qty.min' => 'Jumlah minimal 1 unit.',
            'items.*.harga_modal.required' => 'Biaya modal wajib diisi (minimal 0).',
            'items.*.harga_jual.required' => 'Harga jual wajib diisi (minimal 0).',
        ]);

        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $cabangId = $isOwner && $activeBranchSession !== 'hq' ? (int) $activeBranchSession : ($user->cabang_id ?: 1);

        try {
            DB::beginTransaction();

            $totalModal = 0;
            $totalJual = 0;
            $totalPpnNominal = 0;

            foreach ($this->items as $row) {
                $qty = (int) $row['qty'];
                $hModal = (float) $row['harga_modal'];
                $hJual = (float) $row['harga_jual'];
                $subJual = $hJual * $qty;
                $ppnPersen = (float) ($row['ppn_persen'] ?? 0);
                $rowPpnNominal = ($subJual * $ppnPersen) / 100;

                $totalModal += ($hModal * $qty);
                $totalJual += $subJual;
                $totalPpnNominal += $rowPpnNominal;
            }

            $totalLaba = $totalJual - $totalModal;
            $grandTotal = $totalJual + $totalPpnNominal;

            $statusNormalized = strtolower($this->formStatus) === 'bon' ? 'Bon' : 'Lunas';
            $authUserId = Auth::id();

            if ($this->transaksiId) {
                $existingTrx = TransaksiFull::find($this->transaksiId);
                $tanggalParsed = $existingTrx ? $existingTrx->tanggal : now();
                $userId = $existingTrx?->user_id ?: $authUserId;

                if ($statusNormalized === 'Lunas') {
                    if ($existingTrx && strtolower($existingTrx->status) === 'bon') {
                        // Perubahan dari BON ke Lunas -> catat pelunasan
                        $tanggalLunas = now();
                        $pelunasUserId = $authUserId;
                    } elseif ($existingTrx && $existingTrx->status === 'Lunas') {
                        $tanggalLunas = $existingTrx->tanggal_lunas ?: $existingTrx->tanggal;
                        $pelunasUserId = $existingTrx->pelunas_user_id;
                    } else {
                        $tanggalLunas = now();
                        $pelunasUserId = null;
                    }
                } else {
                    $tanggalLunas = null;
                    $pelunasUserId = null;
                }
            } else {
                $tanggalParsed = now();
                $userId = $authUserId;

                if ($statusNormalized === 'Lunas') {
                    $tanggalLunas = $tanggalParsed;
                    $pelunasUserId = null; // Transaksi baru langsung lunas -> tidak perlu pelunas
                } else {
                    $tanggalLunas = null;
                    $pelunasUserId = null;
                }
            }

            $pegawaiId = $this->formPegawaiId ? (int) $this->formPegawaiId : null;

            $data = [
                'cabang_id' => $cabangId,
                'user_id' => $userId,
                'pegawai_id' => $pegawaiId,
                'customer_id' => $this->formCustomerType === 'langganan' ? $this->formCustomerId : null,
                'nama_pelanggan' => trim($this->formNamaPelanggan) ?: 'Konsumen',
                'no_hp' => trim($this->formNoHp) ?: null,
                'no_transaksi' => trim($this->formNoTransaksi),
                'tipe_transaksi' => $this->formTipeTransaksi,
                'kategori_perangkat' => $this->formTipeTransaksi === 'servis' ? ($this->formKategoriPerangkat ?: 'laptop') : null,
                'tanggal' => $tanggalParsed,
                'tanggal_lunas' => $tanggalLunas,
                'pelunas_user_id' => $pelunasUserId,
                'metode_pembayaran_id' => $this->formMetodePembayaranId,
                'metode_pembayaran' => $this->formMetodePembayaran,
                'ppn_id' => $this->formPpnId,
                'ppn_persen' => $this->formPpnPersen,
                'ppn_nominal' => $totalPpnNominal,
                'total_modal' => $totalModal,
                'total_jual' => $totalJual,
                'total_laba' => $totalLaba,
                'grand_total' => $grandTotal,
                'status' => $statusNormalized,
                'catatan' => trim($this->formCatatan) ?: null,
            ];

            if ($this->transaksiId) {
                $transaksi = TransaksiFull::findOrFail($this->transaksiId);
                $transaksi->update($data);
                $transaksi->details()->delete();
            } else {
                $transaksi = TransaksiFull::create($data);
            }

            foreach ($this->items as $row) {
                $qty = (int) $row['qty'];
                $hModal = (float) $row['harga_modal'];
                $hJual = (float) $row['harga_jual'];
                $subJual = $hJual * $qty;
                $ppnPersen = (float) ($row['ppn_persen'] ?? 0);
                $rowPpnNominal = ($subJual * $ppnPersen) / 100;

                TransaksiFullDetail::create([
                    'transaksi_full_id' => $transaksi->id,
                    'produk_id' => $row['produk_id'] ?? null,
                    'nama_item' => trim($row['nama_item']),
                    'qty' => $qty,
                    'harga_modal' => $hModal,
                    'harga_jual' => $hJual,
                    'subtotal_modal' => $hModal * $qty,
                    'subtotal_jual' => $subJual,
                    'laba' => $subJual - ($hModal * $qty),
                    'ppn_id' => ! empty($row['ppn_id']) ? (int) $row['ppn_id'] : null,
                    'ppn_persen' => $ppnPersen,
                    'ppn_nominal' => $rowPpnNominal,
                ]);
            }

            DB::commit();

            $this->closeFormModal();
            session()->flash('success', "Transaksi {$transaksi->no_transaksi_frontend} berhasil disimpan!");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('formError', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function updateStatusDirect(int $id, string $newStatus): void
    {
        $statusNormalized = strtolower($newStatus) === 'bon' ? 'Bon' : 'Lunas';
        $transaksi = TransaksiFull::find($id);
        if ($transaksi) {
            $oldStatus = $transaksi->status;
            $transaksi->status = $statusNormalized;
            if ($statusNormalized === 'Lunas') {
                if (strtolower($oldStatus) === 'bon') {
                    $transaksi->tanggal_lunas = now();
                    $transaksi->pelunas_user_id = Auth::id();
                } else {
                    $transaksi->tanggal_lunas = $transaksi->tanggal_lunas ?: now();
                }
            } else {
                $transaksi->tanggal_lunas = null;
                $transaksi->pelunas_user_id = null;
            }
            $transaksi->save();
            session()->flash('success', "Status transaksi {$transaksi->no_transaksi_frontend} diubah ke {$statusNormalized}.");
        }
    }

    public function updateMetodeDirect(int $id, ?int $metodeId): void
    {
        $transaksi = TransaksiFull::find($id);
        if ($transaksi) {
            $metode = $metodeId ? MetodePembayaran::find($metodeId) : null;
            $transaksi->metode_pembayaran_id = $metode?->id;
            $transaksi->metode_pembayaran = $metode?->nama ?? '-';
            $transaksi->save();
            session()->flash('success', "Metode pembayaran transaksi {$transaksi->no_transaksi_frontend} berhasil diperbarui.");
        }
    }

    public function openDetailModal(int $id): void
    {
        $this->selectedTransaksi = TransaksiFull::with(['details.produk', 'customer', 'cabang', 'pegawai', 'ppn', 'userInput', 'pelunas'])->findOrFail($id);
        $this->modalDetailOpen = true;
    }

    public function closeDetailModal(): void
    {
        $this->modalDetailOpen = false;
        $this->selectedTransaksi = null;
    }

    public function confirmDelete(int $id, string $code): void
    {
        $this->deleteId = $id;
        $this->deleteCode = $code;
        $this->modalDeleteOpen = true;
    }

    public function deleteTransaksi(): void
    {
        if (! $this->deleteId) return;

        $transaksi = TransaksiFull::find($this->deleteId);
        if ($transaksi) {
            $code = $transaksi->no_transaksi_frontend;
            $transaksi->delete();
            session()->flash('success', "Transaksi {$code} berhasil dihapus.");
        }

        $this->modalDeleteOpen = false;
        $this->deleteId = null;
        $this->deleteCode = '';
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');
        $isHQ = $isOwner && ($activeBranchSession === 'hq' || ! $activeBranchSession);

        $ppnList = MasterPpn::where('is_aktif', true)->orderBy('persen')->get();
        $metodeList = MetodePembayaran::where('is_aktif', true)->orderBy('urutan')->get();

        $pegawaiList = Pegawai::where('is_aktif', true)
            ->when(! $isHQ && $user->cabang_id, fn($q) => $q->where('cabang_id', $user->cabang_id))
            ->orderBy('nama')
            ->get();

        // Autocomplete Customer
        $customerSuggestions = [];
        if ($this->modalFormOpen && ! empty($this->formCustomerSearch) && $this->formCustomerType === 'langganan' && ! $this->formCustomerId) {
            $custQ = Customer::query();
            if (! $isHQ) {
                $targetCabang = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
                if ($targetCabang) $custQ->where('cabang_id', $targetCabang);
            }
            $pattern = '%' . strtolower($this->formCustomerSearch) . '%';
            $customerSuggestions = $custQ->where(function ($q) use ($pattern) {
                $q->whereRaw('LOWER(nama) LIKE ?', [$pattern])
                  ->orWhereRaw('LOWER(COALESCE(no_hp, \'\')) LIKE ?', [$pattern]);
            })->take(5)->get();
        }

        // Suggestions Master Produk & Master Servis untuk Item Baris Form
        $productSuggestionsMap = [];
        if ($this->modalFormOpen) {
            foreach ($this->items as $idx => $itemRow) {
                $queryStr = trim($itemRow['nama_item'] ?? '');
                if (mb_strlen($queryStr) >= 1 && empty($itemRow['produk_id'])) {
                    $keywords = array_values(array_filter(explode(' ', strtolower($queryStr))));

                    $prodQ = Produk::query();
                    foreach ($keywords as $kw) {
                        $prodQ->where(function ($q) use ($kw) {
                            $q->whereRaw('LOWER(nama_produk) LIKE ?', ["%{$kw}%"])
                              ->orWhereRaw('LOWER(COALESCE(barcode, \'\')) LIKE ?', ["%{$kw}%"]);
                        });
                    }
                    $prodMatches = $prodQ->take(5)->get()->map(fn($p) => [
                        'type' => 'produk',
                        'id' => $p->id,
                        'nama' => $p->nama_produk,
                        'kode' => $p->barcode ?: 'PRODUK',
                        'harga_modal' => (float) $p->harga_beli,
                        'harga_jual' => (float) $p->harga_jual,
                        'badge' => 'Master Produk',
                    ]);

                    $servisQ = MasterServis::aktif();
                    foreach ($keywords as $kw) {
                        $servisQ->where(function ($q) use ($kw) {
                            $q->whereRaw('LOWER(nama_item) LIKE ?', ["%{$kw}%"])
                              ->orWhereRaw('LOWER(COALESCE(kode_item, \'\')) LIKE ?', ["%{$kw}%"]);
                        });
                    }
                    $servisMatches = $servisQ->take(5)->get()->map(fn($s) => [
                        'type' => 'servis',
                        'id' => null,
                        'nama' => $s->nama_item,
                        'kode' => $s->kode_item ?: ($s->jenis === 'sparepart' ? 'SPAREPART' : 'JASA'),
                        'harga_modal' => (float) ($s->harga_modal ?? 0),
                        'harga_jual' => (float) ($s->harga_jual ?? 0),
                        'badge' => $s->jenis === 'sparepart' ? 'Sparepart' : 'Jasa Servis',
                    ]);

                    $productSuggestionsMap[$idx] = $prodMatches->concat($servisMatches)->take(8)->values()->all();
                }
            }
        }

        // Query Utama Transaksi Harian
        $query = TransaksiFull::query();

        // Scoping Cabang
        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            if ($targetCabangId) {
                $query->where('transaksi_fulls.cabang_id', $targetCabangId);
            }
        }

        // Filter Rentang Tanggal
        if (! empty($this->startDate)) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate ?: $this->startDate)->endOfDay();
            $query->whereBetween('transaksi_fulls.tanggal', [$start, $end]);
        }

        // Filter Tipe
        if ($this->tipeFilter !== 'semua') {
            $query->where('transaksi_fulls.tipe_transaksi', $this->tipeFilter);
        }

        // Filter Status
        if ($this->statusFilter !== 'semua') {
            $query->where('transaksi_fulls.status', $this->statusFilter);
        }

        // Filter Metode
        if ($this->metodeFilter !== 'semua') {
            $query->where('transaksi_fulls.metode_pembayaran', $this->metodeFilter);
        }

        // Multi-Word Loose Search
        if (! empty($this->search)) {
            $cleanSearch = trim($this->search);
            $words = array_filter(explode(' ', $cleanSearch));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(transaksi_fulls.no_transaksi) LIKE ?', [$pattern])
                           ->orWhereRaw('LOWER(transaksi_fulls.nama_pelanggan) LIKE ?', [$pattern])
                           ->orWhereRaw('LOWER(COALESCE(transaksi_fulls.no_hp, \'\')) LIKE ?', [$pattern])
                           ->orWhereHas('pegawai', fn ($pq) => $pq->whereRaw('LOWER(nama) LIKE ?', [$pattern]))
                           ->orWhereHas('details', fn ($dq) => $dq->whereRaw('LOWER(nama_item) LIKE ?', [$pattern]));
                    });
                }
            });
        }

        // Sorting
        $sortDir = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['tanggal', 'no_transaksi', 'grand_total', 'total_laba', 'status', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? 'transaksi_fulls.' . $this->sortField : 'transaksi_fulls.tanggal';
        $query->orderBy($sortColumn, $sortDir)->orderByDesc('transaksi_fulls.id');

        // Hitung Uang Cash - Terkunci ke Bulan Berjalan (Tanggal 1 s/d Hari Ini) & Pelunasan Lintas Bulan
        $startBulan = now()->startOfMonth()->startOfDay();
        $endBulan = now()->endOfDay();

        $modalAwalQ = ModalAwal::query()->whereBetween('tanggal', [$startBulan, $endBulan]);
        $kasKeluarQ = KasKeluar::query()->whereBetween('tanggal', [$startBulan, $endBulan]);
        $cashMasukQ = TransaksiFull::query()
            ->where('transaksi_fulls.status', 'Lunas')
            ->where(function ($q) {
                $q->whereRaw('LOWER(transaksi_fulls.metode_pembayaran) LIKE ?', ['%cash%'])
                    ->orWhereRaw('LOWER(transaksi_fulls.metode_pembayaran) LIKE ?', ['%tunai%']);
            })
            ->whereRaw('COALESCE(transaksi_fulls.tanggal_lunas, transaksi_fulls.tanggal) BETWEEN ? AND ?', [
                $startBulan->toDateTimeString(),
                $endBulan->toDateTimeString(),
            ]);

        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            if ($targetCabangId) {
                $modalAwalQ->where('cabang_id', $targetCabangId);
                $kasKeluarQ->where('cabang_id', $targetCabangId);
                $cashMasukQ->where('transaksi_fulls.cabang_id', $targetCabangId);
            }
        }

        $totalModalAwal = (float) $modalAwalQ->sum('nominal');

        // Kas Keluar Cash Bulan Berjalan
        $totalKasKeluarCash = (float) $kasKeluarQ->where(function ($q) {
            $q->whereHas('sumberDana', function ($sq) {
                $sq->whereRaw('LOWER(nama) LIKE ?', ['%cash%'])
                    ->orWhereRaw('LOWER(nama) LIKE ?', ['%tunai%'])
                    ->orWhereRaw('LOWER(nama) LIKE ?', ['%kasir%']);
            })->orWhereDoesntHave('sumberDana');
        })->sum('nominal');

        $transaksiCashMasuk = (float) $cashMasukQ->sum('total_jual');

        $totalUangCash = $totalModalAwal + $transaksiCashMasuk - $totalKasKeluarCash;

        // Ultra-Fast toBase() query payload for Tabulator (Zero Eloquent Model Hydration, ~85% RAM reduction)
        $tableData = [];
        if (! $this->modalFormOpen && ! $this->modalDetailOpen && ! $this->modalDeleteOpen) {
            $allCurrent = (clone $query)
                ->toBase()
                ->leftJoin('users as u', 'u.id', '=', 'transaksi_fulls.user_id')
                ->leftJoin('users as p', 'p.id', '=', 'transaksi_fulls.pelunas_user_id')
                ->leftJoin('pegawais as pg', 'pg.id', '=', 'transaksi_fulls.pegawai_id')
                ->select([
                    'transaksi_fulls.id',
                    'transaksi_fulls.no_transaksi',
                    'transaksi_fulls.cabang_id',
                    'transaksi_fulls.tipe_transaksi',
                    'transaksi_fulls.kategori_perangkat',
                    'transaksi_fulls.tanggal',
                    'transaksi_fulls.tanggal_lunas',
                    'transaksi_fulls.nama_pelanggan',
                    'transaksi_fulls.no_hp',
                    'transaksi_fulls.status',
                    'transaksi_fulls.metode_pembayaran_id',
                    'transaksi_fulls.metode_pembayaran',
                    'transaksi_fulls.catatan',
                    'transaksi_fulls.total_modal',
                    'transaksi_fulls.total_jual',
                    'transaksi_fulls.grand_total',
                    'transaksi_fulls.ppn_id',
                    'transaksi_fulls.ppn_persen',
                    'transaksi_fulls.ppn_nominal',
                    'transaksi_fulls.user_id',
                    'transaksi_fulls.pelunas_user_id',
                    'transaksi_fulls.pegawai_id',
                    'u.name as user_input_name',
                    'p.name as pelunas_name',
                    'pg.nama as pegawai_nama',
                ])
                ->get();

            $trxIds = $allCurrent->pluck('id')->all();
            $detailsByTrx = [];
            if (! empty($trxIds)) {
                $rawDetails = DB::table('transaksi_full_details')
                    ->whereIn('transaksi_full_id', $trxIds)
                    ->select([
                        'id',
                        'transaksi_full_id',
                        'qty',
                        'nama_item',
                        'harga_modal',
                        'harga_jual',
                        'subtotal_modal',
                        'subtotal_jual',
                        'ppn_persen',
                        'ppn_nominal',
                    ])
                    ->get();

                foreach ($rawDetails as $d) {
                    $detailsByTrx[$d->transaksi_full_id][] = $d;
                }
            }

            $tableData = $allCurrent->map(function ($item) use ($detailsByTrx) {
                $tglStr = $item->tanggal ? (string) $item->tanggal : '';
                $tglLunasStr = $item->tanggal_lunas ? (string) $item->tanggal_lunas : '';

                $waktuEpoch = $tglStr ? strtotime($tglStr) : 0;
                $waktu = $waktuEpoch ? date('d/m/Y H:i', $waktuEpoch) : '-';
                $jam = $waktuEpoch ? date('H:i', $waktuEpoch) : '-';
                $tglGroup = $tglStr ? Carbon::parse($tglStr)->translatedFormat('l, d F Y') : '-';

                $lunasEpoch = $tglLunasStr ? strtotime($tglLunasStr) : 0;
                $tglLunasFormatted = $lunasEpoch ? date('d/m/Y H:i', $lunasEpoch) : '-';
                $jamLunasFormatted = $lunasEpoch ? date('H:i', $lunasEpoch) : '-';

                $pelunasNama = ($item->status === 'Lunas' && ! empty($item->pelunas_user_id) && ! empty($item->pelunas_name)) ? $item->pelunas_name : null;

                $trxDetails = $detailsByTrx[$item->id] ?? [];
                $itemsList = [];
                $rincianArr = [];

                foreach ($trxDetails as $d) {
                    $qty = (int) $d->qty;
                    $hargaModal = (float) $d->harga_modal;
                    $hargaJual = (float) $d->harga_jual;
                    $subModal = (float) ($d->subtotal_modal ?: ($hargaModal * $qty));
                    $subJual = (float) ($d->subtotal_jual ?: ($hargaJual * $qty));
                    $ppnPersen = (float) $d->ppn_persen;
                    $ppnNominal = (float) $d->ppn_nominal;

                    $itemsList[] = [
                        'qty' => $qty,
                        'nama' => $d->nama_item,
                        'harga_modal' => $hargaModal,
                        'harga_jual' => $hargaJual,
                        'subtotal_modal' => $subModal,
                        'subtotal_jual' => $subJual,
                        'has_ppn' => ($ppnPersen > 0 || $ppnNominal > 0),
                        'ppn_persen' => $ppnPersen,
                        'ppn_nominal' => $ppnNominal,
                    ];

                    $rincianArr[] = "{$qty}x {$d->nama_item}";
                }

                $rincianItem = ! empty($rincianArr) ? implode(', ', $rincianArr) : 'Tanpa item';

                return [
                    'id' => (int) $item->id,
                    'no_transaksi' => TransaksiFull::formatNoTransaksiFrontend($item->no_transaksi),
                    'tipe_transaksi' => $item->tipe_transaksi,
                    'waktu' => $waktu,
                    'waktu_epoch' => $waktuEpoch,
                    'jam' => $jam,
                    'tanggal_group' => $tglGroup,
                    'petugas' => $item->pegawai_nama ?: 'Tanpa Pegawai',
                    'user_input' => $item->user_input_name ?: 'System',
                    'pelunas_nama' => $pelunasNama,
                    'tanggal_lunas' => $tglLunasFormatted,
                    'jam_lunas' => $jamLunasFormatted,
                    'pelanggan' => $item->nama_pelanggan,
                    'no_hp' => $item->no_hp ?: '-',
                    'items_list' => $itemsList,
                    'rincian_item' => $rincianItem,
                    'metode_pembayaran_id' => $item->metode_pembayaran_id,
                    'metode_pembayaran' => $item->metode_pembayaran ?: '-',
                    'total_modal' => (float) $item->total_modal,
                    'total_jual' => (float) $item->total_jual,
                    'grand_total' => (float) $item->grand_total,
                    'has_ppn' => ((float) $item->ppn_persen > 0 || (float) $item->ppn_nominal > 0),
                    'pajak_label' => ((float) $item->ppn_persen > 0 || (float) $item->ppn_nominal > 0) ? 'PPN' : 'Non PPN',
                    'ppn_persen' => (float) $item->ppn_persen,
                    'ppn_nominal' => (float) $item->ppn_nominal,
                    'status' => $item->status,
                    'keterangan' => $item->catatan ?: '-',
                ];
            })->values()->all();

            $this->dispatch('transaksi-harian-table-updated', data: $tableData);
        }

        return view('livewire.transaksi.harian', [
            'totalUangCash' => $totalUangCash,
            'tableData' => $tableData,
            'ppnList' => $ppnList,
            'metodeList' => $metodeList,
            'pegawaiList' => $pegawaiList,
            'customerSuggestions' => $customerSuggestions,
            'productSuggestionsMap' => $productSuggestionsMap,
            'isHQ' => $isHQ,
            'isOwner' => $isOwner,
        ]);
    }
}
