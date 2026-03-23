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
        Schema::create('renewal_sales', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->nullable();
            $table->date('sales_month');
            $table->decimal('sales_value', 15, 0)->default(0);
            $table->decimal('commission', 15, 0)->default(0);
            $table->decimal('sales_percentage', 5, 2)->default(0);
            $table->decimal('sales_amount', 15, 0)->default(0);
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->string('file_path')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('renewal_sales');
    }
};
