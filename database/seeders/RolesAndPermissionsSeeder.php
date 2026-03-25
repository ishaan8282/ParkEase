<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'manage users',
            'manage spaces',
            'manage bookings',
            'view analytics',
            'create space',
            'edit own space',
            'view own bookings',
            'view own earnings',
            'search spaces',
            'create booking',
            'cancel booking',
            'write review',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]); 
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $ownerRole->syncPermissions([
            'create space',
            'edit own space',
            'view own bookings',
            'view own earnings',
        ]);

        $driverRole = Role::firstOrCreate(['name' => 'driver']);
        $driverRole->syncPermissions([
            'search spaces',
            'create booking',
            'cancel booking',
            'write review',
        ]);
    }
}
