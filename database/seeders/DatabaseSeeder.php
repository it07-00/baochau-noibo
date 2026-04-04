<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // 1. Cấu trúc hệ thống
            RolesSeeder::class,
            PermissionsSeeder::class,
            DepartmentsSeeder::class,
            SampleUsersSeeder::class,

            // 2. Dữ liệu nền
            // InternalDocsSeeder::class,
            // ContractWasteSeeder::class,
            // ContractSampleSeeder::class,

            // 3. Kinh doanh & Sales
            // QuotationSeeder::class,
            // SalesDataSeeder::class,
            // SalesTargetSeeder::class,

            // 4. Hóa đơn & Tài chính
            // ContractPaymentScheduleSeeder::class,
            // InvoiceSeeder::class,
            // CommissionRequestSeeder::class,

            // 5. Vận hành
            // PostalDeliverySeeder::class,
            // DailyReportSeeder::class,
        ]);
    }
}
