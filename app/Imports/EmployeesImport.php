<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $rowArray = $row->toArray();

            // Extract values supporting both Thai & English header keys
            $empCode = $this->getValue($rowArray, ['emp_code', 'รหัสพนักงาน', 'รหัสที่เครื่อง', 'code']);
            $firstName = $this->getValue($rowArray, ['first_name', 'ชื่อ', 'ชื่อจริง', 'name']);
            $lastName = $this->getValue($rowArray, ['last_name', 'นามสกุล', 'surname']);
            $prefix = $this->getValue($rowArray, ['prefix', 'คำนำหน้า', 'คำนำหน้านาม']);
            $deptNameOrCode = $this->getValue($rowArray, ['department_code', 'department', 'แผนก', 'ฝ่าย', 'ชื่อแผนก']);
            $positionNameOrCode = $this->getValue($rowArray, ['position_code', 'position', 'ตำแหน่ง']);
            $teamNameOrCode = $this->getValue($rowArray, ['team_code', 'team', 'ทีม']);
            $email = $this->getValue($rowArray, ['email', 'อีเมล']);
            $phone = $this->getValue($rowArray, ['phone', 'เบอร์โทร', 'เบอร์โทรศัพท์']);
            $salary = $this->getValue($rowArray, ['salary', 'เงินเดือน', 'ฐานเงินเดือน', 'ค่าจ้าง']);
            $wageType = $this->getValue($rowArray, ['wage_type', 'ประเภทค่าจ้าง']);

            if (empty($empCode) || empty($firstName)) {
                continue; // Skip empty rows
            }

            // Match Department by Code or Name
            $department = null;
            if (!empty($deptNameOrCode)) {
                $department = Department::where('code', $deptNameOrCode)
                    ->orWhere('name_th', 'like', "%{$deptNameOrCode}%")
                    ->first();
            }

            if (!$department) {
                // Fallback or create department
                $department = Department::firstOrCreate(
                    ['code' => !empty($deptNameOrCode) ? strtoupper(substr($deptNameOrCode, 0, 10)) : 'GEN'],
                    ['name_th' => $deptNameOrCode ?: 'แผนกทั่วไป', 'is_active' => true]
                );
            }

            // Match Position by Code or Title
            $position = null;
            if (!empty($positionNameOrCode)) {
                $position = Position::where('code', $positionNameOrCode)
                    ->orWhere('title_th', 'like', "%{$positionNameOrCode}%")
                    ->first();

                if (!$position) {
                    $position = Position::firstOrCreate(
                        ['code' => strtoupper(substr($positionNameOrCode, 0, 10))],
                        ['title_th' => $positionNameOrCode, 'is_active' => true]
                    );
                }
            }

            // Match Team by Code or Name
            $team = null;
            if (!empty($teamNameOrCode)) {
                $team = Team::where('code', $teamNameOrCode)
                    ->orWhere('name_th', 'like', "%{$teamNameOrCode}%")
                    ->first();
            }

            // Update or Create Employee by emp_code
            Employee::updateOrCreate(
                ['emp_code' => trim($empCode)],
                [
                    'prefix' => !empty($prefix) ? trim($prefix) : 'นาย',
                    'first_name' => trim($firstName),
                    'last_name' => !empty($lastName) ? trim($lastName) : '-',
                    'department_id' => $department->id,
                    'position_id' => $position?->id,
                    'team_id' => $team?->id,
                    'email' => !empty($email) ? trim($email) : null,
                    'phone' => !empty($phone) ? trim($phone) : null,
                    'salary' => !empty($salary) ? (float)$salary : 15000.00,
                    'wage_type' => (!empty($wageType) && strtolower($wageType) === 'daily') ? 'Daily' : 'Monthly',
                    'status' => 'Active',
                ]
            );
        }
    }

    /**
     * Helper to find value from multiple possible column keys.
     */
    private function getValue(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            // Check direct key
            if (isset($row[$key]) && !empty($row[$key])) {
                return (string)$row[$key];
            }

            // Check slugified/lowercased keys
            $normalizedKey = strtolower(str_replace([' ', '_', '-'], '', $key));
            foreach ($row as $rKey => $rVal) {
                $normRKey = strtolower(str_replace([' ', '_', '-'], '', (string)$rKey));
                if ($normRKey === $normalizedKey && !empty($rVal)) {
                    return (string)$rVal;
                }
            }
        }

        return null;
    }
}
