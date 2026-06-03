<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('quotation_document_sections')) {
            Schema::table('quotation_document_sections', function (Blueprint $table) {
                $table->index(['quotation_document_id', 'sort_order'], 'qdoc_sections_doc_sort_idx');
                $table->index('section_key', 'qdoc_sections_key_idx');
            });

            return;
        }

        Schema::create('quotation_document_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_document_id')
                ->constrained('quotation_documents')
                ->cascadeOnDelete();
            $table->string('section_key', 80);
            $table->string('section_type', 50)->default('table');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('title')->nullable();
            $table->json('columns')->nullable();
            $table->json('settings')->nullable();
            $table->json('totals')->nullable();
            $table->timestamps();

            $table->index(['quotation_document_id', 'sort_order'], 'qdoc_sections_doc_sort_idx');
            $table->index('section_key', 'qdoc_sections_key_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_document_sections');
    }
};
