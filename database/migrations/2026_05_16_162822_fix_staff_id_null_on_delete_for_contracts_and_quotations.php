<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'contract_wastes',
        'contract_consultings',
        'contract_projects',
        'contract_commercials',
        'quotations',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $tbl) {
                $tbl->dropForeign(['staff_id']);
                $tbl->unsignedBigInteger('staff_id')->nullable()->change();
                $tbl->foreign('staff_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $tbl) {
                $tbl->dropForeign(['staff_id']);
                $tbl->unsignedBigInteger('staff_id')->nullable(false)->change();
                $tbl->foreign('staff_id')->references('id')->on('users');
            });
        }
    }
};
