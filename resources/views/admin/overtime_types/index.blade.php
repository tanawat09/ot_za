@extends('layouts.app')

@section('title', 'ประเภท OT')
@section('header', 'จัดการประเภทการทำงานล่วงเวลา (Overtime Types)')

@section('content')
<div class="card card-custom p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <h5 class="fw-bold font-heading mb-0">
            <i class="bi bi-clock-split text-primary me-2"></i>ประเภท OT ทั้งหมด
        </h5>
        <a href="{{ route('admin.overtime-types.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> เพิ่มประเภท OT ใหม่
        </a>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>รหัสประเภท</th>
                    <th>ชื่อประเภท OT</th>
                    <th>ตัวคูณ OT (Multiplier)</th>
                    <th>ชั่วโมงสูงสุด/วัน</th>
                    <th>ต้องมีเอกสารเซ็น</th>
                    <th>สถานะ</th>
                    <th class="text-end">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($overtimeTypes as $type)
                    <tr>
                        <td class="fw-bold text-primary">{{ $type->code }}</td>
                        <td class="fw-semibold text-dark">{{ $type->name_th }}</td>
                        <td><span class="badge bg-warning text-dark fs-6">{{ $type->multiplier }} เท่า</span></td>
                        <td>{{ $type->max_hours_per_day }} ชม.</td>
                        <td>
                            @if($type->requires_document)
                                <span class="badge bg-info-subtle text-info">ต้องแนบเอกสาร</span>
                            @else
                                <span class="badge bg-light text-muted">ไม่ต้องแนบ</span>
                            @endif
                        </td>
                        <td>
                            @if($type->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.overtime-types.edit', $type) }}" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.overtime-types.destroy', $type) }}" class="d-inline" onsubmit="return confirm('ยืนยันลบประเภท OT นี้?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">ไม่พบข้อมูลประเภท OT</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $overtimeTypes->withQueryString()->links() }}
    </div>
</div>
@endsection
