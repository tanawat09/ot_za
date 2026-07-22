<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeRequestEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'overtime_request_id',
        'employee_id',
        'start_time',
        'end_time',
        'break_minutes',
        'planned_hours',
        'consent_status',
        'consent_signed_at',
        'remarks',
        'actual_start_time',
        'actual_end_time',
        'actual_break_minutes',
        'actual_hours',
        'actual_recorded_by_user_id',
    ];

    protected $casts = [
        'planned_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'consent_signed_at' => 'datetime',
    ];

    public function overtimeRequest()
    {
        return $this->belongsTo(OvertimeRequest::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
