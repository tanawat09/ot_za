<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_period_locks', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->foreignId('department_id')->nullable()->constrained('departments')->cascadeOnDelete();
            $table->enum('status', ['LOCKED', 'OPEN'])->default('LOCKED');
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['year', 'month', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_period_locks');
    }
};
