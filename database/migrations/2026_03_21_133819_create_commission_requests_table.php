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
        Schema::create('commission_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_waste_id')->nullable()->constrained()->onDelete('set null');
            $table->string('receiver_name');
            $table->string('receiver_phone')->nullable();
            $table->string('bank_account')->nullable();
            $table->decimal('amount', 15, 0)->default(0);
            $table->string('referrer_info')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('Chờ chi');
            $table->dateTime('processed_at')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_requests');
    }
};
