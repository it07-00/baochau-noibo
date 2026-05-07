<?php

namespace App\Models;

use App\Enums\ContractRenewalStatus;
use App\Enums\ContractVoucherStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContractWaste extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }
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
        'payment_method',
        'source',
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
        'note',
        'waste_type',
        'service_type',
        'is_renewal',
        'parent_contract_id',
    ];

    const SERVICE_TYPES = [
        'Thu gom, xử lý chất thải nguy hại và công nghiệp',
        'Xây dựng bản đồ tiếng ồn',
    ];

    protected $casts = [
        'signed_at' => 'date',
        'effective_at' => 'date',
        'end_at' => 'date',
        'submitted_at' => 'date',
        'is_offset' => 'boolean',
        'is_overdue' => 'boolean',
        'is_renewal' => 'boolean',
        'value' => 'decimal:0',
        'commission' => 'decimal:0',
        'revenue' => 'decimal:0',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function handler()
    {
        return $this->belongsTo(Handler::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
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

    public function parentContract()
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
