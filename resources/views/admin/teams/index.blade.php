@extends('layouts.app')

@section('title', 'จัดการทีม')
@section('header', 'จัดการทีม (Team Management)')

@section('content')
<div class="card card-custom p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <h5 class="fw-bold font-heading mb-0">
            <i class="bi bi-diagram-3 text-primary me-2"></i>รายชื่อทีมทั้งหมด
        </h5>
        <a href="{{ route('admin.teams.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> เพิ่มทีมใหม่
        </a>
    </div>

    <!-- Filter & Search Form -->
    <form method="GET" action="{{ route('admin.teams.index') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="ค้นหารหัส หรือชื่อทีม..." value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
            <select name="department_id" class="form-select">
                <option value="">-- แผนกทั้งหมด --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name_th }} ({{ $dept->code }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search"></i> ค้นหา</button>
            <a href="{{ route('admin.teams.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>รหัสทีม</th>
                    <th>ชื่อทีม (ภาษาไทย)</th>
                    <th>แผนกที่สังกัด</th>
                    <th>จำนวนพนักงาน</th>
                    <th>สถานะ</th>
                    <th class="text-end">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teams as $team)
                    <tr>
                        <td class="fw-bold text-primary">{{ $team->code }}</td>
                        <td class="fw-semibold text-dark">{{ $team->name_th }}</td>
                        <td><span class="badge bg-secondary">{{ $team->department?->name_th ?? '-' }}</span></td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $team->employees_count }} คน</span></td>
                        <td>
                            @if($team->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.teams.edit', $team) }}" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" class="d-inline" onsubmit="return confirm('ยืนยันลบทีมนี้?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">ไม่พบข้อมูลทีม</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $teams->withQueryString()->links() }}
    </div>
</div>
@endsection
