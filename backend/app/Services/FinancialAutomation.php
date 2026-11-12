<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class FinancialAutomation
{
    public static function run(): array
    {
        return [
            'interest_postings' => self::postDueInterest(),
            'overdue_installments' => self::markOverdueInstallments(),
            'rd_dues' => self::syncRdDues(),
            'closures_available' => self::activateClosureWithdrawals(),
            'emergency_alerts' => self::alertEmergencyLoans(),
        ];
    }

    public static function postDueInterest(): int
    {
        $posted = 0;
        $accounts = DB::table('accounts')->join('schemes','schemes.id','=','accounts.scheme_id')
            ->where('accounts.status','active')->where('accounts.balance','>',0)
            ->select('accounts.*','schemes.deposit_frequency','schemes.annual_rate')->get();
        foreach ($accounts as $account) {
            $rate = (float) ($account->custom_interest_rate ?? $account->annual_rate);
            if ($rate <= 0) continue;
            $daily = $account->deposit_frequency === 'daily';
            for ($catchUp = 0; $catchUp < 400; $catchUp++) {
                $last = Carbon::parse($account->last_interest_posted_on ?: $account->opened_on ?: $account->created_at)->startOfDay();
                $periodEnd = $daily
                    ? $last->copy()->addDay()
                    : ($account->last_interest_posted_on ? $last->copy()->addDay()->endOfMonth() : $last->copy()->endOfMonth());
                if ($periodEnd->isFuture()) break;
                $opening = self::ledgerBalanceOn($account->id,$last);
                $interest = 0.0;
                for ($day=$last->copy()->addDay(); $day->lte($periodEnd); $day->addDay()) {
                    $interest += self::ledgerBalanceOn($account->id,$day) * ($rate / 100) / 365;
                }
                $interest = round($interest,2);
                if ($interest <= 0) break;
                $inserted = DB::transaction(function () use ($account,$last,$periodEnd,$rate,$opening,$interest) {
                    $exists = DB::table('interest_postings')->where('account_id',$account->id)->whereDate('period_end',$periodEnd)->exists();
                    if ($exists) return false;
                    $ledgerId = DB::table('ledger_entries')->insertGetId([
                        'reference' => (string) Str::uuid(), 'account_id' => $account->id, 'direction' => 'credit',
                        'amount' => $interest, 'description' => 'Interest posting through '.$periodEnd->toDateString(),
                        'status' => 'posted', 'posted_at' => $periodEnd->copy()->startOfDay(), 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    DB::table('accounts')->where('id',$account->id)->increment('balance',$interest,[
                        'last_interest_posted_on'=>$periodEnd->toDateString(),'updated_at'=>now()
                    ]);
                    $postingId = DB::table('interest_postings')->insertGetId([
                        'account_id'=>$account->id,'period_start'=>$last->toDateString(),'period_end'=>$periodEnd->toDateString(),
                        'annual_rate'=>$rate,'opening_balance'=>$opening,'interest_amount'=>$interest,'ledger_entry_id'=>$ledgerId,
                        'created_at'=>now(),'updated_at'=>now(),
                    ]);
                    FinanceLedger::profitLoss(
                        'expense', 'member_interest', $interest,
                        'Interest credited to member account '.$account->account_no,
                        null, 'interest_posting', $postingId, $periodEnd->toDateString()
                    );
                    return true;
                });
                $account->last_interest_posted_on = $periodEnd->toDateString();
                if ($inserted) { $account->balance = $opening + $interest; $posted++; }
                else $account->balance = (float)DB::table('accounts')->where('id',$account->id)->value('balance');
            }
        }
        return $posted;
    }

    private static function ledgerBalanceOn(int $accountId, Carbon $date): float
    {
        return (float) DB::table('ledger_entries')->where('account_id',$accountId)->where('status','posted')
            ->where('posted_at','<=',$date->copy()->endOfDay())
            ->selectRaw("COALESCE(SUM(CASE WHEN direction='credit' THEN amount ELSE -amount END),0) balance")
            ->value('balance');
    }

    public static function markOverdueInstallments(): int
    {
        $count = 0;
        self::syncRdDues();

        DB::table('loan_installments')->whereDate('due_on','<=',today())->where('status','scheduled')
            ->update(['status'=>'due','updated_at'=>now()]);
        $installments = DB::table('loan_installments')->join('loan_applications','loan_applications.id','=','loan_installments.loan_id')
            ->whereDate('loan_installments.due_on','<',today())->whereIn('loan_installments.status',['scheduled','due','partial'])
            ->select('loan_installments.*','loan_applications.member_id')->get();
        foreach ($installments as $installment) {
            DB::table('loan_installments')->where('id',$installment->id)->update([
                'status'=>'overdue','updated_at'=>now()
            ]);
            $userId = MemberNotifier::memberUserId($installment->member_id);
            MemberNotifier::send($userId,'loan_overdue','Loan installment overdue',
                'Installment '.$installment->installment_no.' is overdue. Society staff will review any applicable fine.',
                'installment-overdue-'.$installment->id,'/loan');
            CreditScore::recalculate($installment->member_id);
            $count++;
        }
        return $count;
    }

    public static function syncRdDues(): array
    {
        $markedDue = DB::table('rd_installments')
            ->whereDate('due_on', '<=', today())
            ->where('status', 'scheduled')
            ->update(['status' => 'due', 'updated_at' => now()]);

        $markedOverdue = DB::table('rd_installments')
            ->whereDate('due_on', '<', today())
            ->whereIn('status', ['due', 'partial'])
            ->update(['status' => 'overdue', 'updated_at' => now()]);

        return [
            'marked_due' => $markedDue,
            'marked_overdue' => $markedOverdue,
        ];
    }

    public static function activateClosureWithdrawals(): int
    {
        return DB::table('account_closures')
            ->where('status', 'notice_period')
            ->whereDate('withdrawal_available_on', '<=', today())
            ->update(['status' => 'withdrawal_available', 'updated_at' => now()]);
    }

    public static function alertEmergencyLoans(): int
    {
        $count = 0;
        $loans = DB::table('loan_applications')->where('loan_type','emergency')
            ->whereIn('status',['submitted','under_review'])->where('created_at','<=',now()->subDays(2))->get();
        foreach ($loans as $loan) {
            $days = Carbon::parse($loan->created_at)->diffInDays(now());
            $overdue = $days >= 3;
            $title = $overdue ? 'Emergency loan SLA breached' : 'Emergency loan decision due tomorrow';
            foreach (DB::table('users')->whereIn('role',['super_admin','president','manager'])->where('status','active')->pluck('id') as $userId) {
                MemberNotifier::send((int)$userId,'emergency_sla',$title,
                    $loan->application_no.' requires an immediate decision.',
                    'emergency-'.$loan->id.'-'.($overdue?'breach':'warning'),'/admin#loans');
            }
            MemberNotifier::send(MemberNotifier::memberUserId($loan->member_id),'emergency_sla',
                $overdue ? 'Emergency loan decision delayed' : 'Emergency loan is under priority review',
                $overdue ? 'Your emergency loan exceeded the three-day decision target and has been escalated.' : 'Your emergency loan is due for a decision within one day.',
                'member-emergency-'.$loan->id.'-'.($overdue?'breach':'warning'),'/loan');
            $count++;
        }
        return $count;
    }
}
