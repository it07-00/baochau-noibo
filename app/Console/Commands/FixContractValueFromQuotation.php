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
            // Tìm báo giá khớp: cùng nhân viên + cùng tên công ty + original_value = contract.value hiện tại
            $matches = DB::table('quotations')
                ->where('staff_id', $contract->staff_id)
                ->whereRaw('TRIM(COALESCE(company_name, "")) = ?', [trim($contract->customer_name)])
                ->where('original_value', $contract->value)
                ->select('id', 'original_value', 'total_value')
                ->get();

            if ($matches->count() !== 1) {
                // Không match rõ ràng → bỏ qua
                $reason = $matches->count() === 0 ? 'không tìm thấy báo giá' : "có {$matches->count()} báo giá trùng";
                $this->line("  <fg=yellow>Bỏ qua</> #{$contract->id} [{$contract->customer_name}] — {$reason}");
                $skipped++;
                continue;
            }

            $quotation = $matches->first();

            if ((float) $quotation->total_value <= 0) {
                $this->line("  <fg=yellow>Bỏ qua</> #{$contract->id} [{$contract->customer_name}] — total_value = 0");
                $skipped++;
                continue;
            }

            if ((float) $contract->value === (float) $quotation->total_value) {
                // Đã đúng rồi
                $skipped++;
                continue;
            }

            $oldValue = number_format($contract->value, 0, ',', '.');
            $newValue = number_format($quotation->total_value, 0, ',', '.');

            $this->line("  <fg=green>Cập nhật</> #{$contract->id} [{$contract->customer_name}] value: {$oldValue} → {$newValue}đ");

            if (!$dryRun) {
                DB::table($table)
                    ->where('id', $contract->id)
                    ->update(['value' => $quotation->total_value]);
            }

            $updated++;
        }

        $this->line("  → {$updated} cập nhật, {$skipped} bỏ qua");

        return [$updated, $skipped];
    }
}
