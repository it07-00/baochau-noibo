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
        $tables = [
            'contract_energies',
            'contract_wastes',
            'contract_projects',
            'contract_commercials',
            'contract_sustainabilities',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('workflow_status')->nullable()->after('status');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'contract_energies',
            'contract_wastes',
            'contract_projects',
            'contract_commercials',
            'contract_sustainabilities',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'workflow_status')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('workflow_status');
                });
            }
        }
    }
};
