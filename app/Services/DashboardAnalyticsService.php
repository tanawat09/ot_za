<?php

namespace App\Services;

use App\Enums\OvertimeStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestEmployee;
use App\Models\OvertimeType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    public static function getMetrics(User $user, array $filters = []): array
    {
        $data = self::getDashboardData($user, array_merge(['period_type' => 'daily'], $filters));
        return [
            'requestsToday' => $data['summary']['totalRequests'],
            'totalApprovedHours' => $data['summary']['totalApprovedHours'],
        ];
    }

    /**
     * Get complete dashboard metrics and chart analytics scoped by period (daily, monthly, yearly, custom).
     */
    public static function getDashboardData(User $user, array $filters = []): array
    {
        $periodType = $filters['period_type'] ?? 'monthly';
        $selectedDate = !empty($filters['date']) ? Carbon::parse($filters['date']) : now();
        $selectedMonth = !empty($filters['month']) ? (int)$filters['month'] : (int)now()->format('n');
        $selectedYear = !empty($filters['year']) ? (int)$filters['year'] : (int)now()->format('Y');
        $startDate = !empty($filters['start_date']) ? Carbon::parse($filters['start_date']) : null;
        $endDate = !empty($filters['end_date']) ? Carbon::parse($filters['end_date']) : null;

        // Base Query
        $baseQuery = OvertimeRequest::query();

        // Scope by Role
        if ($user->hasRole('Supervisor')) {
            $baseQuery->where('created_by_user_id', $user->id);
        } elseif ($user->hasRole('Manager')) {
            $managedDeptIds = $user->managedDepartments->pluck('id')->toArray();
            $baseQuery->whereIn('department_id', $managedDeptIds);
        }

        // Apply Extra Filters
        if (!empty($filters['department_id'])) {
            $baseQuery->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['overtime_type_id'])) {
            $baseQuery->where('overtime_type_id', $filters['overtime_type_id']);
        }
        if (!empty($filters['status'])) {
            $baseQuery->where('status', $filters['status']);
        }

        // Apply Period Range Filter to Period Query
        $periodQuery = clone $baseQuery;
        if ($periodType === 'daily') {
            $periodQuery->whereDate('request_date', $selectedDate->format('Y-m-d'));
        } elseif ($periodType === 'monthly') {
            $periodQuery->whereYear('request_date', $selectedYear)->whereMonth('request_date', $selectedMonth);
        } elseif ($periodType === 'yearly') {
            $periodQuery->whereYear('request_date', $selectedYear);
        } elseif ($periodType === 'custom' && $startDate && $endDate) {
            $periodQuery->whereBetween('request_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        }

        // 1. Summary Cards Metrics
        $totalRequests = (clone $periodQuery)->count();
        $approvedRequests = (clone $periodQuery)->where('status', OvertimeStatus::APPROVED)->count();
        $pendingRequests = (clone $periodQuery)->where('status', OvertimeStatus::PENDING_APPROVAL)->count();
        $rejectedRequests = (clone $periodQuery)->where('status', OvertimeStatus::REJECTED)->count();
        $returnedRequests = (clone $periodQuery)->where('status', OvertimeStatus::RETURNED)->count();
        $draftRequests = (clone $periodQuery)->where('status', OvertimeStatus::DRAFT)->count();

        $totalApprovedHours = round((clone $periodQuery)->where('status', OvertimeStatus::APPROVED)->sum('total_hours'), 2);
        $totalPlannedHours = round((clone $periodQuery)->sum('total_hours'), 2);

        // Distinct employees with OT in this period
        $requestIds = (clone $periodQuery)->pluck('id');
        $totalEmployeesCount = OvertimeRequestEmployee::whereIn('overtime_request_id', $requestIds)->distinct('employee_id')->count('employee_id');

        // 2. Trend Chart Datasets
        $trendLabels = [];
        $trendApprovedHours = [];
        $trendTotalRequests = [];

        if ($periodType === 'daily') {
            // Trend for days in the week around selected date
            $startOfWeek = (clone $selectedDate)->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $d = (clone $startOfWeek)->addDays($i);
                $trendLabels[] = $d->format('d/m (D)');
                $q = (clone $baseQuery)->whereDate('request_date', $d->format('Y-m-d'));
                $trendApprovedHours[] = round((clone $q)->where('status', OvertimeStatus::APPROVED)->sum('total_hours'), 2);
                $trendTotalRequests[] = (clone $q)->count();
            }
        } elseif ($periodType === 'monthly') {
            // Trend for each day in selected month
            $daysInMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dStr = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $day);
                $trendLabels[] = (string)$day;
                $q = (clone $baseQuery)->whereDate('request_date', $dStr);
                $trendApprovedHours[] = round((clone $q)->where('status', OvertimeStatus::APPROVED)->sum('total_hours'), 2);
                $trendTotalRequests[] = (clone $q)->count();
            }
        } else {
            // Yearly breakdown for 12 months
            $thaiMonths = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            for ($m = 1; $m <= 12; $m++) {
                $trendLabels[] = $thaiMonths[$m - 1];
                $q = (clone $baseQuery)->whereYear('request_date', $selectedYear)->whereMonth('request_date', $m);
                $trendApprovedHours[] = round((clone $q)->where('status', OvertimeStatus::APPROVED)->sum('total_hours'), 2);
                $trendTotalRequests[] = (clone $q)->count();
            }
        }

        // 3. Department Comparison Dataset
        $deptStats = Department::with('teams')->get()->map(function ($dept) use ($periodQuery) {
            $q = (clone $periodQuery)->where('department_id', $dept->id);
            return [
                'id' => $dept->id,
                'name' => $dept->name_th,
                'code' => $dept->code,
                'total_requests' => (clone $q)->count(),
                'approved_requests' => (clone $q)->where('status', OvertimeStatus::APPROVED)->count(),
                'pending_requests' => (clone $q)->where('status', OvertimeStatus::PENDING_APPROVAL)->count(),
                'approved_hours' => round((clone $q)->where('status', OvertimeStatus::APPROVED)->sum('total_hours'), 2),
            ];
        });

        // 4. Overtime Types Dataset
        $otTypeStats = OvertimeType::all()->map(function ($type) use ($periodQuery) {
            $q = (clone $periodQuery)->where('overtime_type_id', $type->id);
            return [
                'name' => $type->name_th,
                'multiplier' => $type->multiplier,
                'hours' => round((clone $q)->where('status', OvertimeStatus::APPROVED)->sum('total_hours'), 2),
                'requests' => (clone $q)->count(),
            ];
        });

        // 5. Top 5 Employees with Highest OT Hours in Period
        $topEmployees = OvertimeRequestEmployee::whereIn('overtime_request_id', $requestIds)
            ->whereHas('overtimeRequest', function ($q) {
                $q->where('status', OvertimeStatus::APPROVED);
            })
            ->select('employee_id')
            ->selectRaw('SUM(planned_hours) as total_hours, COUNT(DISTINCT overtime_request_id) as total_requests')
            ->groupBy('employee_id')
            ->orderBy('total_hours', 'desc')
            ->with(['employee.department', 'employee.position'])
            ->take(5)
            ->get();

        // 6. Overdue Requests (> 3 Days Pending)
        $overdueRequests = (clone $baseQuery)->where('status', OvertimeStatus::PENDING_APPROVAL)
            ->where('submitted_at', '<', now()->subDays(3))
            ->with(['department', 'creator'])
            ->get();

        // 7. Recent Requests (Latest 10)
        $recentRequests = (clone $periodQuery)
            ->with(['department', 'overtimeType', 'creator', 'employees.employee'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return [
            'periodType' => $periodType,
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'startDate' => $startDate ? $startDate->format('Y-m-d') : null,
            'endDate' => $endDate ? $endDate->format('Y-m-d') : null,
            'summary' => [
                'totalRequests' => $totalRequests,
                'approvedRequests' => $approvedRequests,
                'pendingRequests' => $pendingRequests,
                'rejectedRequests' => $rejectedRequests,
                'returnedRequests' => $returnedRequests,
                'draftRequests' => $draftRequests,
                'totalApprovedHours' => $totalApprovedHours,
                'totalPlannedHours' => $totalPlannedHours,
                'totalEmployeesCount' => $totalEmployeesCount,
            ],
            'trendChart' => [
                'labels' => $trendLabels,
                'approvedHours' => $trendApprovedHours,
                'totalRequests' => $trendTotalRequests,
            ],
            'deptStats' => $deptStats,
            'otTypeStats' => $otTypeStats,
            'topEmployees' => $topEmployees,
            'overdueRequests' => $overdueRequests,
            'recentRequests' => $recentRequests,
        ];
    }
}
