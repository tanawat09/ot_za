<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@company.com'],
            [
                'emp_code' => 'EMP-0001',
                'name' => 'ผู้ดูแลระบบ (Super Admin)',
                'password' => Hash::make('Password123!'),
                'is_active' => true,
                'must_change_password' => false,
            ]
        );
        $admin->assignRole('Super Admin');

        // 2. HR Admin
        $hr = User::firstOrCreate(
            ['email' => 'hr@company.com'],
            [
                'emp_code' => 'EMP-0002',
                'name' => 'เจ้าหน้าที่ HR (HR Admin)',
                'password' => Hash::make('Password123!'),
                'is_active' => true,
                'must_change_password' => false,
            ]
        );
        $hr->assignRole('HR');

        // 3. Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@company.com'],
            [
                'emp_code' => 'EMP-0003',
                'name' => 'ผู้จัดการแผนก (Manager)',
                'password' => Hash::make('Password123!'),
                'is_active' => true,
                'must_change_password' => false,
            ]
        );
        $manager->assignRole('Manager');

        // 4. Supervisor
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@company.com'],
            [
                'emp_code' => 'EMP-0004',
                'name' => 'หัวหน้างาน (Supervisor)',
                'password' => Hash::make('Password123!'),
                'is_active' => true,
                'must_change_password' => false,
            ]
        );
        $supervisor->assignRole('Supervisor');
    }
}
