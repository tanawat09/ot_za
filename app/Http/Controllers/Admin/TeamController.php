<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Team;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $query = Team::with(['department'])->withCount('employees');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_th', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $teams = $query->orderBy('code')->paginate(15);
        $departments = Department::all();

        return view('admin.teams.index', compact('teams', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        return view('admin.teams.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'code' => ['required', 'string', 'max:50', 'unique:teams,code'],
            'name_th' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ], [
            'department_id.required' => 'กรุณาเลือกแผนก',
            'code.required' => 'กรุณากรอกรหัสทีม',
            'code.unique' => 'รหัสทีมนี้ถูกใช้งานแล้ว',
            'name_th.required' => 'กรุณากรอกชื่อทีม',
        ]);

        $team = Team::create([
            'department_id' => $validated['department_id'],
            'code' => strtoupper($validated['code']),
            'name_th' => $validated['name_th'],
            'name_en' => $validated['name_en'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogService::log(action: 'Create Team', module: 'Master Data', recordId: (string)$team->id, newValues: $validated);

        return redirect()->route('admin.teams.index')->with('success', 'เพิ่มทีมสำเร็จ');
    }

    public function edit(Team $team)
    {
        $departments = Department::all();
        return view('admin.teams.edit', compact('team', 'departments'));
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'code' => ['required', 'string', 'max:50', 'unique:teams,code,' . $team->id],
            'name_th' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $oldValues = $team->toArray();

        $team->update([
            'department_id' => $validated['department_id'],
            'code' => strtoupper($validated['code']),
            'name_th' => $validated['name_th'],
            'name_en' => $validated['name_en'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogService::log(action: 'Update Team', module: 'Master Data', recordId: (string)$team->id, oldValues: $oldValues, newValues: $validated);

        return redirect()->route('admin.teams.index')->with('success', 'อัปเดตข้อมูลทีมสำเร็จ');
    }

    public function destroy(Team $team)
    {
        if ($team->employees()->count() > 0) {
            return redirect()->back()->with('error', 'ไม่สามารถลบทีมที่มีพนักงานสังกัดอยู่ได้');
        }

        $team->delete();
        AuditLogService::log(action: 'Delete Team', module: 'Master Data', recordId: (string)$team->id);

        return redirect()->route('admin.teams.index')->with('success', 'ลบข้อมูลทีมสำเร็จ');
    }
}
