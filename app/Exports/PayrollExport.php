<?php

namespace App\Exports;

use App\Enums\OvertimeStatus;
use App\Models\OvertimeRequestEmployee;
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
        $query = OvertimeRequestEmployee::whereHas('overtimeRequest', function ($q) {
            $q->where('status', OvertimeStatus::APPROVED)
              ->whereYear('request_date', $this->year)
              ->whereMonth('request_date', $this->month);

            if ($this->departmentId) {
                $q->where('department_id', $this->departmentId);
            }
        })->with(['employee.department', 'employee.position', 'overtimeRequest.overtimeType']);

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'รหัสพนักงาน',
            'ชื่อ-นามสกุล',
            'แผนก',
            'ตำแหน่ง',
            'เลขที่เอกสาร OT',
            'วันที่ทำ OT',
            'ประเภท OT',
            'ตัวคูณ (Multiplier)',
            'เวลาแผนงาน (ชม.)',
            'เวลาจริง (ชม.)',
            'ชั่วโมงคำนวณเงิน',
        ];
    }

    public function map($row): array
    {
        $emp = $row->employee;
        $req = $row->overtimeRequest;
        $actual = $row->actual_hours ?? $row->planned_hours;

        return [
            $emp?->emp_code,
            $emp?->full_name,
            $emp?->department?->name_th,
            $emp?->position?->title_th ?? '-',
            $req?->document_no,
            $req?->request_date->format('d/m/Y'),
            $req?->overtimeType?->name_th,
            $req?->overtimeType?->multiplier,
            $row->planned_hours,
            $actual,
            round($actual * ($req?->overtimeType?->multiplier ?? 1.5), 2),
        ];
    }
}
