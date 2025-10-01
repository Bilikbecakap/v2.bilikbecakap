<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat permissions
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Buat role Super Admin dengan semua permission
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Buat role Admin 
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'view users', 'create users', 'edit users', 'delete users',
            'view roles',
        ]);

        // Buat role User (hanya bisa view)
        $user = Role::firstOrCreate(['name' => 'user']);
        $user->givePermissionTo(['view users']);
    }
}