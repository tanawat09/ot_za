<?php

namespace Tests\Feature\Overtime;

use App\Enums\OvertimeStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\OvertimeType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OvertimeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $supervisor;
    protected User $manager;
    protected Department $department;
    protected OvertimeType $otType;
    protected Employee $employee1;
    protected Employee $employee2;

    protected function setUp(): void
    {
        parent::setUp();

        $supervisorRole = Role::create(['name' => 'Supervisor', 'guard_name' => 'web']);
        $managerRole = Role::create(['name' => 'Manager', 'guard_name' => 'web']);

        $this->department = Department::create(['code' => 'IT', 'name_th' => 'ไอที']);

        $this->supervisor = User::create([
            'emp_code' => 'SPV-001',
            'name' => 'IT Supervisor',
            'email' => 'spv@test.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->supervisor->assignRole($supervisorRole);

        $this->manager = User::create([
            'emp_code' => 'MGR-001',
            'name' => 'IT Manager',
            'email' => 'mgr@test.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->manager->assignRole($managerRole);
        $this->department->managers()->attach($this->manager->id);

        $this->otType = OvertimeType::create([
            'code' => 'OT-15',
            'name_th' => 'OT วันปกติ',
            'multiplier' => 1.50,
            'max_hours_per_day' => 8.0,
            'is_active' => true,
        ]);

        $this->employee1 = Employee::create([
            'emp_code' => 'EMP-101',
            'first_name' => 'Somchai',
            'last_name' => 'Jaidee',
            'department_id' => $this->department->id,
            'status' => 'Active',
        ]);

        $this->employee2 = Employee::create([
            'emp_code' => 'EMP-102',
            'first_name' => 'Somsri',
            'last_name' => 'Rukthaimai',
            'department_id' => $this->department->id,
            'status' => 'Active',
        ]);
    }

    public function test_supervisor_can_create_ot_request_with_multiple_employees(): void
    {
        $response = $this->actingAs($this->supervisor)->post('/overtime', [
            'department_id' => $this->department->id,
            'overtime_type_id' => $this->otType->id,
            'request_date' => '2026-07-25',
            'start_time' => '17:30',
            'end_time' => '20:30',
            'break_minutes' => 0,
            'reason' => 'ระบบล่มเร่งด่วน',
            'employees' => [$this->employee1->id, $this->employee2->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('overtime_requests', [
            'department_id' => $this->department->id,
            'reason' => 'ระบบล่มเร่งด่วน',
            'total_hours' => 3.00,
        ]);

        $req = OvertimeRequest::first();
        $this->assertCount(2, $req->employees);
        $this->assertStringStartsWith('OT-202607-IT-', $req->document_no);
    }

    public function test_manager_can_approve_pending_request(): void
    {
        $this->actingAs($this->supervisor)->post('/overtime', [
            'department_id' => $this->department->id,
            'overtime_type_id' => $this->otType->id,
            'request_date' => '2026-07-26',
            'start_time' => '17:30',
            'end_time' => '20:30',
            'break_minutes' => 0,
            'reason' => 'งานเร่งด่วน',
            'employees' => [$this->employee1->id],
        ]);

        $ot = OvertimeRequest::first();
        $this->actingAs($this->supervisor)->post("/overtime/{$ot->id}/submit");

        $ot->refresh();
        $this->assertEquals(OvertimeStatus::PENDING_APPROVAL, $ot->status);

        // Manager Approves
        $response = $this->actingAs($this->manager)->post("/approvals/{$ot->id}/approve", [
            'comment' => 'อนุมัติเรียบร้อย',
        ]);

        $response->assertRedirect('/approvals');
        $ot->refresh();
        $this->assertEquals(OvertimeStatus::APPROVED, $ot->status);
        $this->assertEquals($this->manager->id, $ot->manager_user_id);
    }

    public function test_manager_can_reject_pending_request_with_reason(): void
    {
        $this->actingAs($this->supervisor)->post('/overtime', [
            'department_id' => $this->department->id,
            'overtime_type_id' => $this->otType->id,
            'request_date' => '2026-07-27',
            'start_time' => '17:30',
            'end_time' => '20:30',
            'break_minutes' => 0,
            'reason' => 'งานทดสอบ',
            'employees' => [$this->employee1->id],
        ]);

        $ot = OvertimeRequest::first();
        $this->actingAs($this->supervisor)->post("/overtime/{$ot->id}/submit");

        // Manager Rejects
        $response = $this->actingAs($this->manager)->post("/approvals/{$ot->id}/reject", [
            'reason' => 'ยังไม่อนุมัติในสัปดาห์นี้',
        ]);

        $response->assertRedirect('/approvals');
        $ot->refresh();
        $this->assertEquals(OvertimeStatus::REJECTED, $ot->status);
    }

    public function test_pdf_consent_download(): void
    {
        $this->actingAs($this->supervisor)->post('/overtime', [
            'department_id' => $this->department->id,
            'overtime_type_id' => $this->otType->id,
            'request_date' => '2026-07-28',
            'start_time' => '17:30',
            'end_time' => '20:30',
            'break_minutes' => 0,
            'reason' => 'ทดสอบพิมพ์ PDF',
            'employees' => [$this->employee1->id],
        ]);

        $ot = OvertimeRequest::first();
        $response = $this->actingAs($this->supervisor)->get("/overtime/{$ot->id}/pdf-consent");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
