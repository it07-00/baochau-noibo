<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContractSustainability extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    const SERVICE_TYPES = [
        'Báo cáo ESG',
        'Tiêu chí cảng xanh',
        'Báo cáo CBAM',
        'Đánh giá vòng đời sản phẩm',
        'Tín chỉ Carbon',
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
