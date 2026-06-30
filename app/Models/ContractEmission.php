<?php

namespace App\Models;

use App\Models\Concerns\HasContractBehavior;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractEmission extends Model
{
    use HasContractBehavior, HasFactory;

    const TOTAL_STEPS = 6;

    protected $table = 'contract_energies';

    const SERVICE_TYPES = [
        'Kiểm kê KNK',
        'Giảm phát thải KNK',
        'Kiểm toán năng lượng',
        'Hệ thống solar',
    ];

    protected $fillable = [
        'shd_cxl',
        'shd_bc',
        'customer_id',
        'handler_id',
        'staff_id',
        'department_id',
        'signed_at',
        'submitted_at',
        'value',
        'commission',
        'revenue',
        'payment_percentage',
        'service_content',
        'submission_place',
        'ncc_payment',
        'ncc_payment_sheet_url',
        'ncc_payment_updated_at',
        'ncc_payment_status',
        'ncc_payment_paid_at',
        'province',
        'info_source',
        'payment_method',
        'loai_dich_vu',
        'status',
        'workflow_status',
        'renewal_status',
        'voucher_status',
        'is_offset',
        'has_room_fund',
        'is_overdue',
        'notes',
        'is_renewal',
        'parent_contract_id',
    ];
}
