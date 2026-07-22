<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'emp_code',
        'user_id',
        'prefix',
        'first_name',
        'last_name',
        'position_id',
        'department_id',
        'team_id',
        'email',
        'phone',
        'status',
    ];

    public function getFullNameAttribute()
    {
        return trim("{$this->prefix} {$this->first_name} {$this->last_name}");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function supervisors()
    {
        return $this->belongsToMany(User::class, 'employee_supervisors', 'employee_id', 'supervisor_user_id')
                    ->withTimestamps();
    }
}
