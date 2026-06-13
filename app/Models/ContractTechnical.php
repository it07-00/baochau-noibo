<?php

namespace App\Models;

use App\Models\Concerns\HasContractBehavior;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractTechnical extends Model
{
    use HasFactory, HasContractBehavior;

    const TOTAL_STEPS = 6;

    protected $table = 'contract_projects';

    const SERVICE_TYPES = [
        'Ứng phó sự cố hóa chất',
        'Ứng phó sự cố môi trường',
        'Lập kế hoạch ứng phó sự cố hóa chất',
        'Lập biện pháp ứng phó sự cố hóa chất',
        'Diễn tập UPDCHC/MT',
        'Hệ thống xử lý khí thải, nước thải',
        'Xây dựng bản đồ tiếng ồn',
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
        'ncc_payment',
        'ncc_payment_sheet_url',
        'ncc_payment_updated_at',
        'ncc_payment_status',
        'ncc_payment_paid_at',
        'province',
        'info_source',
        'payment_method',
        'status',
        'workflow_status',
        'renewal_status',
        'voucher_status',
        'is_offset',
        'has_room_fund',
        'is_overdue',
        'notes',
        'loai_dich_vu',
        'is_renewal',
        'parent_contract_id',
    ];
}
