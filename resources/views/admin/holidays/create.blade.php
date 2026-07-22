@extends('layouts.app')

@section('title', 'เพิ่มวันหยุดใหม่')
@section('header', 'เพิ่มวันหยุดใหม่ (Create Holiday)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-4 border-bottom pb-2">
                <i class="bi bi-plus-circle text-primary me-2"></i>กรอกข้อมูลวันหยุดใหม่
            </h5>

            <form method="POST" action="{{ route('admin.holidays.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="holiday_date" class="form-label font-heading text-dark">วันที่หยุด <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('holiday_date') is-invalid @enderror" id="holiday_date" name="holiday_date" value="{{ old('holiday_date') }}" required>
                    @error('holiday_date')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name_th" class="form-label font-heading text-dark">ชื่อวันหยุด (ภาษาไทย) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name_th') is-invalid @enderror" id="name_th" name="name_th" value="{{ old('name_th') }}" required placeholder="ตัวอย่าง: วันขึ้นปีใหม่, วันสงกรานต์">
                    @error('name_th')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name_en" class="form-label font-heading text-dark">ชื่อวันหยุด (ภาษาอังกฤษ)</label>
                    <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en') }}" placeholder="New Year Day">
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                    <label class="form-check-label font-heading text-dark" for="is_recurring">วันหยุดซ้ำประจำทุกปี (Recurring Yearly)</label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.holidays.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
