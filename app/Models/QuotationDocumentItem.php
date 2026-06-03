<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationDocumentItem extends Model
{
    protected $fillable = [
        'quotation_document_id',
        'item_type',
        'sort_order',
        'group_name',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'amount',
        'note',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'quantity' => 'decimal:2',
        'unit_price' => 'integer',
        'amount' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(QuotationDocument::class, 'quotation_document_id');
    }
}
