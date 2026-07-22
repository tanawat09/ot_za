@extends('layouts.app')

@section('title', 'สร้างคำขอ OT ใหม่')
@section('header', 'สร้างคำขอ OT ใหม่ (Create Overtime Request)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-9">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-4 border-bottom pb-2">
                <i class="bi bi-file-earmark-plus text-primary me-2"></i>กรอกข้อมูลคำขอทำงานล่วงเวลา (OT)
            </h5>

            <form method="POST" action="{{ route('overtime.store') }}" id="otForm">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="department_id" class="form-label font-heading text-dark">แผนก <span class="text-danger">*</span></label>
                        <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                            <option value="">-- เลือกแผนก --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name_th }} ({{ $dept->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="team_id" class="form-label font-heading text-dark">ทีม (ถ้ามี)</label>
                        <select name="team_id" id="team_id" class="form-select">
                            <option value="">-- เลือกทีม --</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                    {{ $team->name_th }} ({{ $team->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="overtime_type_id" class="form-label font-heading text-dark">ประเภท OT <span class="text-danger">*</span></label>
                        <select name="overtime_type_id" id="overtime_type_id" class="form-select @error('overtime_type_id') is-invalid @enderror" required>
                            <option value="">-- เลือกประเภท OT --</option>
                            @foreach($overtimeTypes as $type)
                                <option value="{{ $type->id }}" {{ old('overtime_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name_th }} (ตัวคูณ {{ $type->multiplier }} เท่า, สูงสุด {{ $type->max_hours_per_day }} ชม.)
                                </option>
                            @endforeach
                        </select>
                        @error('overtime_type_id')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="request_date" class="form-label font-heading text-dark">วันที่ทำ OT <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('request_date') is-invalid @enderror" id="request_date" name="request_date" value="{{ old('request_date', date('Y-m-d')) }}" required>
                        @error('request_date')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="start_time" class="form-label font-heading text-dark">เวลาเริ่ม <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time', '17:30') }}" required>
                        @error('start_time')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="end_time" class="form-label font-heading text-dark">เวลาเลิก <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time', '20:30') }}" required>
                        @error('end_time')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="break_minutes" class="form-label font-heading text-dark">หักเวลาพัก (นาที)</label>
                        <input type="number" class="form-control" id="break_minutes" name="break_minutes" value="{{ old('break_minutes', 0) }}" min="0" step="15">
                    </div>

                    <!-- Calculated Hours Summary Card -->
                    <div class="col-12">
                        <div class="p-3 bg-light rounded border d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-semibold text-dark">ชั่วโมง OT คำนวณเบื้องต้น: </span>
                                <span id="calculated_hours" class="fw-bold text-primary fs-5">3.00</span> ชั่วโมง
                                <span id="cross_midnight_badge" class="badge bg-warning text-dark ms-2 d-none">ข้ามคืน (Cross-Midnight)</span>
                            </div>
                            <div class="text-muted fs-7">ระบบหักเวลาพักและคำนวณอัตโนมัติ</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="location" class="form-label font-heading text-dark">สถานที่ปฏิบัติงาน</label>
                        <input type="text" class="form-control" id="location" name="location" value="{{ old('location') }}" placeholder="ตัวอย่าง: อาคาร A ชั้น 4 / Server Room">
                    </div>

                    <div class="col-md-6">
                        <label for="reason" class="form-label font-heading text-dark">เหตุผลในการทำ OT <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" value="{{ old('reason') }}" required placeholder="ตัวอย่าง: ปรับปรุงระบบฐานข้อมูลประจำเดือน">
                        @error('reason')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="work_details" class="form-label font-heading text-dark">รายละเอียดงานเพิ่มเติม</label>
                        <textarea class="form-control" id="work_details" name="work_details" rows="2" placeholder="รายละเอียดของงานที่จะดำเนินการ...">{{ old('work_details') }}</textarea>
                    </div>
                </div>

                <!-- Employee Multi-Selection Section -->
                <div class="border-top pt-3 mb-4">
                    <h6 class="fw-bold font-heading text-dark mb-3">
                        <i class="bi bi-people-fill text-primary me-2"></i>เลือกพนักงานที่ทำ OT <span class="text-danger">*</span>
                    </h6>

                    @error('employees')
                        <div class="alert alert-danger fs-7 py-2">{{ $message }}</div>
                    @enderror

                    <div class="row g-2 mb-3 align-items-center">
                        <div class="col-md-6">
                            <input type="text" id="empSearch" class="form-control form-control-sm" placeholder="ค้นหาชื่อ หรือรหัสพนักงาน...">
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary me-1" id="selectAllBtn">เลือกทั้งหมด</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">ยกเลิกทั้งหมด</button>
                        </div>
                    </div>

                    <div class="border rounded p-3 bg-white" style="max-height: 250px; overflow-y: auto;">
                        <div class="row g-2" id="employeeList">
                            @foreach($employees as $emp)
                                <div class="col-md-6 emp-item">
                                    <div class="form-check p-2 rounded hover-bg border">
                                        <input class="form-check-input emp-checkbox" type="checkbox" name="employees[]" value="{{ $emp->id }}" id="emp_{{ $emp->id }}" {{ is_array(old('employees')) && in_array($emp->id, old('employees')) ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 cursor-pointer" for="emp_{{ $emp->id }}">
                                            <span class="fw-semibold text-dark">{{ $emp->full_name }}</span>
                                            <span class="text-muted fs-7">({{ $emp->emp_code }} - {{ $emp->department?->name_th }})</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-2 text-muted fs-7">
                        เลือกแล้ว <span id="selectedCount" class="fw-bold text-primary">0</span> คน
                    </div>
                </div>

                <div class="d-flex justify-content-between border-top pt-3">
                    <a href="{{ route('overtime.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> บันทึกร่างคำขอ OT (Draft)</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');
        const breakInput = document.getElementById('break_minutes');
        const calcHoursEl = document.getElementById('calculated_hours');
        const crossMidnightBadge = document.getElementById('cross_midnight_badge');

        function updateHours() {
            const startVal = startTimeInput.value;
            const endVal = endTimeInput.value;
            const breakVal = parseInt(breakInput.value) || 0;

            if (!startVal || !endVal) return;

            const [sH, sM] = startVal.split(':').map(Number);
            const [eH, eM] = endVal.split(':').map(Number);

            let sMin = sH * 60 + sM;
            let eMin = eH * 60 + eM;
            let isCross = false;

            if (eMin <= sMin) {
                eMin += 24 * 60;
                isCross = true;
            }

            let netMin = Math.max(0, (eMin - sMin) - breakVal);
            let hours = (netMin / 60).toFixed(2);

            calcHoursEl.textContent = hours;
            if (isCross) {
                crossMidnightBadge.classList.remove('d-none');
            } else {
                crossMidnightBadge.classList.add('d-none');
            }
        }

        startTimeInput.addEventListener('change', updateHours);
        endTimeInput.addEventListener('change', updateHours);
        breakInput.addEventListener('input', updateHours);
        updateHours();

        // Search & Multi-select
        const empSearch = document.getElementById('empSearch');
        const empItems = document.querySelectorAll('.emp-item');
        const checkboxes = document.querySelectorAll('.emp-checkbox');
        const selectedCount = document.getElementById('selectedCount');

        function updateSelectedCount() {
            const checked = document.querySelectorAll('.emp-checkbox:checked').length;
            selectedCount.textContent = checked;
        }

        empSearch.addEventListener('keyup', function() {
            const q = this.value.toLowerCase();
            empItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(q) ? '' : 'none';
            });
        });

        document.getElementById('selectAllBtn').addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = true);
            updateSelectedCount();
        });

        document.getElementById('deselectAllBtn').addEventListener('click', function() {
            checkboxes.forEach(cb => cb.checked = false);
            updateSelectedCount();
        });

        checkboxes.forEach(cb => cb.addEventListener('change', updateSelectedCount));
        updateSelectedCount();
    });
</script>
@endpush
@endsection
