# ระบบบริหารจัดการการทำงานล่วงเวลา (Enterprise Overtime Management System - OT ZA)

ระบบบริหารจัดการการทำงานล่วงเวลา (Overtime Management System) ครบวงจร พัฒนาด้วย **Laravel 13** และ **MariaDB 10.11** รองรับการนำเข้าข้อมูลพนักงาน, เชื่อมโยงเวลาสแกนจากเครื่องสแกนลายนิ้วมือ **HIP Premium Time**, ระบบอนุมัติคำขอ OT แบบหลายระดับ, การออกเอกสารใบยินยอม (Consent PDF) และการคำนวณค่าตอบแทน OT พร้อมส่งออกไฟล์ Payroll

---

## 🔑 บัญชีผู้ใช้งานระบบและรหัสผ่าน (System Login Credentials)

URL สำหรับเข้าใช้งานระบบ: **[http://localhost:8082](http://localhost:8082)**

> 📌 **รหัสผ่านตั้งต้นของทุกบัญชี (Default Password):** `Password123!`

| บทบาท (Role) | อีเมล (Email) | รหัสพนักงาน (Emp Code) | สิทธิ์การใช้งาน (Permissions) |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@company.com` | `EMP-0001` | สิทธิ์สูงสุดในระบบ เข้าถึงได้ทุกเมนู รวมถึงการจัดการผู้ใช้และ Audit Logs |
| **HR Admin** | `hr@company.com` | `EMP-0002` | จัดการข้อมูลพนักงาน, นำเข้าไฟล์ HIP, ตรวจสอบและส่งออก Payroll, ล็อกงวดประจำเดือน |
| **Manager** | `manager@company.com` | `EMP-0003` | อนุมัติ/ไม่อนุมัติ คำขอ OT ของพนักงานในแผนก, ดูรายงานสรุประดับแผนก |
| **Supervisor** | `supervisor@company.com` | `EMP-0004` | สร้างคำขอ OT ให้พนักงานในทีม, บันทึกเวลาปฏิบัติงานจริง (Actual Time) |

---

## 🗄️ ข้อมูลการเชื่อมต่อฐานข้อมูล (Database Connection Credentials)

ระบบใช้ **MariaDB 10.11** ทำงานบน Docker Container (`ot_mariadb`)

### 1. เชื่อมต่อจากภายนอก (External Access via GUI Clients e.g. DBeaver, TablePlus, Navicat)
* **Host:** `127.0.0.1` (หรือ `localhost`)
* **Port:** `3307`
* **Database Name:** `ot_db`
* **User Application:** `ot_user` | **Password:** `ot_password`
* **Root Admin:** `root` | **Password:** `root_password`

### 2. เชื่อมต่อภายในคอนเทนเนอร์ (Internal Docker Network)
* **Host:** `db`
* **Port:** `3306`
* **Database Name:** `ot_db`

---

## 🚀 คุณสมบัติเด่นของระบบ (Key System Features)

### 1. ระบบนำเข้าพนักงาน (Employee Import & Verification)
* รองรับไฟล์ Excel รายชื่อพนักงานทุกรูปแบบ (รวมถึงไฟล์ `employees_emp.xlsx`)
* ระบบ **2-Step Preview Mode**: แสดงตารางพรีวิวและตรวจสอบความถูกต้อง (รหัส, ชื่อ-นามสกุล, แผนก, ตำแหน่ง, เงินเดือน) ก่อนกดยืนยันบันทึกจริง
* ระบบตรวจจับคำนำหน้าไทย/อังกฤษอัตโนมัติ (`นาย`, `นางสาว`, `น.ส.`, `MR.`, `MS.` ฯลฯ)
* ปุ่ม **"ล้างข้อมูลพนักงานทั้งหมด"** สำหรับล้างข้อมูลเก่าที่ผิดพลาดออกอย่างปลอดภัย

### 2. ระบบเชื่อมโยง HIP Premium Time Integration
* รองรับไฟล์สแกนนิ้ว/ใบหน้าจากโปรแกรม **HIP Premium Time (v2.0, v6)** ทั้งรูปแบบ Excel (`.xlsx`, `.xls`), CSV (`.csv`), Text (`.txt`) และฐานข้อมูล MS Access (`.mdb`)
* ระบบจับคู่เวลาสแกนเข้า-ออกกับคำขอ OT ที่ได้รับอนุมัติแล้วให้อัตโนมัติ
* ปุ่ม **"ล้างข้อมูลสแกนเวลาทั้งหมด"** สำหรับเคลียร์ประวัติสแกนดิบหลังจากคำนวณเงินเดือนเสร็จสิ้น เพื่อลดภาระและประหยัดพื้นที่ฐานข้อมูล

### 3. ระบบคำร้องและการอนุมัติทำงานล่วงเวลา (Overtime Workflow)
* การอนุมัติแบบหลายขั้นตอน (Supervisor ยื่นคำขอ -> Manager พิจารณาอนุมัติ/ไม่อนุมัติ/ส่งกลับแก้ไข)
* ออกเอกสารใบยินยอมทำงานล่วงเวลา **Consent Form (PDF)** พร้อม QR Code และ Barcode อ้างอิงเอกสาร
* บันทึกเวลาปฏิบัติงานจริง (Actual Time) และการล็อกงวดจ่ายเงินประจำเดือน (Monthly Period Lock)

### 4. การคำนวณและส่งออกรายงาน (Payroll Calculation & Reports)
* คำนวณอัตรา OT ตามประเภท (1.0, 1.5, 2.0, 3.0 เท่า) รองรับทั้งพนักงานรายเดือน (Monthly) และรายวัน (Daily)
* ส่งออกไฟล์รายงานค่าตอบแทน OT สำหรับฝ่ายบัญชี/เงินเดือนในรูปแบบ Excel และ PDF

---

## 🛠️ การติดตั้งและการรันระบบ (Setup & Commands Guide)

### 1. การรันระบบด้วย Docker
```bash
# สั่งงาน Docker Container
docker compose up -d

# ตรวจสอบสถานะการทำงานของคอนเทนเนอร์
docker compose ps
```

### 2. คำสั่ง Artisan ที่ใช้อยู่เสมอ
```bash
# รันการทดสอบระบบทั้งหมด (Automated Tests)
docker exec ot_app php artisan test

# รัน Migration ฐานข้อมูล
docker exec ot_app php artisan migrate

# รัน Seeder สร้างข้อมูลเริ่มต้น
docker exec ot_app php artisan db:seed
```

---

## 📄 ลิขสิทธิ์และการพัฒนา (License & Credits)

* **พัฒนาโดย:** Advanced Agentic Coding Team
* **Framework:** Laravel 13.x (PHP 8.4)
* **Database:** MariaDB 10.11
