<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id', 'contract_number', 'contract_type', 'signed_date',
    'start_date', 'end_date', 'salary', 'status', 'notes', 'file_path',
])]
class EmployeeContract extends Model
{
    use HasFactory;

    public const CONTRACT_TYPES = [
        'thu_viec'       => 'Hợp đồng thử việc',
        'co_thoi_han'    => 'HĐLĐ có thời hạn',
        'khong_thoi_han' => 'HĐLĐ không thời hạn',
        'thuc_tap'       => 'Hợp đồng thực tập',
        'cong_tac_vien'  => 'Hợp đồng cộng tác viên',
    ];

    public const STATUSES = [
        'active'     => 'Đang hiệu lực',
        'expired'    => 'Hết hạn',
        'terminated' => 'Đã chấm dứt',
    ];

    protected $casts = [
        'signed_date' => 'date',
        'start_date'  => 'date',
        'end_date'    => 'date',
        'salary'      => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getContractTypeLabelAttribute(): string
    {
        return self::CONTRACT_TYPES[$this->contract_type] ?? $this->contract_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
