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
        Schema::create('contract_payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('contract_type');
            $table->unsignedBigInteger('contract_id');
            $table->unsignedTinyInteger('installment_number')->default(1);
            $table->string('installment_name')->nullable();
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('amount', 15, 0)->default(0);
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->decimal('paid_amount', 15, 0)->default(0);
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['contract_type', 'contract_id'], 'cps_contract_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_payment_schedules');
    }
};
