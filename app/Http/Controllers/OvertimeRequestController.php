<?php

namespace App\Http\Controllers;

use App\Actions\Overtime\SubmitRequestAction;
use App\Enums\OvertimeStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestEmployee;
use App\Models\OvertimeStatusHistory;
use App\Models\OvertimeType;
use App\Models\Team;
use App\Services\AuditLogService;
use App\Services\DocumentNumberService;
use App\Services\OvertimeCalculationService;
use App\Services\OvertimeValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OvertimeRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = OvertimeRequest::with(['department', 'team', 'overtimeType', 'creator', 'employees.employee']);

        // Scope enforcement
        if ($user->hasRole('Supervisor')) {
            $query->where('created_by_user_id', $user->id);
        } elseif ($user->hasRole('Manager')) {
            $managedDeptIds = $user->managedDepartments->pluck('id')->toArray();
            $query->whereIn('department_id', $managedDeptIds);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        // Historical Date Filter (ดูย้อนหลัง)
        if ($request->filled('start_date')) {
            $query->whereDate('request_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('request_date', '<=', $request->input('end_date'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('document_no', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $requests = $query->orderBy('request_date', 'desc')->orderBy('created_at', 'desc')->paginate(15);
        $departments = Department::all();

        return view('overtime.index', compact('requests', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $teams = Team::where('is_active', true)->get();
        $overtimeTypes = OvertimeType::where('is_active', true)->get();
        $employees = Employee::where('status', 'Active')->get();

        return view('overtime.create', compact('departments', 'teams', 'overtimeTypes', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'overtime_type_id' => ['required', 'exists:overtime_types,id'],
            'request_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'break_minutes' => ['required', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'reason' => ['required', 'string'],
            'work_details' => ['nullable', 'string'],
            'employees' => ['required', 'array', 'min:1'],
            'employees.*' => ['exists:employees,id'],
        ], [
            'department_id.required' => 'กรุณาเลือกแผนก',
            'overtime_type_id.required' => 'กรุณาเลือกประเภท OT',
            'request_date.required' => 'กรุณาระบุวันที่ทำ OT',
            'start_time.required' => 'กรุณาระบุเวลาเริ่ม',
            'end_time.required' => 'กรุณาระบุเวลาเลิก',
            'reason.required' => 'กรุณาระบุเหตุผลในการทำ OT',
            'employees.required' => 'กรุณาเลือกพนักงานอย่างน้อย 1 คน',
        ]);

        // Check overlapping OT hours
        $conflicts = OvertimeValidationService::checkOverlapping(
            $validated['employees'],
            $validated['request_date'],
            $validated['start_time'],
            $validated['end_time']
        );

        if (!empty($conflicts)) {
            throw ValidationException::withMessages([
                'employees' => implode(', ', $conflicts),
            ]);
        }

        $calc = OvertimeCalculationService::calculate(
            $validated['request_date'],
            $validated['start_time'],
            $validated['end_time'],
            (int)$validated['break_minutes']
        );

        $documentNo = DocumentNumberService::generate($validated['department_id'], $validated['request_date']);

        $otRequest = DB::transaction(function () use ($validated, $calc, $documentNo) {
            $otRequest = OvertimeRequest::create([
                'document_no' => $documentNo,
                'department_id' => $validated['department_id'],
                'team_id' => $validated['team_id'] ?? null,
                'created_by_user_id' => Auth::id(),
                'overtime_type_id' => $validated['overtime_type_id'],
                'request_date' => $validated['request_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'break_minutes' => $validated['break_minutes'],
                'total_hours' => $calc['total_hours'],
                'is_cross_midnight' => $calc['is_cross_midnight'],
                'location' => $validated['location'] ?? null,
                'reason' => $validated['reason'],
                'work_details' => $validated['work_details'] ?? null,
                'status' => OvertimeStatus::DRAFT,
            ]);

            foreach ($validated['employees'] as $empId) {
                OvertimeRequestEmployee::create([
                    'overtime_request_id' => $otRequest->id,
                    'employee_id' => $empId,
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'break_minutes' => $validated['break_minutes'],
                    'planned_hours' => $calc['total_hours'],
                    'consent_status' => 'PENDING',
                ]);
            }

            OvertimeStatusHistory::create([
                'overtime_request_id' => $otRequest->id,
                'from_status' => null,
                'to_status' => OvertimeStatus::DRAFT->value,
                'changed_by_user_id' => Auth::id(),
                'remarks' => 'สร้างร่างคำขอ OT ใหม่',
            ]);

            AuditLogService::log(action: 'Create OT Request', module: 'OT Management', recordId: (string)$otRequest->id, newValues: ['document_no' => $documentNo]);

            return $otRequest;
        });

        return redirect()->route('overtime.show', $otRequest)->with('success', "สร้างคำขอ OT เลขที่ {$documentNo} สำเร็จแล้ว! กรุณากดปุ่มพิมพ์เอกสารยินยอม (PDF) เพื่อนำไปให้พนักงานลงลายมือชื่อ");
    }

    public function show(OvertimeRequest $overtime)
    {
        $overtime->load(['department', 'team', 'overtimeType', 'creator', 'manager', 'employees.employee', 'consents.uploader', 'approvals.user', 'statusHistories.user']);
        return view('overtime.show', compact('overtime'));
    }

    public function edit(OvertimeRequest $overtime)
    {
        if (!$overtime->isEditable()) {
            return redirect()->route('overtime.show', $overtime)->with('error', 'คำขอนี้ไม่อยู่ในสถานะที่แก้ไขได้');
        }

        $departments = Department::where('is_active', true)->get();
        $teams = Team::where('is_active', true)->get();
        $overtimeTypes = OvertimeType::where('is_active', true)->get();
        $employees = Employee::where('status', 'Active')->get();
        $selectedEmployees = $overtime->employees->pluck('employee_id')->toArray();

        return view('overtime.edit', compact('overtime', 'departments', 'teams', 'overtimeTypes', 'employees', 'selectedEmployees'));
    }

    public function update(Request $request, OvertimeRequest $overtime)
    {
        if (!$overtime->isEditable()) {
            return redirect()->route('overtime.show', $overtime)->with('error', 'คำขอนี้ไม่อยู่ในสถานะที่แก้ไขได้');
        }

        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'overtime_type_id' => ['required', 'exists:overtime_types,id'],
            'request_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'break_minutes' => ['required', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'reason' => ['required', 'string'],
            'work_details' => ['nullable', 'string'],
            'employees' => ['required', 'array', 'min:1'],
            'employees.*' => ['exists:employees,id'],
        ]);

        $conflicts = OvertimeValidationService::checkOverlapping(
            $validated['employees'],
            $validated['request_date'],
            $validated['start_time'],
            $validated['end_time'],
            $overtime->id
        );

        if (!empty($conflicts)) {
            throw ValidationException::withMessages([
                'employees' => implode(', ', $conflicts),
            ]);
        }

        $calc = OvertimeCalculationService::calculate(
            $validated['request_date'],
            $validated['start_time'],
            $validated['end_time'],
            (int)$validated['break_minutes']
        );

        DB::transaction(function () use ($overtime, $validated, $calc) {
            $oldStatus = $overtime->status->value;

            $overtime->update([
                'department_id' => $validated['department_id'],
                'team_id' => $validated['team_id'] ?? null,
                'overtime_type_id' => $validated['overtime_type_id'],
                'request_date' => $validated['request_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'break_minutes' => $validated['break_minutes'],
                'total_hours' => $calc['total_hours'],
                'is_cross_midnight' => $calc['is_cross_midnight'],
                'location' => $validated['location'] ?? null,
                'reason' => $validated['reason'],
                'work_details' => $validated['work_details'] ?? null,
                'status' => OvertimeStatus::DRAFT,
                'updated_by_user_id' => Auth::id(),
            ]);

            // Sync employees
            $overtime->employees()->delete();
            foreach ($validated['employees'] as $empId) {
                OvertimeRequestEmployee::create([
                    'overtime_request_id' => $overtime->id,
                    'employee_id' => $empId,
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'break_minutes' => $validated['break_minutes'],
                    'planned_hours' => $calc['total_hours'],
                ]);
            }

            if ($oldStatus !== OvertimeStatus::DRAFT->value) {
                OvertimeStatusHistory::create([
                    'overtime_request_id' => $overtime->id,
                    'from_status' => $oldStatus,
                    'to_status' => OvertimeStatus::DRAFT->value,
                    'changed_by_user_id' => Auth::id(),
                    'remarks' => 'แก้ไขข้อมูลคำขอ OT',
                ]);
            }

            AuditLogService::log(action: 'Update OT Request', module: 'OT Management', recordId: (string)$overtime->id);
        });

        return redirect()->route('overtime.show', $overtime)->with('success', 'แก้ไขคำขอ OT สำเร็จ');
    }

    public function submit(OvertimeRequest $overtime)
    {
        SubmitRequestAction::execute($overtime);
        return redirect()->route('overtime.show', $overtime)->with('success', 'ส่งคำขออนุมัติเรียบร้อยแล้ว');
    }

    public function cancel(OvertimeRequest $overtime)
    {
        DB::transaction(function () use ($overtime) {
            $fromStatus = $overtime->status->value;
            $overtime->status = OvertimeStatus::CANCELLED;
            $overtime->save();

            OvertimeStatusHistory::create([
                'overtime_request_id' => $overtime->id,
                'from_status' => $fromStatus,
                'to_status' => OvertimeStatus::CANCELLED->value,
                'changed_by_user_id' => Auth::id(),
                'remarks' => 'ยกเลิกคำขอ OT โดยผู้ใช้',
            ]);

            AuditLogService::log(action: 'Cancel OT Request', module: 'OT Workflow', recordId: (string)$overtime->id);
        });

        return redirect()->route('overtime.index')->with('success', 'ยกเลิกคำขอ OT เรียบร้อยแล้ว');
    }
}
