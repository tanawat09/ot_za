<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\OvertimeRequestEmployee;

class PayrollService
{
    /**
     * Calculate monthly OT pay and total compensation per employee for payroll processing.
     */
    public static function calculateMonthlyPayroll(int $year, int $month, ?int $departmentId = null): array
    {
        $employeeQuery = Employee::with(['department', 'position'])->where('status', 'Active');
        if ($departmentId) {
            $employeeQuery->where('department_id', $departmentId);
        }

        $employees = $employeeQuery->get();
        $summary = [];

        $totalBaseSalary = 0;
        $totalOtPay15 = 0;
        $totalOtPay30 = 0;
        $totalOtPay10 = 0;
        $grandTotalOtPay = 0;
        $grandNetPay = 0;

        foreach ($employees as $emp) {
            // Get all approved OT request employee entries for this month & year
            $otEntries = OvertimeRequestEmployee::where('employee_id', $emp->id)
                ->whereHas('overtimeRequest', function ($q) use ($year, $month) {
                    $q->whereYear('request_date', $year)
                      ->whereMonth('request_date', $month)
                      ->where('status', 'APPROVED');
                })
                ->with('overtimeRequest.overtimeType')
                ->get();

            $hours15 = 0;
            $hours30 = 0;
            $hours10 = 0;

            foreach ($otEntries as $entry) {
                $multiplier = (float)($entry->overtimeRequest?->overtimeType?->multiplier ?? 1.5);
                $workedHours = (float)($entry->actual_hours ?? $entry->planned_hours ?? 0);

                if ($multiplier == 1.5) {
                    $hours15 += $workedHours;
                } elseif ($multiplier == 3.0) {
                    $hours30 += $workedHours;
                } else {
                    $hours10 += $workedHours;
                }
            }

            $hourlyRate = $emp->hourly_rate;
            $otPay15 = round($hours15 * $hourlyRate * 1.5, 2);
            $otPay30 = round($hours30 * $hourlyRate * 3.0, 2);
            $otPay10 = round($hours10 * $hourlyRate * 1.0, 2);
            $totalOtPay = round($otPay15 + $otPay30 + $otPay10, 2);
            $baseSalary = (float)$emp->salary;
            $netPay = round($baseSalary + $totalOtPay, 2);

            $summary[] = [
                'employee_id' => $emp->id,
                'emp_code' => $emp->emp_code,
                'full_name' => $emp->full_name,
                'department_name' => $emp->department?->name_th ?? '-',
                'position_title' => $emp->position?->title_th ?? '-',
                'base_salary' => $baseSalary,
                'hourly_rate' => $hourlyRate,
                'hours_1_5' => $hours15,
                'hours_3_0' => $hours30,
                'hours_1_0' => $hours10,
                'total_hours' => $hours15 + $hours30 + $hours10,
                'ot_pay_1_5' => $otPay15,
                'ot_pay_3_0' => $otPay30,
                'ot_pay_1_0' => $otPay10,
                'total_ot_pay' => $totalOtPay,
                'net_pay' => $netPay,
            ];

            $totalBaseSalary += $baseSalary;
            $totalOtPay15 += $otPay15;
            $totalOtPay30 += $otPay30;
            $totalOtPay10 += $otPay10;
            $grandTotalOtPay += $totalOtPay;
            $grandNetPay += $netPay;
        }

        return [
            'year' => $year,
            'month' => $month,
            'department_id' => $departmentId,
            'employees' => $summary,
            'totals' => [
                'total_base_salary' => round($totalBaseSalary, 2),
                'total_ot_pay_1_5' => round($totalOtPay15, 2),
                'total_ot_pay_3_0' => round($totalOtPay30, 2),
                'total_ot_pay_1_0' => round($totalOtPay10, 2),
                'grand_total_ot_pay' => round($grandTotalOtPay, 2),
                'grand_net_pay' => round($grandNetPay, 2),
            ],
        ];
    }
}
