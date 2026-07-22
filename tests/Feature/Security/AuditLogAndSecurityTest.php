<?php

namespace Tests\Feature\Security;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $this->admin = User::create([
            'emp_code' => 'ADM-001',
            'name' => 'Super Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->admin->assignRole($role);
    }

    public function test_admin_can_view_audit_logs(): void
    {
        AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'Test Action',
            'module' => 'Security',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/audit-logs');
        $response->assertStatus(200);
        $response->assertSee('Audit Logs');
        $response->assertSee('Test Action');
    }
}
