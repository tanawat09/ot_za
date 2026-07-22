@extends('layouts.app')

@section('title', $reportTitle)
@section('header', $reportTitle)

@section('content')
<div class="card card-custom p-4 mb-4">
    <!-- Header & Export Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <div>
            <a href="{{ route('reports.index') }}" class="text-decoration-none fs-7 text-muted">
                <i class="bi bi-arrow-left"></i> กลับไปศูนย์รายงาน
            </a>
            <h4 class="fw-bold font-heading text-dark mb-0 mt-1">{{ $reportTitle }}</h4>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('reports.export-excel', array_merge(['type' => $type], request()->all())) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            <a href="{{ route('reports.export-pdf', array_merge(['type' => $type], request()->all())) }}" class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('reports.show', $type) }}" class="row g-3 mb-4 p-3 bg-light rounded border">
        <div class="col-md-3">
            <label class="form-label fs-7 font-heading text-dark">ตั้งแต่วันที่</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fs-7 font-heading text-dark">ถึงวันที่</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fs-7 font-heading text-dark">แผนก</label>
            <select name="department_id" class="form-select form-control-sm">
                <option value="">-- ทุกแผนก --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name_th }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fs-7 font-heading text-dark">สถานะ</label>
            <select name="status" class="form-select form-control-sm">
                <option value="">-- ทุกสถานะ --</option>
                @foreach(\App\Enums\OvertimeStatus::cases() as $st)
                    <option value="{{ $st->value }}" {{ request('status') == $st->value ? 'selected' : '' }}>
                        {{ $st->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-sm btn-primary px-4"><i class="bi bi-filter me-1"></i> กรองข้อมูล</button>
            <a href="{{ route('reports.show', $type) }}" class="btn btn-sm btn-outline-secondary">ล้างค่า</a>
        </div>
    </form>

    <!-- Report Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle border">
            <thead class="table-light">
                <tr>
                    <th>เลขที่เอกสาร</th>
                    <th>วันที่ทำ OT</th>
                    <th>แผนก / ทีม</th>
                    <th>ประเภท OT</th>
                    <th>ช่วงเวลา</th>
                    <th>พนักงาน (คน)</th>
                    <th>ชั่วโมงรวม</th>
                    <th>สถานะ</th>
                    <th>ผู้สร้าง / ผู้อนุมัติ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $row)
                    @if($row instanceof \App\Models\OvertimeRequest)
                        <tr>
                            <td class="fw-bold text-primary">{{ $row->document_no }}</td>
                            <td>{{ $row->request_date->format('d/m/Y') }}</td>
                            <td>{{ $row->department?->name_th }} ({{ $row->team?->name_th ?? 'ทั้งแผนก' }})</td>
                            <td><span class="badge bg-secondary">{{ $row->overtimeType?->name_th }}</span></td>
                            <td class="fs-7">{{ substr($row->start_time, 0, 5) }} - {{ substr($row->end_time, 0, 5) }} น.</td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ $row->employees->count() }} คน</span></td>
                            <td class="fw-bold text-dark">{{ $row->total_hours }} ชม.</td>
                            <td><span class="badge {{ $row->status->badgeClass() }}">{{ $row->status->label() }}</span></td>
                            <td class="fs-7">
                                <div>สร้าง: {{ $row->creator?->name }}</div>
                                <div class="text-muted">อนุมัติ: {{ $row->manager?->name ?? '-' }}</div>
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="9" class="fs-7 text-muted">{{ json_encode($row) }}</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">ไม่พบข้อมูลรายงานตามเงื่อนไขที่ระบุ</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($reportData, 'links'))
        <div class="mt-3">
            {{ $reportData->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
