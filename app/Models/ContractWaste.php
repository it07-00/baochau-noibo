<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ContractWaste extends Model
{
    protected $guarded = [];

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
}
