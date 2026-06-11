<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('contract_consultings')
            || ! Schema::hasTable('customers')
            || ! Schema::hasTable('handlers')
        ) {
            return;
        }

        $customerIds = DB::table('customers')
            ->where('name', 'Công ty CP Thương mại và xây dựng Tài Phú')
            ->pluck('id');

        $autoAssignedHandlerIds = DB::table('handlers')
            ->whereIn('name', [
                'Cong ty CP Cong Nghe Moi Truong Trai Dat Xanh',
                'Công ty CP Công Nghệ Môi Trường Trái Đất Xanh',
            ])
            ->pluck('id');

        if ($customerIds->isEmpty() || $autoAssignedHandlerIds->isEmpty()) {
            return;
        }

        DB::table('contract_consultings')
            ->whereIn('customer_id', $customerIds)
            ->whereIn('handler_id', $autoAssignedHandlerIds)
            ->where('shd_bc', '01/2026/HĐKT.BC-TAIPHU (2.040.000)')
            ->whereDate('signed_at', '2026-01-05')
            ->update(['handler_id' => null]);
    }

    public function down(): void
    {
        // Data correction cannot be reversed without reintroducing the wrong assignment.
    }
};
