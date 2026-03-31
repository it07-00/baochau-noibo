<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContractConsulting extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    const SERVICE_TYPES = [
        'Tư vấn, lập ĐTM, GPMT, DKMT',
        'Quan trắc môi trường',
        'Quan trắc môi trường lao động và phân loại lao động',
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
        'consultant_id',
        'manager_id',
        'status',
        'workflow_status',
        'assigned_at',
        'completed_at',
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

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function workflowSteps(): MorphMany
    {
        return $this->morphMany(ContractWorkflowStep::class, 'contract');
    }

    public function milestoneFiles(): MorphMany
    {
        return $this->morphMany(ContractMilestoneFile::class, 'contract');
    }

    public function assignments(): MorphMany
    {
        return $this->morphMany(ContractAssignment::class, 'assignable');
    }
}
