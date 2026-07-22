<?php

namespace App\Http\Controllers;

use App\Exports\PayrollExport;
use App\Models\Department;
use App\Services\AuditLogService;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PayrollExportController extends Controller
{
    public function index(Request $request)
    {
        $year = (int)$request->input('year', date('Y'));
        $month = (int)$request->input('month', date('n'));
        $departmentId = $request->input('department_id') ? (int)$request->input('department_id') : null;

        $departments = Department::all();
        $payrollData = PayrollService::calculateMonthlyPayroll($year, $month, $departmentId);

        return view('payroll.index', compact('departments', 'payrollData', 'year', 'month', 'departmentId'));
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'format' => ['required', 'in:xlsx,csv'],
        ]);

        $filename = "payroll_ot_{$validated['year']}_{$validated['month']}." . $validated['format'];

        AuditLogService::log(action: "Export Payroll Data ({$validated['format']})", module: 'Payroll Integration');

        $export = new PayrollExport($validated['year'], $validated['month'], $validated['department_id'] ?? null);

        if ($validated['format'] === 'csv') {
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, $filename);
    }
}
