<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Mengizinkan Superadmin untuk bypass pengecekan hak akses biasa,
     * KECUALI untuk aksi sensitif seperti delete, demote, dan promote.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Jangan bypass aksi-aksi sensitif ini agar selalu melewati pengecekan di method masing-masing
        if ($user->hasRole('superadmin') && ! in_array($ability, ['delete', 'demoteFromSuperadmin', 'promoteToSuperadmin'])) {
            return true;
        }

        return null;
    }

    /**
     * Menentukan apakah pengguna ($authenticatedUser) boleh menghapus target user ($targetUser).
     */
    public function delete(User $authenticatedUser, User $targetUser): Response
    {
        // 1. Pastikan hanya Superadmin yang bisa menghapus user
        if (! $authenticatedUser->hasRole('superadmin')) {
            return Response::deny('Hanya Superadmin yang dapat menghapus akun pengguna.');
        }

        // 2. Proteksi Self-Deletion: Mencegah Superadmin menghapus akunnya sendiri dari tabel manajemen user
        if ($authenticatedUser->id === $targetUser->id) {
            return Response::deny('Anda tidak dapat menghapus akun Anda sendiri dari daftar pengguna. Gunakan fitur hapus akun di pengaturan profil jika diperlukan.');
        }

        // 3. Proteksi jika target yang akan dihapus adalah Superadmin lain
        if ($targetUser->hasRole('superadmin')) {
            $activeSuperadmins = User::role('superadmin')->count();

            // Aturan Kritis: Harus menyisakan minimal 1 Superadmin aktif
            if ($activeSuperadmins <= 1) {
                return Response::deny('Gagal menghapus! Sistem wajib memiliki minimal 1 akun Superadmin aktif.');
            }
        }

        return Response::allow();
    }

    /**
     * Menentukan apakah pengguna boleh menaikkan role target menjadi Superadmin.
     */
    public function promoteToSuperadmin(User $authenticatedUser, User $targetUser): Response
    {
        if (! $authenticatedUser->hasRole('superadmin')) {
            return Response::deny('Hanya Superadmin yang dapat menaikkan hak akses pengguna.');
        }

        if ($targetUser->hasRole('superadmin')) {
            return Response::deny('Pengguna ini sudah memiliki role Superadmin.');
        }

        return Response::allow();
    }

    /**
     * Menentukan apakah pengguna boleh menurunkan role Superadmin lain.
     */
    public function demoteFromSuperadmin(User $authenticatedUser, User $targetUser): Response
    {
        if (! $authenticatedUser->hasRole('superadmin')) {
            return Response::deny('Hanya Superadmin yang dapat menurunkan hak akses pengguna.');
        }

        // Mencegah Self-Demotion dari antarmuka ini
        if ($authenticatedUser->id === $targetUser->id) {
            return Response::deny('Anda tidak dapat menurunkan hak akses Anda sendiri.');
        }

        if (! $targetUser->hasRole('superadmin')) {
            return Response::deny('Pengguna ini bukan seorang Superadmin.');
        }

        $activeSuperadmins = User::role('superadmin')->count();

        // Aturan Kritis: Minimal tersisa 1 Superadmin
        if ($activeSuperadmins <= 1) {
            return Response::deny('Gagal menurunkan role! Harus ada minimal 1 akun Superadmin aktif di sistem.');
        }

        return Response::allow();
    }
}
