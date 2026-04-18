<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('device_uid')->comment('ID trên máy chấm công');
            $table->string('name');
            $table->timestamps();

            $table->unique('device_uid');
        });

        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('attendance_employees')->cascadeOnDelete();
            $table->dateTime('checked_at');
            $table->timestamps();

            $table->index(['employee_id', 'checked_at']);
        });

        Schema::create('attendance_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imported_by')->constrained('users')->cascadeOnDelete();
            $table->string('month', 7)->comment('YYYY-MM');
            $table->unsignedInteger('total_records')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_imports');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('attendance_employees');
    }
};
