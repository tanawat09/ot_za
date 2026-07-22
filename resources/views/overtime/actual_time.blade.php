@extends('layouts.app')

@section('title', 'บันทึกเวลาการทำงานจริง')
@section('header', 'บันทึกเวลาปฏิบัติงานจริง (Actual Overtime Recording)')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-4 border-bottom pb-2">
                <i class="bi bi-clock-history text-primary me-2"></i>บันทึกเวลาจริง: {{ $overtime->document_no }}
            </h5>

            <div class="alert alert-info fs-7 mb-4">
                <i class="bi bi-info-circle me-1"></i> กรุณาระบุเวลาที่ปฏิบัติงานจริงของพนักงานแต่ละคน หากไม่ได้ระบุ ระบบจะใช้เวลาตามแผนงานที่ขออนุมัติไว้เป็นค่าเริ่มต้น
            </div>

            <form method="POST" action="{{ route('overtime.actual-time.update', $overtime) }}">
                @csrf

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>รหัสพนักงาน</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>เวลาแผนงาน (Start - End)</th>
                                <th>เวลาเริ่มจริง</th>
                                <th>เวลาเลิกจริง</th>
                                <th>พักจริง (นาที)</th>
                                <th>ชั่วโมงจริง</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overtime->employees as $empReq)
                                <tr>
                                    <td class="fw-bold text-muted">{{ $empReq->employee?->emp_code }}</td>
                                    <td class="fw-semibold text-dark">{{ $empReq->employee?->full_name }}</td>
                                    <td class="fs-7 text-secondary">
                                        {{ substr($empReq->start_time ?? $overtime->start_time, 0, 5) }} - {{ substr($empReq->end_time ?? $overtime->end_time, 0, 5) }}
                                        ({{ $empReq->planned_hours }} ชม.)
                                    </td>
                                    <td>
                                        <input type="time" name="actual[{{ $empReq->id }}][start_time]" class="form-control form-control-sm" value="{{ substr($empReq->actual_start_time ?? $overtime->start_time, 0, 5) }}">
                                    </td>
                                    <td>
                                        <input type="time" name="actual[{{ $empReq->id }}][end_time]" class="form-control form-control-sm" value="{{ substr($empReq->actual_end_time ?? $overtime->end_time, 0, 5) }}">
                                    </td>
                                    <td>
                                        <input type="number" name="actual[{{ $empReq->id }}][break_minutes]" class="form-control form-control-sm" value="{{ $empReq->actual_break_minutes ?? $overtime->break_minutes }}" min="0" step="15">
                                    </td>
                                    <td class="fw-bold text-primary text-center">
                                        {{ $empReq->actual_hours ?? $empReq->planned_hours }} ชม.
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between border-top pt-3">
                    <a href="{{ route('overtime.show', $overtime) }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> บันทึกเวลาปฏิบัติงานจริง</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
