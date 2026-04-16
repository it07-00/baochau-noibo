<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContractWorkflowStep extends Model
{
    protected $fillable = [
        'contract_type',
        'contract_id',
        'user_id',
        'step_name',
        'action',
        'comment',
    ];

    /**
     * 6 bước workflow cho tư vấn (mặc định)
     */
    const STEPS = [
        'receiving' => 'Xác nhận tiếp nhận',
        'survey' => 'Khảo sát / thu thập số liệu',
        'processing' => 'Đang thực hiện',
        'waiting_client' => 'Chờ KH duyệt',
        'client_confirmed' => 'KH xác nhận',
        'finished' => 'Đã hoàn thành',
    ];

    const STEP_KEYS = [
        'receiving', 'survey', 'processing', 'waiting_client', 'client_confirmed', 'finished',
    ];

    /**
     * 6 bước workflow cho kỹ thuật
     */
    const STEPS_TECHNICAL = [
        'receiving' => 'Xác nhận tiếp nhận',
        'survey' => 'Khảo sát / lên chỉ tiêu',
        'processing' => 'Thu thập số liệu / viết báo cáo',
        'waiting_client' => 'Chờ KH duyệt',
        'client_confirmed' => 'KH xác nhận',
        'finished' => 'Đã hoàn thành',
    ];

    const STEP_KEYS_TECHNICAL = [
        'receiving', 'survey', 'processing', 'waiting_client', 'client_confirmed', 'finished',
    ];

    /**
     * Lấy danh sách bước workflow và keys dựa vào role
     *
     * @param  string|null  $role  Role của user (ky-thuat, tu-van, etc.)
     * @return array ['steps' => [...], 'stepKeys' => [...]]
     */
    public static function getStepsByRole(?string $role = null): array
    {
        if ($role === 'ky-thuat') {
            return [
                'steps' => self::STEPS_TECHNICAL,
                'stepKeys' => self::STEP_KEYS_TECHNICAL,
            ];
        }

        return [
            'steps' => self::STEPS,
            'stepKeys' => self::STEP_KEYS,
        ];
    }

    public function contract(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
