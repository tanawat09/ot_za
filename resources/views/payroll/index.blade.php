@extends('layouts.app')

@section('title', 'ส่งออกข้อมูลเงินเดือน')
@section('header', 'ศูนย์ส่งออกข้อมูลสำหรับระบบเงินเดือน (Payroll Integration Export)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-3 border-bottom pb-2">
                <i class="bi bi-currency-dollar text-primary me-2"></i>ส่งออกไฟล์สรุปชั่วโมง OT (Payroll File Export)
            </h5>

            <form method="GET" action="{{ route('payroll.export') }}">
                <div class="mb-3">
                    <label for="year" class="form-label font-heading text-dark">เลือกปี พ.ศ. / ค.ศ. <span class="text-danger">*</span></label>
                    <input type="number" name="year" id="year" class="form-control" value="{{ date('Y') }}" required>
                </div>

                <div class="mb-3">
                    <label for="month" class="form-label font-heading text-dark">เลือกเดือน <span class="text-danger">*</span></label>
                    <select name="month" id="month" class="form-select" required>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 10)) }} (เดือน {{ $m }})
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="mb-3">
                    <label for="department_id" class="form-label font-heading text-dark">เลือกแผนก</label>
                    <select name="department_id" id="department_id" class="form-select">
                        <option value="">-- ทุกแผนก (All Departments) --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name_th }} ({{ $dept->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="format" class="form-label font-heading text-dark">รูปแบบไฟล์ <span class="text-danger">*</span></label>
                    <select name="format" id="format" class="form-select" required>
                        <option value="xlsx">Excel File (.xlsx)</option>
                        <option value="csv">CSV File (.csv)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 font-heading fw-bold">
                    <i class="bi bi-download me-1"></i> ดาวน์โหลดไฟล์ Payroll
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
