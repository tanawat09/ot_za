<?php

namespace App\Http\Controllers;

use App\Exports\OvertimeReportExport;
use App\Models\Department;
use App\Models\Employee;
use App\Models\OvertimeType;
use App\Services\AuditLogService;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $reportTypes = ReportService::getReportTypes();
        return view('reports.index', compact('reportTypes'));
    }

    public function show(Request $request, string $type)
    {
        $reportTypes = ReportService::getReportTypes();
        if (!array_key_exists($type, $reportTypes)) {
            abort(404, 'ไม่พบรูปแบบรายงานที่ระบุ');
        }

        $filters = $request->all();
        $reportTitle = $reportTypes[$type];
        $reportData = ReportService::generate($type, $filters, Auth::user());

        $departments = Department::all();
        $overtimeTypes = OvertimeType::all();
        $employees = Employee::all();

        return view('reports.show', compact('type', 'reportTitle', 'reportData', 'departments', 'overtimeTypes', 'employees', 'filters'));
    }

    public function exportExcel(Request $request, string $type)
    {
        AuditLogService::log(action: "Export Report Excel ({$type})", module: 'Reports');
        return Excel::download(new OvertimeReportExport($type, $request->all()), "report_{$type}_" . date('Y-m-d') . ".xlsx");
    }

    public function exportPdf(Request $request, string $type)
    {
        $reportTypes = ReportService::getReportTypes();
        $reportTitle = $reportTypes[$type] ?? 'รายงาน OT';
        $reportData = ReportService::generate($type, $request->all(), Auth::user());

        $pdf = Pdf::loadView('reports.pdf', [
            'reportTitle' => $reportTitle,
            'reportData' => $reportData,
            'exporter' => Auth::user()->name,
            'exportDate' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        AuditLogService::log(action: "Export Report PDF ({$type})", module: 'Reports');

        return $pdf->download("report_{$type}_" . date('Y-m-d') . ".pdf");
    }
}
