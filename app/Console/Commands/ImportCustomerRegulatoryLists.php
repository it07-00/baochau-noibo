<?php

namespace App\Console\Commands;

use App\Services\CustomerRegulatoryListImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportCustomerRegulatoryLists extends Command
{
    protected $signature = 'customers:import-regulatory-lists
                            {ghg : Đường dẫn CSV danh sách cơ sở phát thải khí nhà kính}
                            {energy : Đường dẫn CSV danh sách cơ sở sử dụng năng lượng trọng điểm}';

    protected $description = 'Nhập hai danh sách khách hàng KKKNK và kiểm toán năng lượng';

    public function handle(CustomerRegulatoryListImporter $importer): int
    {
        try {
            $result = $importer->import(
                (string) $this->argument('ghg'),
                (string) $this->argument('energy')
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Đã nhập danh sách khách hàng thành công.');
        $this->table(
            ['Dòng KKKNK', 'Dòng năng lượng', 'Tạo mới', 'Cập nhật', 'Không đổi', 'Bỏ qua'],
            [[
                $result['ghg_rows'],
                $result['energy_rows'],
                $result['created'],
                $result['updated'],
                $result['unchanged'],
                $result['skipped'],
            ]]
        );

        return self::SUCCESS;
    }
}
