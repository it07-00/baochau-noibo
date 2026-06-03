<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('quotation_documents', 'quotation_id')) {
            return;
        }

        Schema::table('quotation_documents', function (Blueprint $table) {
            $table->foreignId('quotation_id')
                ->nullable()
                ->after('id')
                ->constrained('quotations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('quotation_documents', 'quotation_id')) {
            return;
        }

        Schema::table('quotation_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quotation_id');
        });
    }
};
