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
        Schema::table('commission_requests', function (Blueprint $table) {
            $table->string('bank_code', 20)->nullable()->after('bank_account');
            $table->string('bank_number', 50)->nullable()->after('bank_code');
            $table->string('qr_path')->nullable()->after('bank_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_requests', function (Blueprint $table) {
            $table->dropColumn(['bank_code', 'bank_number', 'qr_path']);
        });
    }
};
