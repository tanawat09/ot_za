@extends('layouts.app')

@section('title', 'นำเข้าข้อมูลสแกน HIP Premium Time')
@section('header', 'นำเข้าข้อมูลสแกนเวลาจาก HIP Premium Time v2.0 / v6 (Excel / Access .mdb / CSV)')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-custom p-4 shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold font-heading mb-0 text-dark">
                    <i class="bi bi-fingerprint text-primary me-2"></i>นำเข้าข้อมูลเวลาสแกน (HIP Premium Time Integration)
                </h5>
                <a href="{{ route('hip.sample-template') }}" class="btn btn-sm btn-outline-success font-heading fw-bold">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> ดาวน์โหลดไฟล์ตัวอย่าง HIP (CSV/Excel)
                </a>
            </div>

            <div class="alert alert-info border-info d-flex align-items-start gap-2 mb-4">
                <i class="bi bi-info-circle-fill fs-5 text-info mt-1"></i>
                <div class="fs-7">
                    <strong>รูปแบบไฟล์ที่รองรับจากโปรแกรม HIP Premium Time v2.0 / v6:</strong><br>
                    * 📊 <strong>ไฟล์รายงาน Excel (.xlsx, .xls)</strong> หรือ <strong>CSV (.csv)</strong> ที่ส่งออกจาก HIP Premium Time (มีคอลัมน์ <code>รหัสที่เครื่อง</code>, <code>ชื่อ-นามสกุล</code>, <code>Date</code>, <code>1</code>, <code>2</code>)<br>
                    * 🗄️ <strong>ไฟล์ฐานข้อมูล MS Access (.mdb)</strong> จากโฟลเดอร์โปรแกรม HIP (เช่น <code>pm2005.mdb</code> หรือ <code>att2000.mdb</code>)<br>
                    * 📝 <strong>ไฟล์ข้อความ Text (.txt, .dat)</strong><br>
                    * ระบบจะนำเวลาเข้า-ออกไป <strong>จับคู่กับคำขอ OT ที่อนุมัติแล้วให้อัตโนมัติ</strong> พร้อมคำนวณชั่วโมงปฏิบัติงานจริง
                </div>
            </div>

            <form method="POST" action="{{ route('hip.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- File Upload Box -->
                <div class="mb-4">
                    <label for="file" class="form-label font-heading text-dark fw-bold">
                        1. เลือกไฟล์จากโปรแกรม HIP Premium Time (Excel / MS Access .mdb / CSV / Text)
                    </label>
                    <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv,.mdb,.txt,.dat">
                    @error('file')
                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                    @enderror
                    <div class="form-text fs-7 text-muted">รองรับไฟล์ <code>.xlsx</code>, <code>.xls</code>, <code>.csv</code>, <code>.mdb</code> ขนาดไม่เกิน 20MB</div>
                </div>

                <div class="text-center my-3 text-muted fw-bold">--- หรือ ---</div>

                <!-- Text Area Input -->
                <div class="mb-4">
                    <label for="raw_text" class="form-label font-heading text-dark fw-bold">2. วางข้อความตารางสแกน (Copy & Paste จาก Excel / CSV)</label>
                    <textarea name="raw_text" id="raw_text" rows="6" class="form-control font-monospace fs-7" placeholder="ตัวอย่างจาก HIP Premium Time:
รหัสที่เครื่อง,รหัสพนักงาน,ชื่อ-นามสกุล,แผนก,Date,1,2,3,4,
563005,,ปริญวัฒน์  ปิยะอารยาภัสร์,ฝ่ายขนส่ง,01/07/2026,07:54,17:59,,,
563005,,ปริญวัฒน์  ปิยะอารยาภัสร์,ฝ่ายขนส่ง,02/07/2026,08:00,20:01,,,"></textarea>
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

        <!-- Danger Zone: Clear All Attendance Logs -->
        <div class="card card-custom p-4 border border-danger shadow-sm bg-danger-subtle bg-opacity-10">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <div>
                    <h6 class="fw-bold font-heading text-danger mb-1">
                        <i class="bi bi-eraser-fill me-1"></i> ล้างประวัติข้อมูลสแกนนิ้ว HIP ทั้งหมด (Clear Attendance Logs)
                    </h6>
                    <div class="fs-7 text-muted">หลังจากคำนวณเงินเดือน/โอทีเสร็จสิ้นแล้ว สามารถกดล้างข้อมูลสแกนเก่าออกเพื่อลดภาระฐานข้อมูล</div>
                </div>
                <form method="POST" action="{{ route('hip.clear-all') }}" onsubmit="return confirm('⚠️ คุณต้องการลบและเคลียร์ประวัติสแกนนิ้ว HIP ทั้งหมดในระบบใช่หรือไม่?\n\n(ช่วยลดขนาดฐานข้อมูลหลังจากประมวลผลเงินเดือนเสร็จสิ้น)');" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger font-heading fw-bold px-3">
                        <i class="bi bi-trash-fill me-1"></i> ล้างข้อมูลสแกนทั้งหมด
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
