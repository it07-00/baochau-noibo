<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contract_wastes', function (Blueprint $table) {
            $table->string('waste_type')->nullable()->after('voucher_status');
            $table->string('service_type')->nullable()->after('waste_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_wastes', function (Blueprint $table) {
            $table->dropColumn(['waste_type', 'service_type']);
        });
    }
};
