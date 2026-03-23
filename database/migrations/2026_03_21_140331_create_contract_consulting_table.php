<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_consultings', function (Blueprint $table) {
            $table->id();
            $table->string('shd_ad')->nullable();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('staff_id')->constrained('users');
            $table->foreignId('department_id')->constrained();
            $table->date('signed_at')->nullable();
            $table->date('submitted_at')->nullable();
            $table->decimal('value', 15, 0)->default(0);
            $table->decimal('commission', 15, 0)->default(0);
            $table->decimal('revenue', 15, 0)->default(0);
            $table->string('province')->nullable();
            $table->string('info_source')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status')->nullable();
            $table->string('renewal_status')->nullable();
            $table->boolean('is_offset')->default(false);
            $table->boolean('has_room_fund')->default(false);
            $table->boolean('is_overdue')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_consultings');
    }
};
