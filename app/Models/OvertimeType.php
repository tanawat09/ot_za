<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_th',
        'multiplier',
        'start_time_limit',
        'end_time_limit',
        'max_hours_per_day',
        'requires_document',
        'is_active',
    ];

    protected $casts = [
        'multiplier' => 'decimal:2',
        'max_hours_per_day' => 'decimal:2',
        'requires_document' => 'boolean',
        'is_active' => 'boolean',
    ];
}
