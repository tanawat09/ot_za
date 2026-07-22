@extends('layouts.app')

@section('title', 'พิจารณาอนุมัติคำขอ OT')
@section('header', 'กล่องงานพิจารณาอนุมัติ (Manager Approval Inbox)')

@section('content')
<div class="card card-custom p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <h5 class="fw-bold font-heading mb-0">
            <i class="bi bi-check2-square text-primary me-2"></i>รายการคำขอ OT ที่รอการพิจารณาอนุมัติ
        </h5>
        <span class="badge bg-warning text-dark fs-6">รออนุมัติ {{ $pendingRequests->total() }} รายการ</span>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>เลขที่เอกสาร</th>
                    <th>วันที่ทำ OT</th>
                    <th>แผนก / ทีม</th>
                    <th>ผู้สร้างคำขอ</th>
                    <th>ประเภท OT</th>
                    <th>ชั่วโมงรวม</th>
                    <th>จำนวนพนักงาน</th>
                    <th>วันที่ส่งมา</th>
                    <th class="text-end">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingRequests as $req)
                    <tr>
                        <td class="fw-bold text-primary">
                            <a href="{{ route('overtime.show', $req) }}" class="text-decoration-none text-primary">
                                {{ $req->document_no }}
                            </a>
                        </td>
                        <td class="fw-semibold">{{ $req->request_date->format('d/m/Y') }}</td>
                        <td>
                            <div class="fw-semibold">{{ $req->department?->name_th }}</div>
                            <div class="fs-7 text-muted">{{ $req->team?->name_th ?? 'ทั้งแผนก' }}</div>
                        </td>
                        <td>{{ $req->creator?->name }}</td>
                        <td><span class="badge bg-secondary">{{ $req->overtimeType?->name_th }}</span></td>
                        <td class="fw-bold text-dark">{{ $req->total_hours }} ชม.</td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $req->employees->count() }} คน</span></td>
                        <td class="fs-7 text-muted">{{ $req->submitted_at ? $req->submitted_at->format('d/m/Y H:i') : '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('overtime.show', $req) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye-fill me-1"></i> ตรวจสอบและอนุมัติ
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle display-4 d-block mb-2 text-success"></i>
                            ไม่มีคำขอ OT ที่รอการอนุมัติในขณะนี้
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $pendingRequests->withQueryString()->links() }}
    </div>
</div>
@endsection
