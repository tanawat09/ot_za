<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $supervisorRole = Role::create(['name' => 'Supervisor', 'guard_name' => 'web']);

        $this->adminUser = User::create([
            'emp_code' => 'ADMIN-001',
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->adminUser->assignRole($adminRole);

        $this->regularUser = User::create([
            'emp_code' => 'USER-001',
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->regularUser->assignRole($supervisorRole);
    }

    public function test_super_admin_can_access_user_management(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('จัดการผู้ใช้งาน');
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_new_user(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/users', [
            'emp_code' => 'EMP-999',
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => 'Password123!',
            'role' => 'Supervisor',
            'is_active' => 1,
            'must_change_password' => 1,
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'email' => 'john@test.com',
            'emp_code' => 'EMP-999',
        ]);
    }

    public function test_super_admin_can_toggle_user_status(): void
    {
        $response = $this->actingAs($this->adminUser)->patch("/admin/users/{$this->regularUser->id}/toggle-status");
        
        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'is_active' => false,
        ]);
    }
}
