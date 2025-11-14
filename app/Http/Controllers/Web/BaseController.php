<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class BaseController extends Controller
{
    /**
     * Periksa apakah pengguna memiliki akses berdasarkan peran yang diizinkan
     *
     * @param array $allowedRoles Peran yang diizinkan (1=admin, 2=petugas, 3=user)
     * @return bool
     */
    protected function hasAccess(array $allowedRoles): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        return in_array($user->role, $allowedRoles);
    }

    /**
     * Redirect pengguna jika tidak memiliki akses
     *
     * @param array $allowedRoles Peran yang diizinkan (1=admin, 2=petugas, 3=user)
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function authorizeRoles(array $allowedRoles): ?RedirectResponse
    {
        if (!$this->hasAccess($allowedRoles)) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return null;
    }

    /**
     * Redirect jika pengguna bukan admin (role 1)
     * 
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function authorizeAdmin(): ?RedirectResponse
    {
        return $this->authorizeRoles([1]);
    }

    /**
     * Redirect jika pengguna bukan admin atau petugas (role 1 atau 2)
     * 
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function authorizeStaff(): ?RedirectResponse
    {
        return $this->authorizeRoles([1, 2]);
    }

    /**
     * Redirect jika pengguna bukan user biasa (role 3)
     * 
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function authorizeUser(): ?RedirectResponse
    {
        return $this->authorizeRoles([3]);
    }
}
