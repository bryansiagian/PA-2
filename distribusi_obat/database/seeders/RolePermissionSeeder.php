<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\CourierDetail;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache permission
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat Permission
        $permissions = ['manage users', 'manage inventory', 'create request', 'delivery task', 'view reports'];
        foreach ($permissions as $p) {
            Permission::create(['name' => $p, 'guard_name' => 'web']);
        }

        // 2. Buat Role & Assign Permission
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $operator = Role::create(['name' => 'operator']);
        $operator->givePermissionTo(['manage users', 'manage inventory', 'view reports']);

        $customer = Role::create(['name' => 'customer']);
        $customer->givePermissionTo(['create request']);

        $courier = Role::create(['name' => 'courier']);
        $courier->givePermissionTo(['delivery task']);

        // 3. Buat User Contoh
        $adminUser = User::create([
            'name' => 'Admin Sistem',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'status' => 1, 'active' => 1
        ]);
        $adminUser->assignRole('admin');

        $opUser = User::create([
            'name' => 'Budi Operator',
            'email' => 'operator@test.com',
            'password' => Hash::make('password'),
            'status' => 1, 'active' => 1
        ]);
        $opUser->assignRole('operator');

        $courierUser = User::create([
            'name' => 'Andi Kurir',
            'email' => 'courier@test.com',
            'password' => Hash::make('password'),
            'status' => 1, 'active' => 1
        ]);
        $courierUser->assignRole('courier');

        // Tambah Detail Kendaraan Kurir
        CourierDetail::create([
            'user_id' => $courierUser->id,
            'vehicle_type' => 'motorcycle',
            'vehicle_plate' => 'B 1234 ABC'
        ]);
    }
}