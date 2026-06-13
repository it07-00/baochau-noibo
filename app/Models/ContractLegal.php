<?php

namespace App\Models;

use App\Models\Concerns\HasContractBehavior;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractLegal extends Model
{
    use HasFactory, HasContractBehavior;

    protected $table = 'contract_consultings';

    const SERVICE_TYPES = [
        'Quan trắc môi trường',
        'QTMT và BCCTBVMT',
        'Hồ sơ môi trường',
        'Quan trắc môi trường lao động',
        'Phân loại lao động',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_ACCOUNTING = 'pending_accounting';
    const STATUS_REJECTED_ACCOUNTING = 'rejected_accounting';
    const STATUS_PENDING_DIRECTOR = 'pending_director';
    const STATUS_REJECTED_DIRECTOR = 'rejected_director';
    const STATUS_APPROVED_DIRECTOR = 'approved_director';
    const STATUS_CONSULTANT_ASSIGNED = 'consultant_assigned';
    const STATUS_CONSULTING_RECEIVING = 'consulting_receiving';
    const STATUS_CONSULTING_SURVEY = 'consulting_survey';
    const STATUS_CONSULTING_PROCESSING = 'consulting_processing';
    const STATUS_WAITING_CLIENT = 'waiting_client';
    const STATUS_CLIENT_CONFIRMED = 'client_confirmed';
    const STATUS_PENDING_FINAL_REVIEW = 'pending_final_review';
    const STATUS_REJECTED_FINAL_REVIEW = 'rejected_final_review';
    const STATUS_FINISHED = 'finished';
    const STATUS_INCIDENT = 'incident';
    const TOTAL_STEPS = 6;

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
        'consultant_id',
        'manager_id',
        'status',
        'workflow_status',
        'assigned_at',
        'completed_at',
        'renewal_status',
        'voucher_status',
        'is_offset',
        'has_room_fund',
        'is_overdue',
        'notes',
        'report_number',
        'loai_dich_vu',
        'is_renewal',
        'parent_contract_id',
    ];

    public function getDetailedStatusColorAttribute(): array
    {
        return match ($this->status) {
            'PTH đang kiểm tra', 'ĐANG THỰC HIỆN' => [
                'bg' => '#cfe2ff',
                'text' => '#0d6efd',
            ],
            'Đang trình BGĐ ký' => ['bg' => '#fff3cd', 'text' => '#b45309'],
            'Đã gửi khách hàng' => ['bg' => '#e2d9f3', 'text' => '#6f42c1'],
            'Đã hoàn thành', 'HOÀN THÀNH' => ['bg' => '#d1e7dd', 'text' => '#198754'],
            'Hợp đồng hủy', 'ĐÃ HỦY' => ['bg' => '#f8d7da', 'text' => '#dc3545'],
            default => ['bg' => '#e9ecef', 'text' => '#6c757d'],
        };
    }

    public function getVoucherBadgeInfoAttribute(): array
    {
        $value = trim((string) ($this->voucher_status ?? ''));
        $key = mb_strtolower($value);

        $class = match ($key) {
            'đã đề nghị thanh toán/tạm ứng' => 'bg-info text-dark',
            'đã xuất hóa đơn' => 'bg-warning text-dark',
            'đã làm biên bản bàn giao hồ sơ' => 'bg-primary text-white',
            'đã làm bb bàn giao và nghiệm thu kết thúc hợp đồng' => 'bg-success text-white',
            '', 'chưa có', 'chưa chọn' => 'bg-light text-dark border',
            default => 'bg-secondary text-white',
        };

        $label = match ($key) {
            'đã đề nghị thanh toán/tạm ứng' => 'Đề nghị TT/TƯ',
            'đã xuất hóa đơn' => 'Xuất hóa đơn',
            'đã làm biên bản bàn giao hồ sơ' => 'BB bàn giao hồ sơ',
            'đã làm bb bàn giao và nghiệm thu kết thúc hợp đồng' => 'BB nghiệm thu kết thúc HĐ',
            '', 'chưa có', 'chưa chọn' => 'Chưa chọn',
            default => $value !== '' ? $value : 'Chưa chọn',
        };

        return [
            'class' => $class,
            'label' => $label,
            'full_value' => $value !== '' ? $value : 'Chưa chọn',
        ];
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
