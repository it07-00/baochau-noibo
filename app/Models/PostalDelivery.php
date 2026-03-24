<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostalDelivery extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_phone',
        'customer_email',
        'address',
        'receiver_province',
        'receiver_district',
        'receiver_ward',
        'sender_name',
        'bill_viettel',
        'bill_247',
        'vtp_order_code',
        'vtp_service',
        'vtp_weight',
        'vtp_total_fee',
        'vtp_money_collection',
        'vtp_status',
        'vtp_status_name',
        'vtp_tracking_data',
        'vtp_last_tracked_at',
        'content',
        'department_id',
        'user_id',
        'status',
    ];

    protected $casts = [
        'vtp_tracking_data' => 'array',
        'vtp_last_tracked_at' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
