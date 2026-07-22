<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class EmployeesImport implements ToCollection
{
    public array $previewRows = [];
    public int $importedCount = 0;
    public int $updatedCount = 0;

    public function collection(Collection $rows)
    {
        $this->previewRows = self::parsePreviewRows($rows);
    }

    /**
     * Helper to extract prefix and clean name from raw name string.
     */
    public static function extractPrefixAndName(string $rawName): array
    {
        $rawName = trim($rawName);
        $prefix = 'นาย';

        $prefixMap = [
            'นางสาว' => 'นางสาว',
            'น.ส.'   => 'นางสาว',
            'น.ส'    => 'นางสาว',
            'นส.'    => 'นางสาว',
            'นาง'    => 'นาง',
            'นาย'    => 'นาย',
            'Mr.'    => 'นาย',
            'Mr'     => 'นาย',
            'Mrs.'   => 'นาง',
            'Mrs'    => 'นาง',
            'Ms.'    => 'นางสาว',
            'Ms'     => 'นางสาว',
            'ด.ช.'   => 'เด็กชาย',
            'ด.ญ.'   => 'เด็กหญิง',
        ];

        foreach ($prefixMap as $pKey => $pVal) {
            if (str_starts_with($rawName, $pKey)) {
                $prefix = $pVal;
                $rawName = trim(mb_substr($rawName, mb_strlen($pKey)));
                break;
            }
        }

        return [$prefix, $rawName];
    }

    /**
     * Parse rows for preview and validation check without mutating database.
     */
    public static function parsePreviewRows(Collection $rows): array
    {
        $previewData = [];
        $seenEmpCodes = [];

        foreach ($rows as $index => $row) {
            $cols = array_values($row->toArray());
            if (count($cols) < 2) continue;

            $c0 = trim((string)($cols[0] ?? ''));
            $c1 = trim((string)($cols[1] ?? ''));
            $c2 = trim((string)($cols[2] ?? ''));
            $c3 = trim((string)($cols[3] ?? ''));
            $c4 = trim((string)($cols[4] ?? ''));
            $c5 = trim((string)($cols[5] ?? ''));

            // Header row detection
            if (
                str_contains($c0, 'รหัส') || str_contains(strtolower($c0), 'code') || str_contains($c0, 'Device') ||
                str_contains($c1, 'รหัส') || str_contains(strtolower($c1), 'code') || str_contains($c2, 'ชื่อ') || str_contains(strtolower($c2), 'name')
            ) {
                continue;
            }

            // Detect Employee Code
            $empCode = !empty($c0) ? $c0 : (!empty($c1) ? $c1 : '');
            if (empty($empCode) || $empCode === 'รหัสพนักงาน') continue;

            // Format empCode if purely numeric (e.g. 1 -> 00001, 10 -> 00010)
            if (ctype_digit($empCode) && strlen($empCode) < 5) {
                $empCode = sprintf('%05d', (int)$empCode);
            }

            $prefix = 'นาย';
            $firstName = '';
            $lastName = '-';
            $deptStr = '';
            $posStr = '';
            $salary = 15000.00;

            if (empty($c1) && !empty($c2)) {
                // Pattern A (HIP Export Matrix): [0: EmpCode, 1: blank, 2: FullName, 3: DeptName]
                [$prefix, $cleanName] = self::extractPrefixAndName($c2);
                $nameParts = preg_split('/\s+/', trim($cleanName));
                $firstName = $nameParts[0] ?? '';
                if (count($nameParts) >= 2) {
                    $lastName = implode(' ', array_slice($nameParts, 1));
                }
                $deptStr = $c3;
            } else {
                // Pattern B (Standard Excel Multi-Col): [0: EmpCode, 1: Prefix+Name, 2: LastName, 3: Dept]
                [$prefix1, $cleanName1] = self::extractPrefixAndName($c1);

                $firstName = $cleanName1;

                if (!empty($c2)) {
                    $isDeptKeyword = str_contains($c2, 'ฝ่าย') || str_contains($c2, 'แผนก') || str_contains($c2, 'สำนัก') || str_contains($c2, 'ส่วน') || str_contains(strtolower($c2), 'dept');
                    if ($isDeptKeyword) {
                        $deptStr = $c2;
                    } else {
                        $lastName = $c2;
                        $deptStr = !empty($c3) ? $c3 : (!empty($c4) ? $c4 : '');
                        $posStr = !empty($c4) && $c4 !== $deptStr ? $c4 : (!empty($c5) ? $c5 : '');
                    }
                } else {
                    $deptStr = !empty($c3) ? $c3 : '';
                }

                $prefix = $prefix1;
            }

            // Clean trailing '-' or space in lastName
            $lastName = trim(str_replace(['-', '–'], '', $lastName));
            if (empty($lastName)) {
                $lastName = '-';
            }

            $firstName = trim($firstName);

            // Find salary if any col has numeric salary value
            foreach ($cols as $cVal) {
                if (is_numeric($cVal) && (float)$cVal >= 1000 && (float)$cVal <= 500000 && (string)$cVal !== $empCode && (string)$cVal !== $c0) {
                    $salary = (float)$cVal;
                    break;
                }
            }

            if (empty($firstName) || $firstName === 'ชื่อ-นามสกุล' || $firstName === 'ชื่อ') {
                continue;
            }

            if (isset($seenEmpCodes[$empCode])) {
                continue;
            }
            $seenEmpCodes[$empCode] = true;

            $existing = Employee::where('emp_code', trim($empCode))->first();
            $status = $existing ? 'UPDATE' : 'NEW';
            $statusLabel = $existing ? 'อัปเดตข้อมูลเดิม' : 'เพิ่มใหม่';

            $previewData[] = [
                'line_no' => $index + 1,
                'emp_code' => trim($empCode),
                'prefix' => $prefix,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'full_name' => "{$prefix} {$firstName} {$lastName}",
                'department_name' => !empty($deptStr) ? $deptStr : 'แผนกทั่วไป',
                'position_title' => !empty($posStr) ? $posStr : '-',
                'salary' => $salary,
                'status' => $status,
                'status_label' => $statusLabel,
            ];
        }

        return $previewData;
    }

    /**
     * Commit preview items into the database.
     */
    public static function executeImport(array $items): array
    {
        $imported = 0;
        $updated = 0;

        foreach ($items as $item) {
            $empCode = trim($item['emp_code'] ?? '');
            $firstName = trim($item['first_name'] ?? '');
            if (empty($empCode) || empty($firstName)) continue;

            $deptName = !empty($item['department_name']) ? trim($item['department_name']) : 'แผนกทั่วไป';
            $posName = !empty($item['position_title']) ? trim($item['position_title']) : null;

            // Department Match or Create
            $department = Department::where('code', $deptName)
                ->orWhere('name_th', 'like', "%{$deptName}%")
                ->first();

            if (!$department) {
                $department = Department::firstOrCreate(
                    ['name_th' => $deptName],
                    ['code' => strtoupper(substr(md5($deptName), 0, 8)), 'is_active' => true]
                );
            }

            // Position Match or Create
            $position = null;
            if (!empty($posName) && $posName !== '-') {
                $position = Position::where('code', $posName)
                    ->orWhere('title_th', 'like', "%{$posName}%")
                    ->first();

                if (!$position) {
                    $position = Position::firstOrCreate(
                        ['title_th' => $posName],
                        ['code' => strtoupper(substr(md5($posName), 0, 8)), 'is_active' => true]
                    );
                }
            }

            $existing = Employee::where('emp_code', $empCode)->first();
            $salary = isset($item['salary']) ? (float)$item['salary'] : 15000.00;

            if ($existing) {
                $existing->update([
                    'prefix' => $item['prefix'] ?? 'นาย',
                    'first_name' => $firstName,
                    'last_name' => $item['last_name'] ?? '-',
                    'department_id' => $department->id,
                    'position_id' => $position?->id ?? $existing->position_id,
                    'salary' => $salary > 1000 ? $salary : $existing->salary,
                    'status' => 'Active',
                ]);
                $updated++;
            } else {
                Employee::create([
                    'emp_code' => $empCode,
                    'prefix' => $item['prefix'] ?? 'นาย',
                    'first_name' => $firstName,
                    'last_name' => $item['last_name'] ?? '-',
                    'department_id' => $department->id,
                    'position_id' => $position?->id,
                    'salary' => $salary,
                    'wage_type' => 'Monthly',
                    'status' => 'Active',
                ]);
                $imported++;
            }
        }

        return [
            'imported' => $imported,
            'updated' => $updated,
            'total' => $imported + $updated,
        ];
    }
}
