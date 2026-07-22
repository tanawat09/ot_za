<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name_th');
            $table->decimal('multiplier', 4, 2)->default(1.50); // e.g. 1.5, 2.0, 3.0
            $table->time('start_time_limit')->nullable();
            $table->time('end_time_limit')->nullable();
            $table->decimal('max_hours_per_day', 4, 2)->default(8.00);
            $table->boolean('requires_document')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_types');
    }
};
