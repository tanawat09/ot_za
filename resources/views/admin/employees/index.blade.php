@extends('layouts.app')

@section('title', 'จัดการพนักงาน')
@section('header', 'จัดการพนักงาน (Employee Management)')

@section('content')
<div class="card card-custom p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <h5 class="fw-bold font-heading mb-0">
            <i class="bi bi-person-vcard text-primary me-2"></i>รายชื่อพนักงานทั้งหมด
        </h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.employees.export') }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            <a href="{{ route('admin.employees.import-form') }}" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Excel (พรีวิว)
            </a>
            <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> เพิ่มพนักงานใหม่
            </a>
            <form method="POST" action="{{ route('admin.employees.clear-all') }}" onsubmit="return confirm('⚠️ คำเตือน: คุณต้องการลบข้อมูลพนักงานทั้งหมดในระบบใช่หรือไม่?\n\nการกระทำนี้ไม่สามารถย้อนกลับได้!');" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger" title="ล้างข้อมูลพนักงานทั้งหมดเพื่อเริ่มนำเข้าใหม่">
                    <i class="bi bi-trash-fill me-1"></i> ล้างข้อมูลพนักงานทั้งหมด
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('admin.employees.index') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="ค้นหารหัส, ชื่อ, นามสกุล หรืออีเมล..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="department_id" class="form-select">
                <option value="">-- แผนกทั้งหมด --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name_th }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="team_id" class="form-select">
                <option value="">-- ทีมทั้งหมด --</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>
                        {{ $team->name_th }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search"></i> ค้นหา</button>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>รหัสพนักงาน</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>ตำแหน่ง</th>
                    <th>แผนก / ทีม</th>
                    <th>อีเมล / โทรศัพท์</th>
                    <th>สถานะ</th>
                    <th class="text-end">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                    <tr>
                        <td class="fw-bold text-primary">{{ $emp->emp_code }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $emp->full_name }}</div>
                            @if($emp->user)
                                <span class="badge bg-light text-secondary fs-7 border"><i class="bi bi-person-check"></i> มี User Account</span>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary">{{ $emp->position?->title_th ?? '-' }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $emp->department?->name_th }}</div>
                            <div class="fs-7 text-muted">{{ $emp->team?->name_th ?? 'ไม่ระบุทีม' }}</div>
                        </td>
                        <td class="fs-7">
                            <div><i class="bi bi-envelope text-muted me-1"></i>{{ $emp->email ?? '-' }}</div>
                            <div><i class="bi bi-telephone text-muted me-1"></i>{{ $emp->phone ?? '-' }}</div>
                        </td>
                        <td>
                            @if($emp->status === 'Active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $emp->status }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.employees.edit', $emp) }}" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.employees.destroy', $emp) }}" class="d-inline" onsubmit="return confirm('ยืนยันลบพนักงานคนนี้?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">ไม่พบข้อมูลพนักงาน</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $employees->withQueryString()->links() }}
    </div>
</div>
@endsection
