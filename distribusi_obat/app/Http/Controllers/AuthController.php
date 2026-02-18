<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\CourierDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
// Import Model Role dari Spatie
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        // CARA SPATIE: Ambil role dari tabel roles Spatie, kecuali admin
        $roles = Role::where('name', '!=', 'admin')->get();
        return view('auth.register', compact('roles'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->status == 0) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda sedang menunggu persetujuan Admin.']);
            }

            if ($user->status == 2) {
                Auth::logout();
                return back()->withErrors(['email' => 'Pendaftaran akun Anda ditolak oleh Admin.']);
            }

            $request->session()->regenerate();
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;
            session(['api_token' => $token]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'LOGIN: Pengguna masuk ke dalam sistem'
            ]);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'Email atau password salah.']);
    }

    public function register(Request $request) {
        // Validasi
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        return DB::transaction(function() use ($request) {
            // 1. Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'status' => 0 // Pending
            ]);

            // 2. Assign Role via Spatie
            $role = \Spatie\Permission\Models\Role::findById($request->role_id);
            $user->assignRole($role->name);

            // 3. Jika Role adalah Courier, simpan ke tabel detail
            if ($role->name === 'courier') {
                CourierDetail::create([
                    'user_id' => $user->id,
                    'vehicle_type' => $request->vehicle_type,
                    'vehicle_plate' => $request->vehicle_plate,
                ]);
            }

            return response()->json(['message' => 'Success'], 201);
        });
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            AuditLog::create(['user_id' => $user->id, 'action' => 'LOGOUT: Keluar dari sistem']);
            $user->tokens()->delete();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}