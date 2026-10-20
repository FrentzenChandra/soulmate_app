<?php

namespace App\Livewire\Master\KodePajak;

use App\Models\MasterKodePajak;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Master Kode Pajak - Soulmate Komputer')]
class Index extends Component
{
    public string $search = '';
    public bool $modalOpen = false;
    public bool $isEditMode = false;
    public ?int $selectedId = null;

    public string $kode = '';
    public string $keterangan = '';
    public bool $is_aktif = true;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isOwner()) {
            abort(403, 'Akses Master Kode Pajak khusus Owner Kantor Pusat (HQ).');
        }
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['selectedId', 'kode', 'keterangan']);
        $this->is_aktif = true;
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function edit(int $id): void
    {
        $item = MasterKodePajak::findOrFail($id);
        $this->selectedId = $item->id;
        $this->kode = $item->kode;
        $this->keterangan = $item->keterangan ?? '';
        $this->is_aktif = (bool) $item->is_aktif;
        $this->resetValidation();
        $this->isEditMode = true;
        $this->modalOpen = true;
    }

    public function save(): void
    {
        $this->validate([
            'kode' => 'required|string|max:50',
            'keterangan' => 'nullable|string|max:255',
            'is_aktif' => 'boolean',
        ], [
            'kode.required' => 'Label kode pajak wajib diisi.',
        ]);

        if ($this->isEditMode && $this->selectedId) {
            MasterKodePajak::findOrFail($this->selectedId)->update([
                'kode' => trim($this->kode),
                'keterangan' => trim($this->keterangan) ?: null,
                'is_aktif' => $this->is_aktif,
            ]);
            session()->flash('success', 'Kode Pajak berhasil diperbarui.');
        } else {
            MasterKodePajak::create([
                'kode' => trim($this->kode),
                'keterangan' => trim($this->keterangan) ?: null,
                'is_aktif' => $this->is_aktif,
            ]);
            session()->flash('success', 'Kode Pajak baru berhasil ditambahkan.');
        }

        $this->modalOpen = false;
    }

    public function delete(int $id): void
    {
        MasterKodePajak::findOrFail($id)->delete();
        session()->flash('success', 'Kode Pajak berhasil dihapus.');
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
    }

    public function render()
    {
        $query = MasterKodePajak::query();

        if (! empty(trim($this->search))) {
            $words = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(kode) LIKE ?', [$pattern])
                           ->orWhereRaw("LOWER(COALESCE(keterangan, '')) LIKE ?", [$pattern]);
                    });
                }
            });
        }

        $items = $query->orderBy('kode')->get();

        return view('livewire.master.kode-pajak.index', [
            'items' => $items,
        ]);
    }
}

