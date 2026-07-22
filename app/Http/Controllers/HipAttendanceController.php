<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\HipAttendanceLog;
use App\Services\AuditLogService;
use App\Services\HipImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class HipAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = HipAttendanceLog::with(['employee.department', 'employee.position']);

        if ($request->filled('start_date')) {
            $query->whereDate('log_date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('log_date', '<=', $request->input('end_date'));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('emp_code', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->orderBy('log_date', 'desc')->orderBy('emp_code', 'asc')->paginate(20);

        return view('hip.index', compact('logs'));
    }

    public function create()
    {
        return view('hip.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['nullable', 'file', 'mimes:xlsx,xls,csv,txt,dat', 'max:10240'],
            'raw_text' => ['nullable', 'string'],
        ], [
            'file.mimes' => 'อนุญาตเฉพาะไฟล์ Excel (.xlsx, .xls), CSV (.csv), Text (.txt, .dat) จาก HIP Premium Time เท่านั้น',
            'file.max' => 'ขนาดไฟล์ต้องไม่เกิน 10MB',
        ]);

        $records = [];
        $batchName = 'HIP_IMPORT_' . date('YMD_His');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());

            if ($ext === 'csv' || $ext === 'txt' || $ext === 'dat') {
                $content = file_get_contents($file->getRealPath());
                $lines = explode("\n", str_replace("\r", "", $content));

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;

                    // Support comma or tab separated
                    $cols = str_contains($line, "\t") ? explode("\t", $line) : explode(",", $line);
                    if (count($cols) >= 3) {
                        $records[] = [
                            'emp_code' => trim($cols[0]),
                            'log_date' => trim($cols[1]),
                            'check_in' => trim($cols[2] ?? ''),
                            'check_out' => trim($cols[3] ?? ''),
                            'device_id' => trim($cols[4] ?? 'HIP-DEV-01'),
                        ];
                    }
                }
            } else {
                // Return error if unsupported binary excel format without library
                return redirect()->back()->with('error', 'กรุณาอัปโหลดไฟล์ในรูปแบบ CSV หรือ Text จาก HIP Premium Time');
            }
        } elseif ($request->filled('raw_text')) {
            $lines = explode("\n", str_replace("\r", "", $request->input('raw_text')));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                $cols = str_contains($line, "\t") ? explode("\t", $line) : explode(",", $line);
                if (count($cols) >= 3) {
                    $records[] = [
                        'emp_code' => trim($cols[0]),
                        'log_date' => trim($cols[1]),
                        'check_in' => trim($cols[2] ?? ''),
                        'check_out' => trim($cols[3] ?? ''),
                        'device_id' => trim($cols[4] ?? 'HIP-DEV-01'),
                    ];
                }
            }
        }

        if (empty($records)) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลสแกนในไฟล์ที่อัปโหลด กรุณาตรวจสอบรูปแบบไฟล์');
        }

        $result = HipImportService::processImport($records, $batchName);

        AuditLogService::log(
            action: 'Import HIP Attendance Logs',
            module: 'HIP Integration',
            newValues: ['imported_count' => $result['imported_count'], 'matched_ot' => $result['matched_ot_count']]
        );

        $msg = "นำเข้าข้อมูลสแกนนิ้ว HIP Premium Time สำเร็จ {$result['imported_count']} รายการ (จับคู่ตรงกับคำขอ OT อัตโนมัติ {$result['matched_ot_count']} รายการ)";
        return redirect()->route('hip.index')->with('success', $msg);
    }

    public function sampleTemplate()
    {
        $csvHeader = "emp_code,log_date,check_in,check_out,device_id\n";
        $sampleData = "EMP001,2026-07-21,17:30,20:30,HIP-DEV-01\nEMP002,2026-07-21,17:30,21:00,HIP-DEV-01\nEMP003,2026-07-21,17:30,20:00,HIP-DEV-01\n";

        return Response::make($csvHeader . $sampleData, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="HIP_Premium_Time_Sample.csv"',
        ]);
    }
}
