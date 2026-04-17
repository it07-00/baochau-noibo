<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultingWorkflowStep extends Model
{
    use HasFactory;

    protected $table = 'consulting_workflow_steps';

    protected $fillable = [
        'contract_id',
        'user_id',
        'step_name',
        'action',
        'comment',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ContractLegal::class, 'contract_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
