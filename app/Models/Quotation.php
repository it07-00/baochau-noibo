<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'date',
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
    ];

    protected $casts = [
        'date' => 'date',
        'original_value' => 'decimal:2',
        'value_inc_vat' => 'decimal:2',
        'commission_value' => 'decimal:2',
        'commission_tax' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
