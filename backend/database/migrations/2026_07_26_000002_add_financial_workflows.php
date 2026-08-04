<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('role');
        });
        Schema::table('members', function (Blueprint $table) {
            $table->unsignedSmallInteger('mcs_score')->default(500)->after('kyc_status');
        });
        Schema::table('schemes', function (Blueprint $table) {
            $table->string('deposit_frequency', 20)->nullable()->after('type');
            $table->text('description')->nullable();
        });
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedSmallInteger('share_count')->default(1)->after('scheme_id');
            $table->decimal('monthly_commitment', 14, 2)->default(0)->after('share_count');
            $table->decimal('custom_interest_rate', 6, 3)->nullable()->after('balance');
            $table->string('onboarding_status', 30)->default('awaiting_review')->after('status')->index();
            $table->text('decision_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
        });
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->string('loan_type', 30)->default('personal')->after('member_id');
            $table->string('emi_frequency', 20)->default('monthly')->after('tenure_months');
            $table->decimal('sanctioned_amount', 14, 2)->nullable()->after('amount');
            $table->unsignedSmallInteger('mcs_snapshot')->nullable();
            $table->boolean('is_offer')->default(false);
            $table->timestamp('offer_expires_at')->nullable();
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('purpose')->default('account_opening');
            $table->string('upi_uri', 1000)->nullable();
            $table->string('utr', 80)->nullable();
            $table->string('receipt_disk')->nullable();
            $table->string('receipt_path')->nullable();
            $table->enum('status', ['requested','submitted','accepted','rejected'])->default('requested')->index();
            $table->text('review_note')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('society_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        DB::table('accounts')->where('status', 'active')->update(['onboarding_status' => 'active']);
        DB::table('accounts')->where('status', 'rejected')->update(['onboarding_status' => 'rejected']);
    }

    public function down(): void
    {
        Schema::dropIfExists('society_settings');
        Schema::dropIfExists('payments');
        Schema::table('loan_applications', fn (Blueprint $table) => $table->dropColumn(['loan_type','emi_frequency','sanctioned_amount','mcs_snapshot','is_offer','offer_expires_at']));
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['share_count','monthly_commitment','custom_interest_rate','onboarding_status','decision_note','approved_by','approved_at']);
        });
        Schema::table('schemes', fn (Blueprint $table) => $table->dropColumn(['deposit_frequency','description']));
        Schema::table('members', fn (Blueprint $table) => $table->dropColumn('mcs_score'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('permissions'));
    }
};
