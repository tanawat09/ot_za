<?php

namespace App\Exports;

use App\Models\OvertimeRequest;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OvertimeReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $type;
    protected $filters;

    public function __construct(string $type, array $filters = [])
    {
        $this->type = $type;
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = OvertimeRequest::with(['department', 'team', 'overtimeType', 'creator', 'manager', 'employees.employee']);

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('request_date', '>=', $this->filters['start_date']);
        }
        if (!empty($this->filters['end_date'])) {
            $query->whereDate('request_date', '<=', $this->filters['end_date']);
        }
        if (!empty($this->filters['department_id'])) {
            $query->where('department_id', $this->filters['department_id']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'เลขที่เอกสาร',
            'วันที่ทำ OT',
            'แผนก',
            'ทีม',
            'ประเภท OT',
            'เวลาเริ่ม',
            'เวลาเลิก',
            'หักพัก (นาที)',
            'ชั่วโมงรวม',
            'จำนวนพนักงาน',
            'สถานะ',
            'เหตุผลในการทำ OT',
            'ผู้สร้างคำขอ',
            'ผู้อนุมัติ',
        ];
    }

    public function map($req): array
    {
        return [
            $req->document_no,
            $req->request_date->format('d/m/Y'),
            $req->department?->name_th,
            $req->team?->name_th ?? 'ทั้งแผนก',
            $req->overtimeType?->name_th,
            substr($req->start_time, 0, 5),
            substr($req->end_time, 0, 5),
            $req->break_minutes,
            $req->total_hours,
            $req->employees->count(),
            $req->status->label(),
            $req->reason,
            $req->creator?->name,
            $req->manager?->name ?? '-',
        ];
    }
}
