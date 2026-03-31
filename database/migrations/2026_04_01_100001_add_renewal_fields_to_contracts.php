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
        'contract_sustainabilities',
        'contract_energies',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->boolean('is_renewal')->default(false)->after('status');
                $t->unsignedBigInteger('parent_contract_id')->nullable()->after('is_renewal');
                $t->foreign('parent_contract_id')->references('id')->on($table)->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['parent_contract_id']);
                $t->dropColumn(['is_renewal', 'parent_contract_id']);
            });
        }
    }
};
