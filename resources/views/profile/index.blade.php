@extends('layouts.app')

@section('title', 'ข้อมูลส่วนตัว')
@section('header', 'ข้อมูลส่วนตัว')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card card-custom p-4 mb-4">
            <h5 class="fw-bold font-heading mb-4 border-bottom pb-2">
                <i class="bi bi-person-lines-fill text-primary me-2"></i>ข้อมูลผู้ใช้งาน
            </h5>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="emp_code" class="form-label font-heading text-muted fs-7">รหัสพนักงาน</label>
                    <input type="text" class="form-control bg-light" id="emp_code" value="{{ $user->emp_code ?? '-' }}" readonly>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label font-heading text-dark">ชื่อ-นามสกุล</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label font-heading text-dark">อีเมล</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label font-heading text-muted fs-7 d-block">บทบาทสิทธิ์การใช้งาน</label>
                    <span class="badge bg-primary fs-6">
                        {{ $user->roles->first()?->name ?? 'User' }}
                    </span>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> อัปเดตข้อมูล
                    </button>
                </div>
            </form>
        </div>

        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-4 text-danger border-bottom pb-2">
                <i class="bi bi-key-fill me-2"></i>เปลี่ยนรหัสผ่าน
            </h5>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <div class="mb-3">
                    <label for="current_password" class="form-label font-heading text-dark">รหัสผ่านปัจจุบัน</label>
                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                    @error('current_password')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label font-heading text-dark">รหัสผ่านใหม่</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                    @error('password')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label font-heading text-dark">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-shield-check me-1"></i> ยืนยันเปลี่ยนรหัสผ่าน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
