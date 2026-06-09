<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        $this->call([
            // 1. Cấu trúc hệ thống
            RolesSeeder::class,
            PermissionsSeeder::class,
            DepartmentsSeeder::class,
            SampleUsersSeeder::class,
            ContractMasterCsvImportSeeder::class,
            QuotationMasterCsvImportSeeder::class,

            // 2. Dữ liệu nền
            // InternalDocsSeeder::class,
            // ContractWasteSeeder::class,
            ContractSampleSeeder::class,

            // 3. Kinh doanh & Sales
            QuotationSeeder::class,
            QuotationDocumentSampleSeeder::class,
            QuotationDocumentPlldSampleSeeder::class,
            QuotationDocumentPlldFullSampleSeeder::class,
            QuotationDocumentQtmtFullSampleSeeder::class,
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
