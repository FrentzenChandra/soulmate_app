<?php

namespace App\Livewire\Master\User;

use App\Models\Cabang;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Master Pengguna - Soulmate Komputer')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = 'semua';
    public string $cabangFilter = 'semua';

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

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => 'semua'],
        'cabangFilter' => ['except' => 'semua'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingCabangFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->reset(['userId', 'name', 'email', 'username', 'password', 'no_hp', 'cabang_id']);
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

        $this->isEditMode = true;
        $this->modalOpen = true;
    }

    public function closeModal()
    {
        $this->modalOpen = false;
        $this->resetValidation();
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
                'unique:users,username,' . ($this->userId ?? 'NULL') . ',id'
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . ($this->userId ?? 'NULL') . ',id'
            ],
            'password' => $this->isEditMode ? 'nullable|min:6' : 'required|min:6',
            'role_name' => 'required|in:Owner,Superadmin',
            'cabang_id' => $this->role_name === 'Superadmin' ? 'required|exists:cabangs,id' : 'nullable',
            'no_hp' => 'nullable|string|max:20',
            'status' => 'required|in:aktif,nonaktif',
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
        ];

        $validated = $this->validate($rules, $messages);

        // Jika role Owner, cabang_id selalu null (akses global)
        if ($this->role_name === 'Owner') {
            $validated['cabang_id'] = null;
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

            if (!empty($validated['password'])) {
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

        session()->flash('success', "Status akun {$user->name} diubah menjadi " . ucfirst($newStatus) . ".");
    }

    public function render()
    {
        $users = User::with(['cabang', 'roles'])
            ->whereHas('roles', function ($q) {
                if ($this->roleFilter === 'semua') {
                    $q->whereIn('name', ['Owner', 'Superadmin']);
                } else {
                    $q->where('name', $this->roleFilter);
                }
            })
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('username', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('no_hp', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->cabangFilter !== 'semua', function ($q) {
                if ($this->cabangFilter === 'hq') {
                    $q->whereNull('cabang_id');
                } else {
                    $q->where('cabang_id', $this->cabangFilter);
                }
            })
            ->orderBy('id', 'asc')
            ->paginate(10);

        $cabangs = Cabang::where('status', 'aktif')->orderBy('nama_cabang')->get();

        return view('livewire.master.user.index', [
            'users' => $users,
            'cabangs' => $cabangs,
        ]);
    }
}
