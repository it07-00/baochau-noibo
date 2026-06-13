<?php

namespace App\Models;

use App\Models\Concerns\HasContractBehavior;
use Illuminate\Database\Eloquent\Model;

class ContractWaste extends Model
{
    use HasContractBehavior;

    protected $fillable = [
        'shd_cxl',
        'shd_bc',
        'customer_id',
        'handler_id',
        'staff_id',
        'department_id',
        'content',
        'loai_dich_vu',
        'value',
        'commission',
        'revenue',
        'ncc_payment',
        'ncc_payment_sheet_url',
        'ncc_payment_updated_at',
        'ncc_payment_status',
        'ncc_payment_paid_at',
        'payment_method',
        'info_source',
        'province',
        'signed_at',
        'effective_at',
        'end_at',
        'submitted_at',
        'billing_address',
        'execution_address',
        'mailing_address',
        'status',
        'workflow_status',
        'renewal_status',
        'voucher_status',
        'is_offset',
        'is_overdue',
        'has_room_fund',
        'notes',
        'waste_type',
        'service_type',
        'is_renewal',
        'parent_contract_id',
    ];

    const SERVICE_TYPES = [
        'Thu gom CTNH',
        'CTCN',
        'Hủy hàng',
    ];

    protected $casts = [
        'effective_at' => 'date',
        'end_at' => 'date',
    ];
}
