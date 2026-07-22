<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Imports\EmployeesImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class EmployeeImportPreviewAndClearTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

        $this->admin = User::factory()->create([
            'name' => 'Admin Test',
            'emp_code' => 'ADM001',
            'is_active' => true,
        ]);
        $this->admin->assignRole('Super Admin');

        $this->department = Department::create(['code' => 'LOG', 'name_th' => 'ฝ่ายขนส่ง']);
    }

    public function test_clear_all_employees(): void
    {
        Employee::create([
            'emp_code' => 'TEST001',
            'prefix' => 'นาย',
            'first_name' => 'ทดสอบ',
            'last_name' => 'ระบบ',
            'department_id' => $this->department->id,
            'salary' => 18000,
        ]);

        $this->assertDatabaseCount('employees', 1);

        $response = $this->actingAs($this->admin)->post('/admin/employees/clear-all');

        $response->assertRedirect('/admin/employees');
        $this->assertDatabaseCount('employees', 0);
    }

    public function test_preview_rows_parsing(): void
    {
        $rows = collect([
            collect(['รหัสที่เครื่อง', 'รหัสพนักงาน', 'ชื่อ-นามสกุล', 'แผนก']),
            collect(['563005', '', 'ปริญวัฒน์  ปิยะอารยาภัสร์', 'ฝ่ายขนส่ง']),
        ]);

        $preview = EmployeesImport::parsePreviewRows($rows);

        $this->assertCount(1, $preview);
        $this->assertEquals('563005', $preview[0]['emp_code']);
        $this->assertEquals('นาย', $preview[0]['prefix']);
        $this->assertEquals('ปริญวัฒน์', $preview[0]['first_name']);
        $this->assertEquals('ปิยะอารยาภัสร์', $preview[0]['last_name']);
        $this->assertEquals('NEW', $preview[0]['status']);
    }

    public function test_execute_import_confirmation(): void
    {
        $items = [
            [
                'emp_code' => '563005',
                'prefix' => 'นาย',
                'first_name' => 'ปริญวัฒน์',
                'last_name' => 'ปิยะอารยาภัสร์',
                'department_name' => 'ฝ่ายขนส่ง',
                'position_title' => 'พนักงานขนส่ง',
                'salary' => 18000.00,
            ]
        ];

        $result = EmployeesImport::executeImport($items);

        $this->assertEquals(1, $result['imported']);
        $this->assertDatabaseHas('employees', [
            'emp_code' => '563005',
            'first_name' => 'ปริญวัฒน์',
            'last_name' => 'ปิยะอารยาภัสร์',
        ]);
    }
}
