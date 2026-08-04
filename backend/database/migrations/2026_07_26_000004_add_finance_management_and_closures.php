<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loan_installments', function (Blueprint $table) {
            $table->text('penalty_note')->nullable()->after('penalty_due');
            $table->foreignId('penalty_applied_by')->nullable()->after('penalty_note')->constrained('users')->nullOnDelete();
            $table->timestamp('penalty_applied_at')->nullable()->after('penalty_applied_by');
        });

        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->decimal('principal_component', 14, 2)->default(0)->after('amount');
            $table->decimal('interest_component', 14, 2)->default(0)->after('principal_component');
            $table->decimal('penalty_component', 14, 2)->default(0)->after('interest_component');
        });

        Schema::create('cash_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->enum('location', ['bank','treasury'])->index();
            $table->enum('direction', ['credit','debit']);
            $table->string('category', 60)->index();
            $table->decimal('amount', 14, 2);
            $table->string('source_type', 60)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('note');
            $table->date('occurred_on')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['source_type','source_id']);
        });

        Schema::create('profit_loss_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->enum('entry_type', ['income','expense'])->index();
            $table->string('category', 60)->index();
            $table->decimal('amount', 14, 2);
            $table->string('source_type', 60)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('note');
            $table->date('occurred_on')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['source_type','source_id']);
        });

        Schema::create('account_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['notice_period','withdrawal_available','completed','rejected'])->default('notice_period')->index();
            $table->date('requested_on');
            $table->date('maturity_on');
            $table->date('withdrawal_available_on')->index();
            $table->decimal('closure_amount', 14, 2)->nullable();
            $table->text('note')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['account_id','status']);
        });

        foreach ([
            'default_account_term_months' => '12',
            'closure_notice_days' => '30',
            'default_overdue_fine_amount' => '0',
        ] as $key => $value) {
            DB::table('society_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        DB::table('accounts')->where('status', 'active')->whereNotNull('opened_on')->whereNull('matures_on')
            ->get()->each(function (object $account) {
                DB::table('accounts')->where('id', $account->id)->update([
                    'matures_on' => \Carbon\Carbon::parse($account->opened_on)->addMonthsNoOverflow(12)->subDay()->toDateString(),
                    'updated_at' => now(),
                ]);
            });

        $openingBankBalance = (float) DB::table('accounts')->where('status', 'active')->sum('balance');
        if ($openingBankBalance > 0) {
            DB::table('cash_ledger_entries')->insert([
                'reference' => (string) Str::uuid(),
                'location' => 'bank',
                'direction' => 'credit',
                'category' => 'opening_balance',
                'amount' => $openingBankBalance,
                'source_type' => 'migration',
                'note' => 'Opening bank balance from active member account balances',
                'occurred_on' => today()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (DB::table('interest_postings')->get() as $posting) {
            DB::table('profit_loss_entries')->insert([
                'reference' => (string) Str::uuid(),
                'entry_type' => 'expense',
                'category' => 'member_interest',
                'amount' => $posting->interest_amount,
                'source_type' => 'interest_posting',
                'source_id' => $posting->id,
                'note' => 'Interest credited to member account',
                'occurred_on' => $posting->period_end,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('account_closures');
        Schema::dropIfExists('profit_loss_entries');
        Schema::dropIfExists('cash_ledger_entries');
        Schema::table('loan_repayments', fn (Blueprint $table) =>
            $table->dropColumn(['principal_component','interest_component','penalty_component'])
        );
        Schema::table('loan_installments', function (Blueprint $table) {
            $table->dropForeign(['penalty_applied_by']);
            $table->dropColumn(['penalty_note','penalty_applied_by','penalty_applied_at']);
        });
    }
};
