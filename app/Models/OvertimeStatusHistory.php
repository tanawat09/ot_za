<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'overtime_request_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'remarks',
    ];

    public function overtimeRequest()
    {
        return $this->belongsTo(OvertimeRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
