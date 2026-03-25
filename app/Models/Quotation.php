<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'date',
        'staff_id',
        'company_name',
        'address',
        'industry',
        'contact_person',
        'work_description',
        'status',
        'original_value',   // Giá chưa VAT
        'value_inc_vat',    // Giá có VAT
        'commission_value', // Tiền hoa hồng
        'commission_tax',   // Tiền thuế
        'total_value',      // Tổng tiền (Giá có VAT - Hoa hồng)
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'original_value' => 'decimal:0',
        'value_inc_vat' => 'decimal:0',
        'commission_value' => 'decimal:0',
        'commission_tax' => 'decimal:0',
        'total_value' => 'decimal:0',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
