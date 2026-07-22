<?php

namespace Database\Seeders;

use App\Enums\OvertimeStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\MonthlyPeriodLock;
use App\Models\Notification;
use App\Models\OvertimeApproval;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestEmployee;
use App\Models\OvertimeStatusHistory;
use App\Models\OvertimeType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OvertimeSampleSeeder extends Seeder
{
    public function run(): void
    {
        $depts = Department::all();
        $otTypes = OvertimeType::all();
        $supervisor = User::where('email', 'supervisor@company.com')->first() ?? User::first();
        $manager = User::where('email', 'manager@company.com')->first() ?? User::first();
        $employees = Employee::all();

        if ($depts->isEmpty() || $otTypes->isEmpty() || $employees->isEmpty()) {
            return;
        }

        $statuses = [
            OvertimeStatus::APPROVED,
            OvertimeStatus::APPROVED,
            OvertimeStatus::PENDING_APPROVAL,
            OvertimeStatus::DRAFT,
            OvertimeStatus::REJECTED,
            OvertimeStatus::RETURNED,
        ];

        // Seed 20 sample OT requests across current month and past months for rich daily/monthly/yearly dashboard testing
        for ($i = 1; $i <= 20; $i++) {
            $dept = $depts->random();
            $otType = $otTypes->random();
            $status = $statuses[array_rand($statuses)];
            $daysAgo = rand(0, 45); // Spread across past 45 days
            $requestDate = now()->subDays($daysAgo);

            $docNo = sprintf("OT-%s-%s-%05d", $requestDate->format('Ym'), strtoupper($dept->code), $i);

            $startTime = rand(0, 1) ? '17:30' : '20:00';
            $endTime = $startTime === '17:30' ? '20:30' : '02:00';
            $breakMins = $startTime === '20:00' ? 60 : 0;
            $totalHours = $startTime === '17:30' ? 3.00 : 5.00;
            $isCross = $startTime === '20:00';

            $otReq = OvertimeRequest::create([
                'document_no' => $docNo,
                'department_id' => $dept->id,
                'created_by_user_id' => $supervisor->id,
                'manager_user_id' => in_array($status, [OvertimeStatus::APPROVED, OvertimeStatus::REJECTED]) ? $manager->id : null,
                'overtime_type_id' => $otType->id,
                'request_date' => $requestDate->format('Y-m-d'),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'break_minutes' => $breakMins,
                'total_hours' => $totalHours,
                'is_cross_midnight' => $isCross,
                'location' => 'อาคารปฏิบัติงานแผนก ' . $dept->name_th,
                'reason' => 'ปฏิบัติงานล่วงเวลาสนับสนุนโครงการพิเศษประจำงวด #' . $i,
                'work_details' => 'ตรวจสอบระบบงาน ทดสอบและสรุปผลการทำงานล่วงเวลาประจำวัน',
                'status' => $status,
                'submitted_at' => $status !== OvertimeStatus::DRAFT ? $requestDate->subHours(2) : null,
                'approved_at' => $status === OvertimeStatus::APPROVED ? $requestDate : null,
                'approval_comment' => $status === OvertimeStatus::APPROVED ? 'อนุมัติเรียบร้อย' : ($status === OvertimeStatus::REJECTED ? 'ปฏิเสธเนื่องจากเกินงบประมาณ' : null),
            ]);

            // Assign 1-3 employees per request
            $requestEmps = $employees->where('department_id', $dept->id);
            if ($requestEmps->isEmpty()) {
                $requestEmps = $employees;
            }
            $selectedEmps = $requestEmps->random(min(rand(1, 3), $requestEmps->count()));

            foreach ($selectedEmps as $emp) {
                OvertimeRequestEmployee::create([
                    'overtime_request_id' => $otReq->id,
                    'employee_id' => $emp->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'break_minutes' => $breakMins,
                    'planned_hours' => $totalHours,
                    'actual_start_time' => $status === OvertimeStatus::APPROVED ? $startTime : null,
                    'actual_end_time' => $status === OvertimeStatus::APPROVED ? $endTime : null,
                    'actual_break_minutes' => $status === OvertimeStatus::APPROVED ? $breakMins : 0,
                    'actual_hours' => $status === OvertimeStatus::APPROVED ? $totalHours : null,
                    'consent_status' => 'CONSENTED',
                    'consent_signed_at' => $requestDate,
                ]);
            }

            OvertimeStatusHistory::create([
                'overtime_request_id' => $otReq->id,
                'from_status' => null,
                'to_status' => $status->value,
                'changed_by_user_id' => $supervisor->id,
                'remarks' => 'บันทึกสถานะคำขอ OT #' . $i,
            ]);

            if ($status === OvertimeStatus::APPROVED || $status === OvertimeStatus::REJECTED) {
                OvertimeApproval::create([
                    'overtime_request_id' => $otReq->id,
                    'action_by_user_id' => $manager->id,
                    'action' => $status->value,
                    'comment' => $status === OvertimeStatus::APPROVED ? 'อนุมัติเรียบร้อย' : 'ไม่อนุมัติ',
                    'ip_address' => '127.0.0.1',
                ]);
            }
        }

        // Monthly Period Lock Sample
        MonthlyPeriodLock::create([
            'year' => (int)now()->format('Y'),
            'month' => (int)now()->subMonth()->format('n'),
            'department_id' => null,
            'status' => 'LOCKED',
            'locked_at' => now()->subDays(15),
            'locked_by_user_id' => $supervisor->id,
            'remarks' => 'ปิดรอบประมวลผล OT ประจำเดือนที่แล้วเรียบร้อยแล้ว',
        ]);
    }
}
