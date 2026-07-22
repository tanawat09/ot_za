<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hip_attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('emp_code', 50)->index(); // รหัสพนักงาน/รหัสสแกนจาก HIP Premium Time
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('log_date')->index(); // วันที่สแกน (YYYY-MM-DD)
            $table->time('check_in')->nullable(); // เวลาสแกนเข้า
            $table->time('check_out')->nullable(); // เวลาสแกนออก
            $table->string('device_id', 50)->nullable(); // รหัสเครื่องสแกน HIP
            $table->string('import_batch', 100)->nullable()->index(); // เลขล็อตการนำเข้า
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // Add salary column to employees for OT Payment calculation
        if (!Schema::hasColumn('employees', 'salary')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->decimal('salary', 12, 2)->default(15000.00)->after('phone'); // ฐานเงินเดือน/ค่าจ้าง
                $table->enum('wage_type', ['Monthly', 'Daily'])->default('Monthly')->after('salary'); // ประเภทค่าจ้าง (รายเดือน/รายวัน)
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hip_attendance_logs');

        if (Schema::hasColumn('employees', 'salary')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn(['salary', 'wage_type']);
            });
        }
    }
};
