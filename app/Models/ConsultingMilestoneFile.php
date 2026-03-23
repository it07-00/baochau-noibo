<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultingMilestoneFile extends Model
{
    use HasFactory;

    protected $table = 'consulting_milestones_files';

    protected $fillable = [
        'contract_id',
        'milestone',
        'file_path',
        'uploader_id',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ContractConsulting::class, 'contract_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
