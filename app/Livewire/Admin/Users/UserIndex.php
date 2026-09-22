<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    // Field pencarian reaktif
    public string $search = '';

    // Reset halaman pagination saat kata kunci pencarian berubah
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Menaikkan role pengguna menjadi Superadmin.
     */
    public function promote(int $userId): void
    {
        $targetUser = User::findOrFail($userId);

        try {
            // Evaluasi Policy
            Gate::authorize('promoteToSuperadmin', $targetUser);

            // Perbarui role menggunakan Spatie Permission
            $targetUser->syncRoles(['superadmin']);

            session()->flash('success', "Berhasil menaikkan {$targetUser->name} menjadi Superadmin.");
        } catch (AuthorizationException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Menurunkan role Superadmin menjadi Admin atau Customer.
     */
    public function demote(int $userId, string $newRole = 'admin'): void
    {
        $targetUser = User::findOrFail($userId);

        try {
            // Evaluasi Policy (Memastikan bukan self-demote atau superadmin terakhir)
            Gate::authorize('demoteFromSuperadmin', $targetUser);

            $targetUser->syncRoles([$newRole]);

            // Catat pesan untuk ditampilkan saat user tersebut login
            $targetUser->update([
                'action_by_id' => auth()->id(),
                'status_message' => "Hak akses Anda telah diubah menjadi " . ucfirst($newRole) . " oleh " . auth()->user()->name,
            ]);

            session()->flash('success', "Berhasil menurunkan hak akses {$targetUser->name} menjadi {$newRole}.");
        } catch (AuthorizationException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Menghapus pengguna (Soft Delete).
     */
    public function delete(int $userId): void
    {
        $targetUser = User::findOrFail($userId);

        try {
            // Evaluasi Policy (Memastikan bukan self-delete atau superadmin terakhir)
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
        // Query user dengan eager loading 'roles' dan filter pencarian
        $users = User::with('roles')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('components.admin.users.user-index', [
            'users' => $users,
        ]);
    }
}
