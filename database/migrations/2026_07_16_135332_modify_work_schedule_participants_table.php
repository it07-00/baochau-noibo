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
        if (DB::getDriverName() === 'mysql') {
            // Drop foreign keys by querying actual constraint names — safe on all Laravel versions
            $this->dropForeignByNameIfExists('work_schedule_participants', 'work_schedule_participants_work_schedule_id_foreign');
            $this->dropForeignByNameIfExists('work_schedule_participants', 'work_schedule_participants_user_id_foreign');
        }

        Schema::table('work_schedule_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();

            if (! Schema::hasColumn('work_schedule_participants', 'greeco_user_id')) {
                $table->unsignedBigInteger('greeco_user_id')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('work_schedule_participants', 'greeco_user_name')) {
                $table->string('greeco_user_name')->nullable()->after('greeco_user_id');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            // Drop the old unique constraint if it still exists
            $this->dropUniqueByNameIfExists('work_schedule_participants', 'work_schedule_participants_work_schedule_id_user_id_unique');

            Schema::table('work_schedule_participants', function (Blueprint $table) {
                $table->foreign('work_schedule_id')->references('id')->on('work_schedules')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (! $this->uniqueExists('work_schedule_participants', 'wsp_schedule_user_greeco_unique')) {
            Schema::table('work_schedule_participants', function (Blueprint $table) {
                $table->unique(['work_schedule_id', 'user_id', 'greeco_user_id'], 'wsp_schedule_user_greeco_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $this->dropForeignByNameIfExists('work_schedule_participants', 'work_schedule_participants_work_schedule_id_foreign');
            $this->dropForeignByNameIfExists('work_schedule_participants', 'work_schedule_participants_user_id_foreign');
        }

        $this->dropUniqueByNameIfExists('work_schedule_participants', 'wsp_schedule_user_greeco_unique');

        Schema::table('work_schedule_participants', function (Blueprint $table) {
            if (Schema::hasColumn('work_schedule_participants', 'greeco_user_id')) {
                $table->dropColumn('greeco_user_id');
            }

            if (Schema::hasColumn('work_schedule_participants', 'greeco_user_name')) {
                $table->dropColumn('greeco_user_name');
            }

            $table->unsignedBigInteger('user_id')->change();
        });

        if (DB::getDriverName() === 'mysql') {
            Schema::table('work_schedule_participants', function (Blueprint $table) {
                $table->foreign('work_schedule_id')->references('id')->on('work_schedules')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->unique(['work_schedule_id', 'user_id']);
            });
        }
    }

    private function uniqueExists(string $table, string $name): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $count = DB::select(
            "SELECT COUNT(*) as cnt FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = 'UNIQUE'",
            [$table, $name]
        );

        return ($count[0]->cnt ?? 0) > 0;
    }

    private function dropForeignByNameIfExists(string $table, string $name): void
    {
        $count = DB::select(
            "SELECT COUNT(*) as cnt FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$table, $name]
        );

        if (($count[0]->cnt ?? 0) > 0) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$name}`");
        }
    }

    private function dropUniqueByNameIfExists(string $table, string $name): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $count = DB::select(
            "SELECT COUNT(*) as cnt FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = 'UNIQUE'",
            [$table, $name]
        );

        if (($count[0]->cnt ?? 0) > 0) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
        }
    }
};
