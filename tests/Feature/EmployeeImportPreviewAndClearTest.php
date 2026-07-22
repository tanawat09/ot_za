<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Imports\EmployeesImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_extract_prefix_and_name(): void
    {
        [$prefix1, $name1] = EmployeesImport::extractPrefixAndName('น.ส. ธิดาวรรณ');
        $this->assertEquals('นางสาว', $prefix1);
        $this->assertEquals('ธิดาวรรณ', $name1);

        [$prefix2, $name2] = EmployeesImport::extractPrefixAndName('นาย วรภัทร');
        $this->assertEquals('นาย', $prefix2);
        $this->assertEquals('วรภัทร', $name2);
    }

    public function test_preview_rows_parsing(): void
    {
        $rows = collect([
            collect(['รหัสพนักงาน', 'ชื่อ', 'นามสกุล', 'แผนก']),
            collect(['00010', 'น.ส. ธิดาวรรณ', 'วงค์', '']),
            collect(['00013', 'น.ส. ฐิตวรรณภรณ์', 'วงษ์มณี', '']),
            collect(['00015', 'เทพพร', 'ธรรมวัติ -', '']),
        ]);

        $preview = EmployeesImport::parsePreviewRows($rows);

        $this->assertCount(3, $preview);

        // Row 10 check
        $this->assertEquals('00010', $preview[0]['emp_code']);
        $this->assertEquals('นางสาว', $preview[0]['prefix']);
        $this->assertEquals('ธิดาวรรณ', $preview[0]['first_name']);
        $this->assertEquals('วงค์', $preview[0]['last_name']);
        $this->assertEquals('แผนกทั่วไป', $preview[0]['department_name']);

        // Row 13 check
        $this->assertEquals('00013', $preview[1]['emp_code']);
        $this->assertEquals('นางสาว', $preview[1]['prefix']);
        $this->assertEquals('ฐิตวรรณภรณ์', $preview[1]['first_name']);
        $this->assertEquals('วงษ์มณี', $preview[1]['last_name']);
        $this->assertEquals('แผนกทั่วไป', $preview[1]['department_name']);

        // Row 15 check (trailing - stripped)
        $this->assertEquals('00015', $preview[2]['emp_code']);
        $this->assertEquals('นาย', $preview[2]['prefix']);
        $this->assertEquals('เทพพร', $preview[2]['first_name']);
        $this->assertEquals('ธรรมวัติ', $preview[2]['last_name']);
    }

    public function test_execute_import_confirmation(): void
    {
        $items = [
            [
                'emp_code' => '00010',
                'prefix' => 'นางสาว',
                'first_name' => 'ธิดาวรรณ',
                'last_name' => 'วงค์',
                'department_name' => 'แผนกทั่วไป',
                'position_title' => '-',
                'salary' => 15000.00,
            ]
        ];

        $result = EmployeesImport::executeImport($items);

        $this->assertEquals(1, $result['imported']);
        $this->assertDatabaseHas('employees', [
            'emp_code' => '00010',
            'prefix' => 'นางสาว',
            'first_name' => 'ธิดาวรรณ',
            'last_name' => 'วงค์',
        ]);
    }
}
