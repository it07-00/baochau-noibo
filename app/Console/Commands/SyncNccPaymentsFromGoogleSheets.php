<?php

namespace App\Console\Commands;

use App\Services\ContractNccPaymentSheetSyncService;
use Illuminate\Console\Command;

class SyncNccPaymentsFromGoogleSheets extends Command
{
    protected $signature = 'contracts:sync-ncc-payments-from-sheets';

    protected $description = 'Dong bo chi nha thau phu tu cac link Google Sheet da luu tren hop dong';

    public function handle(ContractNccPaymentSheetSyncService $syncService): int
    {
        $result = $syncService->syncAll();

        $this->info(sprintf(
            'Dong bo Google Sheet hoan tat: %d cap nhat, %d bo qua, %d loi.',
            $result['updated'],
            $result['skipped'],
            $result['failed'],
        ));

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
