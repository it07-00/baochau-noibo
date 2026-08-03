<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_ghg_inventory')->default(false)->index()->after('representative');
            $table->boolean('is_energy_audit')->default(false)->index()->after('is_ghg_inventory');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['is_ghg_inventory']);
            $table->dropIndex(['is_energy_audit']);
            $table->dropColumn(['is_ghg_inventory', 'is_energy_audit']);
        });
    }
};
