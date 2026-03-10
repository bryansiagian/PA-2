<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        // Tidak perlu lagi mengambil semua role, karena register khusus customer
        return view('auth.register');
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
        // Validasi dihapus bagian role_id-nya
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);

        return DB::transaction(function() use ($request) {
            // 1. Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'status' => 0 // Tetap Pending menunggu Approve Admin
            ]);

            // 2. Kunci Role ke 'customer'
            // Pastikan role 'customer' sudah dibuat di Seeder sebelumnya
            $user->assignRole('customer');

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'REGISTER: Pendaftaran akun faskes baru (Customer)'
            ]);

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