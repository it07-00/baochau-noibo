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
     * 6 bước workflow
     */
    const STEPS = [
        'receiving'        => 'Xác nhận tiếp nhận',
        'survey'           => 'Khảo sát / thu thập số liệu',
        'processing'       => 'Đang thực hiện',
        'waiting_client'   => 'Chờ KH duyệt',
        'client_confirmed' => 'KH xác nhận',
        'finished'         => 'Đã hoàn thành',
    ];

    const STEP_KEYS = [
        'receiving', 'survey', 'processing', 'waiting_client', 'client_confirmed', 'finished',
    ];

    public function contract(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
