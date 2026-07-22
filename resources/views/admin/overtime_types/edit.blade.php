@extends('layouts.app')

@section('title', 'แก้ไขประเภท OT')
@section('header', 'แก้ไขประเภท OT (Edit Overtime Type)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-4 border-bottom pb-2">
                <i class="bi bi-pencil-square text-primary me-2"></i>แก้ไขประเภท OT: {{ $overtimeType->name_th }}
            </h5>

            <form method="POST" action="{{ route('admin.overtime-types.update', $overtimeType) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="code" class="form-label font-heading text-dark">รหัสประเภท OT <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $overtimeType->code) }}" required>
                    @error('code')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name_th" class="form-label font-heading text-dark">ชื่อประเภท OT <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name_th') is-invalid @enderror" id="name_th" name="name_th" value="{{ old('name_th', $overtimeType->name_th) }}" required>
                    @error('name_th')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="multiplier" class="form-label font-heading text-dark">ตัวคูณ OT (Multiplier) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control @error('multiplier') is-invalid @enderror" id="multiplier" name="multiplier" value="{{ old('multiplier', $overtimeType->multiplier) }}" required>
                    @error('multiplier')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="max_hours_per_day" class="form-label font-heading text-dark">จำนวนชั่วโมงสูงสุดต่อวัน <span class="text-danger">*</span></label>
                    <input type="number" step="0.5" class="form-control @error('max_hours_per_day') is-invalid @enderror" id="max_hours_per_day" name="max_hours_per_day" value="{{ old('max_hours_per_day', $overtimeType->max_hours_per_day) }}" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="requires_document" name="requires_document" value="1" {{ old('requires_document', $overtimeType->requires_document) ? 'checked' : '' }}>
                    <label class="form-check-label font-heading text-dark" for="requires_document">ต้องแนบเอกสารยินยอมที่พนักงานลงนาม</label>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $overtimeType->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label font-heading text-dark" for="is_active">เปิดใช้งานประเภท OT นี้</label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.overtime-types.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> อัปเดตข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
