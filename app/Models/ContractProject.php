<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ContractProject extends Model
{
    use HasFactory;

    const SERVICE_TYPES = [
        'Tư vấn, thiết kế và thi công hệ thống xử lý khí thải, nước thải',
        'Ứng phó sự cố hóa chất, tràn dầu',
        'Tư vấn, thiết kế và thi công hệ thống quan trắc tự động',
    ];

    protected $fillable = [
        'shd_ad',
        'customer_id',
        'staff_id',
        'department_id',
        'signed_at',
        'submitted_at',
        'value',
        'commission',
        'revenue',
        'province',
        'info_source',
        'payment_method',
        'status',
        'renewal_status',
        'is_offset',
        'has_room_fund',
        'is_overdue',
        'notes',
        'loai_dich_vu',
    ];

    protected $casts = [
        'signed_at' => 'date',
        'submitted_at' => 'date',
        'value' => 'decimal:0',
        'commission' => 'decimal:0',
        'revenue' => 'decimal:0',
        'is_offset' => 'boolean',
        'has_room_fund' => 'boolean',
        'is_overdue' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
}
