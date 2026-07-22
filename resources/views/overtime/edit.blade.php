@extends('layouts.app')

@section('title', 'แก้ไขคำขอ OT')
@section('header', 'แก้ไขคำขอ OT (Edit Overtime Request)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-9">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-4 border-bottom pb-2">
                <i class="bi bi-pencil-square text-primary me-2"></i>แก้ไขคำขอ OT: {{ $overtime->document_no }}
            </h5>

            <form method="POST" action="{{ route('overtime.update', $overtime) }}" id="otForm">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="department_id" class="form-label font-heading text-dark">แผนก <span class="text-danger">*</span></label>
                        <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                            <option value="">-- เลือกแผนก --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $overtime->department_id) == $dept->id ? 'selected' : '' }}>
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
                                <option value="{{ $team->id }}" {{ old('team_id', $overtime->team_id) == $team->id ? 'selected' : '' }}>
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
                                <option value="{{ $type->id }}" {{ old('overtime_type_id', $overtime->overtime_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name_th }} (ตัวคูณ {{ $type->multiplier }} เท่า)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="request_date" class="form-label font-heading text-dark">วันที่ทำ OT <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('request_date') is-invalid @enderror" id="request_date" name="request_date" value="{{ old('request_date', $overtime->request_date->format('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="start_time" class="form-label font-heading text-dark">เวลาเริ่ม <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="start_time" name="start_time" value="{{ old('start_time', substr($overtime->start_time, 0, 5)) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="end_time" class="form-label font-heading text-dark">เวลาเลิก <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="end_time" name="end_time" value="{{ old('end_time', substr($overtime->end_time, 0, 5)) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="break_minutes" class="form-label font-heading text-dark">หักเวลาพัก (นาที)</label>
                        <input type="number" class="form-control" id="break_minutes" name="break_minutes" value="{{ old('break_minutes', $overtime->break_minutes) }}" min="0" step="15">
                    </div>

                    <div class="col-12">
                        <div class="p-3 bg-light rounded border d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-semibold text-dark">ชั่วโมง OT คำนวณเบื้องต้น: </span>
                                <span id="calculated_hours" class="fw-bold text-primary fs-5">{{ $overtime->total_hours }}</span> ชั่วโมง
                                <span id="cross_midnight_badge" class="badge bg-warning text-dark ms-2 {{ $overtime->is_cross_midnight ? '' : 'd-none' }}">ข้ามคืน (Cross-Midnight)</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="location" class="form-label font-heading text-dark">สถานที่ปฏิบัติงาน</label>
                        <input type="text" class="form-control" id="location" name="location" value="{{ old('location', $overtime->location) }}">
                    </div>

                    <div class="col-md-6">
                        <label for="reason" class="form-label font-heading text-dark">เหตุผลในการทำ OT <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="reason" name="reason" value="{{ old('reason', $overtime->reason) }}" required>
                    </div>

                    <div class="col-12">
                        <label for="work_details" class="form-label font-heading text-dark">รายละเอียดงานเพิ่มเติม</label>
                        <textarea class="form-control" id="work_details" name="work_details" rows="2">{{ old('work_details', $overtime->work_details) }}</textarea>
                    </div>
                </div>

                <!-- Employee Multi-Selection Section -->
                <div class="border-top pt-3 mb-4">
                    <h6 class="fw-bold font-heading text-dark mb-3">
                        <i class="bi bi-people-fill text-primary me-2"></i>เลือกพนักงานที่ทำ OT <span class="text-danger">*</span>
                    </h6>

                    <div class="border rounded p-3 bg-white" style="max-height: 250px; overflow-y: auto;">
                        <div class="row g-2">
                            @foreach($employees as $emp)
                                <div class="col-md-6">
                                    <div class="form-check p-2 rounded hover-bg border">
                                        <input class="form-check-input emp-checkbox" type="checkbox" name="employees[]" value="{{ $emp->id }}" id="emp_{{ $emp->id }}" {{ in_array($emp->id, old('employees', $selectedEmployees)) ? 'checked' : '' }}>
                                        <label class="form-check-label w-100 cursor-pointer" for="emp_{{ $emp->id }}">
                                            <span class="fw-semibold text-dark">{{ $emp->full_name }}</span>
                                            <span class="text-muted fs-7">({{ $emp->emp_code }})</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between border-top pt-3">
                    <a href="{{ route('overtime.show', $overtime) }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> อัปเดตข้อมูลคำขอ OT</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
