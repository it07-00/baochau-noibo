<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code', 20)->nullable()->unique()->after('id');
            $table->string('id_card_number', 20)->nullable()->after('address');
            $table->date('id_card_issued_date')->nullable()->after('id_card_number');
            $table->string('id_card_issued_place')->nullable()->after('id_card_issued_date');
            $table->string('hometown')->nullable()->after('id_card_issued_place');
            $table->string('permanent_address')->nullable()->after('hometown');
            $table->string('temporary_address')->nullable()->after('permanent_address');
            $table->string('tax_code', 20)->nullable()->after('temporary_address');
            $table->string('social_insurance_number', 20)->nullable()->after('tax_code');
            $table->string('bank_account', 30)->nullable()->after('social_insurance_number');
            $table->string('bank_name')->nullable()->after('bank_account');
            $table->string('emergency_contact_name')->nullable()->after('bank_name');
            $table->string('emergency_contact_phone', 30)->nullable()->after('emergency_contact_name');
            $table->string('education_level')->nullable()->after('emergency_contact_phone');
            $table->string('major')->nullable()->after('education_level');
            $table->date('start_date')->nullable()->after('major');
            $table->date('end_date')->nullable()->after('start_date');
            $table->string('employment_status', 20)->default('thu_viec')->after('end_date');
            $table->string('work_type', 20)->default('full_time')->after('employment_status');
            $table->text('hr_notes')->nullable()->after('work_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_code', 'id_card_number', 'id_card_issued_date', 'id_card_issued_place',
                'hometown', 'permanent_address', 'temporary_address',
                'tax_code', 'social_insurance_number', 'bank_account', 'bank_name',
                'emergency_contact_name', 'emergency_contact_phone',
                'education_level', 'major', 'start_date', 'end_date',
                'employment_status', 'work_type', 'hr_notes',
            ]);
        });
    }
};
