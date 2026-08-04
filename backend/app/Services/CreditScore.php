<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class CreditScore
{
    public static function recalculate(int $memberId): int
    {
        $postedCredits = (int) DB::table('ledger_entries')->join('accounts','accounts.id','=','ledger_entries.account_id')
            ->where('accounts.member_id',$memberId)->where('ledger_entries.status','posted')->where('direction','credit')->count();
        $activeMonths = DB::table('accounts')->where('member_id',$memberId)->where('status','active')->pluck('opened_on')
            ->filter()->sum(fn ($date) => max(0, Carbon::parse($date)->diffInMonths(today())));
        $acceptedPayments = (int) DB::table('payments')->join('accounts','accounts.id','=','payments.account_id')
            ->where('accounts.member_id',$memberId)->where('payments.status','accepted')->count();
        $rejectedPayments = (int) DB::table('payments')->join('accounts','accounts.id','=','payments.account_id')
            ->where('accounts.member_id',$memberId)->where('payments.status','rejected')->count();
        $rejectedLoans = (int) DB::table('loan_applications')->where('member_id',$memberId)->where('status','rejected')->count();
        $closedLoans = (int) DB::table('loan_applications')->where('member_id',$memberId)->where('status','closed')->count();
        $paidOnTime = (int) DB::table('loan_installments')->join('loan_applications','loan_applications.id','=','loan_installments.loan_id')
            ->where('loan_applications.member_id',$memberId)->where('loan_installments.status','paid')
            ->whereRaw('DATE(loan_installments.paid_at) <= loan_installments.due_on')->count();
        $overdue = (int) DB::table('loan_installments')->join('loan_applications','loan_applications.id','=','loan_installments.loan_id')
            ->where('loan_applications.member_id',$memberId)->where('loan_installments.status','overdue')->count();

        $score = 500 + min(120, $postedCredits * 3) + min(80, $activeMonths * 2)
            + min(50, $acceptedPayments * 5) + min(100,$paidOnTime * 5) + ($closedLoans * 35)
            - ($rejectedPayments * 20) - ($rejectedLoans * 15) - min(200,$overdue * 20);
        $score = max(300, min(900, $score));
        DB::table('members')->where('id',$memberId)->update(['mcs_score'=>$score,'updated_at'=>now()]);
        return $score;
    }
}
