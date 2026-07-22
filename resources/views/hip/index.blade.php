@extends('layouts.app')

@section('title', 'ประวัติสแกนนิ้ว HIP Premium Time')
@section('header', 'ข้อมูลสแกนเวลาปฏิบัติงาน HIP Premium Time')

@section('content')
<div class="card card-custom p-4 shadow-sm mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <div>
            <h5 class="fw-bold font-heading mb-0 text-dark">
                <i class="bi bi-clock-history text-primary me-2"></i>รายการสแกนนิ้ว/ใบหน้าจาก HIP Premium Time
            </h5>
            <span class="text-muted fs-7">บันทึกสแกนเวลาเข้า-ออกและสถานะการจับคู่กับคำขอ OT</span>
        </div>
        <a href="{{ route('hip.create') }}" class="btn btn-primary font-heading fw-bold shadow-sm">
            <i class="bi bi-file-earmark-arrow-up me-1"></i> นำเข้าข้อมูล HIP สแกนนิ้ว
        </a>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('hip.index') }}" class="row g-3 mb-4 p-3 bg-light rounded border">
        <div class="col-md-4">
            <label class="form-label fs-7 font-heading text-dark fw-bold">ค้นหารหัส/ชื่อพนักงาน</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="รหัส หรือชื่อพนักงาน..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fs-7 font-heading text-dark fw-bold">ตั้งแต่วันที่</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fs-7 font-heading text-dark fw-bold">ถึงวันที่</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-2 d-flex gap-2 align-items-end">
            <button type="submit" class="btn btn-sm btn-primary w-100 font-heading fw-bold">
                <i class="bi bi-search me-1"></i> ค้นหา
            </button>
            <a href="{{ route('hip.index') }}" class="btn btn-sm btn-outline-secondary" title="ล้างค่า">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>วันที่สแกน</th>
                    <th>รหัส HIP</th>
                    <th>ชื่อ-นามสกุล พนักงาน</th>
                    <th>แผนก</th>
                    <th class="text-center">เวลาสแกนเข้า</th>
                    <th class="text-center">เวลาสแกนออก</th>
                    <th class="text-center">รหัสเครื่องสแกน</th>
                    <th class="text-center">ล็อตการนำเข้า</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="fw-bold text-dark">{{ $log->log_date->format('d/m/Y') }}</td>
                        <td><span class="badge bg-secondary font-monospace">{{ $log->emp_code }}</span></td>
                        <td class="fw-semibold text-primary">
                            {{ $log->employee?->full_name ?? 'ไม่พบบัญชีพนักงานในระบบ' }}
                        </td>
                        <td>{{ $log->employee?->department?->name_th ?? '-' }}</td>
                        <td class="text-center fw-bold text-success">{{ $log->check_in ? substr($log->check_in, 0, 5) . ' น.' : '-' }}</td>
                        <td class="text-center fw-bold text-danger">{{ $log->check_out ? substr($log->check_out, 0, 5) . ' น.' : '-' }}</td>
                        <td class="text-center fs-7"><span class="badge bg-light text-dark border">{{ $log->device_id ?? 'HIP-01' }}</span></td>
                        <td class="text-center fs-7 text-muted">{{ $log->import_batch ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-4 d-block mb-2 text-secondary"></i>
                            ยังไม่มีข้อมูลสแกนจาก HIP Premium Time ในระบบ
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $logs->withQueryString()->links() }}
    </div>
</div>
@endsection
