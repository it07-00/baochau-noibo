<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 100);
            $table->date('date');
            $table->date('valid_until')->nullable();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_address', 500)->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('customer_contact')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_tax_code', 50)->nullable();
            $table->string('service_type')->nullable();
            $table->string('work_location', 500)->nullable();
            $table->bigInteger('subtotal')->default(0);
            $table->tinyInteger('vat_rate')->default(8);
            $table->bigInteger('vat_amount')->default(0);
            $table->bigInteger('total')->default(0);
            $table->bigInteger('discount')->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->string('docx_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index('staff_id');
            $table->index('date');
            $table->index('document_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_documents');
    }
};
