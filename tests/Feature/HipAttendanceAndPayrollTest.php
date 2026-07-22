<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestEmployee;
use App\Models\OvertimeType;
use App\Models\User;
use App\Services\HipImportService;
use App\Services\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HipAttendanceAndPayrollTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Employee $employee;
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

        $this->employee = Employee::create([
            'emp_code' => '563005',
            'user_id' => $this->admin->id,
            'prefix' => 'นาย',
            'first_name' => 'ปริญวัฒน์',
            'last_name' => 'ปิยะอารยาภัสร์',
            'department_id' => $this->department->id,
            'salary' => 30000.00,
            'wage_type' => 'Monthly',
            'status' => 'Active',
        ]);
    }

    public function test_import_hip_attendance_matrix_csv_format(): void
    {
        $matrixRows = [
            ['รหัสที่เครื่อง', 'รหัสพนักงาน', 'ชื่อ-นามสกุล', 'แผนก', 'Date', '1', '2', '3', '4'],
            ['563005', '', 'ปริญวัฒน์  ปิยะอารยาภัสร์', 'ฝ่ายขนส่ง', '01/07/2026', '07:54', '17:59', '', ''],
            ['563005', '', 'ปริญวัฒน์  ปิยะอารยาภัสร์', 'ฝ่ายขนส่ง', '02/07/2026', '08:00', '20:01', '', ''],
            ['563005', '', 'ปริญวัฒน์  ปิยะอารยาภัสร์', 'ฝ่ายขนส่ง', '05/07/2026', '', '', '', ''], // Should skip
        ];

        $result = HipImportService::processMatrixRows($matrixRows, 'BATCH_TEST_MATRIX');

        $this->assertEquals(2, $result['imported_count']);
        $this->assertDatabaseHas('hip_attendance_logs', [
            'emp_code' => '563005',
            'check_in' => '07:54',
            'check_out' => '17:59',
        ]);
        $this->assertDatabaseHas('hip_attendance_logs', [
            'emp_code' => '563005',
            'check_in' => '08:00',
            'check_out' => '20:01',
        ]);
    }

    public function test_payroll_ot_payment_calculation(): void
    {
        $otType = OvertimeType::create([
            'code' => 'OT1.5',
            'name_th' => 'OT 1.5 เท่า',
            'multiplier' => 1.5,
            'is_active' => true,
        ]);

        $otRequest = OvertimeRequest::create([
            'document_no' => 'OT-202607-LOG-00001',
            'department_id' => $this->department->id,
            'created_by_user_id' => $this->admin->id,
            'overtime_type_id' => $otType->id,
            'request_date' => '2026-07-21',
            'start_time' => '17:30',
            'end_time' => '20:30',
            'break_minutes' => 0,
            'total_hours' => 3.00,
            'status' => 'APPROVED',
            'reason' => 'ทำ OT ทดสอบ',
        ]);

        OvertimeRequestEmployee::create([
            'overtime_request_id' => $otRequest->id,
            'employee_id' => $this->employee->id,
            'start_time' => '17:30',
            'end_time' => '20:30',
            'break_minutes' => 0,
            'planned_hours' => 3.00,
            'actual_hours' => 3.00,
        ]);

        $payroll = PayrollService::calculateMonthlyPayroll(2026, 7, $this->department->id);

        $this->assertCount(1, $payroll['employees']);
        $empResult = $payroll['employees'][0];

        $this->assertEquals(125.00, $empResult['hourly_rate']);
        $this->assertEquals(562.50, $empResult['ot_pay_1_5']);
        $this->assertEquals(562.50, $empResult['total_ot_pay']);
        $this->assertEquals(30562.50, $empResult['net_pay']);
    }
}
