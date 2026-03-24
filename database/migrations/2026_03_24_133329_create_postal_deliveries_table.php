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
        Schema::create('postal_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->index();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('address')->nullable();
            $table->string('sender_name')->nullable()->index();
            $table->string('bill_viettel')->nullable()->index();
            $table->string('bill_247')->nullable()->index();
            $table->text('content')->nullable();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('sent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postal_deliveries');
    }
};
