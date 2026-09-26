<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class RoleIndex extends Component
{
    use WithPagination;

    public string $search = '';

    // Modal State
    public bool $showCreateModal = false;
    public bool $showEditModal = false;

    public ?int $editingRoleId = null;
    public string $name = '';
    public array $selectedPermissions = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'selectedPermissions', 'editingRoleId']);
        $this->resetValidation();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function createRole(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'selectedPermissions' => ['array'],
        ]);

        $role = Role::create(['name' => strtolower($this->name)]);
        $role->syncPermissions($this->selectedPermissions);

        $this->showCreateModal = false;
        $this->resetForm();

        session()->flash('success', "Role '{$role->name}' berhasil dibuat.");
    }

    public function openEditModal(int $roleId): void
    {
        $this->resetForm();
        $role = Role::findOrFail($roleId);

        $this->editingRoleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();

        $this->showEditModal = true;
    }

    public function updateRole(): void
    {
        $role = Role::findOrFail($this->editingRoleId);

        // Proteksi role superadmin agar namanya tidak diubah sembarangan
        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'selectedPermissions' => ['array'],
        ]);

        $role->update(['name' => strtolower($this->name)]);
        $role->syncPermissions($this->selectedPermissions);

        $this->showEditModal = false;
        $this->resetForm();

        session()->flash('success', "Role '{$role->name}' berhasil diperbarui.");
    }

    public function deleteRole(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        if (in_array($role->name, ['superadmin', 'admin', 'customer'])) {
            session()->flash('error', "Role bawaan sistem ({$role->name}) tidak boleh dihapus.");
            return;
        }

        $role->delete();

        session()->flash('success', "Role '{$role->name}' berhasil dihapus.");
    }

    public function render()
    {
        $roles = Role::with(['permissions'])
            ->withCount('users')
            ->where('name', 'like', '%' . $this->search . '%')
            ->paginate(10);

        $permissions = Permission::all();

        return view('livewire.admin.roles.role-index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }
}