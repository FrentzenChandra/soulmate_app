<?php

namespace App\Livewire\Saldo;

use App\Models\SaldoRekening;
use App\Models\SumberSaldo;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    // Rekening Form State
    public bool $showRekeningModal = false;
    public bool $isEditRekening = false;
    public ?int $rekeningId = null;
    public string $sumber_saldo_id = '';
    public string $nama_bank = '';
    public string $atas_nama = '';
    public string $nomor_rekening = '';
    public $saldo = 0;
    public string $keterangan = '';

    // Quick Saldo Modal State
    public bool $showQuickSaldoModal = false;
    public ?int $quickRekeningId = null;
    public string $quickBankName = '';
    public $quickSaldo = 0;

    // Sumber Saldo Modal State
    public bool $showSumberModal = false;
    public bool $isEditSumber = false;
    public ?int $sumberId = null;
    public string $namaSumber = '';
    public string $deskripsiSumber = '';

    // Delete Confirmation Modal State
    public bool $showDeleteModal = false;
    public string $deleteType = ''; // 'rekening' | 'sumber'
    public ?int $deleteId = null;
    public string $deleteTitle = '';
    public string $deleteMessage = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->can('saldo.view'))) {
            abort(403, 'Akses modul Saldo dibatasi.');
        }

        // Auto-seed default categories if empty
        if (SumberSaldo::count() === 0) {
            SumberSaldo::create(['nama' => 'PRIBADI', 'deskripsi' => 'Rekening Tabungan & Pribadi']);
            SumberSaldo::create(['nama' => 'CV. SM PAMEN SEJAHTERA', 'deskripsi' => 'Rekening Operasional & Bisnis Perusahaan']);
        }
    }

    // Rekening CRUD Actions
    public function openCreateRekening(?int $sumberId = null): void
    {
        $this->resetValidation();
        $this->reset(['rekeningId', 'nama_bank', 'atas_nama', 'nomor_rekening', 'saldo', 'keterangan']);
        $this->isEditRekening = false;

        if ($sumberId) {
            $this->sumber_saldo_id = (string) $sumberId;
        } else {
            $firstSumber = SumberSaldo::first();
            $this->sumber_saldo_id = (string) ($firstSumber?->id ?? '');
        }

        $this->showRekeningModal = true;
    }

    public function openEditRekening(int $id): void
    {
        $this->resetValidation();
        $rekening = SaldoRekening::findOrFail($id);

        $this->rekeningId = $rekening->id;
        $this->sumber_saldo_id = (string) $rekening->sumber_saldo_id;
        $this->nama_bank = $rekening->nama_bank;
        $this->atas_nama = $rekening->atas_nama ?? '';
        $this->nomor_rekening = $rekening->nomor_rekening ?? '';
        $this->saldo = (int) $rekening->saldo;
        $this->keterangan = $rekening->keterangan ?? '';
        $this->isEditRekening = true;

        $this->showRekeningModal = true;
    }

    public function saveRekening(): void
    {
        $this->validate([
            'sumber_saldo_id' => 'required|exists:sumber_saldos,id',
            'nama_bank' => 'required|string|max:100',
            'atas_nama' => 'nullable|string|max:150',
            'nomor_rekening' => 'nullable|string|max:100',
            'saldo' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'sumber_saldo_id.required' => 'Pilih Sumber Saldo wajib diisi.',
            'sumber_saldo_id.exists' => 'Sumber Saldo yang dipilih tidak valid.',
            'nama_bank.required' => 'Nama Bank wajib diisi.',
            'saldo.required' => 'Nominal Saldo wajib diisi.',
            'saldo.numeric' => 'Nominal Saldo harus berupa angka.',
            'saldo.min' => 'Nominal Saldo minimal 0.',
        ]);

        $data = [
            'sumber_saldo_id' => $this->sumber_saldo_id,
            'nama_bank' => trim($this->nama_bank),
            'atas_nama' => trim($this->atas_nama) ?: null,
            'nomor_rekening' => trim($this->nomor_rekening) ?: null,
            'saldo' => (float) $this->saldo,
            'keterangan' => trim($this->keterangan) ?: null,
        ];

        if ($this->isEditRekening && $this->rekeningId) {
            SaldoRekening::where('id', $this->rekeningId)->update($data);
            session()->flash('success', 'Data rekening berhasil diperbarui.');
        } else {
            SaldoRekening::create($data);
            session()->flash('success', 'Rekening baru berhasil ditambahkan.');
        }

        $this->showRekeningModal = false;
        $this->reset(['rekeningId', 'nama_bank', 'atas_nama', 'nomor_rekening', 'saldo', 'keterangan']);
    }

    // Quick Saldo Update
    public function openQuickSaldo(int $id): void
    {
        $rekening = SaldoRekening::findOrFail($id);
        $this->quickRekeningId = $rekening->id;
        $this->quickBankName = $rekening->nama_bank . ($rekening->atas_nama ? ' - ' . $rekening->atas_nama : '');
        $this->quickSaldo = (int) $rekening->saldo;
        $this->showQuickSaldoModal = true;
    }

    public function saveQuickSaldo(): void
    {
        $this->validate([
            'quickSaldo' => 'required|numeric|min:0',
        ], [
            'quickSaldo.required' => 'Nominal Saldo wajib diisi.',
            'quickSaldo.numeric' => 'Nominal Saldo harus berupa angka valid.',
            'quickSaldo.min' => 'Nominal Saldo tidak boleh negatif.',
        ]);

        if ($this->quickRekeningId) {
            SaldoRekening::where('id', $this->quickRekeningId)->update([
                'saldo' => (float) $this->quickSaldo,
            ]);
            session()->flash('success', 'Nominal saldo berhasil diperbarui.');
        }

        $this->showQuickSaldoModal = false;
        $this->reset(['quickRekeningId', 'quickBankName', 'quickSaldo']);
    }

    // Sumber Saldo Actions
    public function openCreateSumber(): void
    {
        $this->resetValidation();
        $this->reset(['sumberId', 'namaSumber', 'deskripsiSumber']);
        $this->isEditSumber = false;
        $this->showSumberModal = true;
    }

    public function openEditSumber(int $id): void
    {
        $this->resetValidation();
        $sumber = SumberSaldo::findOrFail($id);
        $this->sumberId = $sumber->id;
        $this->namaSumber = $sumber->nama;
        $this->deskripsiSumber = $sumber->deskripsi ?? '';
        $this->isEditSumber = true;
        $this->showSumberModal = true;
    }

    public function saveSumber(): void
    {
        $this->validate([
            'namaSumber' => 'required|string|max:100',
            'deskripsiSumber' => 'nullable|string|max:255',
        ], [
            'namaSumber.required' => 'Nama Sumber Saldo wajib diisi.',
        ]);

        if ($this->isEditSumber && $this->sumberId) {
            SumberSaldo::where('id', $this->sumberId)->update([
                'nama' => trim($this->namaSumber),
                'deskripsi' => trim($this->deskripsiSumber) ?: null,
            ]);
            session()->flash('success', 'Sumber Saldo berhasil diperbarui.');
        } else {
            SumberSaldo::create([
                'nama' => trim($this->namaSumber),
                'deskripsi' => trim($this->deskripsiSumber) ?: null,
            ]);
            session()->flash('success', 'Sumber Saldo baru berhasil ditambahkan.');
        }

        $this->showSumberModal = false;
        $this->reset(['sumberId', 'namaSumber', 'deskripsiSumber']);
    }

    // Delete Modal
    public function confirmDeleteRekening(int $id): void
    {
        $rekening = SaldoRekening::with('sumber')->findOrFail($id);
        $this->deleteType = 'rekening';
        $this->deleteId = $rekening->id;
        $this->deleteTitle = 'Hapus Rekening';
        $this->deleteMessage = 'Apakah Anda yakin ingin menghapus rekening ' . $rekening->nama_bank . ($rekening->atas_nama ? ' (a.n ' . $rekening->atas_nama . ')' : '') . '? Data saldo akan dihapus permanen.';
        $this->showDeleteModal = true;
    }

    public function confirmDeleteSumber(int $id): void
    {
        $sumber = SumberSaldo::withCount('rekenings')->findOrFail($id);
        $this->deleteType = 'sumber';
        $this->deleteId = $sumber->id;
        $this->deleteTitle = 'Hapus Sumber Saldo';
        $this->deleteMessage = 'Apakah Anda yakin ingin menghapus Sumber Saldo "' . $sumber->nama . '"? Seluruh ' . $sumber->rekenings_count . ' rekening di dalamnya juga akan terhapus otomatis.';
        $this->showDeleteModal = true;
    }

    public function deleteConfirmed(): void
    {
        if ($this->deleteType === 'rekening' && $this->deleteId) {
            SaldoRekening::where('id', $this->deleteId)->delete();
            session()->flash('success', 'Data rekening berhasil dihapus.');
        } elseif ($this->deleteType === 'sumber' && $this->deleteId) {
            SumberSaldo::where('id', $this->deleteId)->delete();
            session()->flash('success', 'Sumber Saldo beserta rekeningnya berhasil dihapus.');
        }

        $this->showDeleteModal = false;
        $this->reset(['deleteType', 'deleteId', 'deleteTitle', 'deleteMessage']);
    }

    public function render()
    {
        // Get all Sumber Saldo along with their rekenings
        $sumberSaldos = SumberSaldo::with(['rekenings' => function ($q) {
            $q->orderBy('id', 'asc');
        }])
        ->withCount('rekenings')
        ->withSum('rekenings as total_saldo', 'saldo')
        ->orderBy('id', 'asc')
        ->get();

        $totalSaldoKeseluruhan = (float) SaldoRekening::sum('saldo');
        $totalRekeningKeseluruhan = SaldoRekening::count();

        return view('livewire.saldo.index', [
            'sumberSaldos' => $sumberSaldos,
            'totalSaldoKeseluruhan' => $totalSaldoKeseluruhan,
            'totalRekeningKeseluruhan' => $totalRekeningKeseluruhan,
        ])->layout('layouts.app', ['title' => 'Saldo']);
    }
}
