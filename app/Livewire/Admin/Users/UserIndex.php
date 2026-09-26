<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserIndex extends Component
{
    use WithPagination;

    // Search & Filter
    public string $search = '';

    // Form State (Create & Edit)
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingUserId = null;

    public string $name = '';

    public string $email = '';

    public string $username = '';

    public string $role = 'customer';

    public string $password = '';

    public string $password_confirmation = '';

    // Toggle Visibility Password
    public bool $showPassword = false;

    public bool $showPasswordConfirmation = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Toggle status tampil/sembunyi password.
     */
    public function togglePasswordVisibility(): void
    {
        $this->showPassword = ! $this->showPassword;
    }

    public function togglePasswordConfirmationVisibility(): void
    {
        $this->showPasswordConfirmation = ! $this->showPasswordConfirmation;
    }

    /**
     * Reset properti form input.
     */
    public function resetForm(): void
    {
        $this->reset([
            'name',
            'email',
            'username',
            'role',
            'password',
            'password_confirmation',
            'editingUserId',
            'showPassword',
            'showPasswordConfirmation',
        ]);
        $this->resetValidation();
    }

    /**
     * Buka Modal Tambah User.
     */
    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    /**
     * Simpan User Baru (Create).
     */
    public function createUser(): void
    {
        if ($this->role === 'superadmin' && ! auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:30', 'alpha_dash', 'unique:users,username'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username ?: null,
            'password' => Hash::make($this->password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($this->role);

        $this->showCreateModal = false;
        $this->resetForm();

        session()->flash('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    /**
     * Buka Modal Edit User.
     */
    public function openEditModal(int $userId): void
    {
        $this->resetForm();
        $user = User::findOrFail($userId);

        $this->abortIfSuperadminIsHidden($user);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->username = $user->username ?? '';
        $this->role = $user->roles->first()?->name ?? 'customer';

        $this->showEditModal = true;
    }

    /**
     * Perbarui Data User (Update).
     */
    public function updateUser(): void
    {
        $user = User::findOrFail($this->editingUserId);

        $this->abortIfSuperadminIsHidden($user);

        if ($this->role === 'superadmin' && ! auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['nullable', 'string', 'max:30', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', 'exists:roles,name'],
            'password' => ['nullable', 'string', 'min:8'], // Opsional saat edit
        ]);

        // Cek proteksi jika role diubah dari Superadmin
        if ($user->hasRole('superadmin') && $this->role !== 'superadmin') {
            try {
                Gate::authorize('demoteFromSuperadmin', $user);
            } catch (AuthorizationException $e) {
                session()->flash('error', $e->getMessage());
                $this->showEditModal = false;

                return;
            }
        }

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username ?: null,
        ];

        if (! empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        $user->update($userData);
        $user->syncRoles([$this->role]);

        $this->showEditModal = false;
        $this->resetForm();

        session()->flash('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Menghapus user (Soft Delete).
     */
    public function delete(int $userId): void
    {
        $targetUser = User::findOrFail($userId);

        $this->abortIfSuperadminIsHidden($targetUser);

        try {
            Gate::authorize('delete', $targetUser);

            $targetUser->update([
                'action_by_id' => auth()->id(),
            ]);

            $targetUser->delete();

            session()->flash('success', "Pengguna {$targetUser->name} berhasil dihapus.");
        } catch (AuthorizationException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $usersQuery = User::with('roles')
            ->where(function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('username', 'like', '%'.$this->search.'%');
            });

        if (! auth()->user()->hasRole('superadmin')) {
            $usersQuery->withoutRole('superadmin');
        }

        $users = $usersQuery->latest()->paginate(10);

        $availableRoles = Role::query()
            ->when(
                ! auth()->user()->hasRole('superadmin'),
                fn ($query) => $query->where('name', '!=', 'superadmin'),
            )
            ->get();

        return view('components.admin.users.user-index', [
            'users' => $users,
            'availableRoles' => $availableRoles,
        ]);
    }

    private function abortIfSuperadminIsHidden(User $user): void
    {
        if ($user->hasRole('superadmin') && ! auth()->user()->hasRole('superadmin')) {
            abort(403);
        }
    }
}
