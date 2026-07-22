@extends('layouts.app')

@section('title', 'เพิ่มผู้ใช้งานใหม่')
@section('header', 'เพิ่มผู้ใช้งานใหม่ (Create New User)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-4 border-bottom pb-2">
                <i class="bi bi-person-plus-fill text-primary me-2"></i>กรอกข้อมูลผู้ใช้งานใหม่
            </h5>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="emp_code" class="form-label font-heading text-dark">รหัสพนักงาน</label>
                    <input type="text" class="form-control @error('emp_code') is-invalid @enderror" id="emp_code" name="emp_code" value="{{ old('emp_code') }}" placeholder="ตัวอย่าง: EMP-0005">
                    @error('emp_code')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label font-heading text-dark">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="นายสมชาย ใจดี">
                    @error('name')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label font-heading text-dark">อีเมล <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="somchai@company.com">
                    @error('email')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label font-heading text-dark">รหัสผ่านเริ่มต้น <span class="text-danger">*</span></label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="อย่างน้อย 8 ตัวอักษร">
                    @error('password')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label font-heading text-dark">บทบาทผู้ใช้งาน (Role) <span class="text-danger">*</span></label>
                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="">-- เลือกบทบาท --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                    <label class="form-check-label font-heading text-dark" for="is_active">เปิดใช้งานบัญชีทันที (Active Account)</label>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="must_change_password" name="must_change_password" value="1" {{ old('must_change_password', '1') ? 'checked' : '' }}>
                    <label class="form-check-label font-heading text-dark" for="must_change_password">บังคับเปลี่ยนรหัสผ่านในการเข้าใช้งานครั้งแรก</label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> บันทึกผู้ใช้งาน</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
