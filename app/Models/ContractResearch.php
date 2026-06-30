<?php

namespace App\Models;

use App\Models\Concerns\HasContractBehavior;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractResearch extends Model
{
    use HasContractBehavior, HasFactory;

    protected $table = 'contract_commercials';

    const SERVICE_TYPES = [
        'Nghiên cứu khoa học môi trường',
        'Cung cấp giải pháp chuyển đổi công nghệ',
        'Hội thảo',
    ];

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
        'report_number',
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
}
