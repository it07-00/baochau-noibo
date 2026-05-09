<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $columns = [
        'contract_wastes'          => ['value', 'commission', 'revenue'],
        'contract_consultings'     => ['value', 'commission', 'revenue'],
        'contract_projects'        => ['value', 'commission', 'revenue'],
        'contract_commercials'     => ['value', 'commission', 'revenue'],
        'contract_sustainabilities'=> ['value', 'commission', 'revenue'],
        'contract_energies'        => ['value', 'commission', 'revenue'],
        'contract_payment_schedules' => ['amount', 'paid_amount'],
        'commission_requests'      => ['amount'],
        'quotations'               => ['original_value', 'value_inc_vat', 'commission_value', 'commission_tax', 'total_value'],
        'renewal_sales'            => ['sales_value', 'commission', 'sales_amount'],
        'progressive_sales'        => ['amount'],
        'invoice_bao_chau'         => ['amount', 'vat_amount', 'total_amount', 'paid_amount'],
        'invoice_handlers'         => ['amount', 'vat_amount', 'total_amount', 'paid_amount'],
        'employee_contracts'       => ['salary'],
        'sales_targets'            => ['target_amount'],
    ];

    public function up(): void
    {
        foreach ($this->columns as $table => $cols) {
            foreach ($cols as $col) {
                DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `{$col}` DECIMAL(15,2) NULL");
            }
        }
    }

    public function down(): void
    {
        foreach ($this->columns as $table => $cols) {
            foreach ($cols as $col) {
                DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `{$col}` DECIMAL(15,0) NULL");
            }
        }
    }
};
