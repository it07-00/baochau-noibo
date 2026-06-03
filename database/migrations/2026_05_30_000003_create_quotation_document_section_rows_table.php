<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('quotation_document_section_rows')) {
            return;
        }

        Schema::create('quotation_document_section_rows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_document_section_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('row_type', 50)->default('item');
            $table->string('group_name')->nullable();
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('quantity', 12, 2)->nullable();
            $table->bigInteger('unit_price')->nullable();
            $table->bigInteger('amount')->nullable();
            $table->json('columns')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('quotation_document_section_id', 'qdoc_section_rows_section_fk')
                ->references('id')
                ->on('quotation_document_sections')
                ->cascadeOnDelete();
            $table->index(['quotation_document_section_id', 'sort_order'], 'qdoc_section_rows_section_sort_idx');
            $table->index('row_type', 'qdoc_section_rows_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_document_section_rows');
    }
};
