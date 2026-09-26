<?php

namespace App\Livewire\Master\User;

use App\Livewire\Traits\WithSorting;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Manajemen Pengguna & Staff - Soulmate Komputer')]
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

    public string $role_name = 'Admin';

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
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->hasRole('Superadmin'))) {
            abort(403, 'Anda tidak memiliki akses ke halaman manajemen pengguna.');
        }

        $this->sortField = 'name';
        $this->sortDirection = 'asc';

        if (! $user->isOwner()) {
            $this->cabang_id = $user->cabang_id;
            $this->role_name = 'Admin';
        } else {
            $this->role_name = 'Superadmin';
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
        $this->reset(['userId', 'name', 'email', 'username', 'password', 'no_hp', 'foto', 'existing_foto_url']);

        $currentUser = Auth::user();
        if (! $currentUser->isOwner()) {
            $this->cabang_id = $currentUser->cabang_id;
            $this->role_name = 'Admin';
        } else {
            $this->cabang_id = null;
            $this->role_name = 'Superadmin';
        }

        $this->status = 'aktif';
        $this->isEditMode = false;
        $this->modalOpen = true;
    }

    public function openEditModal(int $id)
    {
        $this->resetValidation();
        $currentUser = Auth::user();
        $user = User::with('roles')->findOrFail($id);

        // RBAC Check for Superadmin
        if (! $currentUser->isOwner()) {
            if ($user->isOwner() || (int) $user->cabang_id !== (int) $currentUser->cabang_id) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk mengelola akun pengguna ini.');

                return;
            }
        }

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->username = $user->username ?? '';
        $this->password = ''; // kosongkan password saat edit kecuali ingin diubah
        $roleName = $user->getRoleNames()->first() ?? 'Admin';
        $this->role_name = $roleName === 'Kasir' ? 'Admin' : $roleName;
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
        $imagePath = $uploadedFile->getRealPath();
        $imageInfo = @getimagesize($imagePath);

        if ($imageInfo && function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $source = @imagecreatefromstring(file_get_contents($imagePath));
            if ($source !== false) {
                $filename = 'user_'.time().'_'.Str::random(6).'.webp';

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
                    Storage::disk('public')->put('users/'.$filename, $webContent);

                    return 'storage/users/'.$filename;
                }
            }
        }

        // Fallback jika GD webp tidak aktif
        $fallbackName = 'user_'.time().'_'.Str::random(6).'.'.($uploadedFile->getClientOriginalExtension() ?: 'jpg');
        $storedPath = Storage::disk('public')->putFileAs('users', $uploadedFile, $fallbackName);

        return 'storage/'.$storedPath;
    }

    public function save()
    {
        $currentUser = Auth::user();
        $allowedRoles = $currentUser->isOwner()
            ? ['Owner', 'Superadmin', 'Teknisi', 'Bendahara', 'Admin', 'Kasir']
            : ['Superadmin', 'Teknisi', 'Bendahara', 'Admin', 'Kasir'];

        $rules = [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->whereNull('deleted_at')->ignore($this->userId),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at')->ignore($this->userId),
            ],
            'password' => $this->isEditMode ? 'nullable|min:6' : 'required|min:6',
            'role_name' => 'required|in:'.implode(',', $allowedRoles),
            'cabang_id' => ($currentUser->isOwner() && $this->role_name === 'Owner')
                ? 'nullable'
                : 'required|exists:cabangs,id',
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
            'role_name.in' => 'Peran yang dipilih tidak valid atau di luar hak akses Anda.',
            'cabang_id.required' => 'Pengguna wajib ditugaskan ke salah satu cabang.',
            'foto.image' => 'File harus berupa gambar (JPG, PNG, WEBP).',
            'foto.max' => 'Ukuran foto maksimal 4MB.',
        ];

        // Jika superadmin, kunci cabang_id ke cabangnya sendiri
        if (! $currentUser->isOwner()) {
            $this->cabang_id = $currentUser->cabang_id;
        }

        $validated = $this->validate($rules, $messages);

        // Jika role Owner, cabang_id selalu null (akses global HQ)
        if ($this->role_name === 'Owner') {
            $validated['cabang_id'] = null;
        }

        $fotoPath = null;
        if ($this->foto) {
            $fotoPath = $this->processAndSaveWebp($this->foto);
        }

        if ($this->isEditMode && $this->userId) {
            $user = User::findOrFail($this->userId);

            // RBAC Check for edit
            if (! $currentUser->isOwner()) {
                if ($user->isOwner() || (int) $user->cabang_id !== (int) $currentUser->cabang_id) {
                    session()->flash('error', 'Anda tidak memiliki otoritas untuk memperbarui akun ini.');

                    return;
                }
            }

            // Proteksi agar tidak menonaktifkan akun sendiri
            if ($user->id === Auth::id() && $validated['status'] === 'nonaktif') {
                session()->flash('error', 'Anda tidak dapat menonaktifkan akun yang sedang Anda gunakan.');

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

            // Hapus file lama jika foto diubah atau dihapus
            if (isset($updateData['foto_url']) && $user->foto_url && $user->foto_url !== $updateData['foto_url']) {
                if (str_starts_with($user->foto_url, 'storage/')) {
                    $oldPath = Str::after($user->foto_url, 'storage/');
                    Storage::disk('public')->delete($oldPath);
                }
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
        $currentUser = Auth::user();
        $user = User::findOrFail($id);

        // RBAC check
        if (! $currentUser->isOwner()) {
            if ($user->isOwner() || (int) $user->cabang_id !== (int) $currentUser->cabang_id) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk mengubah status akun ini.');

                return;
            }
        }

        // Self-lockout check
        if ($user->id === Auth::id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun yang sedang Anda gunakan.');

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

    public function deleteUser(int $id): void
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);

        // RBAC check
        if (! $currentUser->isOwner()) {
            if ($user->isOwner() || (int) $user->cabang_id !== (int) $currentUser->cabang_id) {
                session()->flash('error', 'Anda tidak memiliki otoritas untuk menghapus akun ini.');

                return;
            }
        }

        // Self-lockout check
        if ($user->id === Auth::id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun yang sedang Anda gunakan.');

            return;
        }

        // Minimal 1 active owner check
        if ($user->isOwner()) {
            $activeOwnersCount = User::role('Owner')->where('status', 'aktif')->count();
            if ($activeOwnersCount <= 1) {
                session()->flash('error', 'Minimal harus ada 1 akun Owner di sistem.');

                return;
            }
        }

        $namaUser = $user->name;
        $user->delete();

        session()->flash('success', "Akun pengguna {$namaUser} berhasil dihapus.");
    }

    public function render()
    {
        $currentUser = Auth::user();
        $isOwner = $currentUser->isOwner();

        $query = User::with(['cabang', 'roles'])
            ->whereHas('roles', function ($q) use ($isOwner) {
                if (! $isOwner) {
                    // Superadmin hanya mengelola Superadmin, Teknisi, Bendahara, Admin (dan Kasir untuk kompatibilitas) di cabangnya
                    if ($this->roleFilter === 'semua') {
                        $q->whereIn('name', ['Superadmin', 'Teknisi', 'Bendahara', 'Admin', 'Kasir']);
                    } else {
                        if ($this->roleFilter === 'Admin') {
                            $q->whereIn('name', ['Admin', 'Kasir']);
                        } else {
                            $q->where('name', $this->roleFilter);
                        }
                    }
                } else {
                    if ($this->roleFilter !== 'semua') {
                        if ($this->roleFilter === 'Admin') {
                            $q->whereIn('name', ['Admin', 'Kasir']);
                        } else {
                            $q->where('name', $this->roleFilter);
                        }
                    }
                }
            })
            ->when(! $isOwner, function ($q) use ($currentUser) {
                $q->where('cabang_id', $currentUser->cabang_id);
            })
            ->when($isOwner && $this->cabangFilter !== 'semua', function ($q) {
                if ($this->cabangFilter === 'hq') {
                    $q->whereNull('cabang_id');
                } else {
                    $q->where('cabang_id', $this->cabangFilter);
                }
            })
            ->when(! empty($this->search), function ($q) {
                $cleanSearch = trim($this->search);
                $words = array_filter(explode(' ', $cleanSearch));
                $q->where(function ($sub) use ($words) {
                    foreach ($words as $word) {
                        $pattern = '%'.strtolower($word).'%';
                        $sub->where(function ($sq) use ($pattern) {
                            $sq->whereRaw('LOWER(name) LIKE ?', [$pattern])
                                ->orWhereRaw('LOWER(username) LIKE ?', [$pattern])
                                ->orWhereRaw('LOWER(email) LIKE ?', [$pattern])
                                ->orWhereRaw('LOWER(COALESCE(no_hp, \'\')) LIKE ?', [$pattern]);
                        });
                    }
                });
            });

        $allowedSorts = ['name', 'username', 'email', 'cabang_id', 'no_hp', 'status', 'created_at', 'id'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'name';
        $sortDir = strtolower($this->sortDirection) === 'desc' ? 'desc' : 'asc';

        $totalUsers = (clone $query)->count();
        $users = $query->orderBy($sortColumn, $sortDir)->take($this->perPage)->get();
        $hasMore = $totalUsers > $this->perPage;

        $cabangs = $isOwner
            ? Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get()
            : Cabang::where('id', $currentUser->cabang_id)->get();

        return view('livewire.master.user.index', [
            'users' => $users,
            'hasMore' => $hasMore,
            'totalUsers' => $totalUsers,
            'cabangs' => $cabangs,
            'isOwner' => $isOwner,
        ]);
    }
}
