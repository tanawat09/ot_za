<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HipAttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'emp_code',
        'employee_id',
        'log_date',
        'check_in',
        'check_out',
        'device_id',
        'import_batch',
        'remarks',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
