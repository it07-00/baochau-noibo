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
        Schema::table('contract_consultings', function (Blueprint $table) {
            $table->unsignedBigInteger('consultant_id')->nullable()->after('staff_id');
            $table->unsignedBigInteger('manager_id')->nullable()->after('consultant_id'); // TP Kinh doanh
            $table->timestamp('assigned_at')->nullable()->after('submitted_at');
            $table->timestamp('completed_at')->nullable()->after('assigned_at');
            
            $table->foreign('consultant_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_consultings', function (Blueprint $table) {
            $table->dropForeign(['consultant_id']);
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['consultant_id', 'manager_id', 'assigned_at', 'completed_at']);
        });
    }
};
