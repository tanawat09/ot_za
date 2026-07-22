@extends('layouts.app')

@section('title', 'ตั้งค่าระบบ')
@section('header', 'ตั้งค่าระบบและโลโก้องค์กร (System & Logo Settings)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card card-custom p-4 shadow-sm mb-4">
            <h5 class="fw-bold font-heading mb-4 border-bottom pb-2">
                <i class="bi bi-gear-fill text-primary me-2"></i>การตั้งค่าระบบและโลโก้องค์กร
            </h5>

            <!-- Logo Upload Section -->
            <div class="card bg-light p-3 border mb-4">
                <h6 class="fw-bold font-heading text-dark mb-3">
                    <i class="bi bi-image text-primary me-2"></i>โลโก้องค์กร / บริษัท (Company Logo)
                </h6>

                <div class="row align-items-center">
                    <div class="col-md-4 text-center mb-3 mb-md-0">
                        <div class="p-3 bg-white border rounded shadow-sm d-inline-block">
                            @if($companyLogo && file_exists(public_path($companyLogo)))
                                <img src="{{ asset($companyLogo) }}" alt="Company Logo" class="img-fluid" style="max-height: 100px; max-width: 100%;">
                            @else
                                <div class="p-3 text-muted">
                                    <i class="bi bi-building-add display-4 d-block mb-1"></i>
                                    <span class="fs-7">ยังไม่มีโลโก้</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-8">
                        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="company_logo" class="form-label font-heading text-dark fs-7">อัปโหลดโลโก้ใหม่ (PNG, JPG, SVG ขนาดไม่เกิน 2MB)</label>
                                <input type="file" name="company_logo" id="company_logo" class="form-control form-control-sm @error('company_logo') is-invalid @enderror" accept=".png,.jpg,.jpeg,.svg">
                                @error('company_logo')
                                    <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-upload me-1"></i> อัปโหลดโลโก้
                                </button>
                        </form>

                        @if($companyLogo && file_exists(public_path($companyLogo)))
                            <form method="POST" action="{{ route('admin.settings.remove-logo') }}" onsubmit="return confirm('คุณต้องการลบโลโก้องค์กรนี้ใช่หรือไม่?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash me-1"></i> ลบโลโก้
                                </button>
                            </form>
                        @endif
                            </div>
                    </div>
                </div>
            </div>

            <!-- General Settings Form -->
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf

                <div class="mb-4">
                    <h6 class="font-heading text-primary fw-bold border-bottom pb-1">
                        <i class="bi bi-sliders me-1"></i> ข้อมูลทั่วไปองค์กร (General Information)
                    </h6>
                    
                    <div class="mb-3">
                        <label for="company_name" class="form-label font-heading text-dark fw-medium">ชื่อบริษัท / องค์กร (แสดงบนเอกสาร PDF)</label>
                        <input type="text" class="form-control" id="company_name" name="settings[company_name]" value="{{ old('settings.company_name', $companyName) }}" required>
                    </div>
                </div>

                @foreach($settings as $group => $items)
                    @if($group !== 'general')
                        <div class="mb-4">
                            <h6 class="text-uppercase font-heading text-primary fw-bold border-bottom pb-1">
                                กลุ่มการตั้งค่า: {{ strtoupper($group) }}
                            </h6>
                            @foreach($items as $setting)
                                <div class="mb-3">
                                    <label for="setting_{{ $setting->key }}" class="form-label font-heading text-dark fw-medium">
                                        {{ $setting->description ?? $setting->key }}
                                    </label>
                                    <input type="text" class="form-control" id="setting_{{ $setting->key }}" name="settings[{{ $setting->key }}]" value="{{ old('settings.' . $setting->key, $setting->value) }}">
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach

                <div class="d-flex justify-content-end border-top pt-3">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> บันทึกการตั้งค่าทั้งหมด</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
