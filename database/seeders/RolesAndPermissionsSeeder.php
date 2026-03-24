<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            // Admin
            'manage users',
            'manage spaces',
            'manage bookings',
            'view analytics',
            // Owner
            'create space',
            'edit own space',
            'view own bookings',
            'view own earnings',
            // Driver
            'search spaces',
            'create booking',
            'cancel booking',
            'write review',
        ];

        foreach ($permissions as $perm) {
            Permission::create(['name' => $perm]);
        }

        // Roles
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $ownerRole = Role::create(['name' => 'owner']);
        $ownerRole->givePermissionTo([
            'create space',
            'edit own space',
            'view own bookings',
            'view own earnings',
        ]);

        $driverRole = Role::create(['name' => 'driver']);
        $driverRole->givePermissionTo([
            'search spaces',
            'create booking',
            'cancel booking',
            'write review',
        ]);
    }
}
