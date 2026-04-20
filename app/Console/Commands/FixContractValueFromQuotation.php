<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixContractValueFromQuotation extends Command
{
    protected $signature = 'contracts:fix-value-from-quotation {--dry-run : Chỉ hiển thị, không cập nhật thật}';

    protected $description = 'Cập nhật giá trị hợp đồng (value) từ original_value → total_value theo báo giá tương ứng';

    /**
     * Bảng hợp đồng cần xử lý.
     */
    private array $tables = [
        'contract_wastes',
        'contract_consultings',
        'contract_commercials',
        'contract_projects',
        'contract_energies',
        'contract_sustainabilities',
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('--- CHẾ ĐỘ DRY-RUN: không có thay đổi nào được ghi vào DB ---');
        }

        $totalUpdated = 0;
        $totalSkipped = 0;

        foreach ($this->tables as $table) {
            [$updated, $skipped] = $this->processTable($table, $dryRun);
            $totalUpdated += $updated;
            $totalSkipped += $skipped;
        }

        $this->newLine();
        $this->info("Tổng cộng: {$totalUpdated} bản ghi " . ($dryRun ? 'sẽ được' : 'đã được') . " cập nhật, {$totalSkipped} bỏ qua (match không rõ ràng hoặc total_value = 0).");

        return self::SUCCESS;
    }

    private function processTable(string $table, bool $dryRun): array
    {
        $this->line("\n<fg=cyan>Bảng: {$table}</>");

        // Lấy tất cả hợp đồng trong bảng
        $contracts = DB::table($table)
            ->join('customers', "{$table}.customer_id", '=', 'customers.id')
            ->select(
                "{$table}.id",
                "{$table}.value",
                "{$table}.revenue",
                "{$table}.staff_id",
                'customers.name as customer_name',
            )
            ->get();

        $updated = 0;
        $skipped = 0;

        foreach ($contracts as $contract) {
            // Pattern 1: value = original_value (tạo trước khi fix) → cần sửa value → total_value
            // Pattern 2: value = total_value, revenue = total_value (tạo sau khi fix nhưng revenue bị auto-sync) → cần sửa revenue → original_value
            $matchByOriginal = DB::table('quotations')
                ->where('staff_id', $contract->staff_id)
                ->whereRaw('TRIM(COALESCE(company_name, "")) = ?', [trim($contract->customer_name)])
                ->where('original_value', $contract->value)
                ->select('id', 'original_value', 'total_value')
                ->get();

            $matchByTotal = DB::table('quotations')
                ->where('staff_id', $contract->staff_id)
                ->whereRaw('TRIM(COALESCE(company_name, "")) = ?', [trim($contract->customer_name)])
                ->where('total_value', $contract->value)
                ->select('id', 'original_value', 'total_value')
                ->get();

            $updateData = [];

            // Pattern 1: value cần đổi từ original → total
            if ($matchByOriginal->count() === 1) {
                $q = $matchByOriginal->first();
                if ((float) $q->total_value > 0 && (float) $contract->value !== (float) $q->total_value) {
                    $oldVal = number_format($contract->value, 0, ',', '.');
                    $newVal = number_format($q->total_value, 0, ',', '.');
                    $this->line("  <fg=green>Cập nhật</> #{$contract->id} [{$contract->customer_name}] value: {$oldVal} → {$newVal}đ");
                    $updateData['value'] = $q->total_value;
                }
            }

            // Pattern 2: revenue = total_value nhưng phải là original_value
            if ($matchByTotal->count() === 1) {
                $q = $matchByTotal->first();
                if ((float) $q->original_value > 0 && (float) $contract->revenue !== (float) $q->original_value) {
                    $oldRev = number_format($contract->revenue, 0, ',', '.');
                    $newRev = number_format($q->original_value, 0, ',', '.');
                    $this->line("  <fg=green>Cập nhật</> #{$contract->id} [{$contract->customer_name}] revenue: {$oldRev} → {$newRev}đ");
                    $updateData['revenue'] = $q->original_value;
                }
            }

            if (empty($updateData)) {
                // Không match hoặc đã đúng
                $noMatch = $matchByOriginal->count() === 0 && $matchByTotal->count() === 0;
                if ($noMatch) {
                    $this->line("  <fg=yellow>Bỏ qua</> #{$contract->id} [{$contract->customer_name}] — không tìm thấy báo giá");
                }
                $skipped++;
                continue;
            }

            if (!$dryRun) {
                DB::table($table)->where('id', $contract->id)->update($updateData);
            }

            $updated++;
        }

        $this->line("  → {$updated} cập nhật, {$skipped} bỏ qua");

        return [$updated, $skipped];
    }
}
