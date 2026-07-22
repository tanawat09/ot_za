<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::withCount(['teams', 'employees']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_th', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $departments = $query->orderBy('code')->paginate(15);
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        $managers = User::role('Manager')->get();
        return view('admin.departments.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:departments,code'],
            'name_th' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'managers' => ['nullable', 'array'],
            'managers.*' => ['exists:users,id'],
        ], [
            'code.required' => 'กรุณากรอกรหัสแผนก',
            'code.unique' => 'รหัสแผนกนี้ถูกใช้งานแล้ว',
            'name_th.required' => 'กรุณากรอกชื่อแผนก (ภาษาไทย)',
        ]);

        $department = Department::create([
            'code' => strtoupper($validated['code']),
            'name_th' => $validated['name_th'],
            'name_en' => $validated['name_en'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (!empty($validated['managers'])) {
            $department->managers()->sync($validated['managers']);
        }

        AuditLogService::log(action: 'Create Department', module: 'Master Data', recordId: (string)$department->id, newValues: $validated);

        return redirect()->route('admin.departments.index')->with('success', 'เพิ่มแผนกสำเร็จ');
    }

    public function edit(Department $department)
    {
        $managers = User::role('Manager')->get();
        $selectedManagers = $department->managers->pluck('id')->toArray();
        return view('admin.departments.edit', compact('department', 'managers', 'selectedManagers'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:departments,code,' . $department->id],
            'name_th' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'managers' => ['nullable', 'array'],
            'managers.*' => ['exists:users,id'],
        ]);

        $oldValues = $department->toArray();

        $department->update([
            'code' => strtoupper($validated['code']),
            'name_th' => $validated['name_th'],
            'name_en' => $validated['name_en'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $department->managers()->sync($validated['managers'] ?? []);

        AuditLogService::log(action: 'Update Department', module: 'Master Data', recordId: (string)$department->id, oldValues: $oldValues, newValues: $validated);

        return redirect()->route('admin.departments.index')->with('success', 'อัปเดตข้อมูลแผนกสำเร็จ');
    }

    public function destroy(Request $request, Department $department)
    {
        if ($department->employees()->count() > 0 && !$request->boolean('force')) {
            return redirect()->back()->with('error', 'ไม่สามารถลบแผนกที่มีพนักงานสังกัดอยู่ได้ (หากต้องการลบ กรุณาย้ายพนักงานก่อนหรือกดเลือกบังคับลบแผนก)');
        }

        $deptName = $department->name_th;

        if ($department->employees()->count() > 0) {
            $defaultDept = Department::where('code', 'GEN')
                ->orWhere('name_th', 'แผนกทั่วไป')
                ->where('id', '!=', $department->id)
                ->first();

            if (!$defaultDept) {
                $defaultDept = Department::create([
                    'code' => 'GEN',
                    'name_th' => 'แผนกทั่วไป',
                    'is_active' => true,
                ]);
            }

            $department->employees()->update(['department_id' => $defaultDept->id]);
        }

        $department->delete();

        AuditLogService::log(action: 'Delete Department', module: 'Master Data', recordId: (string)$department->id);

        return redirect()->route('admin.departments.index')->with('success', "ลบแผนก {$deptName} สำเร็จ");
    }

    public function clearUnused()
    {
        $deletedCount = 0;
        $departments = Department::withCount('employees')->where('code', '!=', 'GEN')->where('name_th', '!=', 'แผนกทั่วไป')->get();
        foreach ($departments as $dept) {
            if ($dept->employees_count === 0) {
                $dept->delete();
                $deletedCount++;
            }
        }

        AuditLogService::log(action: 'Clear Unused Departments', module: 'Master Data');

        return redirect()->route('admin.departments.index')->with('success', "ลบแผนกที่ไม่มีพนักงานสังกัดออกสำเร็จทั้งหมด {$deletedCount} แผนก");
    }
}
