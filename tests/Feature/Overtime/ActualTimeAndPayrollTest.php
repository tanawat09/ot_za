<?php

namespace Tests\Feature\Overtime;

use App\Models\Department;
use App\Models\Employee;
use App\Models\MonthlyPeriodLock;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestEmployee;
use App\Models\OvertimeType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActualTimeAndPayrollTest extends TestCase
{
    use RefreshDatabase;

    protected User $hrUser;
    protected Department $department;
    protected OvertimeType $otType;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'HR', 'guard_name' => 'web']);
        $this->department = Department::create(['code' => 'IT', 'name_th' => 'ไอที']);

        $this->hrUser = User::create([
            'emp_code' => 'HR-001',
            'name' => 'HR Manager',
            'email' => 'hr@test.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->hrUser->assignRole($role);

        $this->otType = OvertimeType::create([
            'code' => 'OT-15',
            'name_th' => 'OT วันปกติ',
            'multiplier' => 1.50,
            'max_hours_per_day' => 8.0,
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'emp_code' => 'EMP-101',
            'first_name' => 'Somchai',
            'last_name' => 'Jaidee',
            'department_id' => $this->department->id,
            'status' => 'Active',
        ]);
    }

    public function test_recording_actual_ot_time(): void
    {
        $ot = OvertimeRequest::create([
            'document_no' => 'OT-202607-IT-00001',
            'department_id' => $this->department->id,
            'created_by_user_id' => $this->hrUser->id,
            'overtime_type_id' => $this->otType->id,
            'request_date' => '2026-07-25',
            'start_time' => '17:30',
            'end_time' => '20:30',
            'break_minutes' => 0,
            'total_hours' => 3.00,
            'reason' => 'ทดสอบเวลาจริง',
            'status' => 'APPROVED',
        ]);

        $empReq = OvertimeRequestEmployee::create([
            'overtime_request_id' => $ot->id,
            'employee_id' => $this->employee->id,
            'start_time' => '17:30',
            'end_time' => '20:30',
            'break_minutes' => 0,
            'planned_hours' => 3.00,
        ]);

        $response = $this->actingAs($this->hrUser)->post("/overtime/{$ot->id}/actual-time", [
            'actual' => [
                $empReq->id => [
                    'start_time' => '17:30',
                    'end_time' => '21:30',
                    'break_minutes' => 30,
                ],
            ],
        ]);

        $response->assertRedirect("/overtime/{$ot->id}");
        $empReq->refresh();
        $this->assertEquals(3.50, $empReq->actual_hours);
    }

    public function test_monthly_period_lock_toggling(): void
    {
        $response = $this->actingAs($this->hrUser)->post('/monthly-locks/toggle', [
            'year' => 2026,
            'month' => 7,
            'department_id' => $this->department->id,
            'action' => 'LOCK',
        ]);

        $response->assertRedirect();
        $this->assertTrue(MonthlyPeriodLock::isLocked(2026, 7, $this->department->id));
    }

    public function test_payroll_export_download(): void
    {
        $response = $this->actingAs($this->hrUser)->get('/payroll/export?year=2026&month=7&format=xlsx');
        $response->assertStatus(200);
    }
}
