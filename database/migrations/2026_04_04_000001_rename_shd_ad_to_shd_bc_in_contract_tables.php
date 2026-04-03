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
            'contract_commercials',
            'contract_projects',
            'contract_sustainabilities',
            'contract_energies',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->renameColumn('shd_ad', 'shd_bc');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'contract_wastes',
            'contract_consultings',
            'contract_commercials',
            'contract_projects',
            'contract_sustainabilities',
            'contract_energies',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->renameColumn('shd_bc', 'shd_ad');
            });
        }
    }
};
