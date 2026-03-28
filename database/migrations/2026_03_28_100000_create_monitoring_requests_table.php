<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_requests', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['environment', 'labor'])->default('environment')->index();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('sampling_location')->nullable();
            $table->text('content')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->unsignedInteger('duration_days')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('draft')->index();
            $table->string('workflow_status')->nullable();
            $table->text('notes')->nullable();
            $table->text('result_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_requests');
    }
};
