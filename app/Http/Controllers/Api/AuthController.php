<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Mendaftarkan pengguna baru
     */
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Log request untuk debugging
            \Log::info('Register request received', ['data' => $request->all()]);
        
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:user',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                // Log validation errors
                \Log::warning('Register validation failed', ['errors' => $validator->errors()->toArray()]);
                
                // Periksa apakah error adalah tentang username yang sudah ada
                if ($validator->errors()->has('username') && 
                    str_contains($validator->errors()->get('username')[0], 'already been taken')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Username sudah digunakan',
                        'data' => $validator->errors(),
                    ], 422);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error Validasi',
                    'data' => $validator->errors(),
                ], 422);
            }

            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => 3, // Role pengguna biasa
            ]);

            // Buat token dengan Sanctum
            $token = $user->createToken('auth-token')->plainTextToken;

            $response = [
                'success' => true,
                'message' => 'Pengguna berhasil terdaftar',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],
            ];
            
            \Log::info('Register successful', $response);
            
            return response()->json($response, 201);
            
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mendaftarkan pengguna',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login pengguna
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Log request untuk debugging
            \Log::info('Login request received', ['data' => $request->except('password')]);
            
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                \Log::warning('Login validation failed', ['errors' => $validator->errors()->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error Validasi',
                    'data' => $validator->errors(),
                ], 422);
            }

            // Memeriksa username
            $user = User::where('username', $request->username)->first();
            
            if (!$user) {
                \Log::warning('Login failed: user not found', ['username' => $request->username]);
                return response()->json([
                    'success' => false,
                    'message' => 'Username tidak ditemukan',
                ], 401);
            }

            // Memeriksa password
            if (! Hash::check($request->password, $user->password)) {
                \Log::warning('Login failed: invalid password', ['username' => $request->username]);
                return response()->json([
                    'success' => false,
                    'message' => 'Password tidak valid',
                ], 401);
            }

            // Semua user bisa login ke aplikasi mobile
            // Role check sudah dihapus agar admin juga bisa login
            \Log::info('Login successful', ['username' => $request->username, 'role' => $user->role]);

            // Buat token dengan Sanctum
            $token = $user->createToken('auth-token')->plainTextToken;

            $response = [
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],
            ];
            
            \Log::info('Login successful', ['username' => $user->username]);
            
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat proses login',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout pengguna
     */
    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        // Cabut token saat ini dengan Sanctum
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil keluar dari sistem',
        ]);
    }

    /**
     * Mendapatkan profil pengguna
     */
    public function profile(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Profil pengguna berhasil diambil',
            'data' => $request->user(),
        ]);
    }

    /**
     * Memperbarui profil pengguna
     */
    public function updateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:users,username,' . $request->user()->id . ',id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username sudah digunakan',
                    'data' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();
            $user->username = $request->username;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            \Log::error('Update profile error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui profil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengubah password pengguna
     */
    public function changePassword(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error Validasi',
                    'data' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();

            // Memeriksa password saat ini
            if (! Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password saat ini tidak valid',
                ], 422);
            }

            // Memperbarui password
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah',
            ]);
        } catch (\Exception $e) {
            \Log::error('Change password error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
