<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  Peran yang diizinkan (1=admin, 2=petugas, 3=user)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        $allowedRoles = [];

        // Support both numeric roles and string roles (for backward compatibility)
        foreach ($roles as $role) {
            if ($role === 'admin') {
                $allowedRoles[] = 1;
            } elseif ($role === 'petugas') {
                $allowedRoles[] = 2;
            } elseif ($role === 'user') {
                $allowedRoles[] = 3;
            } else {
                $allowedRoles[] = (int) $role;
            }
        }

        if (in_array($user->role, $allowedRoles)) {
            return $next($request);
        }

        return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
