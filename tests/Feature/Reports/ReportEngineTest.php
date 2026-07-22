<?php

namespace Tests\Feature\Reports;

use App\Models\Department;
use App\Models\OvertimeRequest;
use App\Models\OvertimeType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Department $department;
    protected OvertimeType $otType;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $this->department = Department::create(['code' => 'IT', 'name_th' => 'ไอที']);

        $this->admin = User::create([
            'emp_code' => 'ADM-001',
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->admin->assignRole($role);

        $this->otType = OvertimeType::create([
            'code' => 'OT-15',
            'name_th' => 'OT วันปกติ',
            'multiplier' => 1.50,
            'max_hours_per_day' => 8.0,
            'is_active' => true,
        ]);

        OvertimeRequest::create([
            'document_no' => 'OT-202607-IT-00001',
            'department_id' => $this->department->id,
            'created_by_user_id' => $this->admin->id,
            'overtime_type_id' => $this->otType->id,
            'request_date' => now()->format('Y-m-d'),
            'start_time' => '17:30',
            'end_time' => '20:30',
            'break_minutes' => 0,
            'total_hours' => 3.00,
            'reason' => 'ทดสอบระบบ',
            'status' => 'APPROVED',
        ]);
    }

    public function test_user_can_access_reports_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('ศูนย์รายงานและสถิติ');
    }

    public function test_user_can_view_specific_report(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/daily');
        $response->assertStatus(200);
        $response->assertSee('รายงาน OT รายวัน');
    }

    public function test_user_can_export_report_to_excel(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/daily/export-excel');
        $response->assertStatus(200);
    }

    public function test_user_can_export_report_to_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/daily/export-pdf');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
