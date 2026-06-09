<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_document_items', function (Blueprint $table) {
            $table->unsignedInteger('frequency')->default(1)->after('quantity');
        });

        Schema::table('quotation_document_section_rows', function (Blueprint $table) {
            $table->unsignedInteger('frequency')->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_document_items', function (Blueprint $table) {
            $table->dropColumn('frequency');
        });

        Schema::table('quotation_document_section_rows', function (Blueprint $table) {
            $table->dropColumn('frequency');
        });
    }
};
