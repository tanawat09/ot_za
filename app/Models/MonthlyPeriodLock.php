<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyPeriodLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'department_id',
        'status',
        'locked_at',
        'locked_by_user_id',
        'remarks',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by_user_id');
    }

    public static function isLocked(int $year, int $month, ?int $departmentId = null): bool
    {
        $query = self::where('year', $year)
            ->where('month', $month)
            ->where('status', 'LOCKED');

        if ($departmentId) {
            $query->where(function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId)
                  ->orWhereNull('department_id');
            });
        }

        return $query->exists();
    }
}
