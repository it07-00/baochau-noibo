<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_documents', function (Blueprint $table) {
            $table->string('price_subcontractor', 80)
                ->nullable()
                ->after('template_key');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_documents', function (Blueprint $table) {
            $table->dropColumn('price_subcontractor');
        });
    }
};
