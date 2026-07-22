@extends('layouts.app')

@section('title', 'นำเข้าข้อมูลสแกน HIP Premium Time')
@section('header', 'นำเข้าข้อมูลสแกนนิ้ว/ใบหน้าจาก HIP Premium Time v2.0 / v6')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-custom p-4 shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold font-heading mb-0 text-dark">
                    <i class="bi bi-fingerprint text-primary me-2"></i>นำเข้าข้อมูลเวลาสแกน (HIP Premium Time Integration)
                </h5>
                <a href="{{ route('hip.sample-template') }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> ดาวน์โหลดไฟล์ตัวอย่าง HIP (CSV)
                </a>
            </div>

            <div class="alert alert-info border-info d-flex align-items-start gap-2 mb-4">
                <i class="bi bi-info-circle-fill fs-5 text-info mt-1"></i>
                <div class="fs-7">
                    <strong>คำแนะนำการนำเข้าไฟล์จาก HIP Premium Time v2.0 / v6:</strong><br>
                    * รองรับไฟล์ส่งออกจากโปรแกรม HIP ในรูปแบบ <strong>CSV (.csv)</strong> หรือ <strong>Text (.txt)</strong><br>
                    * คอลัมน์ที่ต้องมี: <code>รหัสพนักงาน (emp_code)</code>, <code>วันที่สแกน (YYYY-MM-DD)</code>, <code>เวลาเข้า (HH:MM)</code>, <code>เวลาออก (HH:MM)</code><br>
                    * ระบบจะนำเวลาเข้า-ออกไป <strong>จับคู่กับคำขอ OT ที่ได้รับการอนุมัติอัตโนมัติ</strong> เพื่อคำนวณชั่วโมงปฏิบัติงานจริง
                </div>
            </div>

            <form method="POST" action="{{ route('hip.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- File Upload Box -->
                <div class="mb-4">
                    <label for="file" class="form-label font-heading text-dark fw-bold">1. เลือกไฟล์ข้อมูลจาก HIP Premium Time (CSV / Text File)</label>
                    <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".csv,.txt,.dat,.xlsx,.xls">
                    @error('file')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-center my-3 text-muted fw-bold">--- หรือ ---</div>

                <!-- Text Area Input -->
                <div class="mb-4">
                    <label for="raw_text" class="form-label font-heading text-dark fw-bold">2. วางข้อความข้อมูลสแกน (Copy & Paste Text Logs)</label>
                    <textarea name="raw_text" id="raw_text" rows="5" class="form-control font-monospace fs-7" placeholder="ตัวอย่าง:
EMP001,2026-07-21,17:30,20:30,HIP-DEV-01
EMP002,2026-07-21,17:30,21:00,HIP-DEV-01"></textarea>
                    <div class="form-text fs-7 text-muted">รูปแบบ: รหัสพนักงาน, วันที่ (YYYY-MM-DD), เวลาเข้า, เวลาออก, รหัสเครื่อง</div>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <a href="{{ route('hip.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
                    </a>
                    <button type="submit" class="btn btn-primary px-4 font-heading fw-bold">
                        <i class="bi bi-upload me-1"></i> ประมวลผลนำเข้าข้อมูล HIP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
