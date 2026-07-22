@extends('layouts.app')

@section('title', 'เพิ่มพนักงานใหม่')
@section('header', 'เพิ่มพนักงานใหม่ (Create Employee)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-4 border-bottom pb-2">
                <i class="bi bi-person-plus-fill text-primary me-2"></i>กรอกข้อมูลพนักงานใหม่
            </h5>

            <form method="POST" action="{{ route('admin.employees.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="emp_code" class="form-label font-heading text-dark">รหัสพนักงาน <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('emp_code') is-invalid @enderror" id="emp_code" name="emp_code" value="{{ old('emp_code') }}" required placeholder="EMP-0005">
                        @error('emp_code')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label for="prefix" class="form-label font-heading text-dark">คำนำหน้า</label>
                        <select name="prefix" id="prefix" class="form-select">
                            <option value="นาย" {{ old('prefix') == 'นาย' ? 'selected' : '' }}>นาย</option>
                            <option value="นาง" {{ old('prefix') == 'นาง' ? 'selected' : '' }}>นาง</option>
                            <option value="นางสาว" {{ old('prefix') == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="first_name" class="form-label font-heading text-dark">ชื่อ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" required placeholder="สมชาย">
                        @error('first_name')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="last_name" class="form-label font-heading text-dark">นามสกุล <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" required placeholder="ใจดี">
                        @error('last_name')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="department_id" class="form-label font-heading text-dark">แผนกสังกัด <span class="text-danger">*</span></label>
                        <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                            <option value="">-- เลือกแผนก --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name_th }} ({{ $dept->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="team_id" class="form-label font-heading text-dark">ทีมสังกัด</label>
                        <select name="team_id" id="team_id" class="form-select">
                            <option value="">-- เลือกทีม --</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                    {{ $team->name_th }} ({{ $team->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="position_id" class="form-label font-heading text-dark">ตำแหน่งงาน</label>
                        <select name="position_id" id="position_id" class="form-select">
                            <option value="">-- เลือกตำแหน่ง --</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>
                                    {{ $pos->title_th }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="email" class="form-label font-heading text-dark">อีเมล</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="somchai@company.com">
                    </div>

                    <div class="col-md-4">
                        <label for="phone" class="form-label font-heading text-dark">เบอร์โทรศัพท์</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" placeholder="0812345678">
                    </div>

                    <div class="col-md-4">
                        <label for="user_id" class="form-label font-heading text-dark">ผูกกับ User Account</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">-- ไม่ผูกกับ User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="supervisors" class="form-label font-heading text-dark">หัวหน้างานผู้รับผิดชอบ (Supervisor)</label>
                        <select name="supervisors[]" id="supervisors" class="form-select" multiple>
                            @foreach($supervisors as $spv)
                                <option value="{{ $spv->id }}">{{ $spv->name }} ({{ $spv->email }})</option>
                            @endforeach
                        </select>
                        <div class="form-text fs-7">กด Ctrl / Cmd ค้างไว้เพื่อเลือกหัวหน้างานหลายคน</div>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label font-heading text-dark">สถานะพนักงาน <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Active (ปกติ)</option>
                            <option value="Resigned" {{ old('status') == 'Resigned' ? 'selected' : '' }}>Resigned (ลาออก)</option>
                            <option value="Suspended" {{ old('status') == 'Suspended' ? 'selected' : '' }}>Suspended (พักงาน)</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4 border-top pt-3">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> บันทึกข้อมูลพนักงาน</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
