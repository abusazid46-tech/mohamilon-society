<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name', 120);
            $table->string('account_number', 50)->unique();
            $table->string('ifsc_code', 20)->nullable();
            $table->string('branch_name', 120)->nullable();
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->decimal('current_balance', 14, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 40)->unique();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained('loan_applications')->nullOnDelete();
            $table->string('transaction_reference', 80)->nullable()->index();
            $table->string('category', 40)->index();
            $table->decimal('amount', 14, 2);
            $table->enum('payment_mode', ['cash', 'bank', 'upi'])->default('cash');
            $table->string('payer_name', 150)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('rd_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->unsignedInteger('installment_no');
            $table->date('due_on')->index();
            $table->decimal('amount_due', 14, 2);
            $table->decimal('penalty_due', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->enum('status', ['scheduled', 'due', 'partial', 'paid', 'overdue'])->default('scheduled')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['account_id', 'installment_no']);
        });

        Schema::table('profit_loss_entries', function (Blueprint $table) {
            $table->string('bill_disk', 40)->nullable()->after('note');
            $table->string('bill_path')->nullable()->after('bill_disk');
        });

        Schema::table('cash_ledger_entries', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('location')->constrained('bank_accounts')->nullOnDelete();
            $table->unsignedBigInteger('transfer_target_id')->nullable()->after('bank_account_id');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->string('certificate_no', 40)->nullable()->unique()->after('account_no');
            $table->decimal('maturity_amount', 14, 2)->nullable()->after('principal');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['certificate_no', 'maturity_amount']);
        });

        Schema::table('cash_ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn(['bank_account_id', 'transfer_target_id']);
        });

        Schema::table('profit_loss_entries', function (Blueprint $table) {
            $table->dropColumn(['bill_disk', 'bill_path']);
        });

        Schema::dropIfExists('rd_installments');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('bank_accounts');
    }
};
