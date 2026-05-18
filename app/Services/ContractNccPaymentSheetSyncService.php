<?php

namespace App\Services;

use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContractNccPaymentSheetSyncService
{
    private const CONTRACT_SOURCES = [
        'waste' => ContractWaste::class,
        'consulting' => ContractLegal::class,
        'project' => ContractTechnical::class,
        'commercial' => ContractResearch::class,
        'sustainability' => ContractSustainability::class,
        'energy' => ContractEmission::class,
    ];

    public function __construct(
        private readonly GoogleSheetTotalExtractor $extractor,
    ) {}

    public function syncAll(): array
    {
        $result = [
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        foreach (self::CONTRACT_SOURCES as $sourceKey => $modelClass) {
            $modelClass::query()
                ->whereNotNull('ncc_payment_sheet_url')
                ->where('ncc_payment_sheet_url', '<>', '')
                ->orderBy('id')
                ->chunkById(100, function ($contracts) use (&$result, $sourceKey) {
                    foreach ($contracts as $contract) {
                        $this->syncContract($contract, $sourceKey, $result);
                    }
                });
        }

        return $result;
    }

    private function syncContract(Model $contract, string $sourceKey, array &$result): void
    {
        $sheetUrl = trim((string) $contract->getAttribute('ncc_payment_sheet_url'));

        if ($sheetUrl === '') {
            $result['skipped']++;

            return;
        }

        try {
            $amount = $this->extractor->extractTotalFromUrl($sheetUrl, true);

            $contract->forceFill([
                'ncc_payment' => $amount,
                'ncc_payment_updated_at' => now(),
            ])->save();

            $result['updated']++;
        } catch (Throwable $e) {
            $result['failed']++;

            Log::warning('Contract NCC payment sheet sync failed', [
                'source_key' => $sourceKey,
                'contract_id' => $contract->getKey(),
                'sheet_url' => $sheetUrl,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
