<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // contract_wastes
        DB::table('contract_wastes')
            ->where('loai_dich_vu', 'Thu gom, xử lý chất thải nguy hại và công nghiệp')
            ->update(['loai_dich_vu' => 'Thu gom CTNH']);

        // contract_consultings
        DB::table('contract_consultings')
            ->where('loai_dich_vu', 'Tư vấn, lập ĐTM, GPMT, DKMT')
            ->update(['loai_dich_vu' => 'Hồ sơ môi trường']);

        DB::table('contract_consultings')
            ->where('loai_dich_vu', 'Quan trắc môi trường lao động')
            ->update(['loai_dich_vu' => 'Quan trắc môi trường lao động, Phân loại lao động']);

        DB::table('contract_consultings')
            ->where('loai_dich_vu', 'Phân loại lao động')
            ->update(['loai_dich_vu' => 'Quan trắc môi trường lao động, Phân loại lao động']);

        // contract_projects
        DB::table('contract_projects')
            ->where('loai_dich_vu', 'Tư vấn, thiết kế và thi công hệ thống xử lý khí thải, nước thải')
            ->update(['loai_dich_vu' => 'Hệ thống xử lý khí thải, nước thải']);

        DB::table('contract_projects')
            ->where('loai_dich_vu', 'Ứng phó sự cố hóa chất, tràn dầu')
            ->update(['loai_dich_vu' => 'Ứng phó sự cố hóa chất']);

        DB::table('contract_projects')
            ->where('loai_dich_vu', 'Tư vấn, thiết kế và thi công hệ thống quan trắc tự động')
            ->update(['loai_dich_vu' => 'Hệ thống xử lý khí thải, nước thải']);

        // contract_commercials
        DB::table('contract_commercials')
            ->where('loai_dich_vu', 'Nghiên cứu khoa học về môi trường')
            ->update(['loai_dich_vu' => 'Nghiên cứu khoa học môi trường']);

        // contract_sustainabilities
        DB::table('contract_sustainabilities')
            ->where('loai_dich_vu', 'Tư vấn, lập báo cáo ESG')
            ->update(['loai_dich_vu' => 'Báo cáo ESG']);

        DB::table('contract_sustainabilities')
            ->where('loai_dich_vu', 'Tư vấn tiêu chí cảng xanh')
            ->update(['loai_dich_vu' => 'Tiêu chí cảng xanh']);

        DB::table('contract_sustainabilities')
            ->where('loai_dich_vu', 'Lập báo cáo CBAM')
            ->update(['loai_dich_vu' => 'Báo cáo CBAM']);

        DB::table('contract_sustainabilities')
            ->where('loai_dich_vu', 'Đánh giá vòng đời sản phẩm (ISO 14067)')
            ->update(['loai_dich_vu' => 'Đánh giá vòng đời sản phẩm']);

        // contract_energies
        DB::table('contract_energies')
            ->where('loai_dich_vu', 'Kiểm kê khí nhà kính NĐ 06/2022 và ISO 14064-1')
            ->update(['loai_dich_vu' => 'Kiểm kê KNK']);

        DB::table('contract_energies')
            ->where('loai_dich_vu', 'Kiểm toán năng lượng, giải pháp tiết kiệm năng lượng, và giảm phát thải khí nhà kính')
            ->update(['loai_dich_vu' => 'Kiểm toán năng lượng']);

        DB::table('contract_energies')
            ->where('loai_dich_vu', 'Tư vấn, thiết kế hệ thống điện mặt trời')
            ->update(['loai_dich_vu' => 'Hệ thống solar']);
    }

    public function down(): void
    {
        // contract_wastes
        DB::table('contract_wastes')
            ->where('loai_dich_vu', 'Thu gom CTNH')
            ->update(['loai_dich_vu' => 'Thu gom, xử lý chất thải nguy hại và công nghiệp']);

        // contract_consultings
        DB::table('contract_consultings')
            ->where('loai_dich_vu', 'Hồ sơ môi trường')
            ->update(['loai_dich_vu' => 'Tư vấn, lập ĐTM, GPMT, DKMT']);

        DB::table('contract_consultings')
            ->where('loai_dich_vu', 'Quan trắc môi trường lao động, Phân loại lao động')
            ->update(['loai_dich_vu' => 'Quan trắc môi trường lao động']);

        // contract_projects
        DB::table('contract_projects')
            ->where('loai_dich_vu', 'Hệ thống xử lý khí thải, nước thải')
            ->update(['loai_dich_vu' => 'Tư vấn, thiết kế và thi công hệ thống xử lý khí thải, nước thải']);

        DB::table('contract_projects')
            ->where('loai_dich_vu', 'Ứng phó sự cố hóa chất')
            ->update(['loai_dich_vu' => 'Ứng phó sự cố hóa chất, tràn dầu']);

        // contract_commercials
        DB::table('contract_commercials')
            ->where('loai_dich_vu', 'Nghiên cứu khoa học môi trường')
            ->update(['loai_dich_vu' => 'Nghiên cứu khoa học về môi trường']);

        // contract_sustainabilities
        DB::table('contract_sustainabilities')
            ->where('loai_dich_vu', 'Báo cáo ESG')
            ->update(['loai_dich_vu' => 'Tư vấn, lập báo cáo ESG']);

        DB::table('contract_sustainabilities')
            ->where('loai_dich_vu', 'Tiêu chí cảng xanh')
            ->update(['loai_dich_vu' => 'Tư vấn tiêu chí cảng xanh']);

        DB::table('contract_sustainabilities')
            ->where('loai_dich_vu', 'Báo cáo CBAM')
            ->update(['loai_dich_vu' => 'Lập báo cáo CBAM']);

        DB::table('contract_sustainabilities')
            ->where('loai_dich_vu', 'Đánh giá vòng đời sản phẩm')
            ->update(['loai_dich_vu' => 'Đánh giá vòng đời sản phẩm (ISO 14067)']);

        // contract_energies
        DB::table('contract_energies')
            ->where('loai_dich_vu', 'Kiểm kê KNK')
            ->update(['loai_dich_vu' => 'Kiểm kê khí nhà kính NĐ 06/2022 và ISO 14064-1']);

        DB::table('contract_energies')
            ->where('loai_dich_vu', 'Kiểm toán năng lượng')
            ->update(['loai_dich_vu' => 'Kiểm toán năng lượng, giải pháp tiết kiệm năng lượng, và giảm phát thải khí nhà kính']);

        DB::table('contract_energies')
            ->where('loai_dich_vu', 'Hệ thống solar')
            ->update(['loai_dich_vu' => 'Tư vấn, thiết kế hệ thống điện mặt trời']);
    }
};
