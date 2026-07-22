@extends('layouts.app')

@section('title', 'รายการคำขอ OT')
@section('header', 'รายการคำขอ OT (Overtime Requests)')

@section('content')
<div class="card card-custom p-4 shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <div>
            <h5 class="fw-bold font-heading mb-0 text-dark">
                <i class="bi bi-file-earmark-text text-primary me-2"></i>รายการคำขอ OT ทั้งหมด
            </h5>
            <span class="text-muted fs-7">สามารถเลือกวันที่ดูย้อนหลัง และสั่งพิมพ์ใบยินยอม (PDF) ให้พนักงานลงชื่อได้</span>
        </div>
        @hasanyrole('Supervisor|Super Admin')
            <a href="{{ route('overtime.create') }}" class="btn btn-primary font-heading fw-bold shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> สร้างคำขอ OT ใหม่
            </a>
        @endhasanyrole
    </div>

    <!-- Filter Form with Historical Date Selection -->
    <form method="GET" action="{{ route('overtime.index') }}" class="row g-3 mb-4 p-3 bg-light rounded border">
        <div class="col-md-3">
            <label class="form-label fs-7 font-heading text-dark fw-bold">ค้นหาเอกสาร/เหตุผล</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="เลขที่เอกสาร หรือเหตุผล..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fs-7 font-heading text-dark fw-bold">ตั้งแต่วันที่ (ย้อนหลัง)</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fs-7 font-heading text-dark fw-bold">ถึงวันที่</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fs-7 font-heading text-dark fw-bold">สถานะ</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">-- สถานะทั้งหมด --</option>
                @foreach(\App\Enums\OvertimeStatus::cases() as $st)
                    <option value="{{ $st->value }}" {{ request('status') == $st->value ? 'selected' : '' }}>
                        {{ $st->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2 align-items-end">
            <button type="submit" class="btn btn-sm btn-primary w-100 font-heading fw-bold">
                <i class="bi bi-search me-1"></i> ค้นหา
            </button>

            <!-- Quick Date Filters -->
            <a href="{{ route('overtime.index', ['start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d')]) }}" class="btn btn-sm btn-outline-secondary" title="เฉพาะวันนี้">วันนี้</a>
            <a href="{{ route('overtime.index', ['start_date' => \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'), 'end_date' => \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')]) }}" class="btn btn-sm btn-outline-secondary" title="เดือนนี้">เดือนนี้</a>
            <a href="{{ route('overtime.index') }}" class="btn btn-sm btn-outline-secondary" title="ล้างค่า"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>

    <!-- Request Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>เลขที่เอกสาร</th>
                    <th>วันที่ทำ OT</th>
                    <th>แผนก / ทีม</th>
                    <th>ประเภท OT</th>
                    <th>เวลาปฏิบัติงาน</th>
                    <th>จำนวนคน</th>
                    <th>ชั่วโมงรวม</th>
                    <th>สถานะ</th>
                    <th class="text-end">การจัดการ & พิมพ์ใบยินยอม</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr>
                        <td class="fw-bold text-primary">
                            <a href="{{ route('overtime.show', $req) }}" class="text-decoration-none text-primary">
                                {{ $req->document_no }}
                            </a>
                        </td>
                        <td class="fw-semibold">{{ $req->request_date->format('d/m/Y') }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $req->department?->name_th }}</div>
                            <div class="fs-7 text-muted">{{ $req->team?->name_th ?? 'ทั้งแผนก' }}</div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $req->overtimeType?->name_th }}</span></td>
                        <td class="fs-7">
                            <div>{{ substr($req->start_time, 0, 5) }} - {{ substr($req->end_time, 0, 5) }} น.</div>
                            @if($req->is_cross_midnight)
                                <span class="badge bg-warning text-dark fs-7">ข้ามคืน</span>
                            @endif
                        </td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $req->employees->count() }} คน</span></td>
                        <td class="fw-bold text-dark">{{ $req->total_hours }} ชม.</td>
                        <td>
                            <span class="badge {{ $req->status->badgeClass() }}">
                                {{ $req->status->label() }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <!-- Print PDF Consent Button for Employee Signature -->
                                <a href="{{ route('overtime.pdf-consent', $req) }}" class="btn btn-outline-danger" title="พิมพ์ใบยินยอม (PDF) ให้พนักงานลงชื่อ">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> พิมพ์ใบยินยอม (PDF)
                                </a>

                                <a href="{{ route('overtime.show', $req) }}" class="btn btn-outline-info" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @if($req->isEditable())
                                    <a href="{{ route('overtime.edit', $req) }}" class="btn btn-outline-primary" title="แก้ไข">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-4 d-block mb-2 text-secondary"></i>
                            ไม่พบรายการคำขอ OT ตามเงื่อนไขที่ระบุ
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $requests->withQueryString()->links() }}
    </div>
</div>
@endsection
