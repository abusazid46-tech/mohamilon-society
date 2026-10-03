<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Parent & Family particulars
            if (!Schema::hasColumn('members', 'parent_name')) {
                $table->string('parent_name', 150)->nullable()->after('nominee_name');
            }
            if (!Schema::hasColumn('members', 'mother_name')) {
                $table->string('mother_name', 150)->nullable()->after('parent_name');
            }
            if (!Schema::hasColumn('members', 'father_name')) {
                $table->string('father_name', 150)->nullable()->after('mother_name');
            }
            if (!Schema::hasColumn('members', 'age')) {
                $table->unsignedSmallInteger('age')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('members', 'blood_group')) {
                $table->string('blood_group', 10)->nullable()->after('age');
            }
            if (!Schema::hasColumn('members', 'qualification')) {
                $table->string('qualification', 120)->nullable()->after('blood_group');
            }
            if (!Schema::hasColumn('members', 'marital_status')) {
                $table->string('marital_status', 30)->nullable()->after('qualification');
            }

            // Addresses
            if (!Schema::hasColumn('members', 'present_address')) {
                $table->text('present_address')->nullable()->after('address');
            }
            if (!Schema::hasColumn('members', 'pincode')) {
                $table->string('pincode', 20)->nullable()->after('present_address');
            }

            // Primary Banking Particulars
            if (!Schema::hasColumn('members', 'bank_name')) {
                $table->string('bank_name', 150)->nullable()->after('pincode');
            }
            if (!Schema::hasColumn('members', 'bank_account_no')) {
                $table->string('bank_account_no', 50)->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('members', 'bank_ifsc')) {
                $table->string('bank_ifsc', 25)->nullable()->after('bank_account_no');
            }
            if (!Schema::hasColumn('members', 'bank_branch')) {
                $table->string('bank_branch', 120)->nullable()->after('bank_ifsc');
            }

            // Secondary Banking Particulars (Multiple Bank Details Support)
            if (!Schema::hasColumn('members', 'bank2_name')) {
                $table->string('bank2_name', 150)->nullable()->after('bank_branch');
            }
            if (!Schema::hasColumn('members', 'bank2_account_no')) {
                $table->string('bank2_account_no', 50)->nullable()->after('bank2_name');
            }
            if (!Schema::hasColumn('members', 'bank2_ifsc')) {
                $table->string('bank2_ifsc', 25)->nullable()->after('bank2_account_no');
            }
            if (!Schema::hasColumn('members', 'bank2_branch')) {
                $table->string('bank2_branch', 120)->nullable()->after('bank2_ifsc');
            }

            // Employment & Tax Particulars
            if (!Schema::hasColumn('members', 'office_address')) {
                $table->text('office_address')->nullable()->after('occupation');
            }
            if (!Schema::hasColumn('members', 'designation_salary')) {
                $table->string('designation_salary', 150)->nullable()->after('office_address');
            }
            if (!Schema::hasColumn('members', 'relative_member_details')) {
                $table->string('relative_member_details', 255)->nullable()->after('designation_salary');
            }

            // Nominee Details
            if (!Schema::hasColumn('members', 'nominee_parent')) {
                $table->string('nominee_parent', 150)->nullable()->after('nominee_name');
            }
            if (!Schema::hasColumn('members', 'nominee_relation')) {
                $table->string('nominee_relation', 80)->nullable()->after('nominee_parent');
            }
            if (!Schema::hasColumn('members', 'nominee_occupation')) {
                $table->string('nominee_occupation', 120)->nullable()->after('nominee_relation');
            }
            if (!Schema::hasColumn('members', 'nominee_age')) {
                $table->unsignedSmallInteger('nominee_age')->nullable()->after('nominee_occupation');
            }
            if (!Schema::hasColumn('members', 'nominee_address')) {
                $table->text('nominee_address')->nullable()->after('nominee_age');
            }

            // Introducer 1
            if (!Schema::hasColumn('members', 'intro1_name')) {
                $table->string('intro1_name', 150)->nullable()->after('relative_member_details');
            }
            if (!Schema::hasColumn('members', 'intro1_parent')) {
                $table->string('intro1_parent', 150)->nullable()->after('intro1_name');
            }
            if (!Schema::hasColumn('members', 'intro1_account')) {
                $table->string('intro1_account', 50)->nullable()->after('intro1_parent');
            }
            if (!Schema::hasColumn('members', 'intro1_lf')) {
                $table->string('intro1_lf', 50)->nullable()->after('intro1_account');
            }
            if (!Schema::hasColumn('members', 'intro1_address')) {
                $table->text('intro1_address')->nullable()->after('intro1_lf');
            }

            // Introducer 2
            if (!Schema::hasColumn('members', 'intro2_name')) {
                $table->string('intro2_name', 150)->nullable()->after('intro1_address');
            }
            if (!Schema::hasColumn('members', 'intro2_parent')) {
                $table->string('intro2_parent', 150)->nullable()->after('intro2_name');
            }
            if (!Schema::hasColumn('members', 'intro2_account')) {
                $table->string('intro2_account', 50)->nullable()->after('intro2_parent');
            }
            if (!Schema::hasColumn('members', 'intro2_lf')) {
                $table->string('intro2_lf', 50)->nullable()->after('intro2_account');
            }
            if (!Schema::hasColumn('members', 'intro2_address')) {
                $table->text('intro2_address')->nullable()->after('intro2_lf');
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $columns = [
                'parent_name', 'mother_name', 'father_name', 'age', 'blood_group', 'qualification', 'marital_status',
                'present_address', 'pincode',
                'bank_name', 'bank_account_no', 'bank_ifsc', 'bank_branch',
                'bank2_name', 'bank2_account_no', 'bank2_ifsc', 'bank2_branch',
                'office_address', 'designation_salary', 'relative_member_details',
                'nominee_parent', 'nominee_relation', 'nominee_occupation', 'nominee_age', 'nominee_address',
                'intro1_name', 'intro1_parent', 'intro1_account', 'intro1_lf', 'intro1_address',
                'intro2_name', 'intro2_parent', 'intro2_account', 'intro2_lf', 'intro2_address',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('members', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
