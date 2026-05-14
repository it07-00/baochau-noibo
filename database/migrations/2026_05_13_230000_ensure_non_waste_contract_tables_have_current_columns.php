<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'contract_consultings',
        'contract_projects',
        'contract_commercials',
        'contract_sustainabilities',
        'contract_energies',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'handler_id')) {
                    $table->foreignId('handler_id')->nullable()->after('customer_id')->constrained('handlers')->nullOnDelete();
                }

                if (!Schema::hasColumn($tableName, 'shd_cxl')) {
                    $table->string('shd_cxl')->nullable()->after('shd_bc');
                }

                if (!Schema::hasColumn($tableName, 'voucher_status')) {
                    $table->string('voucher_status')->nullable()->after('renewal_status');
                }

                if (!Schema::hasColumn($tableName, 'is_renewal')) {
                    $table->boolean('is_renewal')->default(false)->after('status');
                }

                if (!Schema::hasColumn($tableName, 'parent_contract_id')) {
                    $table->unsignedBigInteger('parent_contract_id')->nullable()->after('is_renewal');
                    $table->foreign('parent_contract_id')->references('id')->on($tableName)->nullOnDelete();
                }

                if (!Schema::hasColumn($tableName, 'ncc_payment')) {
                    $table->decimal('ncc_payment', 15, 0)->default(0)->after('revenue');
                }
            });
        }
    }

    public function down(): void
    {
        // This migration repairs partially migrated databases. Rolling it back must
        // not remove columns that may have been created by the original migrations.
    }
};
