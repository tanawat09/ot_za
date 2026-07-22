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

        $this->department = Department::create(['code' => 'IT', 'name_th' => 'ไอที']);

        $this->employee = Employee::create([
            'emp_code' => 'EMP001',
            'user_id' => $this->admin->id,
            'prefix' => 'นาย',
            'first_name' => 'สมชาย',
            'last_name' => 'สายสแกน',
            'department_id' => $this->department->id,
            'salary' => 30000.00,
            'wage_type' => 'Monthly',
            'status' => 'Active',
        ]);
    }

    public function test_import_hip_attendance_logs(): void
    {
        $records = [
            [
                'emp_code' => 'EMP001',
                'log_date' => '2026-07-21',
                'check_in' => '17:30',
                'check_out' => '20:30',
                'device_id' => 'HIP-DEV-01',
            ]
        ];

        $result = HipImportService::processImport($records, 'BATCH_TEST_01');

        $this->assertEquals(1, $result['imported_count']);
        $this->assertDatabaseHas('hip_attendance_logs', [
            'emp_code' => 'EMP001',
            'check_in' => '17:30',
            'check_out' => '20:30',
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
            'document_no' => 'OT-202607-IT-00001',
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

        // Monthly salary 30,000 -> Hourly rate = (30000 / 30) / 8 = 125 THB/hr
        // OT 1.5x Pay = 3.0 hrs * 125 * 1.5 = 562.50 THB
        $payroll = PayrollService::calculateMonthlyPayroll(2026, 7, $this->department->id);

        $this->assertCount(1, $payroll['employees']);
        $empResult = $payroll['employees'][0];

        $this->assertEquals(125.00, $empResult['hourly_rate']);
        $this->assertEquals(562.50, $empResult['ot_pay_1_5']);
        $this->assertEquals(562.50, $empResult['total_ot_pay']);
        $this->assertEquals(30562.50, $empResult['net_pay']);
    }
}
