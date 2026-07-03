<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('ward')->nullable()->after('province');
            $table->string('industrial_park')->nullable()->after('ward');

            $table->index('province');
            $table->index('ward');
            $table->index('industrial_park');
        });

    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['province']);
            $table->dropIndex(['ward']);
            $table->dropIndex(['industrial_park']);
            $table->dropColumn(['ward', 'industrial_park']);
        });
    }
};
