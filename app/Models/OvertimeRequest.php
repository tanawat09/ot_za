<?php

namespace App\Models;

use App\Enums\OvertimeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OvertimeRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_no',
        'department_id',
        'team_id',
        'created_by_user_id',
        'manager_user_id',
        'overtime_type_id',
        'request_date',
        'start_time',
        'end_time',
        'break_minutes',
        'total_hours',
        'is_cross_midnight',
        'location',
        'reason',
        'work_details',
        'status',
        'submitted_at',
        'approved_at',
        'approval_comment',
        'updated_by_user_id',
    ];

    protected $casts = [
        'request_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_cross_midnight' => 'boolean',
        'total_hours' => 'decimal:2',
        'status' => OvertimeStatus::class,
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function overtimeType()
    {
        return $this->belongsTo(OvertimeType::class);
    }

    public function employees()
    {
        return $this->hasMany(OvertimeRequestEmployee::class);
    }

    public function consents()
    {
        return $this->hasMany(OvertimeConsent::class);
    }

    public function approvals()
    {
        return $this->hasMany(OvertimeApproval::class)->orderBy('created_at', 'desc');
    }

    public function statusHistories()
    {
        return $this->hasMany(OvertimeStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [OvertimeStatus::DRAFT, OvertimeStatus::RETURNED, OvertimeStatus::WAITING_CONSENT]);
    }
}
