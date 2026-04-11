<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_consultings', function (Blueprint $table) {
            $table->string('voucher_status')->nullable()->after('renewal_status');
        });

        Schema::table('contract_projects', function (Blueprint $table) {
            $table->string('voucher_status')->nullable()->after('renewal_status');
        });

        Schema::table('contract_commercials', function (Blueprint $table) {
            $table->string('voucher_status')->nullable()->after('renewal_status');
        });

        Schema::table('contract_sustainabilities', function (Blueprint $table) {
            $table->string('voucher_status')->nullable()->after('renewal_status');
        });

        Schema::table('contract_energies', function (Blueprint $table) {
            $table->string('voucher_status')->nullable()->after('renewal_status');
        });
    }

    public function down(): void
    {
        Schema::table('contract_consultings', function (Blueprint $table) {
            $table->dropColumn('voucher_status');
        });

        Schema::table('contract_projects', function (Blueprint $table) {
            $table->dropColumn('voucher_status');
        });

        Schema::table('contract_commercials', function (Blueprint $table) {
            $table->dropColumn('voucher_status');
        });

        Schema::table('contract_sustainabilities', function (Blueprint $table) {
            $table->dropColumn('voucher_status');
        });

        Schema::table('contract_energies', function (Blueprint $table) {
            $table->dropColumn('voucher_status');
        });
    }
};
