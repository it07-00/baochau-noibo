<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesProgressive extends Model
{
    use HasFactory;

    protected $table = 'progressive_sales';

    protected $fillable = [
        'payment_schedule_id',
        'contract_number',
        'sales_month',
        'milestone_name',
        'percentage',
        'amount',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'sales_month' => 'date',
        'percentage' => 'decimal:2',
        'amount' => 'decimal:0',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
