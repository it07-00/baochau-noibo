<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postal_deliveries', function (Blueprint $table) {
            // Thông tin người nhận cho VTP API
            $table->integer('receiver_province')->nullable()->after('address');
            $table->integer('receiver_district')->nullable()->after('receiver_province');
            $table->integer('receiver_ward')->nullable()->after('receiver_district');

            // Thông tin đơn hàng VTP
            $table->string('vtp_order_code')->nullable()->index()->after('bill_247');
            $table->string('vtp_service', 10)->nullable()->default('VCN')->after('vtp_order_code');
            $table->integer('vtp_weight')->nullable()->default(100)->after('vtp_service');
            $table->decimal('vtp_total_fee', 15, 0)->nullable()->after('vtp_weight');
            $table->decimal('vtp_money_collection', 15, 0)->nullable()->default(0)->after('vtp_total_fee');

            // Tracking status
            $table->string('vtp_status')->nullable()->after('vtp_money_collection');
            $table->string('vtp_status_name')->nullable()->after('vtp_status');
            $table->json('vtp_tracking_data')->nullable()->after('vtp_status_name');
            $table->timestamp('vtp_last_tracked_at')->nullable()->after('vtp_tracking_data');
        });
    }

    public function down(): void
    {
        Schema::table('postal_deliveries', function (Blueprint $table) {
            $table->dropColumn([
                'receiver_province',
                'receiver_district',
                'receiver_ward',
                'vtp_order_code',
                'vtp_service',
                'vtp_weight',
                'vtp_total_fee',
                'vtp_money_collection',
                'vtp_status',
                'vtp_status_name',
                'vtp_tracking_data',
                'vtp_last_tracked_at',
            ]);
        });
    }
};
