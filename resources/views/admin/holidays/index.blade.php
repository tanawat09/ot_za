@extends('layouts.app')

@section('title', 'วันหยุดองค์กร')
@section('header', 'จัดการวันหยุดองค์กรและวันหยุดนักขัตฤกษ์ (Holidays)')

@section('content')
<div class="card card-custom p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <h5 class="fw-bold font-heading mb-0">
            <i class="bi bi-calendar-event text-primary me-2"></i>รายการวันหยุดทั้งหมด
        </h5>
        <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> เพิ่มวันหยุดใหม่
        </a>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('admin.holidays.index') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อวันหยุด..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <input type="number" name="year" class="form-control" placeholder="ปี ค.ศ. (เช่น 2026)" value="{{ request('year', date('Y')) }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search"></i> ค้นหา</button>
            <a href="{{ route('admin.holidays.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>วันที่</th>
                    <th>ชื่อวันหยุด (ภาษาไทย)</th>
                    <th>ชื่อวันหยุด (English)</th>
                    <th>ซ้ำประจำทุกปี</th>
                    <th class="text-end">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($holidays as $h)
                    <tr>
                        <td class="fw-bold text-primary">{{ $h->holiday_date->format('d/m/Y') }}</td>
                        <td class="fw-semibold text-dark">{{ $h->name_th }}</td>
                        <td class="text-muted">{{ $h->name_en ?? '-' }}</td>
                        <td>
                            @if($h->is_recurring)
                                <span class="badge bg-info-subtle text-info">ประจำทุกปี</span>
                            @else
                                <span class="badge bg-light text-secondary">ปีเดียว</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.holidays.edit', $h) }}" class="btn btn-sm btn-outline-primary" title="แก้ไข">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.holidays.destroy', $h) }}" class="d-inline" onsubmit="return confirm('ยืนยันลบวันหยุดนี้?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">ไม่พบข้อมูลวันหยุด</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $holidays->withQueryString()->links() }}
    </div>
</div>
@endsection
