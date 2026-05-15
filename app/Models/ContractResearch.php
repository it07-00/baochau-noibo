<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContractResearch extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contract_commercials';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    const SERVICE_TYPES = [
        'Nghiên cứu khoa học về môi trường',
        'Cung cấp giải pháp chuyển đổi công nghệ',
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

    protected $casts = [
        'signed_at' => 'date',
        'submitted_at' => 'date',
        'value' => 'integer',
        'commission' => 'integer',
        'revenue' => 'integer',
        'ncc_payment' => 'integer',
        'ncc_payment_updated_at' => 'datetime',
        'ncc_payment_paid_at' => 'date',
        'is_offset' => 'boolean',
        'has_room_fund' => 'boolean',
        'is_overdue' => 'boolean',
        'is_renewal' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(Handler::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'ĐANG THỰC HIỆN' => 'Đang thực hiện',
            'HOÀN THÀNH'     => 'Hoàn thành',
            'ĐÃ HỦY'         => 'Đã hủy',
            default          => $this->status ?? 'Không xác định',
        };
    }

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

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'ĐANG THỰC HIỆN' => 'info',
            'HOÀN THÀNH'     => 'success',
            'ĐÃ HỦY'         => 'danger',
            default          => 'secondary',
        };
    }

    public function assignments(): MorphMany
    {
        return $this->morphMany(ContractAssignment::class, 'assignable');
    }

    public function workflowSteps(): MorphMany
    {
        return $this->morphMany(ContractWorkflowStep::class, 'contract');
    }

    public function milestoneFiles(): MorphMany
    {
        return $this->morphMany(ContractMilestoneFile::class, 'contract');
    }

    public function parentContract(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_contract_id');
    }

    public function renewalContracts()
    {
        return $this->hasMany(self::class, 'parent_contract_id');
    }

    public function paymentSchedules(): MorphMany
    {
        return $this->morphMany(ContractPaymentSchedule::class, 'contract');
    }
}
