<?php

namespace App\Livewire\Master\Pph;

use App\Models\MasterPph;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Master PPh & Tarif - Soulmate Komputer')]
class Index extends Component
{
    public string $search = '';
    public bool $modalOpen = false;
    public bool $isEditMode = false;
    public ?int $selectedId = null;

    public string $nama_pph = '';
    public string $persentase = '0.00';
    public string $deskripsi = '';
    public bool $is_aktif = true;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isOwner()) {
            abort(403, 'Akses Master PPh & Tarif khusus Owner Kantor Pusat (HQ).');
        }
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['selectedId', 'nama_pph', 'deskripsi']);
        $this->persentase = '0.00';
        $this->is_aktif = true;
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function edit(int $id): void
    {
        $item = MasterPph::findOrFail($id);
        $this->selectedId = $item->id;
        $this->nama_pph = $item->nama_pph;
        $this->persentase = (string) $item->persentase;
        $this->deskripsi = $item->deskripsi ?? '';
        $this->is_aktif = (bool) $item->is_aktif;
        $this->resetValidation();
        $this->isEditMode = true;
        $this->modalOpen = true;
    }

    public function save(): void
    {
        $this->validate([
            'nama_pph' => 'required|string|max:100',
            'persentase' => 'required|numeric|min:0|max:100',
            'deskripsi' => 'nullable|string|max:255',
            'is_aktif' => 'boolean',
        ], [
            'nama_pph.required' => 'Nama tarif PPh wajib diisi.',
            'persentase.required' => 'Persentase tarif wajib diisi.',
            'persentase.numeric' => 'Persentase harus berupa angka desimal.',
        ]);

        if ($this->isEditMode && $this->selectedId) {
            MasterPph::findOrFail($this->selectedId)->update([
                'nama_pph' => trim($this->nama_pph),
                'persentase' => (float) $this->persentase,
                'deskripsi' => trim($this->deskripsi) ?: null,
                'is_aktif' => $this->is_aktif,
            ]);
            session()->flash('success', 'Tarif PPh berhasil diperbarui.');
        } else {
            MasterPph::create([
                'nama_pph' => trim($this->nama_pph),
                'persentase' => (float) $this->persentase,
                'deskripsi' => trim($this->deskripsi) ?: null,
                'is_aktif' => $this->is_aktif,
            ]);
            session()->flash('success', 'Tarif PPh baru berhasil ditambahkan.');
        }

        $this->modalOpen = false;
    }

    public function delete(int $id): void
    {
        MasterPph::findOrFail($id)->delete();
        session()->flash('success', 'Tarif PPh berhasil dihapus.');
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
    }

    public function render()
    {
        $query = MasterPph::query();

        if (! empty(trim($this->search))) {
            $words = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->whereRaw('LOWER(nama_pph) LIKE ?', [$pattern])
                       ->orWhereRaw("LOWER(COALESCE(deskripsi, '')) LIKE ?", [$pattern]);
                }
            });
        }

        $items = $query->orderBy('persentase')->get();

        return view('livewire.master.pph.index', [
            'items' => $items,
        ]);
    }
}

