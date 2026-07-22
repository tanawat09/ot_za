<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Employee::with(['department', 'team', 'position', 'user'])->get();
    }

    public function headings(): array
    {
        return [
            'รหัสพนักงาน',
            'คำนำหน้า',
            'ชื่อ',
            'นามสกุล',
            'อีเมล',
            'เบอร์โทรศัพท์',
            'ตำแหน่ง',
            'แผนก',
            'ทีม',
            'สถานะ',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->emp_code,
            $employee->prefix,
            $employee->first_name,
            $employee->last_name,
            $employee->email,
            $employee->phone,
            $employee->position?->title_th,
            $employee->department?->name_th,
            $employee->team?->name_th,
            $employee->status,
        ];
    }
}
