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
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'ncc_payment_sheet_url')) {
                    $table->string('ncc_payment_sheet_url', 2048)
                        ->nullable()
                        ->after('ncc_payment');
                }
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
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'ncc_payment_sheet_url')) {
                    $table->dropColumn('ncc_payment_sheet_url');
                }
            });
        }
    }
};
