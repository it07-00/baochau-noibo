<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationDocumentSection extends Model
{
    protected $fillable = [
        'quotation_document_id',
        'section_key',
        'section_type',
        'sort_order',
        'title',
        'columns',
        'settings',
        'totals',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'columns' => 'array',
        'settings' => 'array',
        'totals' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(QuotationDocument::class, 'quotation_document_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(QuotationDocumentSectionRow::class)
            ->orderBy('sort_order');
    }
}
