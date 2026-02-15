<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\CourierDetail; // Import model baru ini
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Bersihkan cache Spatie agar tidak terjadi duplikasi
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. BUAT DAFTAR PERMISSION (IZIN)
        $permissions = [
            'manage users',      // Admin & Operator (Bisa verifikasi akun baru)
            'manage inventory',  // Admin & Operator (CRUD Obat & Kategori)
            'create request',    // Customer
            'delivery task',     // Courier
            'view audit logs'    // Admin
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. BUAT ROLE & BERIKAN PERMISSION
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all()); // Admin punya semua kunci

        $operator = Role::firstOrCreate(['name' => 'operator']);
        $operator->syncPermissions(['manage users', 'manage inventory']);

        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->syncPermissions(['create request']);

        $courier = Role::firstOrCreate(['name' => 'courier']);
        $courier->syncPermissions(['delivery task']);

        // 3. BUAT USER CONTOH & ASSIGN ROLE

        // --- SUPER ADMIN ---
        $u1 = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'status' => 1,
        ]);
        $u1->assignRole('admin');

        // --- OPERATOR ---
        $u2 = User::create([
            'name' => 'Budi Operator',
            'email' => 'operator@test.com',
            'password' => Hash::make('password123'),
            'status' => 1,
        ]);
        $u2->assignRole('operator');

        // --- CUSTOMER ---
        $u3 = User::create([
            'name' => 'RSUD Sehat Selalu',
            'email' => 'customer@test.com',
            'password' => Hash::make('password123'),
            'status' => 1,
            'address' => 'Jl. Kesehatan No. 10, Jakarta',
        ]);
        $u3->assignRole('customer');

        // --- COURIER ---
        $u4 = User::create([
            'name' => 'Andi Logistik',
            'email' => 'courier@test.com',
            'password' => Hash::make('password123'),
            'status' => 1,
            'address' => 'Jl. Bukit Tinggi No. 50',
        ]);
        $u4->assignRole('courier');

        // PENTING: Tambahkan data kendaraan untuk si Andi
        CourierDetail::create([
            'user_id' => $u4->id,
            'vehicle_type' => 'motorcycle',
            'vehicle_plate' => 'B 1234 ABC'
        ]);
    }
}