<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_request_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overtime_request_id')->constrained('overtime_requests')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('break_minutes')->default(0);
            $table->decimal('planned_hours', 8, 2)->default(0.00);
            $table->enum('consent_status', ['PENDING', 'CONSENTED', 'REFUSED'])->default('PENDING');
            $table->timestamp('consent_signed_at')->nullable();
            $table->string('remarks')->nullable();
            $table->time('actual_start_time')->nullable();
            $table->time('actual_end_time')->nullable();
            $table->integer('actual_break_minutes')->default(0);
            $table->decimal('actual_hours', 8, 2)->nullable();
            $table->foreignId('actual_recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['overtime_request_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_request_employees');
    }
};
