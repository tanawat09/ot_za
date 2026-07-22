<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Team;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeesImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $dept = Department::where('code', $row['department_code'])->first();
        $team = isset($row['team_code']) ? Team::where('code', $row['team_code'])->first() : null;
        $position = isset($row['position_code']) ? Position::where('code', $row['position_code'])->first() : null;

        return new Employee([
            'emp_code' => $row['emp_code'],
            'prefix' => $row['prefix'] ?? null,
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'department_id' => $dept ? $dept->id : 1,
            'team_id' => $team ? $team->id : null,
            'position_id' => $position ? $position->id : null,
            'status' => 'Active',
        ]);
    }

    public function rules(): array
    {
        return [
            'emp_code' => ['required', 'unique:employees,emp_code'],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'department_code' => ['required', 'exists:departments,code'],
        ];
    }
}
