<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $renames = [
        'App\\Models\\ContractConsulting' => 'App\\Models\\ContractLegal',
        'App\\Models\\ContractProject'    => 'App\\Models\\ContractTechnical',
        'App\\Models\\ContractCommercial' => 'App\\Models\\ContractResearch',
        'App\\Models\\ContractEnergy'     => 'App\\Models\\ContractEmission',
        'App\\Models\\RenewalSales'       => 'App\\Models\\SalesRenewal',
        'App\\Models\\ProgressiveSales'   => 'App\\Models\\SalesProgressive',
    ];

    private array $morphColumns = [
        'commission_requests'       => 'contract_type',
        'contract_assignments'      => 'assignable_type',
        'contract_milestone_files'  => 'contract_type',
        'contract_payment_schedules'=> 'contract_type',
        'contract_progress_notes'   => 'contract_type',
        'contract_workflow_steps'   => 'contract_type',
        'activity_log'              => 'subject_type',
    ];

    public function up(): void
    {
        foreach ($this->morphColumns as $table => $column) {
            foreach ($this->renames as $old => $new) {
                DB::table($table)
                    ->where($column, $old)
                    ->update([$column => $new]);
            }
        }

        // activity_log causer_type (User model — không đổi, nhưng xử lý phòng ngừa)
        foreach ($this->renames as $old => $new) {
            DB::table('activity_log')
                ->where('causer_type', $old)
                ->update(['causer_type' => $new]);
        }
    }

    public function down(): void
    {
        $reversed = array_flip($this->renames);

        foreach ($this->morphColumns as $table => $column) {
            foreach ($reversed as $old => $new) {
                DB::table($table)
                    ->where($column, $old)
                    ->update([$column => $new]);
            }
        }

        foreach ($reversed as $old => $new) {
            DB::table('activity_log')
                ->where('causer_type', $old)
                ->update(['causer_type' => $new]);
        }
    }
};
