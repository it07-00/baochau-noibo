<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationDocument extends Model
{
    protected $fillable = [
        'quotation_id',
        'document_number',
        'date',
        'valid_until',
        'staff_id',
        'customer_name',
        'customer_address',
        'customer_phone',
        'customer_contact',
        'customer_email',
        'customer_tax_code',
        'service_type',
        'template_key',
        'work_location',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'total',
        'discount',
        'notes',
        'terms',
        'docx_path',
        'pdf_path',
    ];

    protected $casts = [
        'date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'integer',
        'vat_rate' => 'integer',
        'vat_amount' => 'integer',
        'total' => 'integer',
        'discount' => 'integer',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationDocumentItem::class)->orderBy('sort_order');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(QuotationDocumentSection::class)->orderBy('sort_order');
    }

    public function summaryItems(): HasMany
    {
        return $this->hasMany(QuotationDocumentItem::class)->where('item_type', 'summary')->orderBy('sort_order');
    }

    public function detailItems(): HasMany
    {
        return $this->hasMany(QuotationDocumentItem::class)->where('item_type', 'detail')->orderBy('sort_order');
    }

    /**
     * Tính lại tổng từ các dòng dịch vụ (Bảng 01 - Summary).
     */
    public function recalculate(): void
    {
        $subtotal = $this->summaryItems()->sum('amount');
        $discount = $this->discount ?? 0;
        $afterDiscount = $subtotal - $discount;
        $vatAmount = (int) round($afterDiscount * $this->vat_rate / 100);

        $this->update([
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total' => $afterDiscount + $vatAmount,
        ]);
    }
}
