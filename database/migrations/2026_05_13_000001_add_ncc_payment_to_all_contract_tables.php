<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'contract_wastes',
            'contract_consultings',
            'contract_projects',
            'contract_commercials',
            'contract_sustainabilities',
            'contract_energies',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->decimal('ncc_payment', 15, 0)->default(0)->after('revenue');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'contract_wastes',
            'contract_consultings',
            'contract_projects',
            'contract_commercials',
            'contract_sustainabilities',
            'contract_energies',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('ncc_payment');
            });
        }
    }
};
