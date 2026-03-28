<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_bao_chau', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_waste_id')->nullable()->constrained('contract_wastes')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('invoice_number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('amount', 15, 0)->default(0);
            $table->unsignedTinyInteger('vat_percent')->default(10);
            $table->decimal('vat_amount', 15, 0)->default(0);
            $table->decimal('total_amount', 15, 0)->default(0);
            $table->string('status')->default('unpaid');
            $table->decimal('paid_amount', 15, 0)->default(0);
            $table->date('paid_at')->nullable();
            $table->text('service_description')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_bao_chau');
    }
};
