<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('quotation_documents', 'template_key')) {
            return;
        }

        Schema::table('quotation_documents', function (Blueprint $table) {
            $table->string('template_key', 80)
                ->default('qtmtld')
                ->after('service_type');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('quotation_documents', 'template_key')) {
            return;
        }

        Schema::table('quotation_documents', function (Blueprint $table) {
            $table->dropColumn('template_key');
        });
    }
};
