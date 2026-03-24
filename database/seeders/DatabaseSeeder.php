<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $admin  = Role::create(['name' => 'admin']);
        $owner  = Role::create(['name' => 'owner']);
        $driver = Role::create(['name' => 'driver']);

        // Create Permissions
        $permissions = [
            'manage users', 'manage spaces', 'manage bookings', 'view analytics',
            'create space', 'edit own space', 'view own bookings', 'view own earnings',
            'search spaces', 'create booking', 'cancel booking', 'write review',
        ];

        foreach ($permissions as $perm) {
            Permission::create(['name' => $perm]);
        }

        // Assign permissions to roles
        $admin->givePermissionTo(Permission::all());
        $owner->givePermissionTo(['create space', 'edit own space', 'view own bookings', 'view own earnings']);
        $driver->givePermissionTo(['search spaces', 'create booking', 'cancel booking', 'write review']);

        // Create Admin User
        $adminUser = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@parkease.com',
            'phone'    => '9999999999',
            'password' => Hash::make('password'),
            'status'   => 'active',
        ]);
        $adminUser->assignRole('admin');

        // Create a test Owner
        $ownerUser = User::create([
            'name'     => 'Test Owner',
            'email'    => 'owner@parkease.com',
            'phone'    => '8888888888',
            'password' => Hash::make('password'),
            'status'   => 'active',
        ]);
        $ownerUser->assignRole('owner');

        // Create a test Driver
        $driverUser = User::create([
            'name'     => 'Test Driver',
            'email'    => 'driver@parkease.com',
            'phone'    => '7777777777',
            'password' => Hash::make('password'),
            'status'   => 'active',
        ]);
        $driverUser->assignRole('driver');
    }
}
