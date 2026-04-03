<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('marketing_daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('report_date');

            // Số lượng content theo kênh
            $table->unsignedSmallInteger('facebook_count')->default(0);
            $table->unsignedSmallInteger('zalo_count')->default(0);
            $table->unsignedSmallInteger('website_count')->default(0);
            $table->unsignedSmallInteger('tiktok_count')->default(0);
            $table->unsignedSmallInteger('youtube_count')->default(0);
            $table->unsignedSmallInteger('other_count')->default(0);
            $table->string('other_channel_name')->nullable(); // tên kênh khác

            // Nội dung chi tiết
            $table->text('content_details')->nullable();  // mô tả nội dung đã làm
            $table->text('banners')->nullable();           // banner/ấn phẩm tạo được
            $table->text('targets_achieved')->nullable();  // chỉ tiêu đạt được
            $table->text('notes')->nullable();             // ghi chú

            $table->unique(['user_id', 'report_date']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_daily_reports');
    }
};
