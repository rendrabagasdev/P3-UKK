<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengguna
     */
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    /**
     * Update informasi profil pengguna
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('user', 'email')->ignore($user->id, 'id_user'),
            ],
        ]);
        
        // Update profil
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        
        // Update nama di tabel petugas jika pengguna adalah petugas
        if ($user->role == 2) {
            $petugas = Petugas::where('id_user', $user->id)->first();
            if ($petugas) {
                $petugas->name = $request->name;
                $petugas->save();
            }
        }
        
        return redirect()->route('profile.index')->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update password pengguna
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);
        
        $user = Auth::user();
        
        // Verifikasi password saat ini
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->with('error', 'Password saat ini tidak sesuai.');
        }
        
        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();
        
        return redirect()->route('profile.index')->with('success', 'Password berhasil diperbarui!');
    }

    /**
     * Update informasi petugas
     */
    public function updateStaff(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role != 2) {
            return redirect()->route('profile.index')->with('error', 'Anda bukan petugas.');
        }
        
        $request->validate([
            'no_hp' => 'required|string|max:15',
        ]);
        
        $petugas = Petugas::where('id_user', $user->id)->first();
        
        if ($petugas) {
            $petugas->no_hp = $request->no_hp;
            $petugas->save();
            
            return redirect()->route('profile.index')->with('success', 'Informasi petugas berhasil diperbarui!');
        }
        
        return redirect()->route('profile.index')->with('error', 'Data petugas tidak ditemukan.');
    }
}
