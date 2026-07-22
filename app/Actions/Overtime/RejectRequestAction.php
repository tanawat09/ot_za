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

class RejectRequestAction
{
    public static function execute(OvertimeRequest $request, string $reason): OvertimeRequest
    {
        return DB::transaction(function () use ($request, $reason) {
            $fromStatus = $request->status->value;

            $request->status = OvertimeStatus::REJECTED;
            $request->manager_user_id = Auth::id();
            $request->approval_comment = $reason;
            $request->save();

            OvertimeApproval::create([
                'overtime_request_id' => $request->id,
                'action_by_user_id' => Auth::id(),
                'action' => 'REJECTED',
                'comment' => $reason,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);

            OvertimeStatusHistory::create([
                'overtime_request_id' => $request->id,
                'from_status' => $fromStatus,
                'to_status' => OvertimeStatus::REJECTED->value,
                'changed_by_user_id' => Auth::id(),
                'remarks' => $reason,
            ]);

            AuditLogService::log(
                action: 'Reject OT Request',
                module: 'OT Workflow',
                recordId: (string)$request->id,
                newValues: ['status' => OvertimeStatus::REJECTED->value, 'reason' => $reason]
            );

            NotificationService::send(
                userId: $request->created_by_user_id,
                title: "คำขอ OT [{$request->document_no}] ถูกปฏิเสธ",
                message: "เหตุผลการปฏิเสธ: {$reason}",
                link: route('overtime.show', $request)
            );

            return $request;
        });
    }
}
