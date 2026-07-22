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

class ReturnRequestAction
{
    public static function execute(OvertimeRequest $request, string $comment): OvertimeRequest
    {
        return DB::transaction(function () use ($request, $comment) {
            $fromStatus = $request->status->value;

            $request->status = OvertimeStatus::RETURNED;
            $request->approval_comment = $comment;
            $request->save();

            OvertimeApproval::create([
                'overtime_request_id' => $request->id,
                'action_by_user_id' => Auth::id(),
                'action' => 'RETURNED',
                'comment' => $comment,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);

            OvertimeStatusHistory::create([
                'overtime_request_id' => $request->id,
                'from_status' => $fromStatus,
                'to_status' => OvertimeStatus::RETURNED->value,
                'changed_by_user_id' => Auth::id(),
                'remarks' => $comment,
            ]);

            AuditLogService::log(
                action: 'Return OT Request for Revision',
                module: 'OT Workflow',
                recordId: (string)$request->id,
                newValues: ['status' => OvertimeStatus::RETURNED->value, 'comment' => $comment]
            );

            NotificationService::send(
                userId: $request->created_by_user_id,
                title: "คำขอ OT [{$request->document_no}] ถูกส่งกลับให้แก้ไข",
                message: "ความคิดเห็นเพิ่มเติม: {$comment}",
                link: route('overtime.edit', $request)
            );

            return $request;
        });
    }
}
