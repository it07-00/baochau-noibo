<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesRenewal extends Model
{
    use HasFactory;

    protected $table = 'renewal_sales';

    protected $fillable = [
        'contract_number',
        'sales_month',
        'sales_value',
        'commission',
        'sales_percentage',
        'sales_amount',
        'status',
        'notes',
        'file_path',
        'user_id',
    ];

    protected $casts = [
        'sales_month' => 'date',
        'sales_value' => 'decimal:2',
        'commission' => 'decimal:2',
        'sales_percentage' => 'decimal:2',
        'sales_amount' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
