@extends('layouts.app')

@section('title', 'คำนวณเงินเดือน และจ่ายโอที')
@section('header', 'คำนวณเงินเดือน และสรุปค่าตอบแทนจ่ายโอที (Payroll & OT Payment Sheet)')

@section('content')
<div class="card card-custom p-4 shadow-sm mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom pb-3">
        <div>
            <h5 class="fw-bold font-heading mb-0 text-dark">
                <i class="bi bi-cash-stack text-success me-2"></i>สรุปรายการจ่ายเงินเดือน และค่าตอบแทน OT ประจำงวด
            </h5>
            <span class="text-muted fs-7">คำนวณค่าโอทีตามอัตราเรทกฎหมายแรงงาน (1.5 เท่า, 3.0 เท่า, 1.0 เท่า) พร้อมส่งออกเข้าสู่ระบบเงินเดือน</span>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('payroll.index') }}" class="row g-3 mb-4 p-3 bg-light rounded border">
        <div class="col-md-3">
            <label class="form-label fs-7 font-heading text-dark fw-bold">เลือกปี</label>
            <select name="year" class="form-select form-select-sm">
                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>ปี {{ $y }}</option>
                @endfor
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label fs-7 font-heading text-dark fw-bold">เลือกเดือน</label>
            <select name="month" class="form-select form-select-sm">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>เดือน {{ $m }}</option>
                @endfor
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label fs-7 font-heading text-dark fw-bold">แผนก</label>
            <select name="department_id" class="form-select form-select-sm">
                <option value="">-- ทุกแผนก --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name_th }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2 align-items-end">
            <button type="submit" class="btn btn-sm btn-primary w-100 font-heading fw-bold">
                <i class="bi bi-calculator me-1"></i> คำนวณยอดเงิน
            </button>
        </div>
    </form>

    <!-- Summary Metrics Grid -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-start border-primary border-4 bg-light shadow-sm">
                <div class="text-muted fs-7 font-heading">รวมฐานเงินเดือนพนักงาน</div>
                <div class="fs-3 fw-bold text-dark font-heading">{{ number_format($payrollData['totals']['total_base_salary'], 2) }} <span class="fs-6">บาท</span></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 border-start border-warning border-4 bg-light shadow-sm">
                <div class="text-muted fs-7 font-heading">รวมค่าตอบแทน OT (1.5 เท่า)</div>
                <div class="fs-3 fw-bold text-warning font-heading">{{ number_format($payrollData['totals']['total_ot_pay_1_5'], 2) }} <span class="fs-6">บาท</span></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 border-start border-danger border-4 bg-light shadow-sm">
                <div class="text-muted fs-7 font-heading">รวมค่าตอบแทน OT (3.0 เท่า)</div>
                <div class="fs-3 fw-bold text-danger font-heading">{{ number_format($payrollData['totals']['total_ot_pay_3_0'], 2) }} <span class="fs-6">บาท</span></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 border-start border-success border-4 bg-light shadow-sm">
                <div class="text-muted fs-7 font-heading">รวมจ่ายสุทธิ (เงินเดือน + OT)</div>
                <div class="fs-3 fw-bold text-success font-heading">{{ number_format($payrollData['totals']['grand_net_pay'], 2) }} <span class="fs-6">บาท</span></div>
            </div>
        </div>
    </div>

    <!-- Export Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold font-heading text-dark mb-0">ตารางแสดงรายการจ่ายเงินรายบุคคล</h6>
        
        <form method="POST" action="{{ route('payroll.export') }}" class="d-flex gap-2">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="department_id" value="{{ $departmentId }}">

            <button type="submit" name="format" value="xlsx" class="btn btn-sm btn-outline-success font-heading fw-bold">
                <i class="bi bi-file-earmark-excel me-1"></i> ส่งออกไฟล์ Excel (.xlsx)
            </button>
            <button type="submit" name="format" value="csv" class="btn btn-sm btn-outline-secondary font-heading fw-bold">
                <i class="bi bi-file-earmark-text me-1"></i> ส่งออกไฟล์ CSV (.csv)
            </button>
        </form>
    </div>

    <!-- Calculation Table -->
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr class="text-center align-middle">
                    <th>รหัสพนักงาน</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>แผนก / ตำแหน่ง</th>
                    <th>ฐานเงินเดือน (บาท)</th>
                    <th>ค่าจ้าง (บาท/ชม.)</th>
                    <th>ชั่วโมง OT (1.5x)</th>
                    <th>ชั่วโมง OT (3.0x)</th>
                    <th>ค่า OT 1.5x (บาท)</th>
                    <th>ค่า OT 3.0x (บาท)</th>
                    <th>รวมเงินค่า OT (บาท)</th>
                    <th>รวมรายรับสุทธิ (บาท)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrollData['employees'] as $emp)
                    <tr>
                        <td class="text-center fw-bold">{{ $emp['emp_code'] }}</td>
                        <td class="fw-semibold text-dark">{{ $emp['full_name'] }}</td>
                        <td>
                            <div class="fs-7 fw-semibold">{{ $emp['department_name'] }}</div>
                            <div class="fs-7 text-muted">{{ $emp['position_title'] }}</div>
                        </td>
                        <td class="text-end fw-semibold">{{ number_format($emp['base_salary'], 2) }}</td>
                        <td class="text-end text-muted fs-7">{{ number_format($emp['hourly_rate'], 2) }}</td>
                        <td class="text-center font-monospace">{{ $emp['hours_1_5'] }} ชม.</td>
                        <td class="text-center font-monospace">{{ $emp['hours_3_0'] }} ชม.</td>
                        <td class="text-end text-warning fw-semibold">{{ number_format($emp['ot_pay_1_5'], 2) }}</td>
                        <td class="text-end text-danger fw-semibold">{{ number_format($emp['ot_pay_3_0'], 2) }}</td>
                        <td class="text-end text-primary fw-bold">{{ number_format($emp['total_ot_pay'], 2) }}</td>
                        <td class="text-end text-success fw-bold">{{ number_format($emp['net_pay'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-muted">ไม่พบข้อมูลพนักงานในงวดเดือนนี้</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-secondary fw-bold">
                <tr>
                    <td colspan="3" class="text-end">รวมยอดทั้งหมด (Grand Total):</td>
                    <td class="text-end">{{ number_format($payrollData['totals']['total_base_salary'], 2) }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-end text-warning">{{ number_format($payrollData['totals']['total_ot_pay_1_5'], 2) }}</td>
                    <td class="text-end text-danger">{{ number_format($payrollData['totals']['total_ot_pay_3_0'], 2) }}</td>
                    <td class="text-end text-primary">{{ number_format($payrollData['totals']['grand_total_ot_pay'], 2) }}</td>
                    <td class="text-end text-success fs-6">{{ number_format($payrollData['totals']['grand_net_pay'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
