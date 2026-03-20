<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractWaste extends Model
{
    protected $guarded = [];

    protected $casts = [
        'signed_at' => 'date',
        'effective_at' => 'date',
        'end_at' => 'date',
        'submitted_at' => 'date',
        'is_offset' => 'boolean',
        'is_overdue' => 'boolean',
        'value' => 'decimal:0',
        'commission' => 'decimal:0',
        'revenue' => 'decimal:0',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function handler()
    {
        return $this->belongsTo(Handler::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
