<?php

namespace App\Actions\Overtime;

use App\Enums\OvertimeStatus;
use App\Models\OvertimeApproval;
use App\Models\OvertimeRequest;
use App\Models\OvertimeStatusHistory;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitRequestAction
{
    public static function execute(OvertimeRequest $request): OvertimeRequest
    {
        if ($request->employees()->count() === 0) {
            throw ValidationException::withMessages([
                'employees' => 'ไม่สามารถส่งคำขอที่ไม่มีรายชื่อพนักงานได้',
            ]);
        }

        return DB::transaction(function () use ($request) {
            $fromStatus = $request->status->value;
            $request->status = OvertimeStatus::PENDING_APPROVAL;
            $request->submitted_at = now();
            $request->save();

            // Record status history
            OvertimeStatusHistory::create([
                'overtime_request_id' => $request->id,
                'from_status' => $fromStatus,
                'to_status' => OvertimeStatus::PENDING_APPROVAL->value,
                'changed_by_user_id' => Auth::id(),
                'remarks' => 'ส่งคำขออนุมัติ OT',
            ]);

            AuditLogService::log(
                action: 'Submit OT Request',
                module: 'OT Workflow',
                recordId: (string)$request->id,
                newValues: ['status' => OvertimeStatus::PENDING_APPROVAL->value]
            );

            // Notify Department Managers
            if ($request->department) {
                foreach ($request->department->managers as $mgr) {
                    NotificationService::send(
                        userId: $mgr->id,
                        title: "มีคำขอ OT ใหม่รอการอนุมัติ [{$request->document_no}]",
                        message: "คำขอ OT แผนก {$request->department->name_th} วันที่ {$request->request_date->format('d/m/Y')} รอการอนุมัติจากคุณ",
                        link: route('approvals.index')
                    );
                }
            }

            return $request;
        });
    }
}
