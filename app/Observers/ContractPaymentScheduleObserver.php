<?php

namespace App\Observers;

use App\Models\ContractPaymentSchedule;
use App\Models\SalesProgressive;

class ContractPaymentScheduleObserver
{
    public function created(ContractPaymentSchedule $schedule): void
    {
        $this->sync($schedule);
    }

    public function updated(ContractPaymentSchedule $schedule): void
    {
        $this->sync($schedule);
    }

    public function deleted(ContractPaymentSchedule $schedule): void
    {
        SalesProgressive::where('payment_schedule_id', $schedule->id)->delete();
    }

    // ────────────────────────────────────────────────────────────────────────

    private function sync(ContractPaymentSchedule $schedule): void
    {
        $contractNumber = $schedule->contract_number; // uses accessor

        // Determine sales_month: prefer paid_date, fallback to due_date, then now
        $salesMonth = $schedule->paid_date ?? $schedule->due_date ?? now()->toDate();

        $status = match ($schedule->status) {
            'paid'    => 'Hoàn thành',
            'partial' => 'Một phần',
            'overdue' => 'Quá hạn',
            default   => 'Chờ thanh toán',
        };

        SalesProgressive::updateOrCreate(
            ['payment_schedule_id' => $schedule->id],
            [
                'contract_number' => $contractNumber,
                'sales_month'     => $salesMonth,
                'milestone_name'  => $schedule->installment_name,
                'percentage'      => $schedule->percentage,
                'amount'          => $schedule->amount,
                'status'          => $status,
                'notes'           => $schedule->notes,
                'user_id'         => $schedule->created_by ?? auth()->id() ?? 1,
            ]
        );
    }
}
