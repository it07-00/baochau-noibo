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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->foreignId('staff_id')->constrained('users');
            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->string('industry')->nullable();
            $table->string('contact_person')->nullable();
            $table->text('work_description')->nullable();
            $table->string('status')->default('Đang theo dõi');
            $table->decimal('original_value', 15, 0)->default(0);
            $table->decimal('commission_value', 15, 0)->default(0);
            $table->decimal('commission_tax', 15, 0)->default(0);
            $table->decimal('total_value', 15, 0)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
