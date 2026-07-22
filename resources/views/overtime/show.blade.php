@extends('layouts.app')

@section('title', 'รายละเอียดคำขอ OT ' . $overtime->document_no)
@section('header', 'รายละเอียดคำขอ OT: ' . $overtime->document_no)

@section('content')
<div class="row g-4">
    <!-- Main Details Column -->
    <div class="col-lg-8">
        <!-- Request Summary Card -->
        <div class="card card-custom p-4 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 border-bottom pb-3 mb-3">
                <div>
                    <span class="badge {{ $overtime->status->badgeClass() }} fs-6 mb-2">
                        {{ $overtime->status->label() }}
                    </span>
                    <h4 class="fw-bold font-heading text-dark mb-0">{{ $overtime->document_no }}</h4>
                    <span class="text-muted fs-7">สร้างเมื่อ: {{ $overtime->created_at->format('d/m/Y H:i') }} โดย {{ $overtime->creator?->name }}</span>
                </div>
                
                <div class="d-flex flex-wrap gap-2">
                    <!-- Print PDF Consent Button -->
                    <a href="{{ route('overtime.pdf-consent', $overtime) }}" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-file-earmark-pdf me-1"></i> พิมพ์เอกสารยินยอม (PDF)
                    </a>

                    @if($overtime->isEditable())
                        <a href="{{ route('overtime.edit', $overtime) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i> แก้ไขคำขอ
                        </a>
                    @endif

                    @if($overtime->status === \App\Enums\OvertimeStatus::DRAFT || $overtime->status === \App\Enums\OvertimeStatus::READY_TO_SUBMIT || $overtime->status === \App\Enums\OvertimeStatus::RETURNED)
                        <form method="POST" action="{{ route('overtime.submit', $overtime) }}" class="d-inline" onsubmit="return confirm('ยืนยันการส่งคำขอ OT ให้ผู้จัดการพิจารณาอนุมัติ?');">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-send me-1"></i> ส่งคำขออนุมัติ
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Overview Details Grid -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="text-muted fs-7 font-heading">แผนก / ทีม</div>
                    <div class="fw-semibold text-dark">{{ $overtime->department?->name_th }} ({{ $overtime->team?->name_th ?? 'ทั้งแผนก' }})</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted fs-7 font-heading">ประเภท OT</div>
                    <div class="fw-semibold text-dark">{{ $overtime->overtimeType?->name_th }} ({{ $overtime->overtimeType?->multiplier }} เท่า)</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted fs-7 font-heading">วันที่ทำ OT</div>
                    <div class="fw-bold text-primary fs-6">{{ $overtime->request_date->format('d/m/Y') }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted fs-7 font-heading">ช่วงเวลาทำ OT</div>
                    <div class="fw-semibold text-dark">
                        {{ substr($overtime->start_time, 0, 5) }} - {{ substr($overtime->end_time, 0, 5) }} น.
                        @if($overtime->is_cross_midnight)
                            <span class="badge bg-warning text-dark fs-7 ms-1">ข้ามคืน</span>
                        @endif
                        (หักพัก {{ $overtime->break_minutes }} นาที = <span class="text-primary fw-bold">{{ $overtime->total_hours }} ชม.</span>)
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted fs-7 font-heading">สถานที่ปฏิบัติงาน</div>
                    <div class="text-dark">{{ $overtime->location ?? '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted fs-7 font-heading">ผู้จัดการผู้อนุมัติ</div>
                    <div class="text-dark">{{ $overtime->manager?->name ?? 'รอการอนุมัติ' }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted fs-7 font-heading">เหตุผลในการทำ OT</div>
                    <div class="p-2 bg-light rounded text-dark">{{ $overtime->reason }}</div>
                </div>
                @if($overtime->work_details)
                    <div class="col-12">
                        <div class="text-muted fs-7 font-heading">รายละเอียดงานเพิ่มเติม</div>
                        <div class="p-2 bg-light rounded text-dark fs-7">{{ $overtime->work_details }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Employee & Consent Status Table -->
        <div class="card card-custom p-4 mb-4">
            <h5 class="fw-bold font-heading mb-3">
                <i class="bi bi-people-fill text-primary me-2"></i>รายชื่อพนักงานที่ปฏิบัติงาน ({{ $overtime->employees->count() }} คน)
            </h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>รหัสพนักงาน</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>ตำแหน่ง</th>
                            <th>ชั่วโมงแผนงาน</th>
                            <th>สถานะยินยอม</th>
                            <th class="text-end">อัปเดตสิทธิ์</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overtime->employees as $empReq)
                            <tr>
                                <td class="fw-bold text-muted">{{ $empReq->employee?->emp_code }}</td>
                                <td class="fw-semibold text-dark">{{ $empReq->employee?->full_name }}</td>
                                <td class="fs-7 text-muted">{{ $empReq->employee?->position?->title_th ?? '-' }}</td>
                                <td class="fw-bold text-primary">{{ $empReq->planned_hours }} ชม.</td>
                                <td>
                                    @if($empReq->consent_status === 'CONSENTED')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> ยินยอมแล้ว</span>
                                    @elseif($empReq->consent_status === 'REFUSED')
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> ปฏิเสธ</span>
                                    @else
                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> รอลงชื่อ</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($overtime->isEditable())
                                        <form method="POST" action="{{ route('overtime.consent-status', $overtime) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="employee_id" value="{{ $empReq->employee_id }}">
                                            @if($empReq->consent_status !== 'CONSENTED')
                                                <button type="submit" name="consent_status" value="CONSENTED" class="btn btn-sm btn-outline-success" title="กดยินยอมแทนพนักงาน">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            @endif
                                            @if($empReq->consent_status !== 'REFUSED')
                                                <button type="submit" name="consent_status" value="REFUSED" class="btn btn-sm btn-outline-danger" title="ปฏิเสธ">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            @endif
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Signed Consent File Attachments (Phase 4) -->
        <div class="card card-custom p-4 mb-4">
            <h5 class="fw-bold font-heading mb-3 border-bottom pb-2">
                <i class="bi bi-paperclip text-primary me-2"></i>ไฟล์เอกสารยินยอมที่ลงนามแล้ว (Signed Consent Documents)
            </h5>

            @if($overtime->consents->count() > 0)
                <ul class="list-group mb-3">
                    @foreach($overtime->consents as $consent)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-file-earmark-text text-danger me-2 fs-5"></i>
                                <span class="fw-semibold text-dark">{{ $consent->file_name }}</span>
                                <span class="text-muted fs-7">({{ round($consent->file_size / 1024, 1) }} KB) โดย {{ $consent->uploader?->name }}</span>
                            </div>
                            <a href="{{ route('consents.download', $consent) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i> ดาวน์โหลด
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="alert alert-light text-muted fs-7 border mb-3">ยังไม่มีการอัปโหลดไฟล์เอกสารยินยอมที่เซ็นแล้ว</div>
            @endif

            @if($overtime->isEditable())
                <form method="POST" action="{{ route('overtime.upload-consent', $overtime) }}" enctype="multipart/form-data" class="row g-2 align-items-center">
                    @csrf
                    <div class="col-md-9">
                        <input type="file" class="form-control form-control-sm" name="file" required accept=".pdf,.jpg,.png">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm btn-success w-100"><i class="bi bi-upload me-1"></i> อัปโหลดไฟล์</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Manager Actions & Audit Timeline Column -->
    <div class="col-lg-4">
        <!-- Manager Action Box (Phase 5) -->
        @hasanyrole('Manager|Super Admin')
            @if($overtime->status === \App\Enums\OvertimeStatus::PENDING_APPROVAL)
                <div class="card card-custom p-4 mb-4 border-start border-primary border-4 shadow-sm">
                    <h5 class="fw-bold font-heading text-primary mb-3">
                        <i class="bi bi-check2-square me-2"></i>พิจารณาอนุมัติคำขอ OT
                    </h5>

                    <!-- Approve Form -->
                    <form method="POST" action="{{ route('approvals.approve', $overtime) }}" class="mb-3" onsubmit="return confirm('ยืนยันอนุมัติคำขอ OT นี้?');">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label font-heading text-dark fs-7">ความคิดเห็นผู้จัดการ (ไม่บังคับ)</label>
                            <input type="text" name="comment" class="form-control form-control-sm" placeholder="ความคิดเห็นเพิ่มเติม...">
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold">
                            <i class="bi bi-check-circle-fill me-1"></i> อนุมัติคำขอ (Approve)
                        </button>
                    </form>

                    <hr>

                    <!-- Return & Reject Modals/Forms -->
                    <div class="d-grid gap-2">
                        <!-- Return Button -->
                        <button type="button" class="btn btn-warning text-dark fw-bold btn-sm" data-bs-toggle="collapse" data-bs-target="#returnCollapse">
                            <i class="bi bi-arrow-return-left me-1"></i> ส่งกลับให้แก้ไข (Return)
                        </button>

                        <div class="collapse mt-2" id="returnCollapse">
                            <form method="POST" action="{{ route('approvals.return', $overtime) }}" class="p-3 bg-light rounded border">
                                @csrf
                                <label class="form-label font-heading text-dark fs-7">ระบุความคิดเห็นในการส่งกลับ <span class="text-danger">*</span></label>
                                <textarea name="comment" class="form-control form-control-sm mb-2" required placeholder="สิ่งที่ต้องแก้ไข..."></textarea>
                                <button type="submit" class="btn btn-warning btn-sm w-100">ยืนยันส่งกลับ</button>
                            </form>
                        </div>

                        <!-- Reject Button -->
                        <button type="button" class="btn btn-danger fw-bold btn-sm" data-bs-toggle="collapse" data-bs-target="#rejectCollapse">
                            <i class="bi bi-x-circle me-1"></i> ไม่อนุมัติคำขอ (Reject)
                        </button>

                        <div class="collapse mt-2" id="rejectCollapse">
                            <form method="POST" action="{{ route('approvals.reject', $overtime) }}" class="p-3 bg-light rounded border">
                                @csrf
                                <label class="form-label font-heading text-dark fs-7">ระบุเหตุผลการปฏิเสธ <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control form-control-sm mb-2" required placeholder="เหตุผลที่ไม่อนุมัติ..."></textarea>
                                <button type="submit" class="btn btn-danger btn-sm w-100">ยืนยันการปฏิเสธ</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endhasanyrole

        <!-- Status Timeline History -->
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-3 border-bottom pb-2">
                <i class="bi bi-history text-primary me-2"></i>ประวัติการเปลี่ยนสถานะ
            </h5>

            <div class="timeline">
                @foreach($overtime->statusHistories as $history)
                    <div class="border-start border-2 border-primary ps-3 pb-3 position-relative">
                        <div class="fw-semibold text-dark fs-7">
                            <span class="badge bg-secondary me-1">{{ $history->to_status }}</span>
                            {{ $history->user?->name }}
                        </div>
                        <div class="text-muted fs-7">{{ $history->created_at->format('d/m/Y H:i:s') }}</div>
                        @if($history->remarks)
                            <div class="fst-italic fs-7 text-secondary mt-1">"{{ $history->remarks }}"</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
