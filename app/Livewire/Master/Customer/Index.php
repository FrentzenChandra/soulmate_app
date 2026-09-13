<?php

namespace App\Livewire\Master\Customer;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\ServisMasuk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Data Pelanggan - Soulmate Komputer')]
class Index extends Component
{
    use WithFileUploads, WithPagination, WithSorting;

    public string $search = '';

    public string $tipeFilter = 'semua';

    public string $cabangFilter = 'semua';

    public int $perPage = 15;

    public bool $modalOpen = false;

    public bool $isEditMode = false;

    public ?int $customerId = null;

    // Form fields
    public string $nama = '';

    public string $no_hp = '';

    public string $alamat = '';

    public string $tipe = 'langganan';

    public ?int $cabang_id = null;

    public $foto = null;

    public ?string $existing_foto_url = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'tipeFilter' => ['except' => 'semua'],
        'cabangFilter' => ['except' => 'semua'],
        'sortField' => ['except' => 'nama'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $this->sortField = 'nama';
        $this->sortDirection = 'asc';

        if (! $user->isOwner()) {
            $this->cabang_id = $user->cabang_id;
        } else {
            $activeBranch = session('active_branch_id', 'hq');
            if ($activeBranch !== 'hq') {
                $this->cabang_id = (int) $activeBranch;
            }
        }
    }

    public function loadMore(): void
    {
        $this->perPage += 15;
    }

