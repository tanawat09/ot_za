@extends('layouts.guest')

@section('title', 'เปลี่ยนรหัสผ่าน')

@section('content')
<div class="auth-header">
    <div class="mb-2">
        <i class="bi bi-shield-lock fs-1 text-warning"></i>
    </div>
    <h4 class="mb-1 fw-bold">เปลี่ยนรหัสผ่านสำหรับการเข้าใช้งาน</h4>
    <p class="text-muted fs-7 mb-0">กรุณากำหนดรหัสผ่านใหม่เพื่อความปลอดภัยของระบบ</p>
</div>

<div class="auth-body">
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show fs-7" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <!-- Current Password -->
        <div class="mb-3">
            <label for="current_password" class="form-label font-heading text-dark fw-medium">รหัสผ่านปัจจุบัน</label>
            <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required placeholder="••••••••">
            @error('current_password')
                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- New Password -->
        <div class="mb-3">
            <label for="password" class="form-label font-heading text-dark fw-medium">รหัสผ่านใหม่</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="••••••••">
            @error('password')
                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label font-heading text-dark fw-medium">ยืนยันรหัสผ่านใหม่</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 text-white font-heading shadow-sm">
            <i class="bi bi-save me-1"></i> บันทึกรหัสผ่านใหม่
        </button>
    </form>
</div>
@endsection
