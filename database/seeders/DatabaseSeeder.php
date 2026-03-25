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
        // Call roles and permissions seeder first
        $this->call(RolesAndPermissionsSeeder::class);

        // Create Admin User (only if not exists)
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@parkease.com'],
            [
                'name'     => 'Admin',
                'phone'    => '9999999999',
                'password' => Hash::make('password'),
                'status'   => 'active',
            ]
        );
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }

        // Create test Owner (only if not exists)
        $ownerUser = User::firstOrCreate(
            ['email' => 'owner@parkease.com'],
            [
                'name'     => 'Test Owner',
                'phone'    => '8888888888',
                'password' => Hash::make('password'),
                'status'   => 'active',
            ]
        );
        if (!$ownerUser->hasRole('owner')) {
            $ownerUser->assignRole('owner');
        }

        // Create test Driver (only if not exists)
        $driverUser = User::firstOrCreate(
            ['email' => 'driver@parkease.com'],
            [
                'name'     => 'Test Driver',
                'phone'    => '7777777777',
                'password' => Hash::make('password'),
                'status'   => 'active',
            ]
        );
        if (!$driverUser->hasRole('driver')) {
            $driverUser->assignRole('driver');
        }
    }
}
