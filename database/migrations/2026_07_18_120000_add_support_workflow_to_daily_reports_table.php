<?php

use App\Enums\DailyReportStatus;
use App\Enums\DailyReportSupportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->string('support_status')->nullable()->after('issues')->index();
            $table->foreignId('support_handler_id')->nullable()->after('support_status')->constrained('users')->nullOnDelete();
            $table->text('support_response')->nullable()->after('support_handler_id');
            $table->timestamp('support_started_at')->nullable()->after('support_response');
            $table->timestamp('support_resolved_at')->nullable()->after('support_started_at');
        });

        DB::table('daily_reports')
            ->where('status', DailyReportStatus::GAP_VAN_DE->value)
            ->orWhereRaw("TRIM(COALESCE(issues, '')) <> ''")
            ->update(['support_status' => DailyReportSupportStatus::PENDING->value]);
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('support_handler_id');
            $table->dropColumn([
                'support_status',
                'support_response',
                'support_started_at',
                'support_resolved_at',
            ]);
        });
    }
};
