<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_requests', function (Blueprint $table) {
            $table->string('contract_type')->nullable()->after('id');
            $table->unsignedBigInteger('contract_id')->nullable()->after('contract_type');
            $table->index(['contract_type', 'contract_id']);
        });

        // Migrate existing data
        DB::table('commission_requests')
            ->whereNotNull('contract_waste_id')
            ->update([
                'contract_type' => 'App\\Models\\ContractWaste',
                'contract_id'   => DB::raw('contract_waste_id'),
            ]);

        try {
            Schema::table('commission_requests', function (Blueprint $table) {
                $table->dropForeign(['contract_waste_id']);
            });
        } catch (\Throwable $e) {
            // Ignore if foreign key drop fails
        }

        Schema::table('commission_requests', function (Blueprint $table) {
            $table->dropColumn('contract_waste_id');
        });
    }

    public function down(): void
    {
        Schema::table('commission_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_waste_id')->nullable()->after('id');
        });

        DB::table('commission_requests')
            ->where('contract_type', 'App\\Models\\ContractWaste')
            ->update([
                'contract_waste_id' => DB::raw('contract_id'),
            ]);

        Schema::table('commission_requests', function (Blueprint $table) {
            $table->dropIndex(['contract_type', 'contract_id']);
            $table->dropColumn(['contract_type', 'contract_id']);
            $table->foreign('contract_waste_id')->references('id')->on('contract_wastes')->nullOnDelete();
        });
    }
};
