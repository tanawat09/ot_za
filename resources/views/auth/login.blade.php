@extends('layouts.guest')

@section('title', 'เข้าสู่ระบบ')

@section('content')
<div class="auth-header">
    <div class="mb-2">
        <i class="bi bi-clock-history fs-1 text-primary"></i>
    </div>
    <h3 class="mb-1 fw-bold">Enterprise OT</h3>
    <p class="text-muted fs-7 mb-0">ระบบบริหารจัดการการขอทำงานล่วงเวลา</p>
</div>

<div class="auth-body">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show fs-7" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label font-heading text-dark fw-medium">อีเมลพนักงาน</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@company.com">
            </div>
            @error('email')
                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label font-heading text-dark fw-medium">รหัสผ่าน</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" class="form-control border-start-0 @error('password') is-invalid @enderror" id="password" name="password" required placeholder="••••••••">
            </div>
            @error('password')
                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label text-secondary fs-7" for="remember">จดจำการเข้าสู่ระบบ</label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary-custom w-100 text-white font-heading shadow-sm">
            <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ
        </button>
    </form>
</div>
@endsection
