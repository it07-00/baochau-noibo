<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contract_wastes') || ! Schema::hasColumn('contract_wastes', 'handler_id')) {
            return;
        }

        Schema::table('contract_wastes', function (Blueprint $table) {
            $table->unsignedBigInteger('handler_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Existing records may legitimately have no subcontractor after this migration.
    }
};
