@extends('layouts.app')

@section('title', 'นำเข้าข้อมูลพนักงานจาก Excel')
@section('header', 'นำเข้าข้อมูลพนักงาน (Import Employees - Step 1: เลือกไฟล์)')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <!-- Main Import Card -->
        <div class="card card-custom p-4 shadow-sm mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 border-bottom pb-3">
                <div>
                    <h5 class="fw-bold font-heading mb-1 text-dark">
                        <i class="bi bi-file-earmark-arrow-up text-primary me-2 fs-4"></i>นำเข้าและอัปเดตข้อมูลพนักงานจากไฟล์ Excel / CSV
                    </h5>
                    <div class="fs-7 text-muted">รองรับไฟล์ Excel ของ HIP Premium Time และไฟล์โครงสร้าง <strong>employees_emp.xlsx</strong> ทุกรูปแบบ</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.employees.sample-template') }}" class="btn btn-sm btn-outline-success font-heading fw-bold">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> ดาวน์โหลดไฟล์ตัวอย่าง (CSV)
                    </a>
                </div>
            </div>

            <!-- Preview Mode Notice -->
            <div class="alert alert-info border-info-subtle bg-info-subtle text-dark fs-7 mb-4 d-flex align-items-start gap-3 p-3 rounded-3">
                <i class="bi bi-shield-check fs-3 text-info flex-shrink-0 mt-1"></i>
                <div>
                    <strong class="font-heading text-primary fs-6">ระบบตรวจสอบความถูกต้องก่อนนำเข้าจริง (2-Step Preview Mode)</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        <li>เมื่อคุณเลือกไฟล์และกดอ่านข้อมูล ระบบจะยัง <strong>ไม่แก้ไขฐานข้อมูลทันที</strong> แต่จะแสดงตารางตรวจสอบพรีวิว</li>
                        <li>คุณสามารถตรวจสอบรหัสพนักงาน คำนำหน้า ชื่อ-นามสกุล แผนก ตำแหน่ง และสถานะรายการก่อนยืนยันได้ 100%</li>
                        <li>หากพบข้อมูลผิดพลาด คุณสามารถกดย้อนกลับเพื่อแก้ไขไฟล์ หรือกด <strong>"ล้างข้อมูลพนักงานทั้งหมด"</strong> ก่อนนำเข้าใหม่ได้</li>
                    </ul>
                </div>
            </div>

            <!-- Upload & Preview Form -->
            <form method="POST" action="{{ route('admin.employees.preview-import') }}" enctype="multipart/form-data" class="mb-2" id="importForm">
                @csrf

                <!-- Drag and Drop Area -->
                <div class="mb-4">
                    <label class="form-label font-heading text-dark fw-bold mb-2">
                        เลือกหรือลากไฟล์ Excel / CSV มาวางในช่องด้านล่าง (.xlsx, .xls, .csv)
                    </label>

                    <div class="border border-2 border-dashed border-primary rounded-4 p-4 text-center bg-light position-relative hover-shadow" id="dropZone" style="cursor: pointer; transition: all 0.2s ease-in-out;">
                        <input type="file" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 @error('file') is-invalid @enderror" id="file" name="file" required accept=".xlsx,.xls,.csv,.txt" onchange="updateFileLabel(this)" style="cursor: pointer;">

                        <div id="dropZoneContent">
                            <div class="avatar avatar-lg bg-primary-subtle text-primary rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-cloud-arrow-up-fill fs-2"></i>
                            </div>
                            <h6 class="fw-bold font-heading text-dark mb-1">คลิกที่นี่ หรือลากไฟล์มาวางเพื่ออัปโหลด</h6>
                            <p class="fs-7 text-muted mb-2">รองรับไฟล์ .xlsx, .xls, .csv (ขนาดไม่เกิน 10 MB)</p>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 font-heading">
                                <i class="bi bi-check-circle me-1"></i> รองรับรูปแบบ employees_emp.xlsx และ HIP Matrix
                            </span>
                        </div>

                        <div id="fileSelectedInfo" class="d-none">
                            <i class="bi bi-file-earmark-excel-fill text-success fs-1 mb-2"></i>
                            <h6 class="fw-bold font-heading text-dark mb-1" id="fileNameDisplay">ชื่อไฟล์.xlsx</h6>
                            <p class="fs-7 text-muted mb-2" id="fileSizeDisplay">0 KB</p>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 font-heading">
                                <i class="bi bi-check2-all me-1"></i> พร้อมอ่านข้อมูล Step 1
                            </span>
                        </div>
                    </div>

                    @error('file')
                        <div class="text-danger fs-7 mt-2"><i class="bi bi-exclamation-circle me-1"></i> {{ $message }}</div>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 border-top pt-3">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary font-heading">
                        <i class="bi bi-arrow-left me-1"></i> ย้อนกลับไปหน้ารายชื่อพนักงาน
                    </a>
                    <button type="submit" class="btn btn-primary font-heading fw-bold px-4 py-2">
                        <i class="bi bi-eye me-1"></i> อ่านไฟล์และพรีวิวตรวจสอบข้อมูล (Step 1) <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Format Specs Guide Card -->
        <div class="card card-custom p-4 shadow-sm mb-4 bg-white">
            <h6 class="fw-bold font-heading text-dark mb-3">
                <i class="bi bi-info-circle text-primary me-2"></i>คำแนะนำโครงสร้างคอลัมน์ในไฟล์ Excel (Column Format Specifications)
            </h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle fs-7 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ลำดับคอลัมน์</th>
                            <th>ชื่อหัวข้อ (Header)</th>
                            <th>ตัวอย่างข้อมูลในไฟล์</th>
                            <th>การประมวลผลของระบบ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold text-center">Col A (1)</td>
                            <td>รหัสพนักงาน (emp_code)</td>
                            <td><code>00001</code>, <code>EMP001</code></td>
                            <td>รหัสประจำตัวพนักงานสำหรับอ้างอิง</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-center">Col B (2)</td>
                            <td>คำนำหน้า (prefix)</td>
                            <td><code>นาย</code>, <code>น.ส.</code>, <code>นางสาว</code>, <code>MR.</code></td>
                            <td>แยกคำนำหน้าชื่อให้อัตโนมัติ</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-center">Col C (3)</td>
                            <td>ชื่อจริง (first_name)</td>
                            <td><code>วรภัทร</code>, <code>กรรณิกา</code></td>
                            <td>ชื่อพนักงานภาษาไทยหรืออังกฤษ</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-center">Col D (4)</td>
                            <td>นามสกุล (last_name)</td>
                            <td><code>พุฒพันธ์</code>, <code>สีหะวงษ์</code></td>
                            <td>นามสกุลพนักงาน</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-center">Col E-F (5-6)</td>
                            <td>แผนก / ตำแหน่ง (department / position)</td>
                            <td><code>สำนักงาน</code>, <code>พนักงานขับรถ</code>, <code>เซลล์</code></td>
                            <td>จับคู่แผนกและตำแหน่งเข้าสู่ระบบอัตโนมัติ</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-center">Col I (7+)</td>
                            <td>เงินเดือน (salary)</td>
                            <td><code>15000</code>, <code>25000</code> (หรือเว้นว่าง)</td>
                            <td>อัตราเงินเดือน (หากเว้นว่างจะกำหนดเป็น 15,000)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Danger Zone: Clear All Employees -->
        <div class="card card-custom p-4 border border-danger shadow-sm bg-danger-subtle bg-opacity-10">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <div>
                    <h6 class="fw-bold font-heading text-danger mb-1">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> ล้างข้อมูลพนักงานทั้งหมดในระบบ (Clear All Employees)
                    </h6>
                    <div class="fs-7 text-muted">กรณีต้องการลบรายการพนักงานเก่าทั้งหมดในฐานข้อมูล เพื่อเริ่มต้นนำเข้าไฟล์ใหม่ตั้งแต่ต้น</div>
                </div>
                <form method="POST" action="{{ route('admin.employees.clear-all') }}" onsubmit="return confirm('⚠️ คำเตือนสำคัญ: คุณต้องการลบข้อมูลพนักงานทั้งหมดในระบบใช่หรือไม่?\n\nข้อมูลพนักงานทั้งหมดจะถูกลบออกจากฐานข้อมูลและไม่สามารถย้อนกลับได้!');" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger font-heading fw-bold px-3">
                        <i class="bi bi-trash-fill me-1"></i> ล้างข้อมูลพนักงานทั้งหมด
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateFileLabel(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('dropZoneContent').classList.add('d-none');
        document.getElementById('fileSelectedInfo').classList.remove('d-none');
        document.getElementById('fileNameDisplay').textContent = file.name;
        
        let sizeStr = (file.size / 1024).toFixed(1) + ' KB';
        if (file.size > 1024 * 1024) {
            sizeStr = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        }
        document.getElementById('fileSizeDisplay').textContent = 'ขนาดไฟล์: ' + sizeStr;
    }
}
</script>
@endsection
