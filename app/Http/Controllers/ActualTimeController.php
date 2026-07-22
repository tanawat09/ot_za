<?php

namespace App\Http\Controllers;

use App\Models\MonthlyPeriodLock;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestEmployee;
use App\Services\AuditLogService;
use App\Services\OvertimeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActualTimeController extends Controller
{
    public function edit(OvertimeRequest $overtime)
    {
        // Check if period is locked
        if (MonthlyPeriodLock::isLocked($overtime->request_date->year, $overtime->request_date->month, $overtime->department_id)) {
            return redirect()->route('overtime.show', $overtime)->with('error', 'งวดประจำเดือนนี้ถูกปิดรอบแล้ว (Monthly Period Locked) ไม่สามารถบันทึกเวลาได้');
        }

        $overtime->load(['department', 'employees.employee']);
        return view('overtime.actual_time', compact('overtime'));
    }

    public function update(Request $request, OvertimeRequest $overtime)
    {
        if (MonthlyPeriodLock::isLocked($overtime->request_date->year, $overtime->request_date->month, $overtime->department_id)) {
            return redirect()->route('overtime.show', $overtime)->with('error', 'งวดประจำเดือนนี้ถูกปิดรอบแล้ว (Monthly Period Locked)');
        }

        $validated = $request->validate([
            'actual' => ['required', 'array'],
            'actual.*.start_time' => ['nullable'],
            'actual.*.end_time' => ['nullable'],
            'actual.*.break_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        foreach ($validated['actual'] as $empReqId => $data) {
            $empReq = OvertimeRequestEmployee::where('overtime_request_id', $overtime->id)->find($empReqId);
            if ($empReq && !empty($data['start_time']) && !empty($data['end_time'])) {
                $calc = OvertimeCalculationService::calculate(
                    $overtime->request_date->format('Y-m-d'),
                    $data['start_time'],
                    $data['end_time'],
                    (int)($data['break_minutes'] ?? 0)
                );

                $empReq->update([
                    'actual_start_time' => $data['start_time'],
                    'actual_end_time' => $data['end_time'],
                    'actual_break_minutes' => $data['break_minutes'] ?? 0,
                    'actual_hours' => $calc['total_hours'],
                    'actual_recorded_by_user_id' => Auth::id(),
                ]);
            }
        }

        AuditLogService::log(action: 'Record Actual OT Time', module: 'OT Management', recordId: (string)$overtime->id);

        return redirect()->route('overtime.show', $overtime)->with('success', 'บันทึกเวลาการทำงานจริงเรียบร้อยแล้ว');
    }
}
