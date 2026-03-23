<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotation_sales', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->nullable();
            $table->foreignId('staff_id')->constrained('users');
            $table->date('sales_month');
            $table->string('service')->nullable();
            $table->string('info_source')->nullable();
            $table->date('quotation_date')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->decimal('value_ext_vat', 15, 0)->default(0);
            $table->decimal('commission', 15, 0)->default(0);
            $table->decimal('sales_percentage', 5, 2)->default(0);
            $table->decimal('sales_amount', 15, 0)->default(0);
            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->string('province')->nullable();
            $table->text('content')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->integer('total_workers')->default(0);
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_sales');
    }
};
