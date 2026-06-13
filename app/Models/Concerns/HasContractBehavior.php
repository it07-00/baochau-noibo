<?php

namespace App\Models\Concerns;

use App\Models\Customer;
use App\Models\Handler;
use App\Models\User;
use App\Models\Department;
use App\Models\ContractAssignment;
use App\Models\ContractWorkflowStep;
use App\Models\ContractMilestoneFile;
use App\Models\ContractPaymentSchedule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait HasContractBehavior
{
    use SoftDeletes, LogsActivity;

    /**
     * Initialize the trait by merging common casts.
     */
    protected function initializeHasContractBehavior(): void
    {
        $this->mergeCasts([
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
        ]);
    }

    /**
     * Get the log options for Activity Log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    /**
     * Customer relationship.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Handler (subcontractor) relationship.
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(Handler::class);
    }

    /**
     * Staff (handler in-charge) relationship.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Department relationship.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Status label accessor.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'ĐANG THỰC HIỆN' => 'Đang thực hiện',
            'HOÀN THÀNH'     => 'Hoàn thành',
            'ĐÃ HỦY'         => 'Đã hủy',
            default          => $this->status ?? 'Không xác định',
        };
    }

    /**
     * Status color accessor.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'ĐANG THỰC HIỆN' => 'info',
            'HOÀN THÀNH'     => 'success',
            'ĐÃ HỦY'         => 'danger',
            default          => 'secondary',
        };
    }

    /**
     * Assignments morph relation.
     */
    public function assignments(): MorphMany
    {
        return $this->morphMany(ContractAssignment::class, 'assignable');
    }

    /**
     * Workflow steps morph relation.
     */
    public function workflowSteps(): MorphMany
    {
        return $this->morphMany(ContractWorkflowStep::class, 'contract');
    }

    /**
     * Milestone files morph relation.
     */
    public function milestoneFiles(): MorphMany
    {
        return $this->morphMany(ContractMilestoneFile::class, 'contract');
    }

    /**
     * Parent contract relationship (self-reference).
     */
    public function parentContract(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_contract_id');
    }

    /**
     * Renewal contracts relationship (self-reference).
     */
    public function renewalContracts(): HasMany
    {
        return $this->hasMany(self::class, 'parent_contract_id');
    }

    /**
     * Payment schedules morph relation.
     */
    public function paymentSchedules(): MorphMany
    {
        return $this->morphMany(ContractPaymentSchedule::class, 'contract');
    }
}
