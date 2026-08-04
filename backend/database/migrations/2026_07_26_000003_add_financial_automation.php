<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->date('last_interest_posted_on')->nullable()->after('opened_on');
        });
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->decimal('annual_interest_rate', 6, 3)->nullable()->after('sanctioned_amount');
            $table->decimal('outstanding_principal', 14, 2)->nullable()->after('annual_interest_rate');
            $table->date('repayment_started_on')->nullable();
            $table->date('next_due_on')->nullable()->index();
        });
        Schema::create('interest_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->date('period_start'); $table->date('period_end');
            $table->decimal('annual_rate', 6, 3); $table->decimal('opening_balance', 14, 2);
            $table->decimal('interest_amount', 14, 2); $table->foreignId('ledger_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->unique(['account_id','period_end']);
        });
        Schema::create('loan_installments', function (Blueprint $table) {
            $table->id(); $table->foreignId('loan_id')->constrained('loan_applications')->cascadeOnDelete();
            $table->unsignedInteger('installment_no'); $table->date('due_on')->index();
            $table->decimal('principal_due', 14, 2); $table->decimal('interest_due', 14, 2);
            $table->decimal('penalty_due', 14, 2)->default(0); $table->decimal('paid_amount', 14, 2)->default(0);
            $table->enum('status', ['scheduled','due','partial','paid','overdue'])->default('scheduled')->index();
            $table->timestamp('paid_at')->nullable(); $table->timestamps();
            $table->unique(['loan_id','installment_no']);
        });
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id(); $table->string('reference')->unique();
            $table->foreignId('loan_id')->constrained('loan_applications')->restrictOnDelete();
            $table->decimal('amount', 14, 2); $table->string('utr', 80);
            $table->enum('status', ['submitted','accepted','rejected'])->default('submitted')->index();
            $table->text('review_note')->nullable(); $table->foreignId('submitted_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('submitted_at'); $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable(); $table->timestamps();
        });
        Schema::create('member_notifications', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('dedupe_key')->nullable(); $table->string('category', 40)->index();
            $table->string('title'); $table->text('message'); $table->string('action_url')->nullable();
            $table->timestamp('read_at')->nullable(); $table->timestamps();
            $table->unique(['user_id','dedupe_key']);
        });
        // Existing balances remain untouched; automated accrual starts on deployment day.
        DB::table('accounts')->where('status','active')->whereNull('last_interest_posted_on')
            ->update(['last_interest_posted_on'=>today()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('member_notifications');
        Schema::dropIfExists('loan_repayments');
        Schema::dropIfExists('loan_installments');
        Schema::dropIfExists('interest_postings');
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn(['annual_interest_rate','outstanding_principal','repayment_started_on','next_due_on']);
        });
        Schema::table('accounts', fn (Blueprint $table) => $table->dropColumn('last_interest_posted_on'));
    }
};
