@extends('layouts.app')

@section('title', 'ประวัติการใช้งานระบบ')
@section('header', 'ประวัติการใช้งานและตรวจสอบความปลอดภัย (Audit Logs & Security Viewer)')

@section('content')
<div class="card card-custom p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <h5 class="fw-bold font-heading mb-0">
            <i class="bi bi-shield-check text-primary me-2"></i>รายการ Audit Logs ทั้งหมด
        </h5>
        <span class="badge bg-secondary fs-6">รวม {{ $logs->total() }} รายการ</span>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-3 mb-4 p-3 bg-light rounded border">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหา Action หรือ IP..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="user_id" class="form-select form-control-sm">
                <option value="">-- ผู้ใช้งานทั้งหมด --</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }} ({{ $u->emp_code }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-2 text-end">
            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search me-1"></i> ค้นหา</button>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>วัน-เวลา</th>
                    <th>ผู้ใช้งาน</th>
                    <th>การกระทำ (Action)</th>
                    <th>โมดูล (Module)</th>
                    <th>Record ID</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="fs-7 text-muted">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="fw-semibold text-dark">{{ $log->user?->name ?? 'System' }}</td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $log->action }}</span></td>
                        <td><span class="badge bg-secondary">{{ $log->module }}</span></td>
                        <td class="fs-7 text-muted">{{ $log->record_id ?? '-' }}</td>
                        <td class="fs-7 text-dark">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">ไม่พบประวัติการใช้งานระบบ</td>
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
