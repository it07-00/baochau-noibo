<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month'); // 1-12
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('target_amount', 15, 0)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['year', 'month', 'staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_targets');
    }
};
