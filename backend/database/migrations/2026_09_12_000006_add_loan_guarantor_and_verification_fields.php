<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->string('guarantor_name', 150)->nullable()->after('purpose');
            $table->string('guarantor_phone', 30)->nullable()->after('guarantor_name');
            $table->string('guarantor_relation', 80)->nullable()->after('guarantor_phone');
            $table->string('document_path')->nullable()->after('guarantor_relation');
            $table->foreignId('verified_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('verification_note')->nullable()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'guarantor_name',
                'guarantor_phone',
                'guarantor_relation',
                'document_path',
                'verified_by',
                'verified_at',
                'verification_note',
            ]);
        });
    }
};
