<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // workflow_status was already added in the create migration.
        // This migration is kept for tracking purposes only.
        if (Schema::hasTable('monitoring_requests') && !Schema::hasColumn('monitoring_requests', 'workflow_status')) {
            Schema::table('monitoring_requests', function (Blueprint $table) {
                $table->string('workflow_status')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        //
    }
};
