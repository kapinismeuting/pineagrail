<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticateUserCustom
{
    /**
     * Eksekusi verifikasi kredensial dan status akun user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Models\User|null
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __invoke(Request $request)
    {
        // Fortify mengirimkan field 'login' (atau 'email' tergantung form)
        $loginInput = $request->input('login') ?? $request->input('email');

        if (! $loginInput) {
            return null;
        }

        // 1. Cari user berdasarkan Email ATAU Username (termasuk yang di-Soft Delete)
        $user = User::withTrashed()
            ->where(function ($query) use ($loginInput) {
                $query->where('email', $loginInput)
                    ->orWhere('username', $loginInput);
            })
            ->first();

        // 2. Verifikasi Password
        if ($user && Hash::check($request->password, $user->password)) {

            // Cek 1: Jika akun dalam kondisi Soft Deleted
            if ($user->trashed()) {
                $superadminName = $user->actionBy ? $user->actionBy->name : 'Superadmin';

                throw ValidationException::withMessages([
                    'login' => ["Maaf, akun Anda telah dihapus oleh {$superadminName}. Jika ada keberatan, silakan hubungi yang bersangkutan secara langsung."],
                ]);
            }

            // Cek 2: Jika ada pesan status kustom (misal: pemberitahuan demote)
            if ($user->status_message) {
                session()->flash('status', $user->status_message);

                // Hapus pesan setelah dimasukkan ke flash session agar tidak muncul berulang kali
                $user->update(['status_message' => null]);
            }

            return $user;
        }

        // Jika kredensial salah, kembalikan null agar Fortify menangani error standar
        return null;
    }
}
