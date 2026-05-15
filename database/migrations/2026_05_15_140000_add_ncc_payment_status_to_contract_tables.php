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
                if (! Schema::hasColumn($tableName, 'ncc_payment_status')) {
                    $table->string('ncc_payment_status', 20)
                        ->default('unpaid')
                        ->after('ncc_payment_updated_at');
                }

                if (! Schema::hasColumn($tableName, 'ncc_payment_paid_at')) {
                    $table->date('ncc_payment_paid_at')
                        ->nullable()
                        ->after('ncc_payment_status');
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
                if (Schema::hasColumn($tableName, 'ncc_payment_paid_at')) {
                    $table->dropColumn('ncc_payment_paid_at');
                }

                if (Schema::hasColumn($tableName, 'ncc_payment_status')) {
                    $table->dropColumn('ncc_payment_status');
                }
            });
        }
    }
};
