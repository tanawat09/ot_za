<?php

namespace App\Exports;

use App\Services\PayrollService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayrollExport implements FromCollection, WithHeadings, WithMapping
{
    protected $year;
    protected $month;
    protected $departmentId;

    public function __construct(int $year, int $month, ?int $departmentId = null)
    {
        $this->year = $year;
        $this->month = $month;
        $this->departmentId = $departmentId;
    }

    public function collection()
    {
        $data = PayrollService::calculateMonthlyPayroll($this->year, $this->month, $this->departmentId);
        return collect($data['employees']);
    }

    public function headings(): array
    {
        return [
            'รหัสพนักงาน',
            'ชื่อ-นามสกุล',
            'แผนก',
            'ตำแหน่ง',
            'ฐานเงินเดือน/ค่าจ้าง (บาท)',
            'อัตราค่าจ้างต่อชั่วโมง (บาท/ชม.)',
            'ชั่วโมง OT 1.5 เท่า',
            'ชั่วโมง OT 3.0 เท่า',
            'ชั่วโมง OT 1.0 เท่า',
            'รวมชั่วโมง OT ทั้งหมด',
            'ค่าตอบแทน OT 1.5 เท่า (บาท)',
            'ค่าตอบแทน OT 3.0 เท่า (บาท)',
            'ค่าตอบแทน OT 1.0 เท่า (บาท)',
            'รวมเงินค่า OT ทั้งหมด (บาท)',
            'รวมเงินรายรับสุทธิ (เงินเดือน + OT)',
        ];
    }

    public function map($row): array
    {
        return [
            $row['emp_code'],
            $row['full_name'],
            $row['department_name'],
            $row['position_title'],
            number_format($row['base_salary'], 2),
            number_format($row['hourly_rate'], 2),
            $row['hours_1_5'],
            $row['hours_3_0'],
            $row['hours_1_0'],
            $row['total_hours'],
            number_format($row['ot_pay_1_5'], 2),
            number_format($row['ot_pay_3_0'], 2),
            number_format($row['ot_pay_1_0'], 2),
            number_format($row['total_ot_pay'], 2),
            number_format($row['net_pay'], 2),
        ];
    }
}
