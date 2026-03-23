<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationSales extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_number',
        'staff_id',
        'sales_month',
        'service',
        'info_source',
        'quotation_date',
        'follow_up_date',
        'value_ext_vat',
        'commission',
        'sales_percentage',
        'sales_amount',
        'company_name',
        'address',
        'province',
        'content',
        'customer_name',
        'customer_phone',
        'customer_email',
        'total_workers',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'sales_month' => 'date',
        'quotation_date' => 'date',
        'follow_up_date' => 'date',
        'value_ext_vat' => 'decimal:0',
        'commission' => 'decimal:0',
        'sales_percentage' => 'decimal:2',
        'sales_amount' => 'decimal:0',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
