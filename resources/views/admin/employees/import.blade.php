@extends('layouts.app')

@section('title', 'นำเข้าข้อมูลพนักงานจาก Excel')
@section('header', 'นำเข้าข้อมูลพนักงาน - ขั้นตอนที่ 1: เลือกไฟล์ (Import Employees Step 1)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card card-custom p-4 shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold font-heading mb-0 text-dark">
                    <i class="bi bi-file-earmark-arrow-up text-primary me-2"></i>เลือกไฟล์ Excel / CSV เพื่อพรีวิวตรวจสอบข้อมูล
                </h5>
                <a href="{{ route('admin.employees.sample-template') }}" class="btn btn-sm btn-outline-success font-heading fw-bold">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> ดาวน์โหลดไฟล์ตัวอย่าง (CSV)
                </a>
            </div>

            <div class="alert alert-info border-info fs-7 mb-4 d-flex align-items-start gap-2">
                <i class="bi bi-shield-check fs-4 text-info mt-1"></i>
                <div>
                    <strong>ระบบตรวจสอบก่อนนำเข้า (Preview & Verification Mode):</strong><br>
                    * เมื่อเลือกอัปโหลดไฟล์ ระบบจะ <strong>แสดงตารางพรีวิวตรวจสอบความถูกต้อง</strong> ก่อนบันทึกลงฐานข้อมูลจริง<br>
                    * ท่านสามารถตรวจสอบรหัสพนักงาน ชื่อ-นามสกุล แผนก ตำแหน่ง และสถานะรายการก่อนกดยืนยันได้<br>
                    * หากต้องการล้างข้อมูลเก่าที่ผิดพลาดออกทั้งหมด สามารถใช้ปุ่ม <strong>"ล้างข้อมูลพนักงานทั้งหมด"</strong> ด้านล่างได้
                </div>
            </div>

            <!-- Upload & Preview Form -->
            <form method="POST" action="{{ route('admin.employees.preview-import') }}" enctype="multipart/form-data" class="mb-4">
                @csrf

                <div class="mb-4">
                    <label for="file" class="form-label font-heading text-dark fw-bold">เลือกไฟล์ Excel / CSV รายชื่อพนักงาน (.xlsx, .xls, .csv)</label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" required accept=".xlsx,.xls,.csv,.txt">
                    @error('file')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
                    </a>
                    <button type="submit" class="btn btn-primary font-heading fw-bold px-4">
                        <i class="bi bi-eye me-1"></i> อ่านไฟล์และพรีวิวตรวจสอบข้อมูล (Step 1)
                    </button>
                </div>
            </form>
        </div>

        <!-- Danger Zone: Clear All Employees -->
        <div class="card card-custom p-4 border border-danger shadow-sm bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold font-heading text-danger mb-1">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> ล้างข้อมูลพนักงานทั้งหมดในระบบ (Clear All)
                    </h6>
                    <div class="fs-7 text-muted">กรณีต้องการลบรายการพนักงานที่ผิดพลาดทั้งหมดเพื่อเริ่มนำเข้าใหม่ตั้งแต่ต้น</div>
                </div>
                <form method="POST" action="{{ route('admin.employees.clear-all') }}" onsubmit="return confirm('⚠️ คำเตือน: คุณต้องการลบข้อมูลพนักงานทั้งหมดในระบบใช่หรือไม่?\n\nการกระทำนี้ไม่สามารถย้อนกลับได้!');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger font-heading fw-bold">
                        <i class="bi bi-trash-fill me-1"></i> ล้างข้อมูลพนักงานทั้งหมด
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
