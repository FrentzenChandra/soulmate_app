<?php

namespace App\Livewire\Master\WifiAbsen;

use App\Models\Cabang;
use App\Models\WifiAbsen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Master WiFi Absensi')]
#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $cabangFilter = 'semua';

    // Modal state
    public bool $modalOpen = false;
    public bool $isEdit = false;
    public ?int $wifiId = null;

    // Form fields
    public string $nama = '';
    public string $ip_address = '';
    public bool $is_aktif = true;
    public ?int $cabang_id = null;

    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user->isOwner() && ! $user->isSuperadmin()) {
            abort(403, 'Akses master WiFi absensi hanya untuk Owner dan Superadmin.');
        }

        if ($user->isSuperadmin()) {
            $this->cabang_id = $user->cabang_id;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCabangFilter(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        $user = Auth::user();
        $targetCabangId = $user->isOwner() ? $this->cabang_id : $user->cabang_id;

        return [
            'nama' => ['required', 'string', 'max:100'],
            'ip_address' => [
                'required',
                'string',
                'max:45',
                Rule::unique('wifi_absens', 'ip_address')
                    ->where('cabang_id', $targetCabangId)
                    ->ignore($this->wifiId),
            ],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'is_aktif' => ['boolean'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nama' => 'Nama WiFi',
            'ip_address' => 'IP Address',
            'cabang_id' => 'Cabang',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $user = Auth::user();

        $this->isEdit = false;
        $this->wifiId = null;
        $this->nama = '';
        $this->ip_address = trim(request()->ip() ?? '');
        $this->is_aktif = true;

        if ($user->isSuperadmin()) {
            $this->cabang_id = $user->cabang_id;
        } else {
            $firstCabang = Cabang::where('status', 'aktif')->first();
            $this->cabang_id = $this->cabangFilter !== 'semua' ? (int) $this->cabangFilter : ($firstCabang?->id ?? null);
        }

        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $user = Auth::user();

        $wifi = WifiAbsen::findOrFail($id);

        if (! $user->isOwner() && $wifi->cabang_id !== $user->cabang_id) {
            abort(403, 'Akses dibatasi ke cabang Anda sendiri.');
        }

        $this->isEdit = true;
        $this->wifiId = $wifi->id;
        $this->nama = $wifi->nama;
        $this->ip_address = $wifi->ip_address;
        $this->is_aktif = (bool) $wifi->is_aktif;
        $this->cabang_id = $wifi->cabang_id;

        $this->modalOpen = true;
    }

    public function useCurrentIp(): void
    {
        $this->ip_address = trim(request()->ip() ?? '');
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $user = Auth::user();

        if ($user->isSuperadmin()) {
            $this->cabang_id = $user->cabang_id;
        }

        $validated = $this->validate();

        try {
            if ($this->isEdit && $this->wifiId) {
                $wifi = WifiAbsen::findOrFail($this->wifiId);
                if (! $user->isOwner() && $wifi->cabang_id !== $user->cabang_id) {
                    abort(403);
                }

                $wifi->update([
                    'nama' => $validated['nama'],
                    'ip_address' => trim($validated['ip_address']),
                    'cabang_id' => $validated['cabang_id'],
                    'is_aktif' => $this->is_aktif,
                ]);

                $this->successMessage = 'Data WiFi absensi berhasil diperbarui.';
            } else {
                WifiAbsen::create([
                    'cabang_id' => $validated['cabang_id'],
                    'nama' => $validated['nama'],
                    'ip_address' => trim($validated['ip_address']),
                    'is_aktif' => $this->is_aktif,
                    'created_by' => $user->id,
                ]);

                $this->successMessage = 'Data WiFi absensi baru berhasil didaftarkan.';
            }

            $this->closeModal();
        } catch (\Throwable $e) {
            $this->errorMessage = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }

    public function toggleStatus(int $id): void
    {
        $user = Auth::user();
        $wifi = WifiAbsen::findOrFail($id);

        if (! $user->isOwner() && $wifi->cabang_id !== $user->cabang_id) {
            abort(403);
        }

        $wifi->update(['is_aktif' => ! $wifi->is_aktif]);
        $this->successMessage = 'Status WiFi berhasil diubah.';
    }

    public function delete(int $id): void
    {
        $user = Auth::user();
        $wifi = WifiAbsen::findOrFail($id);

        if (! $user->isOwner() && $wifi->cabang_id !== $user->cabang_id) {
            abort(403);
        }

        $wifi->delete();
        $this->successMessage = 'Data WiFi absensi berhasil dihapus.';
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();

        $query = WifiAbsen::with(['cabang', 'creator'])->latest();

        if (! $isOwner) {
            $query->where('cabang_id', $user->cabang_id);
        } else {
            if ($this->cabangFilter !== 'semua') {
                $query->where('cabang_id', $this->cabangFilter);
            }
        }

        if (! empty(trim($this->search))) {
            $tokens = array_filter(explode(' ', trim($this->search)));
            foreach ($tokens as $token) {
                $word = '%' . strtolower($token) . '%';
                $query->where(function ($q) use ($word) {
                    $q->whereRaw('LOWER(nama) LIKE ?', [$word])
                      ->orWhereRaw('LOWER(ip_address) LIKE ?', [$word]);
                });
            }
        }

        $wifis = $query->paginate(15);
        $cabangs = $isOwner ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get() : collect();

        return view('livewire.master.wifi-absen.index', [
            'wifis' => $wifis,
            'cabangs' => $cabangs,
            'isOwner' => $isOwner,
        ]);
    }
}

