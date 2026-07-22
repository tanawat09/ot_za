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
    public int $importedCount = 0;
    public int $updatedCount = 0;

    public function collection(Collection $rows)
    {
        $seenEmpCodes = [];

        foreach ($rows as $index => $row) {
            $cols = array_values($row->toArray());
            if (count($cols) < 2) continue;

            $col0 = trim((string)($cols[0] ?? ''));
            $col1 = trim((string)($cols[1] ?? ''));
            $col2 = trim((string)($cols[2] ?? ''));
            $col3 = trim((string)($cols[3] ?? ''));

            // Header row detection
            if (
                str_contains($col0, 'รหัส') || str_contains(strtolower($col0), 'code') || str_contains($col0, 'Device') ||
                str_contains($col1, 'รหัส') || str_contains(strtolower($col1), 'code') || str_contains($col2, 'ชื่อ') || str_contains(strtolower($col2), 'name')
            ) {
                continue;
            }

            // Detect Employee Code
            $empCode = !empty($col0) ? $col0 : (!empty($col1) ? $col1 : '');

            // Detect Full Name or First Name / Last Name
            $prefix = 'นาย';
            $firstName = '';
            $lastName = '-';
            $deptStr = '';
            $posStr = '';
            $salary = 15000.00;

            if (!empty($col2) && (empty($col3) || preg_match('/^\d{2}\/\d{2}/', $col3) || str_contains($col3, 'ฝ่าย') || str_contains($col3, 'แผนก'))) {
                // Layout pattern: [0: EmpCode, 1: blank, 2: FullName, 3: DeptName]
                $fullName = $col2;
                $deptStr = $col3;

                // Extract Prefix
                if (str_starts_with($fullName, 'นาย')) {
                    $prefix = 'นาย';
                    $fullName = trim(mb_substr($fullName, 3));
                } elseif (str_starts_with($fullName, 'นางสาว')) {
                    $prefix = 'นางสาว';
                    $fullName = trim(mb_substr($fullName, 6));
                } elseif (str_starts_with($fullName, 'นาง')) {
                    $prefix = 'นาง';
                    $fullName = trim(mb_substr($fullName, 3));
                }

                $nameParts = preg_split('/\s+/', trim($fullName));
                if (count($nameParts) >= 2) {
                    $firstName = $nameParts[0];
                    $lastName = implode(' ', array_slice($nameParts, 1));
                } else {
                    $firstName = $fullName;
                }
            } else {
                // Standard Layout pattern: [0: EmpCode, 1: Prefix, 2: FirstName, 3: LastName, 4: Dept, 5: Pos]
                if (in_array($col1, ['นาย', 'นาง', 'นางสาว', 'Mr.', 'Mrs.', 'Ms.'])) {
                    $prefix = $col1;
                    $firstName = $col2;
                    $lastName = !empty($col3) ? $col3 : '-';
                    $deptStr = !empty($cols[4]) ? trim((string)$cols[4]) : '';
                    $posStr = !empty($cols[5]) ? trim((string)$cols[5]) : '';
                } else {
                    $firstName = !empty($col1) ? $col1 : $col0;
                    $lastName = !empty($col2) ? $col2 : '-';
                    $deptStr = !empty($col3) ? $col3 : '';
                    $posStr = !empty($cols[4]) ? trim((string)$cols[4]) : '';
                }
            }

            // Find salary if any col has numeric salary value
            foreach ($cols as $cVal) {
                if (is_numeric($cVal) && (float)$cVal >= 1000 && (float)$cVal <= 500000 && (string)$cVal !== $empCode && (string)$cVal !== $col0) {
                    $salary = (float)$cVal;
                    break;
                }
            }

            if (empty($empCode) || empty($firstName) || $empCode === 'รหัสพนักงาน' || $firstName === 'ชื่อ-นามสกุล') {
                continue;
            }

            // Prevent duplicate processing in same file loop
            if (isset($seenEmpCodes[$empCode])) {
                continue;
            }
            $seenEmpCodes[$empCode] = true;

            // Department Match or Auto Create
            $department = null;
            if (!empty($deptStr)) {
                $department = Department::where('code', $deptStr)
                    ->orWhere('name_th', 'like', "%{$deptStr}%")
                    ->first();
            }

            if (!$department) {
                $deptName = !empty($deptStr) ? $deptStr : 'แผนกทั่วไป';
                $department = Department::firstOrCreate(
                    ['name_th' => $deptName],
                    ['code' => strtoupper(substr(md5($deptName), 0, 8)), 'is_active' => true]
                );
            }

            // Position Match or Auto Create
            $position = null;
            if (!empty($posStr)) {
                $position = Position::where('code', $posStr)
                    ->orWhere('title_th', 'like', "%{$posStr}%")
                    ->first();

                if (!$position) {
                    $position = Position::firstOrCreate(
                        ['title_th' => $posStr],
                        ['code' => strtoupper(substr(md5($posStr), 0, 8)), 'is_active' => true]
                    );
                }
            }

            // Update or Create Employee
            $existing = Employee::where('emp_code', trim($empCode))->first();
            if ($existing) {
                $existing->update([
                    'prefix' => $prefix,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'department_id' => $department->id,
                    'position_id' => $position?->id ?? $existing->position_id,
                    'salary' => $salary > 1000 ? $salary : $existing->salary,
                    'status' => 'Active',
                ]);
                $this->updatedCount++;
            } else {
                Employee::create([
                    'emp_code' => trim($empCode),
                    'prefix' => $prefix,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'department_id' => $department->id,
                    'position_id' => $position?->id,
                    'salary' => $salary,
                    'wage_type' => 'Monthly',
                    'status' => 'Active',
                ]);
                $this->importedCount++;
            }
        }
    }
}
