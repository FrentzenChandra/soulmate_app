<?php

namespace App\Livewire\Master\ViaPajak;

use App\Models\MasterViaPajak;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Master Via Pajak - Soulmate Komputer')]
class Index extends Component
{
    public string $search = '';
    public bool $modalOpen = false;
    public bool $isEditMode = false;
    public ?int $selectedId = null;

    public string $nama = '';
    public bool $is_aktif = true;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->isOwner()) {
            abort(403, 'Akses Master Via Pajak khusus Owner Kantor Pusat (HQ).');
        }
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['selectedId', 'nama']);
        $this->is_aktif = true;
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function edit(int $id): void
    {
        $item = MasterViaPajak::findOrFail($id);
        $this->selectedId = $item->id;
        $this->nama = $item->nama;
        $this->is_aktif = (bool) $item->is_aktif;
        $this->resetValidation();
        $this->isEditMode = true;
        $this->modalOpen = true;
    }

    public function save(): void
    {
        $this->validate([
            'nama' => 'required|string|max:100',
            'is_aktif' => 'boolean',
        ], [
            'nama.required' => 'Nama saluran via pajak wajib diisi.',
        ]);

        if ($this->isEditMode && $this->selectedId) {
            MasterViaPajak::findOrFail($this->selectedId)->update([
                'nama' => trim($this->nama),
                'is_aktif' => $this->is_aktif,
            ]);
            session()->flash('success', 'Via Pajak berhasil diperbarui.');
        } else {
            MasterViaPajak::create([
                'nama' => trim($this->nama),
                'is_aktif' => $this->is_aktif,
            ]);
            session()->flash('success', 'Via Pajak baru berhasil ditambahkan.');
        }

        $this->modalOpen = false;
    }

    public function delete(int $id): void
    {
        MasterViaPajak::findOrFail($id)->delete();
        session()->flash('success', 'Via Pajak berhasil dihapus.');
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
    }

    public function render()
    {
        $query = MasterViaPajak::query();

        if (! empty(trim($this->search))) {
            $words = array_filter(explode(' ', trim($this->search)));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->whereRaw('LOWER(nama) LIKE ?', [$pattern]);
                }
            });
        }

        $items = $query->orderBy('nama')->get();

        return view('livewire.master.via-pajak.index', [
            'items' => $items,
        ]);
    }
}