    public function updatingSearch()
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingTipeFilter()
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingCabangFilter()
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['customerId', 'nama', 'no_hp', 'alamat', 'foto', 'existing_foto_url']);
        $this->tipe = 'langganan';

        $user = Auth::user();
        if (! $user->isOwner()) {
            $this->cabang_id = $user->cabang_id;
        } else {
            $activeBranch = session('active_branch_id', 'hq');
            $this->cabang_id = $activeBranch !== 'hq' ? (int) $activeBranch : (Cabang::where('status', 'aktif')->first()?->id ?? null);
        }

        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $user = Auth::user();
        $customer = Customer::findOrFail($id);

        if (! $user->isOwner() && (int) $customer->cabang_id !== (int) $user->cabang_id) {
            session()->flash('error', 'Anda tidak memiliki otoritas untuk mengelola pelanggan cabang lain.');

            return;
        }

        $this->customerId = $customer->id;
        $this->nama = $customer->nama;
        $this->no_hp = $customer->no_hp ?? '';
        $this->alamat = $customer->alamat ?? '';
        $this->tipe = $customer->tipe ?? 'langganan';
        $this->cabang_id = $customer->cabang_id;
        $this->existing_foto_url = $customer->foto_url;
        $this->foto = null;

        $this->isEditMode = true;
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->foto = null;
        $this->resetValidation();
    }

    public function cancelUploadFoto(): void
    {
        $this->foto = null;
    }

    public function removeExistingFoto(): void
    {
        $this->existing_foto_url = null;
        $this->foto = null;
    }

    protected function processAndSaveWebp($uploadedFile): string
    {
        $imagePath = $uploadedFile->getRealPath();
        $imageInfo = @getimagesize($imagePath);

        if ($imageInfo && function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $source = @imagecreatefromstring(file_get_contents($imagePath));
            if ($source !== false) {
                $filename = 'cust_'.time().'_'.Str::random(6).'.webp';

                $width = imagesx($source);
                $height = imagesy($source);

                $maxDim = 500;
                if ($width > $maxDim || $height > $maxDim) {
                    if ($width > $height) {
                        $newWidth = $maxDim;
                        $newHeight = (int) ($height * ($maxDim / $width));
                    } else {
                        $newHeight = $maxDim;
                        $newWidth = (int) ($width * ($maxDim / $height));
                    }
                    $resized = imagecreatetruecolor($newWidth, $newHeight);
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    ob_start();
                    imagewebp($resized, null, 80);
                    $webContent = ob_get_clean();
                    imagedestroy($resized);
                } else {
                    ob_start();
                    imagewebp($source, null, 80);
                    $webContent = ob_get_clean();
                }
                imagedestroy($source);

                if ($webContent !== false) {
                    Storage::disk('public')->put('customers/'.$filename, $webContent);

                    return 'storage/customers/'.$filename;
                }
            }
        }

        $fallbackName = 'cust_'.time().'_'.Str::random(6).'.'.($uploadedFile->getClientOriginalExtension() ?: 'jpg');
        $storedPath = Storage::disk('public')->putFileAs('customers', $uploadedFile, $fallbackName);

        return 'storage/'.$storedPath;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string'],
            'tipe' => ['required', 'in:umum,langganan'],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'foto' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama pelanggan wajib diisi.',
            'cabang_id.required' => 'Cabang pendaftaran wajib dipilih.',
            'foto.image' => 'File foto harus berupa format gambar (JPG, PNG, WEBP).',
            'foto.max' => 'Ukuran file foto maksimal 4MB.',
        ];
    }

    public function save(): void
    {
        $user = Auth::user();
        if (! $user->isOwner()) {
            $this->cabang_id = $user->cabang_id;
        }

        $this->validate();

        $data = [
            'cabang_id' => $this->cabang_id,
            'nama' => trim($this->nama),
            'no_hp' => ! empty($this->no_hp) ? trim($this->no_hp) : null,
            'alamat' => ! empty($this->alamat) ? trim($this->alamat) : null,
            'tipe' => $this->tipe,
        ];

        if ($this->foto) {
            $data['foto_url'] = $this->processAndSaveWebp($this->foto);
        } elseif ($this->isEditMode && $this->existing_foto_url === null) {
            $data['foto_url'] = null;
        }

        if ($this->isEditMode && $this->customerId) {
            $customer = Customer::findOrFail($this->customerId);

            if (! $user->isOwner() && (int) $customer->cabang_id !== (int) $user->cabang_id) {
                session()->flash('error', 'Anda tidak memiliki otoritas mengubah pelanggan ini.');

                return;
            }

            if (isset($data['foto_url']) && $customer->foto_url && $customer->foto_url !== $data['foto_url']) {
                if (str_starts_with($customer->foto_url, 'storage/')) {
                    $oldPath = Str::after($customer->foto_url, 'storage/');
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $customer->update($data);
            session()->flash('success', "Data pelanggan \"{$customer->nama}\" berhasil diperbarui.");
        } else {
            $customer = Customer::create($data);
            session()->flash('success', "Pelanggan baru \"{$customer->nama}\" berhasil didaftarkan.");
        }

        $this->closeModal();
    }

    public function deleteCustomer(int $id): void
    {
        $user = Auth::user();
        $customer = Customer::findOrFail($id);

        if (! $user->isOwner() && (int) $customer->cabang_id !== (int) $user->cabang_id) {
            session()->flash('error', 'Anda tidak memiliki otoritas menghapus data pelanggan ini.');

            return;
        }

        $hasPenjualan = Penjualan::where('customer_id', $id)->exists();
        $hasServis = ServisMasuk::where('customer_id', $id)->exists();

        if ($hasPenjualan || $hasServis) {
            session()->flash('error', "Pelanggan \"{$customer->nama}\" tidak dapat dihapus karena memiliki riwayat transaksi atau servis aktif.");

            return;
        }

        if ($customer->foto_url && str_starts_with($customer->foto_url, 'storage/')) {
            $oldPath = Str::after($customer->foto_url, 'storage/');
            Storage::disk('public')->delete($oldPath);
        }

        $customer->delete();
        session()->flash('success', "Data pelanggan \"{$customer->nama}\" berhasil dihapus.");
    }

    public function render()
    {
        $user = Auth::user();
        $isOwner = $user->isOwner();
        $activeBranchSession = session('active_branch_id', 'hq');

        $query = Customer::with('cabang')
            ->when(! $isOwner, function ($q) use ($user) {
                $q->where('cabang_id', $user->cabang_id);
            })
            ->when($isOwner && $activeBranchSession !== 'hq', function ($q) use ($activeBranchSession) {
                $q->where('cabang_id', (int) $activeBranchSession);
            })
            ->when($isOwner && $activeBranchSession === 'hq' && $this->cabangFilter !== 'semua', function ($q) {
                $q->where('cabang_id', $this->cabangFilter);
            })
            ->when($this->tipeFilter !== 'semua', function ($q) {
                $q->where('tipe', $this->tipeFilter);
            })
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nama', 'like', '%'.$this->search.'%')
                        ->orWhere('no_hp', 'like', '%'.$this->search.'%')
                        ->orWhere('alamat', 'like', '%'.$this->search.'%');
                });
            });

        $allowedSorts = ['nama', 'no_hp', 'tipe', 'cabang_id', 'created_at', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'nama';
        $sortDir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';

        $totalCustomers = (clone $query)->count();
        $customers = $query->orderBy($sortColumn, $sortDir)->take($this->perPage)->get();
        $hasMore = $totalCustomers > $this->perPage;

        $cabangs = $isOwner
            ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get()
            : Cabang::where('id', $user->cabang_id)->get();

        return view('livewire.master.customer.index', [
            'customers' => $customers,
            'hasMore' => $hasMore,
            'totalCustomers' => $totalCustomers,
            'cabangs' => $cabangs,
            'isOwner' => $isOwner,
            'isHQ' => $isOwner && $activeBranchSession === 'hq',
        ]);
    }
}
