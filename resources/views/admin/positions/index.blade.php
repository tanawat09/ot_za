@extends('layouts.app')

@section('title', 'จัดการตำแหน่งงาน')
@section('header', 'จัดการตำแหน่งงาน (Position Management)')

@section('content')
<div class="card card-custom p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <h5 class="fw-bold font-heading mb-0">
            <i class="bi bi-briefcase text-primary me-2"></i>รายชื่อตำแหน่งงานทั้งหมด
        </h5>
        <a href="{{ route('admin.positions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> เพิ่มตำแหน่งใหม่
        </a>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.positions.index') }}" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="ค้นหารหัส หรือชื่อตำแหน่ง..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search"></i> ค้นหา</button>
            <a href="{{ route('admin.positions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>รหัสตำแหน่ง</th>
                    <th>ชื่อตำแหน่ง (ภาษาไทย)</th>
                    <th>ชื่อตำแหน่ง (English)</th>
                    <th>จำนวนพนักงาน</th>
                    <th>สถานะ</th>
                    <th class="text-end">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($positions as $pos)
                    <tr>
                        <td class="fw-bold text-primary">{{ $pos->code }}</td>
                        <td class="fw-semibold text-dark">{{ $pos->title_th }}</td>
                        <td class="text-muted">{{ $pos->title_en ?? '-' }}</td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $pos->employees_count }} คน</span></td>
                        <td>
                            @if($pos->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.positions.edit', $pos) }}" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.positions.destroy', $pos) }}" class="d-inline" onsubmit="return confirm('ยืนยันลบตำแหน่งนี้?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">ไม่พบข้อมูลตำแหน่งงาน</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $positions->withQueryString()->links() }}
    </div>
</div>
@endsection
