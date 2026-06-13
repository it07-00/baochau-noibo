<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('commission_requests')
            ->where('status', 'Chờ chi')
            ->update(['status' => 'Dự chi']);

        DB::table('commission_requests')
            ->where('status', 'Đã chi')
            ->whereNull('payment_bill_path')
            ->update(['status' => 'Đã duyệt']);

        Schema::table('commission_requests', function (Blueprint $table) {
            $table->string('status')->default('Dự chi')->change();
        });
    }

    public function down(): void
    {
        DB::table('commission_requests')
            ->where('status', 'Dự chi')
            ->update(['status' => 'Chờ chi']);

        DB::table('commission_requests')
            ->where('status', 'Đã duyệt')
            ->update(['status' => 'Đã chi']);

        Schema::table('commission_requests', function (Blueprint $table) {
            $table->string('status')->default('Chờ chi')->change();
        });
    }
};
