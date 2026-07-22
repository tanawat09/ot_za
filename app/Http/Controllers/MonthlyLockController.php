<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\MonthlyPeriodLock;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonthlyLockController extends Controller
{
    public function index()
    {
        $locks = MonthlyPeriodLock::with(['department', 'lockedBy'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(15);

        $departments = Department::all();

        return view('monthly_locks.index', compact('locks', 'departments'));
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'action' => ['required', 'in:LOCK,UNLOCK'],
            'remarks' => ['nullable', 'string'],
        ]);

        $lock = MonthlyPeriodLock::firstOrNew([
            'year' => $validated['year'],
            'month' => $validated['month'],
            'department_id' => $validated['department_id'] ?? null,
        ]);

        $status = $validated['action'] === 'LOCK' ? 'LOCKED' : 'OPEN';

        $lock->status = $status;
        $lock->locked_at = $status === 'LOCKED' ? now() : null;
        $lock->locked_by_user_id = Auth::id();
        $lock->remarks = $validated['remarks'] ?? null;
        $lock->save();

        AuditLogService::log(action: "Period {$status}", module: 'Period Lock', recordId: (string)$lock->id);

        $statusText = $status === 'LOCKED' ? 'ปิดรอบประจำเดือน' : 'เปิดรอบประจำเดือน';
        return redirect()->back()->with('success', "{$statusText} เดือน {$validated['month']}/{$validated['year']} สำเร็จ");
    }
}
