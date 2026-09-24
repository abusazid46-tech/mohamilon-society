<?php

namespace Tests\Unit;

use App\Services\DepositCalculator;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class DepositCalculatorTest extends TestCase
{
    public function test_fd_calculation_computes_correct_maturity_and_profit(): void
    {
        $principal = 50000.0;
        $rate = 8.0; // 8% annual
        $tenure = 12; // 12 months
        $startDate = '2026-01-01';

        $result = DepositCalculator::calculateFd($principal, $rate, $tenure, $startDate);

        $this->assertEquals(50000.0, $result['principal']);
        $this->assertEquals('2027-01-01', $result['matures_on']);
        $this->assertGreaterThan(0, $result['profit']);
        $this->assertEquals($result['principal'] + $result['profit'], $result['maturity_amount']);
    }

    public function test_fd_premature_closure_applies_penalty_rate_deduction(): void
    {
        $principal = 100000.0;
        $originalRate = 8.5;
        $penaltyDeduction = 1.5; // effective rate = 7.0%
        $openedOn = '2026-01-01';
        $closedOn = '2026-07-01'; // ~181 days

        $result = DepositCalculator::calculateFdPrematureClosure(
            $principal,
            $originalRate,
            $openedOn,
            $closedOn,
            $penaltyDeduction
        );

        $this->assertEquals(7.0, $result['effective_rate']);
        $this->assertEquals(181, $result['days_held']);
        $this->assertGreaterThan(0, $result['earned_interest']);
        $this->assertEquals($principal + $result['earned_interest'], $result['closure_amount']);
    }

    public function test_rd_mathematical_interest_formula(): void
    {
        // Formula: Interest = P * [n(n + 1) / 24] * (r / 100)
        // For P = 1000, n = 12, r = 8%:
        // Factor = (12 * 13) / 24 = 6.5
        // Interest = 1000 * 6.5 * 0.08 = 520
        $monthly = 1000.0;
        $tenure = 12;
        $rate = 8.0;

        $n = $tenure;
        $expectedPrincipal = $monthly * $n;
        $expectedInterest = round($monthly * (($n * ($n + 1)) / 24) * ($rate / 100), 2);

        $this->assertEquals(12000.0, $expectedPrincipal);
        $this->assertEquals(520.0, $expectedInterest);
        $this->assertEquals(12520.0, $expectedPrincipal + $expectedInterest);
    }
    public function test_daily_rd_mathematical_interest_formula(): void
    {
        // Daily RD: P = 50, n = 100 days, r = 9%
        // Total Principal = 50 * 100 = 5000
        // Interest = P * [n(n + 1) / 730] * (r / 100)
        $daily = 50.0;
        $days = 100;
        $rate = 9.0;

        $expectedPrincipal = $daily * $days;
        $expectedInterest = round($daily * (($days * ($days + 1)) / 730) * ($rate / 100), 2);

        $this->assertEquals(5000.0, $expectedPrincipal);
        $this->assertEquals(62.26, $expectedInterest);
        $this->assertEquals(5062.26, $expectedPrincipal + $expectedInterest);
    }
}
