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
        Schema::create('contract_wastes', function (Blueprint $table) {
            $table->id();
            $table->string('shd_cxl')->nullable();
            $table->string('shd_ad')->nullable();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('handler_id')->constrained();
            $table->foreignId('staff_id')->constrained('users');
            $table->foreignId('department_id')->constrained();
            $table->text('content')->nullable();
            $table->decimal('value', 15, 0)->default(0);
            $table->decimal('commission', 15, 0)->default(0);
            $table->decimal('revenue', 15, 0)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('source')->nullable();
            $table->date('signed_at')->nullable();
            $table->date('effective_at')->nullable();
            $table->date('end_at')->nullable();
            $table->date('submitted_at')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('execution_address')->nullable();
            $table->text('mailing_address')->nullable();
            $table->string('status')->nullable();
            $table->string('renewal_status')->nullable();
            $table->string('voucher_status')->nullable();
            $table->boolean('is_offset')->default(false);
            $table->boolean('is_overdue')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_wastes');
    }
};
