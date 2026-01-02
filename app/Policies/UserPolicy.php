<?php

namespace App\Policies;

use App\Models\User;
use App\Enums\UserRole;

class UserPolicy
{
    /**
     * PERINGATAN: Jangan gunakan 'before' yang mengembalikan true untuk Admin
     * pada policy ini jika Anda ingin proteksi 'self-reset' tetap berjalan.
     */

    public function resetTwoFactor(User $admin, User $target): bool
    {
        // 1. Harus memiliki role Admin
        $isAdmin = $admin->role === UserRole::ADMIN;

        // 2. ID Admin tidak boleh sama dengan ID Target
        $isNotSelf = $admin->id !== $target->id;

        return $isAdmin && $isNotSelf;
    }
}
