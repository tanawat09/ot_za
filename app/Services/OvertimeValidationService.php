<?php

namespace App\Services;

use App\Enums\OvertimeStatus;
use App\Models\Employee;
use App\Models\OvertimeRequestEmployee;
use Carbon\Carbon;

class OvertimeValidationService
{
    /**
     * Check if any of the given employee IDs have overlapping OT hours on the specified date/time.
     *
     * @param array $employeeIds
     * @param string $requestDate YYYY-MM-DD
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeRequestId
     * @return array Array of conflicting employee names if any
     */
    public static function checkOverlapping(array $employeeIds, string $requestDate, string $startTime, string $endTime, ?int $excludeRequestId = null): array
    {
        $start = Carbon::parse("{$requestDate} {$startTime}");
        $end = Carbon::parse("{$requestDate} {$endTime}");

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $conflicts = [];

        foreach ($employeeIds as $empId) {
            $existingReqs = OvertimeRequestEmployee::where('employee_id', $empId)
                ->whereHas('overtimeRequest', function ($q) use ($requestDate, $excludeRequestId) {
                    $q->whereDate('request_date', $requestDate)
                      ->whereNotIn('status', [OvertimeStatus::CANCELLED->value, OvertimeStatus::REJECTED->value]);
                    if ($excludeRequestId) {
                        $q->where('id', '!=', $excludeRequestId);
                    }
                })
                ->get();

            foreach ($existingReqs as $existing) {
                $req = $existing->overtimeRequest;
                $eStart = Carbon::parse("{$req->request_date->format('Y-m-d')} {$req->start_time}");
                $eEnd = Carbon::parse("{$req->request_date->format('Y-m-d')} {$req->end_time}");
                if ($eEnd->lessThanOrEqualTo($eStart)) {
                    $eEnd->addDay();
                }

                // Check overlap: (StartA < EndB) and (EndA > StartB)
                if ($start->lessThan($eEnd) && $end->greaterThan($eStart)) {
                    $emp = Employee::find($empId);
                    $conflicts[] = $emp ? "{$emp->full_name} ({$emp->emp_code}) มี OT ทับซ้อนกับเอกสาร {$req->document_no}" : "พนักงาน ID {$empId} มี OT ทับซ้อน";
                    break;
                }
            }
        }

        return $conflicts;
    }
}
