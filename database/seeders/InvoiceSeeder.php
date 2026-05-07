<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\InvoiceBaoChau;
use App\Models\InvoiceHandler;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Handler;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::whereHas('roles', fn($q) => $q->where('name', Role::KE_TOAN->value))->first();
        if (!$creator) $creator = User::first();
        $contracts = ContractWaste::with(['customer', 'handler'])->limit(5)->get();
        if ($contracts->isEmpty()) return;

        $statusPool = ['unpaid', 'partial', 'paid', 'overdue'];

        foreach ($contracts as $i => $contract) {
            $amount = (int) ($contract->value * rand(40, 100) / 100);
            $vatPct = 10;
            $vatAmount = (int) ($amount * $vatPct / 100);
            $totalAmount = $amount + $vatAmount;
            $status = $statusPool[$i % 4];
            $paidAmount = match($status) {
                'paid' => $totalAmount,
                'partial' => (int) ($totalAmount * rand(30, 70) / 100),
                default => 0,
            };

            // Hóa đơn Bảo Châu (xuất cho KH)
            InvoiceBaoChau::create([
                'contract_waste_id'   => $contract->id,
                'customer_id'         => $contract->customer_id,
                'invoice_number'      => 'BC-2025-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'issue_date'          => now()->subDays(rand(10, 90)),
                'due_date'            => now()->addDays(rand(10, 60)),
                'amount'              => $amount,
                'vat_percent'         => $vatPct,
                'vat_amount'          => $vatAmount,
                'total_amount'        => $totalAmount,
                'status'              => $status,
                'paid_amount'         => $paidAmount,
                'paid_at'             => $status === 'paid' ? now()->subDays(rand(1, 30)) : null,
                'service_description' => 'Thu gom, vận chuyển và xử lý chất thải — ' . $contract->content,
                'created_by'          => $creator->id,
            ]);

            // Hóa đơn chủ xử lý (nhận từ CXL)
            $hAmount = (int) ($contract->value * rand(20, 50) / 100);
            $hVat = (int) ($hAmount * $vatPct / 100);
            $hTotal = $hAmount + $hVat;
            $hStatus = $statusPool[($i + 1) % 4];

            InvoiceHandler::create([
                'contract_waste_id' => $contract->id,
                'handler_id'        => $contract->handler_id,
                'invoice_number'    => 'CXL-2025-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'issue_date'        => now()->subDays(rand(5, 60)),
                'due_date'          => now()->addDays(rand(15, 90)),
                'amount'            => $hAmount,
                'vat_percent'       => $vatPct,
                'vat_amount'        => $hVat,
                'total_amount'      => $hTotal,
                'status'            => $hStatus,
                'paid_amount'       => $hStatus === 'paid' ? $hTotal : 0,
                'paid_at'           => $hStatus === 'paid' ? now()->subDays(rand(1, 20)) : null,
                'created_by'        => $creator->id,
            ]);
        }
    }
}
