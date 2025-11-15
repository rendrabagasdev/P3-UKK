<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah ada token di cookie
        $token = $request->cookie('auth_token');
        
        if ($token) {
            // Cari token di database
            $accessToken = PersonalAccessToken::findToken($token);
            
            if ($accessToken) {
                // Set user yang terautentikasi
                Auth::guard('web')->setUser($accessToken->tokenable);
            }
        }
        
        // Jika tidak ada user yang terautentikasi, redirect ke login
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        return $next($request);
    }
}
