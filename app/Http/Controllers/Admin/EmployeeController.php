<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EmployeesExport;
use App\Http\Controllers\Controller;
use App\Imports\EmployeesImport;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Team;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'team', 'position', 'user', 'supervisors']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->input('team_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('emp_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('emp_code')->paginate(15);
        $departments = Department::all();
        $teams = Team::all();

        return view('admin.employees.index', compact('employees', 'departments', 'teams'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $teams = Team::where('is_active', true)->get();
        $positions = Position::where('is_active', true)->get();
        $users = User::where('is_active', true)->get();
        $supervisors = User::role('Supervisor')->get();

        return view('admin.employees.create', compact('departments', 'teams', 'positions', 'users', 'supervisors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'emp_code' => ['required', 'string', 'max:50', 'unique:employees,emp_code'],
            'user_id' => ['nullable', 'exists:users,id'],
            'prefix' => ['nullable', 'string', 'max:20'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'wage_type' => ['nullable', 'in:Monthly,Daily'],
            'status' => ['required', 'in:Active,Resigned,Suspended'],
            'supervisors' => ['nullable', 'array'],
            'supervisors.*' => ['exists:users,id'],
        ], [
            'emp_code.required' => 'กรุณากรอกรหัสพนักงาน',
            'emp_code.unique' => 'รหัสพนักงานนี้มีอยู่แล้วในระบบ',
            'first_name.required' => 'กรุณากรอกชื่อ',
            'last_name.required' => 'กรุณากรอกนามสกุล',
            'department_id.required' => 'กรุณาเลือกแผนก',
        ]);

        $employee = Employee::create([
            'emp_code' => strtoupper($validated['emp_code']),
            'user_id' => $validated['user_id'] ?? null,
            'prefix' => $validated['prefix'] ?? null,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'position_id' => $validated['position_id'] ?? null,
            'department_id' => $validated['department_id'],
            'team_id' => $validated['team_id'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'salary' => $validated['salary'] ?? 15000.00,
            'wage_type' => $validated['wage_type'] ?? 'Monthly',
            'status' => $validated['status'],
        ]);

        if (!empty($validated['supervisors'])) {
            $employee->supervisors()->sync($validated['supervisors']);
        }

        AuditLogService::log(action: 'Create Employee', module: 'Master Data', recordId: (string)$employee->id, newValues: $validated);

        return redirect()->route('admin.employees.index')->with('success', 'เพิ่มข้อมูลพนักงานสำเร็จ');
    }

    public function edit(Employee $employee)
    {
        $departments = Department::all();
        $teams = Team::all();
        $positions = Position::all();
        $users = User::all();
        $supervisors = User::role('Supervisor')->get();
        $selectedSupervisors = $employee->supervisors->pluck('id')->toArray();

        return view('admin.employees.edit', compact('employee', 'departments', 'teams', 'positions', 'users', 'supervisors', 'selectedSupervisors'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'emp_code' => ['required', 'string', 'max:50', 'unique:employees,emp_code,' . $employee->id],
            'user_id' => ['nullable', 'exists:users,id'],
            'prefix' => ['nullable', 'string', 'max:20'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'wage_type' => ['nullable', 'in:Monthly,Daily'],
            'status' => ['required', 'in:Active,Resigned,Suspended'],
            'supervisors' => ['nullable', 'array'],
            'supervisors.*' => ['exists:users,id'],
        ]);

        $oldValues = $employee->toArray();

        $employee->update([
            'emp_code' => strtoupper($validated['emp_code']),
            'user_id' => $validated['user_id'] ?? null,
            'prefix' => $validated['prefix'] ?? null,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'position_id' => $validated['position_id'] ?? null,
            'department_id' => $validated['department_id'],
            'team_id' => $validated['team_id'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'salary' => $validated['salary'] ?? 15000.00,
            'wage_type' => $validated['wage_type'] ?? 'Monthly',
            'status' => $validated['status'],
        ]);

        $employee->supervisors()->sync($validated['supervisors'] ?? []);

        AuditLogService::log(action: 'Update Employee', module: 'Master Data', recordId: (string)$employee->id, oldValues: $oldValues, newValues: $validated);

        return redirect()->route('admin.employees.index')->with('success', 'อัปเดตข้อมูลพนักงานสำเร็จ');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        AuditLogService::log(action: 'Delete Employee', module: 'Master Data', recordId: (string)$employee->id);

        return redirect()->route('admin.employees.index')->with('success', 'ลบข้อมูลพนักงานสำเร็จ');
    }

    public function clearAll()
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Employee::query()->forceDelete();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        AuditLogService::log(action: 'Clear All Employees Data', module: 'Master Data');

        return redirect()->route('admin.employees.index')->with('success', 'ลบข้อมูลพนักงานทั้งหมดในระบบเรียบร้อยแล้ว ท่านสามารถเริ่มนำเข้าไฟล์ใหม่ได้');
    }

    public function export()
    {
        AuditLogService::log(action: 'Export Employees Excel', module: 'Master Data');
        return Excel::download(new EmployeesExport, 'employees_' . date('Y-m-d') . '.xlsx');
    }

    public function showImportForm()
    {
        return view('admin.employees.import');
    }

    /**
     * Step 1: Preview & Validate Excel File before DB Insertion
     */
    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ], [
            'file.required' => 'กรุณาเลือกไฟล์ Excel หรือ CSV รายชื่อพนักงาน',
            'file.mimes' => 'อนุญาตเฉพาะไฟล์ .xlsx, .xls, .csv เท่านั้น',
            'file.max' => 'ขนาดไฟล์ต้องไม่เกิน 10MB',
        ]);

        try {
            $import = new EmployeesImport();
            Excel::import($import, $request->file('file'));

            $previewRows = $import->previewRows;
            $newCount = collect($previewRows)->where('status', 'NEW')->count();
            $updateCount = collect($previewRows)->where('status', 'UPDATE')->count();

            if (empty($previewRows)) {
                return redirect()->back()->with('warning', 'อ่านไฟล์สำเร็จ แต่ไม่พบรายการพนักงานในไฟล์ กรุณาตรวจสอบว่ามีข้อมูลในไฟล์');
            }

            return view('admin.employees.preview', compact('previewRows', 'newCount', 'updateCount'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการอ่านไฟล์: ' . $e->getMessage());
        }
    }

    /**
     * Step 2: Confirm & Commit Import into DB
     */
    public function confirmImport(Request $request)
    {
        $itemsJson = $request->input('preview_items');
        $items = json_decode($itemsJson, true);

        if (empty($items)) {
            return redirect()->route('admin.employees.import-form')->with('error', 'ไม่พบรายการข้อมูลที่จะนำเข้า');
        }

        $result = EmployeesImport::executeImport($items);

        AuditLogService::log(action: 'Confirm Import Employees', module: 'Master Data', newValues: ['imported' => $result['imported'], 'updated' => $result['updated']]);

        $msg = "นำเข้าและอัปเดตข้อมูลพนักงานเข้าสู่ระบบสำเร็จรวม {$result['total']} รายการ (เพิ่มพนักงานใหม่ {$result['imported']} รายการ, อัปเดตข้อมูลเดิม {$result['updated']} รายการ)";
        return redirect()->route('admin.employees.index')->with('success', $msg);
    }

    public function sampleTemplate()
    {
        $csvHeader = "emp_code,prefix,first_name,last_name,department,position,email,phone,salary\n";
        $sampleData = "EMP001,นาย,สมชาย,ใจดี,ฝ่ายขนส่ง,พนักงานขับรถ,somchai@company.com,0812345678,18000\nEMP002,นางสาว,สมหญิง,รักงาน,ฝ่ายบุคคล,เจ้าหน้าที่บุคคล,somying@company.com,0823456789,22000\nEMP003,นาย,ปริญวัฒน์,ปิยะอารยาภัสร์,ฝ่ายขนส่ง,พนักงานขนส่ง,,0834567890,16500\n";

        return Response::make("\xEF\xBB\xBF" . $csvHeader . $sampleData, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=Employee_Import_Template.csv',
        ]);
    }
}
