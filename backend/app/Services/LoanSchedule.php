<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class LoanSchedule
{
    public static function defaultRate(string $type): float
    {
        return match ($type) { 'business' => 12.0, 'emergency' => 10.0, default => 14.0 };
    }

    public static function generate(int $loanId, float $principal, float $annualRate): void
    {
        $loan = DB::table('loan_applications')->where('id', $loanId)->firstOrFail();
        DB::table('loan_installments')->where('loan_id', $loanId)->delete();
        $frequency = $loan->emi_frequency;
        $periods = match ($frequency) {
            'daily' => max(1, (int) $loan->tenure_months * 30),
            'weekly' => max(1, (int) ceil((int) $loan->tenure_months * 4.345)),
            default => max(1, (int) $loan->tenure_months),
        };
        $periodsPerYear = match ($frequency) { 'daily' => 365, 'weekly' => 52, default => 12 };
        $periodRate = ($annualRate / 100) / $periodsPerYear;
        $payment = $periodRate > 0
            ? $principal * $periodRate * pow(1 + $periodRate, $periods) / (pow(1 + $periodRate, $periods) - 1)
            : $principal / $periods;
        $remaining = $principal;
        $date = Carbon::today();
        for ($i = 1; $i <= $periods; $i++) {
            $date = match ($frequency) { 'daily' => $date->addDay(), 'weekly' => $date->addWeek(), default => $date->addMonthNoOverflow() };
            $interest = round($remaining * $periodRate, 2);
            $principalDue = $i === $periods ? round($remaining, 2) : round(min($remaining, $payment - $interest), 2);
            $remaining = max(0, $remaining - $principalDue);
            DB::table('loan_installments')->insert([
                'loan_id' => $loanId, 'installment_no' => $i, 'due_on' => $date->toDateString(),
                'principal_due' => $principalDue, 'interest_due' => $interest, 'penalty_due' => 0,
                'paid_amount' => 0, 'status' => 'scheduled', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        DB::table('loan_applications')->where('id', $loanId)->update([
            'annual_interest_rate' => $annualRate, 'outstanding_principal' => $principal,
            'repayment_started_on' => today(), 'next_due_on' => DB::table('loan_installments')->where('loan_id',$loanId)->min('due_on'),
            'updated_at' => now(),
        ]);
    }

    public static function applyRepayment(int $loanId, float $amount): array
    {
        $remainingPayment = round($amount, 2);
        $principalApplied = 0.0;
        $interestApplied = 0.0;
        $penaltyApplied = 0.0;
        $installments = DB::table('loan_installments')->where('loan_id', $loanId)
            ->whereIn('status', ['scheduled','due','partial','overdue'])->orderBy('installment_no')->lockForUpdate()->get();
        foreach ($installments as $installment) {
            if ($remainingPayment <= 0) break;
            $totalDue = (float) $installment->principal_due + (float) $installment->interest_due + (float) $installment->penalty_due;
            $remainingDue = max(0, $totalDue - (float) $installment->paid_amount);
            $applied = min($remainingDue, $remainingPayment);
            $newPaid = round((float) $installment->paid_amount + $applied, 2);
            $beforePenalty = min((float) $installment->penalty_due, (float) $installment->paid_amount);
            $afterPenalty = min((float) $installment->penalty_due, $newPaid);
            $beforeInterest = min((float) $installment->interest_due, max(0, (float) $installment->paid_amount - (float) $installment->penalty_due));
            $afterInterest = min((float) $installment->interest_due, max(0, $newPaid - (float) $installment->penalty_due));
            $principalBefore = min((float) $installment->principal_due, max(0, (float) $installment->paid_amount - (float) $installment->penalty_due - (float) $installment->interest_due));
            $principalAfter = min((float) $installment->principal_due, max(0, $newPaid - (float) $installment->penalty_due - (float) $installment->interest_due));
            $penaltyApplied += max(0, $afterPenalty - $beforePenalty);
            $interestApplied += max(0, $afterInterest - $beforeInterest);
            $principalApplied += max(0, $principalAfter - $principalBefore);
            $paid = $newPaid + 0.005 >= $totalDue;
            DB::table('loan_installments')->where('id', $installment->id)->update([
                'paid_amount' => $newPaid, 'status' => $paid ? 'paid' : 'partial',
                'paid_at' => $paid ? now() : null, 'updated_at' => now(),
            ]);
            $remainingPayment = round($remainingPayment - $applied, 2);
        }
        $principalApplied = min((float) DB::table('loan_applications')->where('id',$loanId)->value('outstanding_principal'), $principalApplied);
        DB::table('loan_applications')->where('id', $loanId)->decrement('outstanding_principal', $principalApplied);
        $open = DB::table('loan_installments')->where('loan_id',$loanId)->where('status','!=','paid')->orderBy('installment_no')->first();
        DB::table('loan_applications')->where('id',$loanId)->update([
            'next_due_on' => $open?->due_on, 'status' => $open ? 'disbursed' : 'closed',
            'updated_at' => now(),
        ]);
        return [
            'principal' => round($principalApplied, 2),
            'interest' => round($interestApplied, 2),
            'penalty' => round($penaltyApplied, 2),
            'unallocated' => round($remainingPayment, 2),
        ];
    }
}
