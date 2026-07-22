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

    public function test_is_real_department_validation(): void
    {
        $this->assertFalse(EmployeesImport::isRealDepartment('วงค์'));
        $this->assertFalse(EmployeesImport::isRealDepartment('สีหะวงษ์'));
        $this->assertTrue(EmployeesImport::isRealDepartment('ฝ่ายขนส่ง'));
        $this->assertTrue(EmployeesImport::isRealDepartment('LOG'));
    }

    public function test_extract_prefix_and_name(): void
    {
        [$prefix1, $name1] = EmployeesImport::extractPrefixAndName('น.ส. ธิดาวรรณ');
        $this->assertEquals('นางสาว', $prefix1);
        $this->assertEquals('ธิดาวรรณ', $name1);

        [$prefix2, $name2] = EmployeesImport::extractPrefixAndName('นาย วรภัทร');
        $this->assertEquals('นาย', $prefix2);
        $this->assertEquals('วรภัทร', $name2);

        [$prefix3, $name3] = EmployeesImport::extractPrefixAndName('MRS. PAYKHAM');
        $this->assertEquals('นาง', $prefix3);
        $this->assertEquals('PAYKHAM', $name3);

        [$prefix4, $name4] = EmployeesImport::extractPrefixAndName('MR. CHEK');
        $this->assertEquals('นาย', $prefix4);
        $this->assertEquals('CHEK', $name4);
    }

    public function test_preview_rows_parsing_thai_and_english(): void
    {
        $rows = collect([
            collect(['ลำดับ', 'รหัสพนักงาน', 'ชื่อ-นามสกุล', 'แผนก']),
            collect(['2', '00001', 'วรภัทร พุฒพันธ์', '']),
            collect(['3', '00002', 'คำสอน ใยพันธ์', '']),
            collect(['57', '00062', 'MRS. PAYKHAM', 'KEOMALAYTHONG']),
            collect(['62', '00074', 'ว่าที่ ร.ต.หญิง ปริศนา', 'สำนักงาน']),
        ]);

        $preview = EmployeesImport::parsePreviewRows($rows);

        $this->assertCount(4, $preview);

        // Thai Row 2 check
        $this->assertEquals('00001', $preview[0]['emp_code']);
        $this->assertEquals('นาย', $preview[0]['prefix']);
        $this->assertEquals('วรภัทร', $preview[0]['first_name']);
        $this->assertEquals('พุฒพันธ์', $preview[0]['last_name']);

        // Thai Row 3 check
        $this->assertEquals('00002', $preview[1]['emp_code']);
        $this->assertEquals('คำสอน', $preview[1]['first_name']);
        $this->assertEquals('ใยพันธ์', $preview[1]['last_name']);

        // English Row 57 check (no more นาย MRS.)
        $this->assertEquals('00062', $preview[2]['emp_code']);
        $this->assertEquals('นาง', $preview[2]['prefix']);
        $this->assertEquals('PAYKHAM', $preview[2]['first_name']);
        $this->assertEquals('นาง PAYKHAM KEOMALAYTHONG', $preview[2]['full_name']);

        // Row 62 check (ว่าที่ ร.ต.หญิง)
        $this->assertEquals('00074', $preview[3]['emp_code']);
        $this->assertEquals('ว่าที่ ร.ต.หญิง', $preview[3]['prefix']);
        $this->assertEquals('ปริศนา', $preview[3]['first_name']);
    }

    public function test_execute_import_confirmation(): void
    {
        $items = [
            [
                'emp_code' => '00001',
                'prefix' => 'นาย',
                'first_name' => 'วรภัทร',
                'last_name' => 'พุฒพันธ์',
                'department_name' => 'แผนกทั่วไป',
                'position_title' => '-',
                'salary' => 15000.00,
            ]
        ];

        $result = EmployeesImport::executeImport($items);

        $this->assertEquals(1, $result['imported']);
        $this->assertDatabaseHas('employees', [
            'emp_code' => '00001',
            'first_name' => 'วรภัทร',
            'last_name' => 'พุฒพันธ์',
        ]);
    }
}
