<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractConsulting extends Model
{
    use HasFactory;

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
        return match ($this->workflow_status) {
            self::STATUS_DRAFT => 'Nháp',
            self::STATUS_PENDING_ACCOUNTING => 'Chờ kế toán duyệt',
            self::STATUS_REJECTED_ACCOUNTING => 'Kế toán trả về',
            self::STATUS_PENDING_DIRECTOR => 'Chờ Giám đốc ký',
            self::STATUS_REJECTED_DIRECTOR => 'Giám đốc trả về',
            self::STATUS_APPROVED_DIRECTOR => 'Giám đốc đã ký',
            self::STATUS_CONSULTANT_ASSIGNED => 'Đã gán NV tư vấn',
            self::STATUS_CONSULTING_RECEIVING => 'Đã tiếp nhận',
            self::STATUS_CONSULTING_SURVEY => 'Đang khảo sát',
            self::STATUS_CONSULTING_PROCESSING => 'Đang thực hiện',
            self::STATUS_WAITING_CLIENT => 'Chờ KH duyệt',
            self::STATUS_CLIENT_CONFIRMED => 'KH đã xác nhận',
            self::STATUS_PENDING_FINAL_REVIEW => 'Chờ duyệt hoàn thành',
            self::STATUS_REJECTED_FINAL_REVIEW => 'Yêu cầu cập nhật lại',
            self::STATUS_FINISHED => 'Đã hoàn thành',
            self::STATUS_INCIDENT => 'Sự cố',
            default => 'Nháp',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->workflow_status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_PENDING_ACCOUNTING, self::STATUS_PENDING_DIRECTOR, self::STATUS_PENDING_FINAL_REVIEW => 'warning',
            self::STATUS_REJECTED_ACCOUNTING, self::STATUS_REJECTED_DIRECTOR, self::STATUS_REJECTED_FINAL_REVIEW, self::STATUS_INCIDENT => 'danger',
            self::STATUS_APPROVED_DIRECTOR, self::STATUS_CONSULTANT_ASSIGNED, self::STATUS_CONSULTING_RECEIVING, self::STATUS_CONSULTING_SURVEY, self::STATUS_CONSULTING_PROCESSING, self::STATUS_WAITING_CLIENT, self::STATUS_CLIENT_CONFIRMED => 'info',
            self::STATUS_FINISHED => 'success',
            default => 'dark',
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

    public function workflowSteps()
    {
        return $this->hasMany(ConsultingWorkflowStep::class, 'contract_id');
    }

    public function milestoneFiles()
    {
        return $this->hasMany(ConsultingMilestoneFile::class, 'contract_id');
    }
}
