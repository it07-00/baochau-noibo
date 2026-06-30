<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_consultings', function (Blueprint $table) {
            $table->decimal('payment_percentage', 5, 2)->default(100)->after('revenue');
            $table->text('service_content')->nullable()->after('loai_dich_vu');
            $table->string('submission_place', 500)->nullable()->after('service_content');
        });
    }

    public function down(): void
    {
        Schema::table('contract_consultings', function (Blueprint $table) {
            $table->dropColumn(['payment_percentage', 'service_content', 'submission_place']);
        });
    }
};
