<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    protected $fillable = [
        'date',
        'expected_signing_date',
        'quotation_number',
        'staff_id',
        'source',           // Nguồn
        'company_name',
        'address',          // Địa chỉ xuất hóa đơn
        'work_address',     // Địa chỉ làm việc
        'province',
        'industry',
        'service',          // Dịch vụ
        'contact_person',
        'work_description',
        'status',
        'original_value',   // Giá trị gốc (GIÁ TRỊ GÓC)
        'value_inc_vat',    // Giá trị chưa VAT
        'commission_value', // Hoa hồng KH
        'commission_tax',   // Thuế HH
        'total_value',      // Giá trị hợp đồng (có VAT)
        'notes',
        'pdf_path',
    ];

    protected $casts = [
        'date' => 'date',
        'expected_signing_date' => 'date',
        'original_value' => 'integer',
        'value_inc_vat' => 'integer',
        'commission_value' => 'integer',
        'commission_tax' => 'integer',
        'total_value' => 'integer',
    ];

    protected function expectedSigningDate(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: fn ($value) => $value ?: null,
        );
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(QuotationFile::class)->latest();
    }

    public function quotationDocuments(): HasMany
    {
        return $this->hasMany(QuotationDocument::class)->latest();
    }
}
