@extends('layouts.app')

@section('title', 'จัดการแผนก')
@section('header', 'จัดการแผนก (Department Management)')

@section('content')
<div class="card card-custom p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <h5 class="fw-bold font-heading mb-0">
            <i class="bi bi-building text-primary me-2"></i>รายชื่อแผนกทั้งหมด
        </h5>
        <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> เพิ่มแผนกใหม่
        </a>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.departments.index') }}" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="ค้นหารหัส หรือชื่อแผนก..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search"></i> ค้นหา</button>
            <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>รหัสแผนก</th>
                    <th>ชื่อแผนก (ภาษาไทย)</th>
                    <th>ชื่อแผนก (English)</th>
                    <th>จำนวนทีม</th>
                    <th>จำนวนพนักงาน</th>
                    <th>สถานะ</th>
                    <th class="text-end">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                    <tr>
                        <td class="fw-bold text-primary">{{ $dept->code }}</td>
                        <td class="fw-semibold text-dark">{{ $dept->name_th }}</td>
                        <td class="text-muted">{{ $dept->name_en ?? '-' }}</td>
                        <td><span class="badge bg-info-subtle text-info">{{ $dept->teams_count }} ทีม</span></td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $dept->employees_count }} คน</span></td>
                        <td>
                            @if($dept->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.departments.edit', $dept) }}" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.departments.destroy', $dept) }}" class="d-inline" onsubmit="return confirm('ยืนยันลบแผนกนี้?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">ไม่พบข้อมูลแผนก</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $departments->withQueryString()->links() }}
    </div>
</div>
@endsection
