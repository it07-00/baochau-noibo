<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_document_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_document_id')->constrained('quotation_documents')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('description');
            $table->string('unit', 50)->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->bigInteger('unit_price')->default(0);
            $table->bigInteger('amount')->default(0);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index('quotation_document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_document_items');
    }
};
