@extends('layouts.app')

@section('title', 'คู่มือการใช้งานโปรแกรม')
@section('header', 'คู่มือการใช้งานระบบบริหารจัดการการขอทำงานล่วงเวลา (User Manual)')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Quick Nav & Header -->
        <div class="card card-custom p-4 shadow-sm mb-4 border" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-color: #cbd5e1 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="fw-bold font-heading text-dark mb-2">
                        <i class="bi bi-book text-primary me-2"></i>คู่มือการใช้งานระบบ (User Manual)
                    </h3>
                    <p class="text-muted mb-0 fs-7">ระบบบริหารจัดการการขอทำงานล่วงเวลาองค์กร (Enterprise Overtime Management System)</p>
                </div>
                <div class="d-none d-md-block text-end">
                    <span class="badge bg-primary text-white px-3 py-2 fs-7 font-heading shadow-sm">
                        เวอร์ชัน 1.0.0 (Production Ready)
                    </span>
                </div>
            </div>
        </div>

        <!-- Role Credentials Matrix -->
        <div class="card card-custom p-4 shadow-sm mb-4">
            <h5 class="fw-bold font-heading mb-3 border-bottom pb-2 text-dark">
                <i class="bi bi-key-fill text-primary me-2"></i>1. ข้อมูลการเข้าสู่ระบบและสิทธิ์การใช้งาน (Roles & Credentials)
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>บทบาท (Role)</th>
                            <th>อีเมลเข้าใช้งาน (Email)</th>
                            <th>รหัสผ่าน (Password)</th>
                            <th>สิทธิ์และความรับผิดชอบหลัก</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-danger">Super Admin</span></td>
                            <td><code>admin@company.com</code></td>
                            <td><code>Password123!</code></td>
                            <td>สิทธิ์สูงสุด: จัดการผู้ใช้, ตั้งค่าโลโก้/ระบบ, ดู Audit Logs และทำได้ทุกฟังก์ชัน</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-info text-dark">HR</span></td>
                            <td><code>hr@company.com</code></td>
                            <td><code>Password123!</code></td>
                            <td>จัดการข้อมูลหลัก, ปิดรอบประจำเดือน, ส่งออกไฟล์ Payroll เข้าสู่ระบบเงินเดือน</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-warning text-dark">Manager</span></td>
                            <td><code>manager@company.com</code></td>
                            <td><code>Password123!</code></td>
                            <td>กล่องงานอนุมัติ (Manager Inbox) อนุมัติ/ไม่อนุมัติ/ส่งกลับแก้ไขคำขอ OT</td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-primary">Supervisor</span></td>
                            <td><code>supervisor@company.com</code></td>
                            <td><code>Password123!</code></td>
                            <td>สร้างคำขอ OT, พิมพ์เอกสารยินยอม PDF, อัปโหลดเอกสารเซ็นแล้ว, บันทึกเวลาจริง</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Manual Sections Accordion -->
        <div class="accordion mb-4" id="manualAccordion">
            <!-- Section 2: Supervisor -->
            <div class="accordion-item card-custom mb-3 border">
                <h2 class="accordion-header" id="headingSup">
                    <button class="accordion-button font-heading fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSup" aria-expanded="true">
                        <i class="bi bi-person-badge text-primary me-2 fs-5"></i> 2. คู่มือสำหรับหัวหน้างาน (Supervisor Manual)
                    </button>
                </h2>
                <div id="collapseSup" class="accordion-collapse collapse show" data-bs-parent="#manualAccordion">
                    <div class="accordion-body">
                        <h6 class="fw-bold font-heading text-primary">2.1 การยื่นคำขอ OT ใหม่</h6>
                        <ul>
                            <li>เข้าเมนู <strong>"ยื่นคำขอ OT"</strong> -> กดปุ่ม <strong>"สร้างคำขอ OT ใหม่"</strong></li>
                            <li>เลือกแผนก, ประเภท OT, วันที่ และระบุเวลาเริ่ม-เลิก (รองรับการทำ OT ข้ามคืน เช่น 20:00 - 02:00 น.)</li>
                            <li>ระบุเวลาพัก (นาที) ระบบจะคำนวณชั่วโมงสุทธิให้อัตโนมัติ</li>
                            <li>ค้นหาและเลือกพนักงานหลายคนพร้อมกันในคำขอเดียว</li>
                        </ul>

                        <h6 class="fw-bold font-heading text-primary mt-3">2.2 การพิมพ์เอกสารยินยอมภาษาไทย (PDF)</h6>
                        <ul>
                            <li>ในตารางรายการคำขอ OT กดปุ่ม <strong>"พิมพ์ใบยินยอม (PDF)"</strong></li>
                            <li>ระบบจะสร้างเอกสาร PDF ภาษาไทย พร้อมโลโก้บริษัท และช่องให้พนักงานลงลายมือชื่อ</li>
                        </ul>

                        <h6 class="fw-bold font-heading text-primary mt-3">2.3 การส่งขออนุมัติและบันทึกเวลาจริง</h6>
                        <ul>
                            <li>สแกนไฟล์เอกสารที่พนักงานเซ็นยินยอมแล้วอัปโหลดเข้าระบบ</li>
                            <li>กดปุ่ม <strong>"ส่งคำขออนุมัติ"</strong> เพื่อส่งเรื่องให้ Manager</li>
                            <li>เมื่อทำงานเสร็จแล้ว เข้ามาบันทึกเวลาปฏิบัติงานจริง (Actual Time)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Section 3: Manager -->
            <div class="accordion-item card-custom mb-3 border">
                <h2 class="accordion-header" id="headingMgr">
                    <button class="accordion-button collapsed font-heading fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMgr">
                        <i class="bi bi-check2-square text-warning me-2 fs-5"></i> 3. คู่มือสำหรับผู้จัดการ (Manager Manual)
                    </button>
                </h2>
                <div id="collapseMgr" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                    <div class="accordion-body">
                        <ul>
                            <li>เข้าเมนู <strong>"พิจารณาอนุมัติ (Manager Inbox)"</strong></li>
                            <li>ระบบจะแสดงคำขอรออนุมัติของพนักงานในแผนกที่รับผิดชอบ</li>
                            <li><strong>การดำเนินการ 3 รูปแบบ</strong>:
                                <ul class="mt-1">
                                    <li><span class="badge bg-success">อนุมัติ (Approve)</span>: อนุมัติคำขอ และล็อกสถานะ</li>
                                    <li><span class="badge bg-danger">ไม่อนุมัติ (Reject)</span>: ปฏิเสธคำขอ พร้อมระบุเหตุผล</li>
                                    <li><span class="badge bg-warning text-dark">ส่งกลับแก้ไข (Return)</span>: ส่งกลับให้ Supervisor แก้ไข</li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Section 4: HR -->
            <div class="accordion-item card-custom mb-3 border">
                <h2 class="accordion-header" id="headingHR">
                    <button class="accordion-button collapsed font-heading fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHR">
                        <i class="bi bi-building text-info me-2 fs-5"></i> 4. คู่มือสำหรับฝ่ายบุคคล (HR Manual)
                    </button>
                </h2>
                <div id="collapseHR" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>จัดการ Master Data</strong>: เพิ่ม/แก้ไข แผนก, ทีม, ตำแหน่ง, ประเภท OT, วันหยุด</li>
                            <li><strong>นำเข้า/ส่งออกพนักงาน</strong>: Export/Import ข้อมูลพนักงานด้วยไฟล์ Excel</li>
                            <li><strong>ปิดรอบประจำเดือน (Period Lock)</strong>: ปิดรอบตัดยอด OT ประจำเดือน ป้องกันการแก้ไขคำขอย้อนหลัง</li>
                            <li><strong>ส่งออกเงินเดือน (Payroll Export)</strong>: สรุปชั่วโมง OT ส่งออกไฟล์ Excel / CSV เข้าสู่ระบบ Payroll</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Section 5: Admin & Dashboard -->
            <div class="accordion-item card-custom border">
                <h2 class="accordion-header" id="headingAdmin">
                    <button class="accordion-button collapsed font-heading fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdmin">
                        <i class="bi bi-gear-fill text-danger me-2 fs-5"></i> 5. ผู้ดูแลระบบ และศูนย์รายงาน (Admin & Reports)
                    </button>
                </h2>
                <div id="collapseAdmin" class="accordion-collapse collapse" data-bs-parent="#manualAccordion">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>การตั้งค่าโลโก้</strong>: เข้าเมนู <code>Settings</code> อัปโหลดโลโก้องค์กรที่จะแสดงบน Sidebar และเอกสาร PDF</li>
                            <li><strong>Executive Dashboard</strong>: กรองสถิติ รายวัน, รายเดือน, รายปี และ Custom Range พร้อม 4 กราฟวิเคราะห์</li>
                            <li><strong>ศูนย์รายงาน 16 รูปแบบ</strong>: ส่งออกรายงานสรุปเชิงลึกเป็น Excel และ PDF ภาษาไทยได้ทันที</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
