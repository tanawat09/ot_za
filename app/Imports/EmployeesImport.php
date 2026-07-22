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
     * Helper to check if a string is a real Department name or keyword.
     */
    public static function isRealDepartment(?string $str): bool
    {
        if (empty($str)) return false;
        $str = trim($str);

        // Department keywords
        $deptKeywords = ['ฝ่าย', 'แผนก', 'สำนัก', 'ส่วน', 'คลัง', 'กลุ่มงาน', 'ศูนย์', 'dept', 'department', 'division', 'office', 'section'];
        foreach ($deptKeywords as $kw) {
            if (str_contains(strtolower($str), $kw)) {
                return true;
            }
        }

        // Match existing department in DB
        return Department::where('code', $str)
            ->orWhere('name_th', $str)
            ->orWhere('name_en', $str)
            ->exists();
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
            $cols = array_values(array_map(fn($v) => trim((string)$v), $row->toArray()));

            // Remove trailing empty elements
            while (count($cols) > 0 && end($cols) === '') {
                array_pop($cols);
            }

            if (count($cols) < 2) continue;

            // Header row detection (only check first 2 rows)
            if ($index < 2) {
                $rowStr = implode(' ', $cols);
                if (
                    str_contains($rowStr, 'รหัสพนักงาน') || str_contains(strtolower($rowStr), 'emp_code') ||
                    str_contains($rowStr, 'ชื่อ-นามสกุล') || str_contains($rowStr, 'รหัสที่เครื่อง')
                ) {
                    continue;
                }
            }

            // Find Emp Code column intelligently
            $empCode = '';
            $empCodeColIdx = -1;

            foreach ($cols as $idx => $val) {
                if (empty($val)) continue;
                if (preg_match('/^[A-Za-z0-9_-]{1,20}$/', $val) && $val !== 'รหัสพนักงาน' && $val !== 'ลำดับ' && $val !== 'No') {
                    // Check if $val is a sequence number like 1, 2, 3 and next col is actual emp code
                    if (ctype_digit($val) && (int)$val < 500 && isset($cols[$idx + 1]) && preg_match('/^[A-Za-z0-9_-]{3,20}$/', $cols[$idx + 1]) && !preg_match('/[\x{0E00}-\x{0E7F}]/u', $cols[$idx + 1])) {
                        continue;
                    }
                    $empCode = $val;
                    $empCodeColIdx = $idx;
                    break;
                }
            }

            if (empty($empCode)) continue;

            // Format empCode if purely numeric and < 5 digits (e.g. 1 -> 00001)
            if (ctype_digit($empCode) && strlen($empCode) < 5) {
                $empCode = sprintf('%05d', (int)$empCode);
            }

            // Gather remaining text columns after empCodeColIdx for Name, Surname, Dept
            $textValues = [];
            for ($i = 0; $i < count($cols); $i++) {
                if ($i === $empCodeColIdx) continue;
                if ($cols[$i] !== '') {
                    $textValues[] = $cols[$i];
                }
            }

            if (empty($textValues)) continue;

            $prefix = 'นาย';
            $firstName = '';
            $lastName = '-';
            $deptStr = '';
            $posStr = '';
            $salary = 15000.00;

            $val0 = $textValues[0];
            [$extractedPrefix, $cleanVal0] = self::extractPrefixAndName($val0);
            $prefix = $extractedPrefix;

            $words0 = preg_split('/\s+/', trim($cleanVal0));

            if (count($words0) >= 2) {
                // Val0 contains First Name and Last Name (e.g. "ธิดาวรรณ วงค์")
                $firstName = $words0[0];
                $lastName = implode(' ', array_slice($words0, 1));

                // Check remaining columns for Department or Position
                foreach (array_slice($textValues, 1) as $remVal) {
                    if (self::isRealDepartment($remVal)) {
                        $deptStr = $remVal;
                    } else {
                        $posStr = $remVal;
                    }
                }
            } else {
                // Val0 is First Name only
                $firstName = $words0[0] ?? $cleanVal0;

                if (count($textValues) >= 2) {
                    $val1 = $textValues[1];
                    if (self::isRealDepartment($val1)) {
                        $deptStr = $val1;
                    } else {
                        $lastName = $val1;
                    }

                    if (count($textValues) >= 3) {
                        $val2 = $textValues[2];
                        if (self::isRealDepartment($val2)) {
                            $deptStr = $val2;
                        } else {
                            $posStr = $val2;
                        }
                    }
                }
            }

            // Clean trailing '-' or space in lastName
            $lastName = trim(str_replace(['-', '–'], '', $lastName));
            if (empty($lastName)) {
                $lastName = '-';
            }

            $firstName = trim($firstName);

            // Find salary if any col has numeric salary value
            foreach ($cols as $cVal) {
                if (is_numeric($cVal) && (float)$cVal >= 1000 && (float)$cVal <= 500000 && (string)$cVal !== $empCode) {
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
