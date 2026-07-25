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
        if (Schema::hasTable('contract_wastes') && ! Schema::hasColumn('contract_wastes', 'collection_time')) {
            Schema::table('contract_wastes', function (Blueprint $table) {
                $table->string('collection_time')->nullable()->after('content');
            });
        }

        if (Schema::hasTable('quotations') && Schema::hasColumn('quotations', 'collection_time')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropColumn('collection_time');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('contract_wastes') && Schema::hasColumn('contract_wastes', 'collection_time')) {
            Schema::table('contract_wastes', function (Blueprint $table) {
                $table->dropColumn('collection_time');
            });
        }
    }
};
