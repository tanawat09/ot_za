@extends('layouts.app')

@section('title', 'พรีวิวและตรวจสอบข้อมูลพนักงาน')
@section('header', 'ตรวจสอบข้อมูลก่อนนำเข้า (Preview & Verify Step 2)')

@section('content')
<div class="card card-custom p-4 shadow-sm mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <div>
            <h5 class="fw-bold font-heading mb-0 text-dark">
                <i class="bi bi-shield-check text-success me-2"></i>ตารางพรีวิวและตรวจสอบรายชื่อพนักงานก่อนบันทึก
            </h5>
            <span class="text-muted fs-7">กรุณาตรวจสอบความถูกต้องของข้อมูล รหัสพนักงาน ชื่อ-นามสกุล แผนก และยอดรวมก่อนกดยืนยัน</span>
        </div>

        <form method="POST" action="{{ route('admin.employees.confirm-import') }}">
            @csrf
            <input type="hidden" name="preview_items" value="{{ json_encode($previewRows) }}">
            
            <div class="d-flex gap-2">
                <a href="{{ route('admin.employees.import-form') }}" class="btn btn-outline-secondary font-heading">
                    <i class="bi bi-arrow-left me-1"></i> ยกเลิก / ยกเลิกไฟล์นี้
                </a>
                <button type="submit" class="btn btn-success font-heading fw-bold shadow-sm px-4">
                    <i class="bi bi-check-circle-fill me-1"></i> ยืนยันนำเข้าข้อมูลเข้าสู่ระบบ (Confirm Import)
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3 bg-light border-start border-primary border-4 shadow-sm">
                <div class="text-muted fs-7 font-heading">รวมรายการทั้งหมดที่อ่านได้</div>
                <div class="fs-3 fw-bold text-primary font-heading">{{ count($previewRows) }} <span class="fs-6">คน</span></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 bg-light border-start border-success border-4 shadow-sm">
                <div class="text-muted fs-7 font-heading">พนักงานใหม่ (เพิ่มเข้าระบบ)</div>
                <div class="fs-3 fw-bold text-success font-heading">{{ $newCount }} <span class="fs-6">คน</span></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 bg-light border-start border-warning border-4 shadow-sm">
                <div class="text-muted fs-7 font-heading">พนักงานเดิม (อัปเดตข้อมูล)</div>
                <div class="fs-3 fw-bold text-warning font-heading">{{ $updateCount }} <span class="fs-6">คน</span></div>
            </div>
        </div>
    </div>

    <!-- Preview Table -->
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th class="text-center" style="width: 5%;">#</th>
                    <th style="width: 15%;">รหัสพนักงาน</th>
                    <th style="width: 25%;">ชื่อ-นามสกุล</th>
                    <th style="width: 20%;">แผนก</th>
                    <th style="width: 15%;">ตำแหน่ง</th>
                    <th class="text-end" style="width: 10%;">เงินเดือน</th>
                    <th class="text-center" style="width: 10%;">สถานะนำเข้า</th>
                </tr>
            </thead>
            <tbody>
                @foreach($previewRows as $index => $row)
                    <tr>
                        <td class="text-center text-muted fs-7">{{ $row['line_no'] }}</td>
                        <td class="fw-bold text-primary">{{ $row['emp_code'] }}</td>
                        <td class="fw-semibold text-dark">{{ $row['full_name'] }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $row['department_name'] }}</span></td>
                        <td>{{ $row['position_title'] }}</td>
                        <td class="text-end fw-semibold">{{ number_format($row['salary'], 2) }}</td>
                        <td class="text-center">
                            @if($row['status'] === 'NEW')
                                <span class="badge bg-success"><i class="bi bi-plus-circle me-1"></i> เพิ่มใหม่</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-pencil-square me-1"></i> อัปเดตเดิม</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
