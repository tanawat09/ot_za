<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\HipAttendanceLog;
use App\Services\AuditLogService;
use App\Services\HipImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
            'file' => ['nullable', 'file', 'mimes:xlsx,xls,csv,txt,dat,mdb', 'max:20480'], // Max 20MB
            'raw_text' => ['nullable', 'string'],
        ], [
            'file.mimes' => 'อนุญาตเฉพาะไฟล์ Excel (.xlsx, .xls), CSV (.csv), Access Database (.mdb), Text (.txt) จาก HIP Premium Time เท่านั้น',
            'file.max' => 'ขนาดไฟล์ต้องไม่เกิน 20MB',
        ]);

        $batchName = 'HIP_IMPORT_' . date('Ymd_His');
        $result = ['imported_count' => 0, 'matched_ot_count' => 0, 'errors' => []];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            $filePath = $file->getRealPath();

            if ($ext === 'mdb') {
                // Parse Access MDB Database File
                $result = HipImportService::processMdbFile($filePath, $batchName);
            } elseif ($ext === 'xlsx' || $ext === 'xls') {
                // Parse Excel File using PhpSpreadsheet
                try {
                    $spreadsheet = IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $matrixRows = $worksheet->toArray();
                    $result = HipImportService::processMatrixRows($matrixRows, $batchName);
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการอ่านไฟล์ Excel: ' . $e->getMessage());
                }
            } else {
                // Parse CSV or TXT file (e.g. test.csv format)
                $content = file_get_contents($filePath);
                $matrixRows = [];
                $lines = explode("\n", str_replace("\r", "", $content));

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    $cols = str_contains($line, "\t") ? explode("\t", $line) : str_getcsv($line);
                    $matrixRows[] = $cols;
                }

                $result = HipImportService::processMatrixRows($matrixRows, $batchName);
            }
        } elseif ($request->filled('raw_text')) {
            $matrixRows = [];
            $lines = explode("\n", str_replace("\r", "", $request->input('raw_text')));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                $cols = str_contains($line, "\t") ? explode("\t", $line) : str_getcsv($line);
                $matrixRows[] = $cols;
            }
            $result = HipImportService::processMatrixRows($matrixRows, $batchName);
        }

        if (empty($result['imported_count']) && empty($result['errors'])) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลสแกนในไฟล์ที่อัปโหลด กรุณาตรวจสอบรูปแบบไฟล์');
        }

        AuditLogService::log(
            action: 'Import HIP Attendance Logs',
            module: 'HIP Integration',
            newValues: ['imported_count' => $result['imported_count'], 'matched_ot' => $result['matched_ot_count']]
        );

        $msg = "นำเข้าข้อมูลเวลาสแกน HIP Premium Time สำเร็จ {$result['imported_count']} รายการ (จับคู่คำขอ OT อัตโนมัติ {$result['matched_ot_count']} รายการ)";
        if (!empty($result['errors'])) {
            $msg .= " (คำเตือน: " . implode('; ', array_slice($result['errors'], 0, 3)) . ")";
        }

        return redirect()->route('hip.index')->with('success', $msg);
    }

    public function sampleTemplate()
    {
        $csvHeader = "รหัสที่เครื่อง,รหัสพนักงาน,ชื่อ-นามสกุล,แผนก,Date,1,2,3,4,\n";
        $sampleData = "563005,,ปริญวัฒน์  ปิยะอารยาภัสร์,ฝ่ายขนส่ง,01/07/2026,07:54,17:59,,,\n563005,,ปริญวัฒน์  ปิยะอารยาภัสร์,ฝ่ายขนส่ง,02/07/2026,08:00,20:01,,,\n563005,,ปริญวัฒน์  ปิยะอารยาภัสร์,ฝ่ายขนส่ง,04/07/2026,08:16,17:02,,,\n";

        return Response::make("\xEF\xBB\xBF" . $csvHeader . $sampleData, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="HIP_Premium_Time_Sample.csv"',
        ]);
    }
}
