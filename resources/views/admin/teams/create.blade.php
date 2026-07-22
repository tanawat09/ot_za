@extends('layouts.app')

@section('title', 'เพิ่มทีมใหม่')
@section('header', 'เพิ่มทีมใหม่ (Create Team)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-4 border-bottom pb-2">
                <i class="bi bi-plus-circle text-primary me-2"></i>กรอกข้อมูลทีมใหม่
            </h5>

            <form method="POST" action="{{ route('admin.teams.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="department_id" class="form-label font-heading text-dark">แผนกสังกัด <span class="text-danger">*</span></label>
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

                <div class="mb-3">
                    <label for="code" class="form-label font-heading text-dark">รหัสทีม <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required placeholder="ตัวอย่าง: IT-DEV, IT-INFRA">
                    @error('code')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name_th" class="form-label font-heading text-dark">ชื่อทีม (ภาษาไทย) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name_th') is-invalid @enderror" id="name_th" name="name_th" value="{{ old('name_th') }}" required placeholder="ทีมพัฒนาซอฟต์แวร์">
                    @error('name_th')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name_en" class="form-label font-heading text-dark">ชื่อทีม (ภาษาอังกฤษ)</label>
                    <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en') }}" placeholder="Software Dev Team">
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                    <label class="form-check-label font-heading text-dark" for="is_active">เปิดใช้งานทีมนี้</label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.teams.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
