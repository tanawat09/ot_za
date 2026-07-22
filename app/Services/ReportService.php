<?php

namespace App\Services;

use App\Enums\OvertimeStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestEmployee;
use App\Models\OvertimeType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    /**
     * Get Report titles mapping.
     */
    public static function getReportTypes(): array
    {
        return [
            'daily' => '1. รายงาน OT รายวัน',
            'monthly' => '2. รายงาน OT รายเดือน',
            'yearly' => '3. รายงาน OT รายปี',
            'department' => '4. รายงานแยกตามแผนก',
            'employee' => '5. รายงานแยกตามพนักงาน',
            'type' => '6. รายงานแยกตามประเภท OT',
            'status' => '7. รายงานแยกตามสถานะ',
            'creator' => '8. รายงานตามผู้สร้างคำขอ',
            'approver' => '9. รายงานตามผู้อนุมัติ',
            'rejected' => '10. รายงานคำขอที่ถูกปฏิเสธ',
            'returned' => '11. รายงานคำขอที่ถูกส่งกลับ',
            'top_employees' => '12. รายงานพนักงานที่มี OT สูงสุด',
            'over_limit' => '13. รายงาน OT ที่เกินจำนวนชั่วโมงตามเงื่อนไข',
            'consent_status' => '14. รายงานสถานะเอกสารยินยอม',
            'department_comparison' => '15. รายงานเปรียบเทียบ OT ระหว่างแผนก',
            'planned_vs_actual' => '16. รายงานเปรียบเทียบชั่วโมงขอกับชั่วโมงจริง',
        ];
    }

    /**
     * Generate report data based on report type and request filters.
     */
    public static function generate(string $type, array $filters = [], ?User $user = null)
    {
        $query = OvertimeRequest::with(['department', 'team', 'overtimeType', 'creator', 'manager', 'employees.employee']);

        // Scope by user role if provided
        if ($user && $user->hasRole('Supervisor')) {
            $query->where('created_by_user_id', $user->id);
        } elseif ($user && $user->hasRole('Manager')) {
            $managedDeptIds = $user->managedDepartments->pluck('id')->toArray();
            $query->whereIn('department_id', $managedDeptIds);
        }

        // Apply Common Filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('request_date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('request_date', '<=', $filters['end_date']);
        }
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['overtime_type_id'])) {
            $query->where('overtime_type_id', $filters['overtime_type_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return match ($type) {
            'daily' => $query->whereDate('request_date', $filters['request_date'] ?? now()->format('Y-m-d'))->orderBy('request_date', 'desc')->paginate(20),
            'monthly' => $query->whereYear('request_date', $filters['year'] ?? now()->year)->whereMonth('request_date', $filters['month'] ?? now()->month)->orderBy('request_date', 'desc')->paginate(20),
            'yearly' => $query->whereYear('request_date', $filters['year'] ?? now()->year)->orderBy('request_date', 'desc')->paginate(20),
            'department' => $query->orderBy('department_id')->paginate(20),
            'employee' => $query->whereHas('employees', function ($q) use ($filters) {
                if (!empty($filters['employee_id'])) {
                    $q->where('employee_id', $filters['employee_id']);
                }
            })->paginate(20),
            'type' => $query->orderBy('overtime_type_id')->paginate(20),
            'status' => $query->orderBy('status')->paginate(20),
            'creator' => $query->orderBy('created_by_user_id')->paginate(20),
            'approver' => $query->whereNotNull('manager_user_id')->orderBy('manager_user_id')->paginate(20),
            'rejected' => $query->where('status', OvertimeStatus::REJECTED)->paginate(20),
            'returned' => $query->where('status', OvertimeStatus::RETURNED)->paginate(20),
            'top_employees' => OvertimeRequestEmployee::select('employee_id')
                ->selectRaw('SUM(planned_hours) as total_hours, COUNT(DISTINCT overtime_request_id) as total_requests')
                ->groupBy('employee_id')
                ->orderBy('total_hours', 'desc')
                ->with(['employee.department', 'employee.position'])
                ->paginate(20),
            'over_limit' => $query->where('total_hours', '>', 8.00)->paginate(20),
            'consent_status' => $query->whereIn('status', [OvertimeStatus::WAITING_CONSENT, OvertimeStatus::READY_TO_SUBMIT])->paginate(20),
            'department_comparison' => Department::withSum(['employees' => function ($q) {
                // Sum
            }], 'id')->paginate(20),
            'planned_vs_actual' => $query->where('status', OvertimeStatus::APPROVED)->paginate(20),
            default => $query->orderBy('created_at', 'desc')->paginate(20),
        };
    }
}
