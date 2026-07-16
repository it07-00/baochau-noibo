<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_schedule_participants', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['work_schedule_id']);
                $table->dropForeign(['user_id']);
            }
        });

        Schema::table('work_schedule_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('greeco_user_id')->nullable()->after('user_id');
            $table->string('greeco_user_name')->nullable()->after('greeco_user_id');
        });

        Schema::table('work_schedule_participants', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropUnique('work_schedule_participants_work_schedule_id_user_id_unique');
                $table->foreign('work_schedule_id')->references('id')->on('work_schedules')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            }
            $table->unique(['work_schedule_id', 'user_id', 'greeco_user_id'], 'wsp_schedule_user_greeco_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_schedule_participants', function (Blueprint $table) {
            $table->dropUnique('wsp_schedule_user_greeco_unique');
            if (DB::getDriverName() === 'mysql') {
                $table->dropForeign(['work_schedule_id']);
                $table->dropForeign(['user_id']);
            }
        });

        Schema::table('work_schedule_participants', function (Blueprint $table) {
            $table->dropColumn(['greeco_user_id', 'greeco_user_name']);
            $table->unsignedBigInteger('user_id')->change();
        });

        Schema::table('work_schedule_participants', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->foreign('work_schedule_id')->references('id')->on('work_schedules')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->unique(['work_schedule_id', 'user_id']);
            }
        });
    }
};
