<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'overtime_request_id',
        'action_by_user_id',
        'action',
        'comment',
        'ip_address',
        'user_agent',
    ];

    public function overtimeRequest()
    {
        return $this->belongsTo(OvertimeRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'action_by_user_id');
    }
}
