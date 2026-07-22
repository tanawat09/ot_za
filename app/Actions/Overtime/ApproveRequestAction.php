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
use Illuminate\Support\Facades\Request;

class ApproveRequestAction
{
    public static function execute(OvertimeRequest $request, ?string $comment = null): OvertimeRequest
    {
        return DB::transaction(function () use ($request, $comment) {
            $fromStatus = $request->status->value;

            $request->status = OvertimeStatus::APPROVED;
            $request->manager_user_id = Auth::id();
            $request->approved_at = now();
            $request->approval_comment = $comment;
            $request->save();

            // Record Approval Audit Entry
            OvertimeApproval::create([
                'overtime_request_id' => $request->id,
                'action_by_user_id' => Auth::id(),
                'action' => 'APPROVED',
                'comment' => $comment,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);

            // Record Status History
            OvertimeStatusHistory::create([
                'overtime_request_id' => $request->id,
                'from_status' => $fromStatus,
                'to_status' => OvertimeStatus::APPROVED->value,
                'changed_by_user_id' => Auth::id(),
                'remarks' => $comment ?? 'ผู้จัดการอนุมัติคำขอ OT',
            ]);

            AuditLogService::log(
                action: 'Approve OT Request',
                module: 'OT Workflow',
                recordId: (string)$request->id,
                newValues: ['status' => OvertimeStatus::APPROVED->value, 'comment' => $comment]
            );

            // Notify Supervisor Creator
            NotificationService::send(
                userId: $request->created_by_user_id,
                title: "คำขอ OT [{$request->document_no}] ได้รับการอนุมัติแล้ว",
                message: "ผู้จัดการได้อนุมัติคำขอ OT วันที่ {$request->request_date->format('d/m/Y')} เรียบร้อยแล้ว",
                link: route('overtime.show', $request)
            );

            return $request;
        });
    }
}
