<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationDocumentSectionRow extends Model
{
    protected $fillable = [
        'quotation_document_section_id',
        'sort_order',
        'row_type',
        'group_name',
        'label',
        'description',
        'unit',
        'quantity',
        'frequency',
        'unit_price',
        'amount',
        'columns',
        'note',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'quantity' => 'decimal:2',
        'frequency' => 'integer',
        'unit_price' => 'integer',
        'amount' => 'integer',
        'columns' => 'array',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(QuotationDocumentSection::class, 'quotation_document_section_id');
    }
}
