<?php

namespace App\Livewire\Master\User;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Master Pengguna - Soulmate Komputer')]
class Index extends Component
{
    use WithFileUploads, WithPagination, WithSorting;

    public string $search = '';

    public string $roleFilter = 'semua';

    public string $cabangFilter = 'semua';

    public int $perPage = 15;

    public bool $modalOpen = false;

    public bool $isEditMode = false;

    public ?int $userId = null;

    // Form fields
    public string $name = '';

    public string $email = '';

    public string $username = '';

    public string $password = '';

    public string $role_name = 'Superadmin';

    public ?int $cabang_id = null;

    public string $no_hp = '';

    public string $status = 'aktif';

    public $foto = null;

    public ?string $existing_foto_url = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => 'semua'],
        'cabangFilter' => ['except' => 'semua'],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount()
    {
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
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

    public function updatingRoleFilter()
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function updatingCabangFilter()
    {
        $this->perPage = 15;
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['userId', 'name', 'email', 'username', 'password', 'no_hp', 'cabang_id', 'foto', 'existing_foto_url']);
        $this->role_name = 'Superadmin';
        $this->status = 'aktif';
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id)
    {
        $this->resetValidation();
        $user = User::with('roles')->findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->username = $user->username ?? '';
        $this->password = ''; // kosongkan password saat edit kecuali ingin diubah
        $this->role_name = $user->getRoleNames()->first() ?? 'Superadmin';
        $this->cabang_id = $user->cabang_id;
        $this->no_hp = $user->no_hp ?? '';
        $this->status = $user->status;
        $this->existing_foto_url = $user->foto_url;
        $this->foto = null;

        $this->isEditMode = true;
        $this->modalOpen = true;
    }

    public function closeModal()
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
        $targetDir = public_path('images/users');
        if (! file_exists($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        $imagePath = $uploadedFile->getRealPath();
        $imageInfo = @getimagesize($imagePath);

        if ($imageInfo && function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $source = @imagecreatefromstring(file_get_contents($imagePath));
            if ($source !== false) {
                $filename = 'user_'.time().'_'.Str::random(6).'.webp';
                $destination = $targetDir.'/'.$filename;

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
                    imagewebp($resized, $destination, 80);
                    imagedestroy($resized);
                } else {
                    imagewebp($source, $destination, 80);
                }
                imagedestroy($source);

                return 'images/users/'.$filename;
            }
        }

        // Fallback jika GD webp tidak aktif
        $fallbackName = 'user_'.time().'_'.Str::random(6).'.'.($uploadedFile->getClientOriginalExtension() ?: 'jpg');
        $destination = $targetDir.'/'.$fallbackName;

        try {
            $uploadedFile->move($targetDir, $fallbackName);
        } catch (\Throwable $e) {
            @copy($imagePath, $destination);
        }

        return 'images/users/'.$fallbackName;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                'unique:users,username,'.($this->userId ?? 'NULL').',id',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,'.($this->userId ?? 'NULL').',id',
            ],
            'password' => $this->isEditMode ? 'nullable|min:6' : 'required|min:6',
            'role_name' => 'required|in:Owner,Superadmin',
            'cabang_id' => $this->role_name === 'Superadmin' ? 'required|exists:cabangs,id' : 'nullable',
            'no_hp' => 'nullable|string|max:20',
            'status' => 'required|in:aktif,nonaktif',
            'foto' => 'nullable|image|max:4096',
        ];

        $messages = [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username login wajib diisi.',
            'username.unique' => 'Username ini sudah dipakai oleh akun lain.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah dipakai oleh akun lain.',
            'password.required' => 'Password wajib diisi untuk akun baru.',
            'password.min' => 'Password minimal 6 karakter.',
            'cabang_id.required' => 'Superadmin wajib ditugaskan ke salah satu cabang.',
            'foto.image' => 'File harus berupa gambar (JPG, PNG, WEBP).',
            'foto.max' => 'Ukuran foto maksimal 4MB.',
        ];

        $validated = $this->validate($rules, $messages);

        // Jika role Owner, cabang_id selalu null (akses global)
        if ($this->role_name === 'Owner') {
            $validated['cabang_id'] = null;
        }

        $fotoPath = null;
        if ($this->foto) {
            $fotoPath = $this->processAndSaveWebp($this->foto);
        }

        if ($this->isEditMode && $this->userId) {
            $user = User::findOrFail($this->userId);

            // Proteksi agar tidak menonaktifkan akun sendiri
            if ($user->id === Auth::id() && $validated['status'] === 'nonaktif') {
                session()->flash('error', 'Anda tidak dapat menonaktifkan akun Owner Anda sendiri!');

                return;
            }

            $updateData = [
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'cabang_id' => $validated['cabang_id'],
                'no_hp' => $validated['no_hp'],
                'status' => $validated['status'],
            ];

            if ($fotoPath) {
                $updateData['foto_url'] = $fotoPath;
            } elseif ($this->existing_foto_url === null) {
                $updateData['foto_url'] = null;
            }

            if (! empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            // Sync role
            $role = Role::firstOrCreate(['name' => $this->role_name]);
            $user->syncRoles([$role]);

            session()->flash('success', "Akun pengguna {$user->name} berhasil diperbarui.");
        } else {
            $userData = [
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'cabang_id' => $validated['cabang_id'],
                'no_hp' => $validated['no_hp'],
                'status' => $validated['status'],
                'foto_url' => $fotoPath,
            ];

            $newUser = User::create($userData);
            $role = Role::firstOrCreate(['name' => $this->role_name]);
            $newUser->syncRoles([$role]);

            session()->flash('success', "Akun {$this->role_name} baru atas nama {$newUser->name} berhasil dibuat.");
        }

        $this->closeModal();
    }

    public function toggleStatus(int $id)
    {
        $user = User::findOrFail($id);

        // Self-lockout check
        if ($user->id === Auth::id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun Owner yang sedang Anda gunakan.');

            return;
        }

        // Minimal 1 active owner check
        if ($user->isOwner() && $user->status === 'aktif') {
            $activeOwnersCount = User::role('Owner')->where('status', 'aktif')->count();
            if ($activeOwnersCount <= 1) {
                session()->flash('error', 'Minimal harus ada 1 akun Owner yang aktif di sistem.');

                return;
            }
        }

        $newStatus = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->update(['status' => $newStatus]);

        session()->flash('success', "Status akun {$user->name} diubah menjadi ".ucfirst($newStatus).'.');
    }

    public function render()
    {
        $query = User::with(['cabang', 'roles'])
            ->whereHas('roles', function ($q) {
                if ($this->roleFilter === 'semua') {
                    $q->whereIn('name', ['Owner', 'Superadmin']);
                } else {
                    $q->where('name', $this->roleFilter);
                }
            })
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('username', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('no_hp', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->cabangFilter !== 'semua', function ($q) {
                if ($this->cabangFilter === 'hq') {
                    $q->whereNull('cabang_id');
                } else {
                    $q->where('cabang_id', $this->cabangFilter);
                }
            });

        $allowedSorts = ['name', 'username', 'email', 'cabang_id', 'no_hp', 'status', 'created_at', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'name';
        $sortDir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';

        $totalUsers = (clone $query)->count();
        $users = $query->orderBy($sortColumn, $sortDir)->take($this->perPage)->get();
        $hasMore = $totalUsers > $this->perPage;

        $cabangs = Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get();

        return view('livewire.master.user.index', [
            'users' => $users,
            'hasMore' => $hasMore,
            'totalUsers' => $totalUsers,
            'cabangs' => $cabangs,
        ]);
    }
}
