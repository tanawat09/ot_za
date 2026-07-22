<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\OvertimeType;
use App\Models\Position;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $this->adminUser = User::create([
            'emp_code' => 'ADMIN-001',
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->adminUser->assignRole($adminRole);
    }

    public function test_admin_can_crud_departments(): void
    {
        // 1. Create Department
        $response = $this->actingAs($this->adminUser)->post('/admin/departments', [
            'code' => 'ACC',
            'name_th' => 'แผนกบัญชีและการเงิน',
            'name_en' => 'Accounting & Finance',
            'is_active' => 1,
        ]);
        $response->assertRedirect('/admin/departments');
        $this->assertDatabaseHas('departments', ['code' => 'ACC']);

        // 2. Edit Department
        $dept = Department::where('code', 'ACC')->first();
        $response = $this->actingAs($this->adminUser)->put("/admin/departments/{$dept->id}", [
            'code' => 'ACC',
            'name_th' => 'แผนกบัญชี',
            'is_active' => 1,
        ]);
        $response->assertRedirect('/admin/departments');
        $this->assertDatabaseHas('departments', ['name_th' => 'แผนกบัญชี']);
    }

    public function test_admin_can_crud_teams(): void
    {
        $dept = Department::create(['code' => 'IT', 'name_th' => 'ไอที']);

        $response = $this->actingAs($this->adminUser)->post('/admin/teams', [
            'department_id' => $dept->id,
            'code' => 'IT-DEV',
            'name_th' => 'ทีมพัฒนาระบบ',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/teams');
        $this->assertDatabaseHas('teams', ['code' => 'IT-DEV']);
    }

    public function test_admin_can_crud_positions(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/positions', [
            'code' => 'SA',
            'title_th' => 'นักวิเคราะห์ระบบ',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/positions');
        $this->assertDatabaseHas('positions', ['code' => 'SA']);
    }

    public function test_admin_can_crud_overtime_types(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/overtime-types', [
            'code' => 'OT-15',
            'name_th' => 'OT 1.5 เท่า',
            'multiplier' => 1.50,
            'max_hours_per_day' => 8.0,
            'requires_document' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/overtime-types');
        $this->assertDatabaseHas('overtime_types', ['code' => 'OT-15']);
    }

    public function test_cannot_delete_department_with_employees(): void
    {
        $dept = Department::create(['code' => 'HR', 'name_th' => 'ทรัพยากรบุคคล']);
        Employee::create([
            'emp_code' => 'EMP-001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'department_id' => $dept->id,
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->adminUser)->delete("/admin/departments/{$dept->id}");
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('departments', ['id' => $dept->id]);
    }
}
