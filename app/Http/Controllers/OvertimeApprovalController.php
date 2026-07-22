<?php

namespace App\Http\Controllers;

use App\Actions\Overtime\ApproveRequestAction;
use App\Actions\Overtime\RejectRequestAction;
use App\Actions\Overtime\ReturnRequestAction;
use App\Enums\OvertimeStatus;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeApprovalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $managedDeptIds = $user->managedDepartments->pluck('id')->toArray();

        // If super admin, allow viewing all pending requests
        $query = OvertimeRequest::with(['department', 'team', 'overtimeType', 'creator', 'employees.employee'])
            ->where('status', OvertimeStatus::PENDING_APPROVAL);

        if (!$user->hasRole('Super Admin')) {
            $query->whereIn('department_id', $managedDeptIds);
        }

        $pendingRequests = $query->orderBy('submitted_at', 'asc')->paginate(15);

        return view('approvals.index', compact('pendingRequests'));
    }

    public function approve(Request $request, OvertimeRequest $overtime)
    {
        $this->authorizeManager($overtime);

        $comment = $request->input('comment');
        ApproveRequestAction::execute($overtime, $comment);

        return redirect()->route('approvals.index')->with('success', "อนุมัติคำขอ OT เลขที่ {$overtime->document_no} เรียบร้อยแล้ว");
    }

    public function reject(Request $request, OvertimeRequest $overtime)
    {
        $this->authorizeManager($overtime);

        $request->validate([
            'reason' => ['required', 'string', 'min:3'],
        ], [
            'reason.required' => 'กรุณาระบุเหตุผลการปฏิเสธคำขอ',
        ]);

        RejectRequestAction::execute($overtime, $request->input('reason'));

        return redirect()->route('approvals.index')->with('success', "ปฏิเสธคำขอ OT เลขที่ {$overtime->document_no} เรียบร้อยแล้ว");
    }

    public function returnForRevision(Request $request, OvertimeRequest $overtime)
    {
        $this->authorizeManager($overtime);

        $request->validate([
            'comment' => ['required', 'string', 'min:3'],
        ], [
            'comment.required' => 'กรุณาระบุความคิดเห็นสำหรับการส่งกลับแก้ไข',
        ]);

        ReturnRequestAction::execute($overtime, $request->input('comment'));

        return redirect()->route('approvals.index')->with('success', "ส่งกลับคำขอ OT เลขที่ {$overtime->document_no} เพื่อแก้ไขเรียบร้อยแล้ว");
    }

    private function authorizeManager(OvertimeRequest $overtime)
    {
        $user = Auth::user();
        if ($user->hasRole('Super Admin')) {
            return;
        }

        $managedDeptIds = $user->managedDepartments->pluck('id')->toArray();
        if (!in_array($overtime->department_id, $managedDeptIds)) {
            abort(403, 'คุณไม่มีสิทธิ์อนุมัติคำขอ OT ของแผนกนี้');
        }
    }
}
