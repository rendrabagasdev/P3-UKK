<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class RoleAccessService
{
    /**
     * Periksa apakah pengguna memiliki akses berdasarkan peran yang diizinkan
     *
     * @param array $allowedRoles Peran yang diizinkan (1=admin, 2=petugas, 3=user)
     * @return bool
     */
    public static function hasAccess(array $allowedRoles): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        $processedRoles = [];

        // Support both numeric roles and string roles
        foreach ($allowedRoles as $role) {
            if ($role === 'admin') {
                $processedRoles[] = 1;
            } elseif ($role === 'petugas') {
                $processedRoles[] = 2;
            } elseif ($role === 'user') {
                $processedRoles[] = 3;
            } else {
                $processedRoles[] = (int) $role;
            }
        }

        return in_array($user->role, $processedRoles);
    }
}
