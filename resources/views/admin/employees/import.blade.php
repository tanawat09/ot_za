@extends('layouts.app')

@section('title', 'นำเข้าข้อมูลพนักงานจาก Excel')
@section('header', 'นำเข้าข้อมูลพนักงาน (Import Employees)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card card-custom p-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold font-heading mb-0 text-dark">
                    <i class="bi bi-file-earmark-arrow-up text-primary me-2"></i>อัปโหลดไฟล์ Excel / CSV รายชื่อพนักงาน
                </h5>
                <a href="{{ route('admin.employees.sample-template') }}" class="btn btn-sm btn-outline-success font-heading fw-bold">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> ดาวน์โหลดไฟล์ตัวอย่าง (CSV)
                </a>
            </div>

            <div class="alert alert-info border-info fs-7 mb-4">
                <strong>คำแนะนำโครงสร้างคอลัมน์ในไฟล์ Excel / CSV (รองรับหัวข้อภาษาไทยและอังกฤษ):</strong>
                <ul class="mb-0 mt-1">
                    <li><code>emp_code</code> หรือ <code>รหัสพนักงาน</code> / <code>รหัสที่เครื่อง</code>: รหัสพนักงาน (จำเป็น)</li>
                    <li><code>first_name</code> หรือ <code>ชื่อ</code>: ชื่อพนักงาน (จำเป็น)</li>
                    <li><code>last_name</code> หรือ <code>นามสกุล</code>: นามสกุล (ถ้าไม่มีระบบจะใส่ - ให้)</li>
                    <li><code>department</code> หรือ <code>แผนก</code> / <code>ฝ่าย</code>: ชื่อแผนก หรือรหัสแผนก เช่น ฝ่ายขนส่ง, IT, HR</li>
                    <li><code>position</code> หรือ <code>ตำแหน่ง</code>: ชื่อตำแหน่ง เช่น พนักงานขนส่ง, DEV</li>
                    <li><code>salary</code> หรือ <code>เงินเดือน</code>: ฐานเงินเดือนเพื่อนำไปคำนวณเงินค่า OT (ไม่ระบุระบบจะตั้ง 15,000)</li>
                    <li><code>prefix</code> หรือ <code>คำนำหน้า</code>: นาย, นาง, นางสาว</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('admin.employees.import') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label for="file" class="form-label font-heading text-dark fw-bold">เลือกไฟล์ Excel (.xlsx, .xls, .csv)</label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" required accept=".xlsx,.xls,.csv,.txt">
                    @error('file')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> ย้อนกลับ
                    </a>
                    <button type="submit" class="btn btn-success font-heading fw-bold">
                        <i class="bi bi-upload me-1"></i> อัปโหลดและนำเข้าข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
