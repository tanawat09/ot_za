@extends('layouts.app')

@section('title', 'จัดการแผนก')
@section('header', 'จัดการแผนก (Department Management)')

@section('content')
<div class="card card-custom p-4 shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <h5 class="fw-bold font-heading mb-0 text-dark">
            <i class="bi bi-building text-primary me-2"></i>รายชื่อแผนกทั้งหมด
        </h5>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.departments.clear-unused') }}" onsubmit="return confirm('⚠️ คุณต้องการลบแผนกที่ไม่มีพนักงานสังกัดอยู่ทั้งหมดใช่หรือไม่?');" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger font-heading" title="ลบแผนกที่ว่างเปล่าไม่มีพนักงาน">
                    <i class="bi bi-eraser-fill me-1"></i> ล้างแผนกที่ไม่มีพนักงาน
                </button>
            </form>
            <a href="{{ route('admin.departments.create') }}" class="btn btn-primary font-heading fw-bold">
                <i class="bi bi-plus-circle me-1"></i> เพิ่มแผนกใหม่
            </a>
        </div>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.departments.index') }}" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="ค้นหารหัส หรือชื่อแผนก..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-secondary w-100 font-heading"><i class="bi bi-search me-1"></i> ค้นหา</button>
            <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 15%;">รหัสแผนก</th>
                    <th style="width: 30%;">ชื่อแผนก (ภาษาไทย)</th>
                    <th style="width: 20%;">ชื่อแผนก (English)</th>
                    <th style="width: 10%;">จำนวนทีม</th>
                    <th style="width: 12%;">จำนวนพนักงาน</th>
                    <th style="width: 8%;">สถานะ</th>
                    <th class="text-end" style="width: 18%;">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                    <tr>
                        <td class="fw-bold text-primary">{{ $dept->code }}</td>
                        <td class="fw-semibold text-dark">{{ $dept->name_th }}</td>
                        <td class="text-muted">{{ $dept->name_en ?? '-' }}</td>
                        <td><span class="badge bg-info text-dark">{{ $dept->teams_count }} ทีม</span></td>
                        <td>
                            @if($dept->employees_count > 0)
                                <span class="badge bg-primary">{{ $dept->employees_count }} คน</span>
                            @else
                                <span class="badge bg-secondary">0 คน</span>
                            @endif
                        </td>
                        <td>
                            @if($dept->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.departments.edit', $dept) }}" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                                <i class="bi bi-pencil"></i> แก้ไข
                            </a>
                            @if($dept->employees_count > 0)
                                <form method="POST" action="{{ route('admin.departments.destroy', $dept) }}" class="d-inline" onsubmit="return confirm('⚠️ แผนกนี้มีพนักงานอยู่ {{ $dept->employees_count }} คน!\n\nต้องการย้ายพนักงานไปแผนกทั่วไปและบังคับลบแผนก &quot;{{ $dept->name_th }}&quot; ใช่หรือไม่?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="force" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="บังคับลบแผนกและย้ายพนักงาน"><i class="bi bi-trash"></i> ลบ</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.departments.destroy', $dept) }}" class="d-inline" onsubmit="return confirm('⚠️ ยืนยันลบแผนก &quot;{{ $dept->name_th }}&quot; ใช่หรือไม่?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบแผนกนี้"><i class="bi bi-trash"></i> ลบ</button>
                                </form>
                            @endif
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
