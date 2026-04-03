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
        Schema::table('progressive_sales', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_schedule_id')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('progressive_sales', function (Blueprint $table) {
            $table->dropColumn('payment_schedule_id');
        });
    }
};
