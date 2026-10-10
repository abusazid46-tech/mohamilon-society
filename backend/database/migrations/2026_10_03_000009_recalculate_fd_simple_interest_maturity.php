<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Recalculate Fixed Deposit (FD) accounts to use Simple Interest instead of quarterly compounding.
     */
    public function up(): void
    {
        $fdAccounts = DB::table('accounts')
            ->join('schemes', 'accounts.scheme_id', '=', 'schemes.id')
            ->where('schemes.type', 'fd')
            ->select(
                'accounts.id',
                'accounts.principal',
                'accounts.custom_interest_rate',
                'schemes.annual_rate',
                'accounts.opened_on',
                'accounts.matures_on',
                'schemes.term_months'
            )
            ->get();

        foreach ($fdAccounts as $acc) {
            $principal = (float) $acc->principal;
            $rate = (float) ($acc->custom_interest_rate ?: $acc->annual_rate);

            // Determine tenure in months
            $tenureMonths = 12;
            if (!empty($acc->term_months) && (int) $acc->term_months > 0) {
                $tenureMonths = (int) $acc->term_months;
            } elseif (!empty($acc->opened_on) && !empty($acc->matures_on)) {
                $start = Carbon::parse($acc->opened_on);
                $end = Carbon::parse($acc->matures_on);
                $days = $start->diffInDays($end);

                if ($days >= 350 && $days <= 375) {
                    $tenureMonths = 12;
                } elseif ($days >= 700 && $days <= 750) {
                    $tenureMonths = 24;
                } elseif ($days >= 1050 && $days <= 1125) {
                    $tenureMonths = 36;
                } elseif ($days >= 170 && $days <= 195) {
                    $tenureMonths = 6;
                } elseif ($days >= 80 && $days <= 100) {
                    $tenureMonths = 3;
                } else {
                    $tenureMonths = max(1, (int) round($days / 30.4375));
                }
            }

            // Simple Interest formula: P * (r / 100) * (t / 12)
            $interest = round($principal * ($rate / 100) * ($tenureMonths / 12), 2);
            $newMaturity = round($principal + $interest, 2);

            DB::table('accounts')->where('id', $acc->id)->update([
                'maturity_amount' => $newMaturity,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for data recalculation
    }
};
