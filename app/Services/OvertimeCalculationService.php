<?php

namespace App\Services;

use Carbon\Carbon;

class OvertimeCalculationService
{
    /**
     * Calculate OT hours with cross-midnight support and break time deduction.
     *
     * @param string $date YYYY-MM-DD
     * @param string $startTime HH:MM or HH:MM:SS
     * @param string $endTime HH:MM or HH:MM:SS
     * @param int $breakMinutes
     * @return array ['total_hours' => float, 'is_cross_midnight' => bool]
     */
    public static function calculate(string $date, string $startTime, string $endTime, int $breakMinutes = 0): array
    {
        $start = Carbon::parse("{$date} {$startTime}");
        $end = Carbon::parse("{$date} {$endTime}");

        $isCrossMidnight = false;

        if ($end->lessThanOrEqualTo($start)) {
            // End time is on the next day
            $end->addDay();
            $isCrossMidnight = true;
        }

        $durationMinutes = $start->diffInMinutes($end);
        $netMinutes = max(0, $durationMinutes - $breakMinutes);
        $totalHours = round($netMinutes / 60, 2);

        return [
            'total_hours' => $totalHours,
            'is_cross_midnight' => $isCrossMidnight,
        ];
    }
}
