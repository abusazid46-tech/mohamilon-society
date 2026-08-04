<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 15)->unique()->nullable();
            $table->enum('role', ['super_admin','president','manager','secretary','agent','member'])->default('member')->index();
            $table->enum('status', ['pending','active','suspended'])->default('pending')->index();
            $table->string('mpin_hash')->nullable();
            $table->timestamp('last_login_at')->nullable();
        });
        Schema::create('members', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('member_no')->unique(); $table->text('address'); $table->string('occupation')->nullable();
            $table->string('nominee_name'); $table->date('date_of_birth')->nullable();
            $table->string('aadhaar_last4', 4)->nullable(); $table->string('pan_last4', 4)->nullable();
            $table->enum('kyc_status', ['not_submitted','pending','approved','rejected'])->default('not_submitted')->index();
            $table->timestamp('approved_at')->nullable(); $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); $table->softDeletes();
        });
        Schema::create('schemes', function (Blueprint $table) {
            $table->id(); $table->string('code')->unique(); $table->string('name');
            $table->enum('type', ['savings','rd','fd']); $table->unsignedInteger('term_months')->nullable();
            $table->decimal('annual_rate', 6, 3); $table->decimal('minimum_amount', 14, 2); $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('accounts', function (Blueprint $table) {
            $table->id(); $table->string('account_no')->unique(); $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->foreignId('scheme_id')->constrained()->restrictOnDelete(); $table->decimal('principal', 14, 2)->default(0);
            $table->decimal('balance', 14, 2)->default(0); $table->enum('status', ['pending','active','matured','closed','rejected'])->default('pending')->index();
            $table->date('opened_on')->nullable(); $table->date('matures_on')->nullable(); $table->timestamps();
        });
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id(); $table->uuid('reference')->unique(); $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->enum('direction', ['credit','debit']); $table->decimal('amount', 14, 2); $table->string('description');
            $table->enum('status', ['pending','posted','reversed'])->default('posted')->index(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable(); $table->timestamps();
        });
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id(); $table->string('application_no')->unique(); $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 14, 2); $table->decimal('monthly_income', 14, 2); $table->unsignedSmallInteger('tenure_months'); $table->text('purpose');
            $table->enum('status', ['submitted','under_review','approved','rejected','disbursed','closed'])->default('submitted')->index();
            $table->text('decision_note')->nullable(); $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
        Schema::create('kyc_documents', function (Blueprint $table) {
            $table->id(); $table->foreignId('member_id')->constrained()->cascadeOnDelete(); $table->enum('type', ['aadhaar','pan','photo','address_proof']);
            $table->string('disk')->default('private'); $table->string('path'); $table->string('sha256', 64); $table->string('mime', 80);
            $table->enum('status', ['pending','approved','rejected'])->default('pending'); $table->text('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('reviewed_at')->nullable(); $table->timestamps();
        });
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('token_hash', 64)->unique();
            $table->string('device_name'); $table->timestamp('last_used_at')->nullable(); $table->timestamp('expires_at')->index(); $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('action')->index();
            $table->string('entity_type'); $table->unsignedBigInteger('entity_id')->nullable(); $table->json('before')->nullable(); $table->json('after')->nullable();
            $table->ipAddress('ip_address')->nullable(); $table->string('user_agent', 500)->nullable(); $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void
    {
        foreach (['audit_logs','api_tokens','kyc_documents','loan_applications','ledger_entries','accounts','schemes','members'] as $table) Schema::dropIfExists($table);
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['phone','role','status','mpin_hash','last_login_at']));
    }
};
