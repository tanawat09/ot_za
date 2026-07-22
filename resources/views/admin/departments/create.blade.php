@extends('layouts.app')

@section('title', 'เพิ่มแผนกใหม่')
@section('header', 'เพิ่มแผนกใหม่ (Create Department)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-4 border-bottom pb-2">
                <i class="bi bi-plus-circle text-primary me-2"></i>กรอกข้อมูลแผนกใหม่
            </h5>

            <form method="POST" action="{{ route('admin.departments.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="code" class="form-label font-heading text-dark">รหัสแผนก <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required placeholder="ตัวอย่าง: IT, HR, PROD">
                    @error('code')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name_th" class="form-label font-heading text-dark">ชื่อแผนก (ภาษาไทย) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name_th') is-invalid @enderror" id="name_th" name="name_th" value="{{ old('name_th') }}" required placeholder="เทคโนโลยีสารสนเทศ">
                    @error('name_th')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name_en" class="form-label font-heading text-dark">ชื่อแผนก (ภาษาอังกฤษ)</label>
                    <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en') }}" placeholder="Information Technology">
                </div>

                <div class="mb-3">
                    <label for="managers" class="form-label font-heading text-dark">ผู้จัดการประจำแผนก (Manager)</label>
                    <select name="managers[]" id="managers" class="form-select" multiple>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }} ({{ $manager->email }})</option>
                        @endforeach
                    </select>
                    <div class="form-text fs-7">กด Ctrl / Cmd ค้างไว้เพื่อเลือกผู้จัดการหลายคน</div>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                    <label class="form-check-label font-heading text-dark" for="is_active">เปิดใช้งานแผนกนี้</label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
