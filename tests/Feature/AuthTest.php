<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
    }

    public function test_user_can_view_login_form(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('เข้าสู่ระบบ');
    }

    public function test_active_user_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'emp_code' => 'EMP-001',
            'name' => 'Test User',
            'email' => 'test@company.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $user->assignRole('Super Admin');

        $response = $this->post('/login', [
            'email' => 'test@company.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::create([
            'emp_code' => 'EMP-002',
            'name' => 'Inactive User',
            'email' => 'inactive@company.com',
            'password' => Hash::make('Password123!'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@company.com',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_with_must_change_password_flag_redirected_to_change_password_page(): void
    {
        $user = User::create([
            'emp_code' => 'EMP-003',
            'name' => 'New User',
            'email' => 'new@company.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
            'must_change_password' => true,
        ]);
        $user->assignRole('Super Admin');

        $response = $this->post('/login', [
            'email' => 'new@company.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/change-password');
    }

    public function test_user_can_logout(): void
    {
        $user = User::create([
            'emp_code' => 'EMP-004',
            'name' => 'Logout User',
            'email' => 'logout@company.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $user->assignRole('Super Admin');

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
