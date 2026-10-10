<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class DepositCalculator
{
    /**
     * Calculates Fixed Deposit (FD) maturity date, profit, and maturity amount using Simple Interest.
     * Formula:
     * Interest = Principal * (Annual Rate / 100) * (Tenure in Months / 12)
     * Maturity Amount = Principal + Interest
     */
    public static function calculateFd(
        float $principal,
        float $annualRate,
        int $tenureMonths,
        ?string $openingDate = null
    ): array {
        $startDate = Carbon::parse($openingDate ?: today());
        $maturityDate = $startDate->copy()->addMonthsNoOverflow($tenureMonths);

        // Simple interest formula: Interest = P * (r / 100) * (t / 12)
        $t = $tenureMonths / 12;
        $profit = round($principal * ($annualRate / 100) * $t, 2);
        $maturityAmount = round($principal + $profit, 2);

        return [
            'principal' => round($principal, 2),
            'annual_rate' => $annualRate,
            'tenure_months' => $tenureMonths,
            'opened_on' => $startDate->toDateString(),
            'matures_on' => $maturityDate->toDateString(),
            'profit' => $profit,
            'maturity_amount' => $maturityAmount,
        ];
    }

    /**
     * Calculates FD Premature Closure values with applicable early withdrawal rate deduction.
     */
    public static function calculateFdPrematureClosure(
        float $principal,
        float $originalRate,
        string $openedOn,
        ?string $closedOn = null,
        float $penaltyDeduction = 1.0
    ): array {
        $start = Carbon::parse($openedOn);
        $end = Carbon::parse($closedOn ?: today());
        $daysHeld = max(0, $start->diffInDays($end));
        $effectiveRate = max(0, $originalRate - $penaltyDeduction);

        // Simple day-count pro-rated earned interest
        $earnedInterest = round($principal * ($effectiveRate / 100) * ($daysHeld / 365), 2);
        $totalPayable = round($principal + $earnedInterest, 2);

        return [
            'principal' => $principal,
            'days_held' => $daysHeld,
            'effective_rate' => $effectiveRate,
            'penalty_rate_deducted' => $penaltyDeduction,
            'earned_interest' => $earnedInterest,
            'closure_amount' => $totalPayable,
        ];
    }

    /**
     * Generates and persists an RD installment schedule (Section 5).
     * Supports both 'monthly' and 'daily' recurring deposit frequencies.
     */
    public static function generateRdSchedule(
        int $accountId,
        float $installmentAmount,
        int $tenure,
        float $annualRate,
        ?string $startDate = null,
        string $frequency = 'monthly',
        ?int $tenureDays = null
    ): array {
        $start = Carbon::parse($startDate ?: today());
        DB::table('rd_installments')->where('account_id', $accountId)->delete();

        $isDaily = in_array(strtolower($frequency), ['daily', 'day'], true);

        if ($isDaily) {
            // Daily Recurring Deposit:
            // $n is total days (e.g. 100 days, 365 days)
            $n = $tenureDays ?: ($tenure <= 12 ? ($tenure === 12 ? 365 : $tenure * 30) : $tenure);
            $totalPrincipal = round($installmentAmount * $n, 2);
            // Day-wise interest formula: P * [n(n + 1) / (2 * 365)] * (r / 100)
            $profit = round($installmentAmount * (($n * ($n + 1)) / 730) * ($annualRate / 100), 2);
            $maturityAmount = round($totalPrincipal + $profit, 2);
            $maturityDate = $start->copy()->addDays($n);

            $installments = [];
            for ($i = 1; $i <= $n; $i++) {
                $dueOn = $start->copy()->addDays($i - 1)->toDateString();
                $installments[] = [
                    'account_id' => $accountId,
                    'installment_no' => $i,
                    'due_on' => $dueOn,
                    'amount_due' => round($installmentAmount, 2),
                    'penalty_due' => 0,
                    'paid_amount' => 0,
                    'status' => 'scheduled',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($installments, 150) as $chunk) {
                DB::table('rd_installments')->insert($chunk);
            }
        } else {
            // Monthly Recurring Deposit:
            $n = $tenure;
            $totalPrincipal = round($installmentAmount * $n, 2);
            $profit = round($installmentAmount * (($n * ($n + 1)) / 24) * ($annualRate / 100), 2);
            $maturityAmount = round($totalPrincipal + $profit, 2);
            $maturityDate = $start->copy()->addMonthsNoOverflow($n);

            $installments = [];
            $currentDate = $start->copy();

            for ($i = 1; $i <= $n; $i++) {
                $dueOn = $currentDate->toDateString();
                $installments[] = [
                    'account_id' => $accountId,
                    'installment_no' => $i,
                    'due_on' => $dueOn,
                    'amount_due' => round($installmentAmount, 2),
                    'penalty_due' => 0,
                    'paid_amount' => 0,
                    'status' => 'scheduled',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $currentDate->addMonthNoOverflow();
            }

            foreach (array_chunk($installments, 150) as $chunk) {
                DB::table('rd_installments')->insert($chunk);
            }
        }

        // Update the master account with calculated maturity and commitments
        DB::table('accounts')->where('id', $accountId)->update([
            'principal' => $totalPrincipal,
            'maturity_amount' => $maturityAmount,
            'monthly_commitment' => round($installmentAmount, 2),
            'opened_on' => $start->toDateString(),
            'matures_on' => $maturityDate->toDateString(),
            'updated_at' => now(),
        ]);

        return [
            'total_principal' => $totalPrincipal,
            'profit' => $profit,
            'maturity_amount' => $maturityAmount,
            'installments_count' => $n,
            'matures_on' => $maturityDate->toDateString(),
            'frequency' => $isDaily ? 'daily' : 'monthly',
        ];
    }
}
