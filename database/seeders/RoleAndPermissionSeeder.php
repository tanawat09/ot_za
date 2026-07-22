<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'manage-users',
            'view-users',
            'view-audit-logs',
            'access-dashboard',
            'manage-master-data',
            'create-ot-request',
            'approve-ot-request',
            'view-reports',
            'export-reports',
            'hr-close-monthly',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Roles & Sync Permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        $hrRole = Role::firstOrCreate(['name' => 'HR', 'guard_name' => 'web']);
        $hrRole->syncPermissions([
            'access-dashboard',
            'view-users',
            'view-audit-logs',
            'view-reports',
            'export-reports',
            'hr-close-monthly',
        ]);

        $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $managerRole->syncPermissions([
            'access-dashboard',
            'approve-ot-request',
            'view-reports',
            'export-reports',
        ]);

        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);
        $supervisorRole->syncPermissions([
            'access-dashboard',
            'create-ot-request',
            'view-reports',
            'export-reports',
        ]);

        $employeeRole = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);
        $employeeRole->syncPermissions([
            'access-dashboard',
        ]);
    }
}
