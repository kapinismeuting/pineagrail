<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class DeleteUserForm extends Component
{
    use PasswordValidationRules;

    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        // 1. Validasi Password Saat Ini
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        $user = Auth::user();

        try {
            // 2. KUNCI UTAMA: Jalankan pengecekan aturan di UserPolicy
            // Jika aturan gagal (misal: Superadmin terakhir), Policy akan melempar AuthorizationException
            Gate::authorize('delete', $user);

            // 3. Catat audit trail siapa yang melakukan aksi
            $user->update([
                'action_by_id' => $user->id,
            ]);

            // 4. Logout user dan jalankan Soft Delete
            $logout();
            $user->delete();

            $this->redirect('/', navigate: true);

        } catch (AuthorizationException $e) {
            // 5. Tangkap pesan penolakan dari Policy dan tampilkan ke UI
            $this->addError('password', $e->getMessage());
        }
    }
}
