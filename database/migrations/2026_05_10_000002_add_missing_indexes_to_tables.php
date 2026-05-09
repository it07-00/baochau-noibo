<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $contractTables = [
        'contract_wastes',
        'contract_consultings',
        'contract_projects',
        'contract_commercials',
        'contract_sustainabilities',
        'contract_energies',
    ];

    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'daily_reports_user_id_date_idx');
        });

        foreach ($this->contractTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $prefix = str_replace(['contract_', 's'], ['', ''], $tbl);
                $table->index('status', "{$tbl}_status_idx");
                $table->index('signed_at', "{$tbl}_signed_at_idx");
            });
        }
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropIndex('daily_reports_user_id_date_idx');
        });

        foreach ($this->contractTables as $tbl) {
            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                $table->dropIndex("{$tbl}_status_idx");
                $table->dropIndex("{$tbl}_signed_at_idx");
            });
        }
    }
};
