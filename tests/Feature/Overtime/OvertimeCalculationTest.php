<?php

namespace Tests\Feature\Overtime;

use App\Services\OvertimeCalculationService;
use Tests\TestCase;

class OvertimeCalculationTest extends TestCase
{
    public function test_normal_ot_hours_calculation(): void
    {
        $calc = OvertimeCalculationService::calculate('2026-07-22', '17:30', '20:30', 0);
        $this->assertEquals(3.00, $calc['total_hours']);
        $this->assertFalse($calc['is_cross_midnight']);
    }

    public function test_ot_hours_calculation_with_break_deduction(): void
    {
        $calc = OvertimeCalculationService::calculate('2026-07-22', '17:00', '21:00', 30);
        $this->assertEquals(3.50, $calc['total_hours']);
        $this->assertFalse($calc['is_cross_midnight']);
    }

    public function test_cross_midnight_ot_hours_calculation(): void
    {
        // 20:00 to 02:00 next day = 6 hours - 60 mins break = 5 hours
        $calc = OvertimeCalculationService::calculate('2026-07-22', '20:00', '02:00', 60);
        $this->assertEquals(5.00, $calc['total_hours']);
        $this->assertTrue($calc['is_cross_midnight']);
    }
}
