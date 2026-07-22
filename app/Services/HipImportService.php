<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\HipAttendanceLog;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestEmployee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HipImportService
{
    /**
     * Import attendance log records from HIP Premium Time v2.0 / v6 export array or parsed file.
     */
    public static function processImport(array $records, string $batchName): array
    {
        $importedCount = 0;
        $matchedOtCount = 0;
        $errors = [];

        DB::transaction(function () use ($records, $batchName, &$importedCount, &$matchedOtCount, &$errors) {
            foreach ($records as $index => $row) {
                $empCode = trim($row['emp_code'] ?? '');
                $logDate = trim($row['log_date'] ?? '');
                $checkIn = !empty($row['check_in']) ? trim($row['check_in']) : null;
                $checkOut = !empty($row['check_out']) ? trim($row['check_out']) : null;
                $deviceId = !empty($row['device_id']) ? trim($row['device_id']) : 'HIP-DEV-01';

                if (empty($empCode) || empty($logDate)) {
                    continue;
                }

                // Format Log Date (YYYY-MM-DD)
                try {
                    $formattedDate = Carbon::parse($logDate)->format('Y-m-d');
                } catch (\Exception $e) {
                    $errors[] = "บรรทัดที่ " . ($index + 1) . ": รูปแบบวันที่ไม่ถูกต้อง ({$logDate})";
                    continue;
                }

                // Find employee by emp_code
                $employee = Employee::where('emp_code', $empCode)->first();

                // Save or update HIP Attendance Log
                $log = HipAttendanceLog::updateOrCreate(
                    [
                        'emp_code' => $empCode,
                        'log_date' => $formattedDate,
                    ],
                    [
                        'employee_id' => $employee?->id,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'device_id' => $deviceId,
                        'import_batch' => $batchName,
                    ]
                );

                $importedCount++;

                // Auto Match with Approved OT Requests
                if ($employee && $checkIn && $checkOut) {
                    $matchedCount = self::matchOtRequest($employee, $formattedDate, $checkIn, $checkOut);
                    $matchedOtCount += $matchedCount;
                }
            }
        });

        return [
            'imported_count' => $importedCount,
            'matched_ot_count' => $matchedOtCount,
            'errors' => $errors,
        ];
    }

    /**
     * Match HIP scan check-in/check-out against approved OT request for specific employee and date.
     */
    public static function matchOtRequest(Employee $employee, string $logDate, string $checkIn, string $checkOut): int
    {
        $otEmpRequests = OvertimeRequestEmployee::where('employee_id', $employee->id)
            ->whereHas('overtimeRequest', function ($q) use ($logDate) {
                $q->whereDate('request_date', $logDate)
                  ->where('status', 'APPROVED');
            })
            ->get();

        $matched = 0;

        foreach ($otEmpRequests as $empReq) {
            $otRequest = $empReq->overtimeRequest;
            
            // Calculate actual worked hours based on HIP check-in & check-out vs planned time
            $startTime = Carbon::parse("{$logDate} {$checkIn}");
            $endTime = Carbon::parse("{$logDate} {$checkOut}");
            if ($endTime->lt($startTime)) {
                $endTime->addDay(); // Handle cross-midnight scan
            }

            $diffMinutes = $endTime->diffInMinutes($startTime) - ($empReq->break_minutes ?? 0);
            $actualHours = max(0, round($diffMinutes / 60, 2));

            // Don't exceed planned hours by default unless approved
            $cappedActualHours = min($actualHours, $empReq->planned_hours);

            $empReq->update([
                'actual_start_time' => $checkIn,
                'actual_end_time' => $checkOut,
                'actual_hours' => $cappedActualHours > 0 ? $cappedActualHours : $empReq->planned_hours,
                'remarks' => "ซิงค์อัตโนมัติจากเครื่องสแกน HIP Premium Time ({$checkIn} - {$checkOut})",
            ]);

            $matched++;
        }

        return $matched;
    }
}
