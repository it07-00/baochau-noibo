<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 'username', 'email', 'avatar', 'phone', 'gender', 'date_of_birth', 'address',
    'password', 'department_id', 'is_active',
    'employee_code', 'id_card_number', 'id_card_issued_date', 'id_card_issued_place',
    'hometown', 'permanent_address', 'temporary_address',
    'tax_code', 'social_insurance_number', 'bank_account', 'bank_name',
    'emergency_contact_name', 'emergency_contact_phone',
    'education_level', 'major', 'start_date', 'end_date',
    'employment_status', 'work_type', 'hr_notes',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'username', 'email', 'phone', 'is_active', 'department_id', 'employment_status'])
            ->logOnlyDirty()
            ->dontLogIfAttributesUnchanged();
    }

    public const EMPLOYMENT_STATUSES = [
        'thu_viec'   => 'Thử việc',
        'chinh_thuc' => 'Chính thức',
        'thuc_tap'   => 'Thực tập',
        'nghi_viec'  => 'Nghỉ việc',
    ];

    public const WORK_TYPES = [
        'full_time' => 'Toàn thời gian',
        'part_time' => 'Bán thời gian',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password'           => 'hashed',
            'date_of_birth'      => 'date',
            'is_active'          => 'boolean',
            'id_card_issued_date'=> 'date',
            'start_date'         => 'date',
            'end_date'           => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function dailyReports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    public function salesRenewals(): HasMany
    {
        return $this->hasMany(SalesRenewal::class);
    }

    public function salesProgressives(): HasMany
    {
        return $this->hasMany(SalesProgressive::class);
    }

    public function workSchedules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkSchedule::class);
    }

    public function employeeContracts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeContract::class);
    }

    public function employeeDocuments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function getActiveContractAttribute(): ?EmployeeContract
    {
        return $this->employeeContracts()->where('status', 'active')->latest('signed_date')->first();
    }

    public function getEmploymentStatusLabelAttribute(): string
    {
        return self::EMPLOYMENT_STATUSES[$this->employment_status] ?? $this->employment_status ?? '—';
    }

    public function getWorkTypeLabelAttribute(): string
    {
        return self::WORK_TYPES[$this->work_type] ?? $this->work_type ?? '—';
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }

        $avatarDisk = config('filesystems.avatar_disk', 'public');

        if (Storage::disk($avatarDisk)->exists($this->avatar)) {
            return Storage::disk($avatarDisk)->url($this->avatar);
        }

        if ($avatarDisk !== 'public' && Storage::disk('public')->exists($this->avatar)) {
            return Storage::disk('public')->url($this->avatar);
        }

        return null;
    }
}
