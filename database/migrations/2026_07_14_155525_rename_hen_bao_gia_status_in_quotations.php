<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Đổi giá trị status 'hẹn báo giá thời gian sau' và 'Hẹn báo giá' → 'Hẹn báo giá sau' cho ngắn gọn và nhất quán.
     */
    public function up(): void
    {
        DB::table('quotations')
            ->whereIn('status', ['hẹn báo giá thời gian sau', 'Hẹn báo giá'])
            ->update(['status' => 'Hẹn báo giá sau']);
    }

    public function down(): void
    {
        DB::table('quotations')
            ->where('status', 'Hẹn báo giá sau')
            ->update(['status' => 'hẹn báo giá thời gian sau']);
    }
};
