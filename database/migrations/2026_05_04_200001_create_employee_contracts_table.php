<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('contract_number')->nullable();
            $table->string('contract_type', 30);
            $table->date('signed_date');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary', 15, 0)->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};
