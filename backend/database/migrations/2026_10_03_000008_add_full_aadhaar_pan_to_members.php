<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'aadhaar_no')) {
                $table->string('aadhaar_no', 25)->nullable()->after('aadhaar_last4');
            }
            if (!Schema::hasColumn('members', 'pan_no')) {
                $table->string('pan_no', 20)->nullable()->after('pan_last4');
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (Schema::hasColumn('members', 'aadhaar_no')) {
                $table->dropColumn('aadhaar_no');
            }
            if (Schema::hasColumn('members', 'pan_no')) {
                $table->dropColumn('pan_no');
            }
        });
    }
};
