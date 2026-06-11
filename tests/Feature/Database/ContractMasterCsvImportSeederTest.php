<?php

namespace Tests\Feature\Database;

use App\Models\ContractLegal;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Handler;
use App\Models\User;
use Database\Seeders\ContractMasterCsvImportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class ContractMasterCsvImportSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_blank_handler_is_not_replaced_with_a_default_handler(): void
    {
        $department = Department::create([
            'name' => 'Phong Kinh Doanh',
            'slug' => 'kinh-doanh',
            'is_active' => true,
        ]);
        User::factory()->create([
            'username' => 'kinhdoanh',
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        $csvPath = storage_path('app/contract-import-with-blank-handler.csv');
        $keys = (new ReflectionClass(ContractMasterCsvImportSeeder::class))
            ->getConstant('CSV_KEYS');
        $data = array_fill_keys($keys, '');
        $data['category'] = 'Pháp lý hồ sơ môi trường';
        $data['customer_name'] = 'Công ty CP Thương mại và xây dựng Tài Phú';
        $data['shd_bc'] = '01/2026/HĐKT.BC-TAIPHU (2.040.000)';
        $data['service_content'] = 'Quan trắc môi trường';
        $data['contract_value'] = '2.040.000';
        $data['signed_date'] = '05/01/2026';

        $handle = fopen($csvPath, 'wb');
        fputcsv($handle, $keys);
        fputcsv($handle, array_values($data));
        fclose($handle);

        putenv("CONTRACT_IMPORT_CSV_PATH={$csvPath}");

        try {
            $this->seed(ContractMasterCsvImportSeeder::class);
        } finally {
            putenv('CONTRACT_IMPORT_CSV_PATH');
            @unlink($csvPath);
        }

        $this->assertDatabaseHas('contract_consultings', [
            'shd_bc' => '01/2026/HĐKT.BC-TAIPHU (2.040.000)',
            'handler_id' => null,
        ]);
        $this->assertDatabaseCount('handlers', 0);
    }

    public function test_data_correction_only_clears_the_reported_tai_phu_contract(): void
    {
        $department = Department::create([
            'name' => 'Phong Kinh Doanh',
            'slug' => 'kinh-doanh',
            'is_active' => true,
        ]);
        $staff = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $customer = Customer::create([
            'name' => 'Công ty CP Thương mại và xây dựng Tài Phú',
        ]);
        $handler = Handler::create([
            'name' => 'Công ty CP Công Nghệ Môi Trường Trái Đất Xanh',
        ]);

        $reportedContract = ContractLegal::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $staff->id,
            'department_id' => $department->id,
            'shd_bc' => '01/2026/HĐKT.BC-TAIPHU (2.040.000)',
            'signed_at' => '2026-01-05',
            'value' => 2_040_000,
        ]);
        $otherContract = ContractLegal::create([
            'customer_id' => $customer->id,
            'handler_id' => $handler->id,
            'staff_id' => $staff->id,
            'department_id' => $department->id,
            'shd_bc' => '02/2026/HĐKT.BC-TAIPHU',
            'signed_at' => '2026-02-05',
            'value' => 3_000_000,
        ]);

        $migration = require database_path(
            'migrations/2026_06_11_000001_clear_auto_assigned_handler_from_tai_phu_contract.php'
        );
        $migration->up();

        $this->assertNull($reportedContract->refresh()->handler_id);
        $this->assertSame($handler->id, $otherContract->refresh()->handler_id);
    }
}
