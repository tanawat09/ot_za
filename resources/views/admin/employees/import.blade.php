@extends('layouts.app')

@section('title', 'นำเข้าข้อมูลพนักงานจาก Excel')
@section('header', 'นำเข้าข้อมูลพนักงาน (Import Employees)')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card card-custom p-4">
            <h5 class="fw-bold font-heading mb-3 border-bottom pb-2">
                <i class="bi bi-file-earmark-arrow-up text-primary me-2"></i>อัปโหลดไฟล์ Excel รายชื่อพนักงาน
            </h5>

            <div class="alert alert-info fs-7 mb-4">
                <strong>คำแนะนำโครงสร้างคอลัมน์ในไฟล์ Excel:</strong>
                <ul class="mb-0 mt-1">
                    <li><code>emp_code</code>: รหัสพนักงาน (จำเป็น)</li>
                    <li><code>first_name</code>: ชื่อ (จำเป็น)</li>
                    <li><code>last_name</code>: นามสกุล (จำเป็น)</li>
                    <li><code>department_code</code>: รหัสแผนก เช่น IT, HR, PROD (จำเป็น)</li>
                    <li><code>team_code</code>: รหัสทีม เช่น IT-DEV (ไม่บังคับ)</li>
                    <li><code>position_code</code>: รหัสตำแหน่ง เช่น DEV, LEAD (ไม่บังคับ)</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('admin.employees.import') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label for="file" class="form-label font-heading text-dark">เลือกไฟล์ Excel (.xlsx, .xls, .csv)</label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" required accept=".xlsx,.xls,.csv">
                    @error('file')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> ย้อนกลับ</a>
                    <button type="submit" class="btn btn-success"><i class="bi bi-upload me-1"></i> อัปโหลดและนำเข้าข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
