<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\FinancialAutomation;
use App\Services\LoanSchedule;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('finance:run-daily', function () {
    $this->info(json_encode(FinancialAutomation::run(), JSON_PRETTY_PRINT));
})->purpose('Post due interest, update overdue installments, and issue emergency-loan SLA alerts');

Artisan::command('finance:backfill-loan-schedules', function () {
    $loans = DB::table('loan_applications')->whereIn('status',['approved','disbursed'])
        ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('loan_installments')
            ->whereColumn('loan_installments.loan_id','loan_applications.id'))->get();
    foreach ($loans as $loan) {
        LoanSchedule::generate($loan->id,(float)($loan->sanctioned_amount ?: $loan->amount),
            (float)($loan->annual_interest_rate ?: LoanSchedule::defaultRate($loan->loan_type)));
    }
    $this->info('Generated '.$loans->count().' missing loan schedules.');
})->purpose('Generate repayment schedules for previously approved loans');

Schedule::command('finance:run-daily')->dailyAt('00:10')->timezone('Asia/Kolkata')->withoutOverlapping();
