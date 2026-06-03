<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_document_items', function (Blueprint $table) {
            $table->string('item_type', 20)->default('detail')->after('quotation_document_id');
            $table->string('group_name', 255)->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_document_items', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'group_name']);
        });
    }
};
