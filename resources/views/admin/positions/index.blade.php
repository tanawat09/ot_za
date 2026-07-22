@extends('layouts.app')

@section('title', 'จัดการตำแหน่งงาน')
@section('header', 'จัดการตำแหน่งงาน (Position Management)')

@section('content')
<div class="card card-custom p-4 shadow-sm">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <h5 class="fw-bold font-heading mb-0 text-dark">
            <i class="bi bi-briefcase text-primary me-2"></i>รายชื่อตำแหน่งงานทั้งหมด
        </h5>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.positions.clear-unused') }}" onsubmit="return confirm('⚠️ คุณต้องการลบตำแหน่งงานที่ไม่มีพนักงานสังกัดอยู่ทั้งหมดใช่หรือไม่?');" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger font-heading" title="ลบตำแหน่งงานที่ว่างเปล่าไม่มีพนักงาน">
                    <i class="bi bi-eraser-fill me-1"></i> ล้างตำแหน่งที่ไม่มีพนักงาน
                </button>
            </form>
            <a href="{{ route('admin.positions.create') }}" class="btn btn-primary font-heading fw-bold">
                <i class="bi bi-plus-circle me-1"></i> เพิ่มตำแหน่งใหม่
            </a>
        </div>
    </div>

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.positions.index') }}" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="ค้นหารหัส หรือชื่อตำแหน่ง..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-secondary w-100 font-heading"><i class="bi bi-search me-1"></i> ค้นหา</button>
            <a href="{{ route('admin.positions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 20%;">รหัสตำแหน่ง</th>
                    <th style="width: 35%;">ชื่อตำแหน่ง (ภาษาไทย)</th>
                    <th style="width: 20%;">ชื่อตำแหน่ง (English)</th>
                    <th style="width: 15%;">จำนวนพนักงาน</th>
                    <th style="width: 10%;">สถานะ</th>
                    <th class="text-end" style="width: 18%;">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($positions as $pos)
                    <tr>
                        <td class="fw-bold text-primary">{{ $pos->code }}</td>
                        <td class="fw-semibold text-dark">{{ $pos->title_th }}</td>
                        <td class="text-muted">{{ $pos->title_en ?? '-' }}</td>
                        <td>
                            @if($pos->employees_count > 0)
                                <span class="badge bg-primary">{{ $pos->employees_count }} คน</span>
                            @else
                                <span class="badge bg-secondary">0 คน</span>
                            @endif
                        </td>
                        <td>
                            @if($pos->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.positions.edit', $pos) }}" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                                <i class="bi bi-pencil"></i> แก้ไข
                            </a>
                            @if($pos->employees_count > 0)
                                <form method="POST" action="{{ route('admin.positions.destroy', $pos) }}" class="d-inline" onsubmit="return confirm('⚠️ ตำแหน่งนี้มีพนักงานอยู่ {{ $pos->employees_count }} คน!\n\nต้องการถอดตำแหน่งออกแล้วบังคับลบตำแหน่ง &quot;{{ $pos->title_th }}&quot; ใช่หรือไม่?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="force" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="บังคับลบตำแหน่ง"><i class="bi bi-trash"></i> ลบ</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.positions.destroy', $pos) }}" class="d-inline" onsubmit="return confirm('⚠️ ยืนยันลบตำแหน่ง &quot;{{ $pos->title_th }}&quot; ใช่หรือไม่?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบตำแหน่งนี้"><i class="bi bi-trash"></i> ลบ</button>
                                </form>
                            @endif
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
