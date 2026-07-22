<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\HipAttendanceLog;
use App\Models\OvertimeRequestEmployee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HipImportService
{
    /**
     * Process matrix rows from HIP Premium Time CSV / Excel export (e.g. test.csv).
     */
    public static function processMatrixRows(array $rows, string $batchName): array
    {
        $importedCount = 0;
        $matchedOtCount = 0;
        $errors = [];

        DB::transaction(function () use ($rows, $batchName, &$importedCount, &$matchedOtCount, &$errors) {
            foreach ($rows as $index => $cols) {
                if (count($cols) < 5) continue;

                // Skip header row
                $firstCol = trim((string)$cols[0]);
                if ($firstCol === 'รหัสที่เครื่อง' || str_contains(strtolower($firstCol), 'device') || str_contains($firstCol, 'Date')) {
                    continue;
                }

                $deviceEmpCode = trim((string)$cols[0]);
                $empCode = !empty($cols[1]) ? trim((string)$cols[1]) : $deviceEmpCode;
                $fullName = !empty($cols[2]) ? trim((string)$cols[2]) : '';
                $deptName = !empty($cols[3]) ? trim((string)$cols[3]) : '';
                $dateStr = !empty($cols[4]) ? trim((string)$cols[4]) : '';

                if (empty($dateStr) || empty($empCode)) {
                    continue;
                }

                // Extract all scan times from col 5, 6, 7, 8, etc.
                $scanTimes = [];
                for ($c = 5; $c < count($cols); $c++) {
                    $t = trim((string)$cols[$c]);
                    if (!empty($t) && preg_match('/^\d{1,2}:\d{2}/', $t)) {
                        $scanTimes[] = substr($t, 0, 5);
                    }
                }

                if (empty($scanTimes)) {
                    continue; // Skip days with no scans
                }

                $checkIn = null;
                $checkOut = null;

                if (count($scanTimes) === 1) {
                    $t = $scanTimes[0];
                    $hour = (int)substr($t, 0, 2);
                    if ($hour >= 12) {
                        $checkOut = $t;
                    } else {
                        $checkIn = $t;
                    }
                } else {
                    $checkIn = $scanTimes[0];
                    $checkOut = end($scanTimes);
                }

                // Parse Date (DD/MM/YYYY or YYYY-MM-DD)
                $formattedDate = null;
                try {
                    if (str_contains($dateStr, '/')) {
                        $parts = explode('/', $dateStr);
                        if (count($parts) === 3) {
                            $day = sprintf('%02d', (int)$parts[0]);
                            $month = sprintf('%02d', (int)$parts[1]);
                            $year = (int)$parts[2];
                            if ($year > 2500) $year -= 543; // Convert BE to AD if needed
                            $formattedDate = "{$year}-{$month}-{$day}";
                        }
                    } else {
                        $formattedDate = Carbon::parse($dateStr)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $errors[] = "บรรทัดที่ " . ($index + 1) . ": ไม่สามารถแปลงวันที่ ({$dateStr})";
                    continue;
                }

                if (!$formattedDate) continue;

                // Match employee by emp_code OR by Full Name
                $employee = Employee::where('emp_code', $empCode)
                    ->orWhere('emp_code', $deviceEmpCode)
                    ->first();

                if (!$employee && !empty($fullName)) {
                    $nameParts = preg_split('/\s+/', $fullName);
                    if (count($nameParts) >= 2) {
                        $firstName = $nameParts[0];
                        $lastName = end($nameParts);
                        $employee = Employee::where('first_name', 'like', "%{$firstName}%")
                            ->where('last_name', 'like', "%{$lastName}%")
                            ->first();
                    }
                }

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
                        'device_id' => 'HIP-DEV-' . ($deviceEmpCode ?: '01'),
                        'import_batch' => $batchName,
                        'remarks' => "นำเข้าจาก HIP Premium Time (" . implode(', ', $scanTimes) . ")",
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
     * Parse Access MDB Database File using mdb-export or binary extraction.
     */
    public static function processMdbFile(string $filePath, string $batchName): array
    {
        $records = [];

        // Check if mdb-export tool is available
        $cmdCheckin = "mdb-export " . escapeshellarg($filePath) . " CHECKINOUT 2>/dev/null";
        $cmdUsers = "mdb-export " . escapeshellarg($filePath) . " USERINFO 2>/dev/null";

        $outputCheckin = shell_exec($cmdCheckin);
        $outputUsers = shell_exec($cmdUsers);

        if (!empty($outputCheckin) && !empty($outputUsers)) {
            // Parse USERINFO
            $userMap = [];
            $userLines = explode("\n", $outputUsers);
            foreach ($userLines as $idx => $line) {
                if ($idx === 0 || empty(trim($line))) continue;
                $cols = str_getcsv($line);
                if (count($cols) >= 3) {
                    $userId = trim($cols[0]);
                    $badgenumber = !empty($cols[1]) ? trim($cols[1]) : $userId;
                    $name = !empty($cols[2]) ? trim($cols[2]) : '';
                    $userMap[$userId] = ['emp_code' => $badgenumber, 'name' => $name];
                }
            }

            // Parse CHECKINOUT
            $logGroup = [];
            $checkinLines = explode("\n", $outputCheckin);
            foreach ($checkinLines as $idx => $line) {
                if ($idx === 0 || empty(trim($line))) continue;
                $cols = str_getcsv($line);
                if (count($cols) >= 2) {
                    $userId = trim($cols[0]);
                    $checkTime = trim($cols[1]);

                    if (isset($userMap[$userId]) && !empty($checkTime)) {
                        try {
                            $dt = Carbon::parse($checkTime);
                            $dateStr = $dt->format('Y-m-d');
                            $timeStr = $dt->format('H:i');
                            $empCode = $userMap[$userId]['emp_code'];

                            $logGroup[$empCode][$dateStr][] = $timeStr;
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }
            }

            // Build Matrix Rows
            foreach ($logGroup as $empCode => $dates) {
                foreach ($dates as $dateStr => $times) {
                    sort($times);
                    $records[] = [
                        $empCode,
                        $empCode,
                        '',
                        '',
                        $dateStr,
                        $times[0],
                        end($times),
                    ];
                }
            }

            return self::processMatrixRows($records, $batchName);
        }

        return [
            'imported_count' => 0,
            'matched_ot_count' => 0,
            'errors' => ['ไม่สามารถอ่านไฟล์ MS Access .mdb ได้ กรุณาแปลงเป็น CSV/Excel หรือตรวจสอบแพ็กเกจ mdbtools'],
        ];
    }

    /**
     * Import attendance log records from HIP Premium Time v2.0 / v6 export array or parsed file.
     */
    public static function processImport(array $records, string $batchName): array
    {
        return self::processMatrixRows($records, $batchName);
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
            
            $startTime = Carbon::parse("{$logDate} {$checkIn}");
            $endTime = Carbon::parse("{$logDate} {$checkOut}");
            if ($endTime->lt($startTime)) {
                $endTime->addDay();
            }

            $diffMinutes = $endTime->diffInMinutes($startTime) - ($empReq->break_minutes ?? 0);
            $actualHours = max(0, round($diffMinutes / 60, 2));

            $cappedActualHours = min($actualHours, $empReq->planned_hours);

            $empReq->update([
                'actual_start_time' => $checkIn,
                'actual_end_time' => $checkOut,
                'actual_hours' => $cappedActualHours > 0 ? $cappedActualHours : $empReq->planned_hours,
                'remarks' => "ซิงค์อัตโนมัติจากสแกน HIP Premium Time ({$checkIn} - {$checkOut})",
            ]);

            $matched++;
        }

        return $matched;
    }
}
