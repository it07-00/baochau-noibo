<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('contract_wastes')
            ->where('status', 'Đã trình ký Chủ xử lý')
            ->update(['status' => 'Đã trình ký nhà thầu phụ']);

        DB::table('contract_wastes')
            ->where('status', 'Chủ xử lý đã gửi về')
            ->update(['status' => 'Nhà thầu phụ đã gửi về']);

        // Các bảng contract khác nếu có dùng cùng status
        foreach (['contract_consultings', 'contract_projects', 'contract_commercials', 'contract_sustainabilities', 'contract_energies'] as $table) {
            DB::table($table)
                ->where('status', 'Đã trình ký Chủ xử lý')
                ->update(['status' => 'Đã trình ký nhà thầu phụ']);

            DB::table($table)
                ->where('status', 'Chủ xử lý đã gửi về')
                ->update(['status' => 'Nhà thầu phụ đã gửi về']);
        }
    }

    public function down(): void
    {
        DB::table('contract_wastes')
            ->where('status', 'Đã trình ký nhà thầu phụ')
            ->update(['status' => 'Đã trình ký Chủ xử lý']);

        DB::table('contract_wastes')
            ->where('status', 'Nhà thầu phụ đã gửi về')
            ->update(['status' => 'Chủ xử lý đã gửi về']);

        foreach (['contract_consultings', 'contract_projects', 'contract_commercials', 'contract_sustainabilities', 'contract_energies'] as $table) {
            DB::table($table)
                ->where('status', 'Đã trình ký nhà thầu phụ')
                ->update(['status' => 'Đã trình ký Chủ xử lý']);

            DB::table($table)
                ->where('status', 'Nhà thầu phụ đã gửi về')
                ->update(['status' => 'Chủ xử lý đã gửi về']);
        }
    }
};
