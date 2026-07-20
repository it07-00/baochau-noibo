<?php

namespace App\Livewire\Concerns;

trait HasContractFilters
{
    protected function applyContractFilters($query): void
    {
        $f = $this->filter;

        if ($f['signed_from'] ?? null)    $query->whereDate('signed_at', '>=', $f['signed_from']);
        if ($f['signed_to'] ?? null)      $query->whereDate('signed_at', '<=', $f['signed_to']);
        if ($f['submitted_from'] ?? null) $query->whereDate('submitted_at', '>=', $f['submitted_from']);
        if ($f['submitted_to'] ?? null)   $query->whereDate('submitted_at', '<=', $f['submitted_to']);
        if ($f['province'] ?? null)       $query->where('province', $f['province']);
        if ($f['department_id'] ?? null)  $query->where('department_id', $f['department_id']);
        if ($f['staff_id'] ?? null)       $query->where('staff_id', $f['staff_id']);
        if ($f['info_source'] ?? null)    $query->where('info_source', $f['info_source']);
        if ($f['payment_method'] ?? null) $query->where('payment_method', $f['payment_method']);
        if ($f['status'] ?? null)         $query->where('status', $f['status']);
        if ($f['renewal_status'] ?? null) $query->where('renewal_status', $f['renewal_status']);
        if ($f['voucher_status'] ?? null) $query->where('voucher_status', $f['voucher_status']);
        if ($f['is_offset'] ?? false)     $query->where('is_offset', true);
        if ($f['has_room_fund'] ?? false) $query->where('has_room_fund', true);
        if ($f['is_overdue'] ?? false)    $query->where('is_overdue', true);
        if ($f['loai_dich_vu'] ?? null) {
            if ($f['loai_dich_vu'] === 'BCCTBVMT') {
                $query->whereIn('loai_dich_vu', ['BCCTBVMT', 'QTMT và BCCTBVMT']);
            } else {
                $query->where('loai_dich_vu', $f['loai_dich_vu']);
            }
        }
        if ($f['handler_id'] ?? null)     $query->where('handler_id', $f['handler_id']);
        if ($f['hide_completed_workflow'] ?? false) {
            $query->whereDoesntHave('workflowSteps', fn($stepQuery) => $stepQuery->where('step_name', 'finished'));
        }
    }
}
